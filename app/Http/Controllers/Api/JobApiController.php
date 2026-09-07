<?php

namespace App\Http\Controllers\Api;

use App\Actions\AssignMachineAction;
use App\Actions\CreateJobAction;
use App\Actions\RecordPaymentAction;
use App\Actions\TransitionJobStatusAction;
use App\Enums\JobStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\MachineOccupiedException;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class JobApiController extends Controller
{
    /**
     * GET /api/v1/jobs
     * List all laundry jobs with itemized services, balances, and machine status.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $jobs = Job::with(['customer', 'machine', 'items.service', 'payments'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'total_pages' => $jobs->lastPage(),
                'total_records' => $jobs->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/jobs
     * Create a new laundry order ticket via API.
     */
    public function store(Request $request, CreateJobAction $createJobAction): JsonResponse
    {
        $validated = $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_phone'     => 'required|string|max:20',
            'items'              => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'machine_id'         => 'nullable|exists:machines,id',
            'assigned_worker_id' => 'nullable|exists:users,id',
            'notes'              => 'nullable|string|max:1000',
            'paid_amount'        => 'nullable|numeric|min:0',
        ]);

        try {
            // 1. Resolve or create the customer record
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['customer_phone']],
                ['name'  => $validated['customer_name']]
            );

            $depositCents = isset($validated['paid_amount']) ? (int) round(((float) $validated['paid_amount']) * 100) : 0;

            // 2. Execute job creation
            $job = $createJobAction->execute(
                customer: $customer,
                items: $validated['items'],
                notes: $validated['notes'] ?? null,
                paidAmountCents: $depositCents,
                paymentMethod: 'mpesa',
                creatorId: $validated['assigned_worker_id'] ?? null,
                applyVat: false
            );

            // 3. If machine_id provided, assign it concurrency-safe
            if (!empty($validated['machine_id'])) {
                $assignMachineAction = app(AssignMachineAction::class);
                $assignMachineAction->execute(
                    job: $job,
                    machine: (int) $validated['machine_id'],
                    operatorId: $validated['assigned_worker_id'] ?? null
                );
            }

            $job->load(['customer', 'items.service', 'payments', 'machine']);

            return response()->json([
                'success' => true,
                'message' => 'Laundry order created successfully.',
                'data' => [
                    'job_number'  => $job->job_number,
                    'customer'    => $job->customer?->name,
                    'status'      => is_object($job->status) ? $job->status->value : $job->status,
                    'total'       => $job->formatted_total ?? ('KSh ' . number_format($job->total_amount_cents / 100, 2)),
                    'paid'        => $job->formatted_paid ?? ('KSh ' . number_format($job->amount_paid_cents / 100, 2)),
                    'balance_due' => $job->formatted_balance ?? ('KSh ' . number_format($job->balance_due_cents / 100, 2)),
                ],
            ], 201);
        } catch (MachineOccupiedException | RuntimeException $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/v1/jobs/{job_number}
     * Look up a specific order's status and balance (Used for Customer Tracking API).
     */
    public function show(string $jobNumber): JsonResponse
    {
        $job = Job::where('job_number', $jobNumber)
            ->with(['customer', 'machine', 'items.service', 'payments'])
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'error'   => 'Order not found.',
            ], 404);
        }

        $statusVal = is_object($job->status) ? $job->status->value : $job->status;
        $statusLabel = method_exists($job->status, 'label') ? $job->status->label() : ucfirst((string)$statusVal);

        return response()->json([
            'success' => true,
            'data' => [
                'job_number'     => $job->job_number,
                'customer_name'  => $job->customer?->name,
                'customer_phone' => $job->customer?->phone,
                'status'         => $statusVal,
                'status_label'   => $statusLabel,
                'rack_location'  => $job->rack_location ?? null,
                'machine'        => $job->machine?->name,
                'items'          => $job->items->map(fn($item) => [
                    'service'  => $item->service?->name,
                    'quantity' => $item->quantity,
                    'price'    => 'KSh ' . number_format(($item->price_in_cents ?? 0) / 100, 2),
                    'subtotal' => 'KSh ' . number_format((($item->price_in_cents ?? 0) * $item->quantity) / 100, 2),
                ]),
                'financials' => [
                    'total'        => $job->formatted_total ?? ('KSh ' . number_format($job->total_amount_cents / 100, 2)),
                    'paid'         => $job->formatted_paid ?? ('KSh ' . number_format($job->amount_paid_cents / 100, 2)),
                    'balance_due'  => $job->formatted_balance ?? ('KSh ' . number_format($job->balance_due_cents / 100, 2)),
                    'is_fully_paid'=> method_exists($job, 'isFullyPaid') ? $job->isFullyPaid() : ($job->balance_due <= 0),
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/jobs/{id}/assign-machine
     * Concurrency-safe machine assignment endpoint.
     */
    public function assignMachine(Request $request, Job $job, AssignMachineAction $assignMachineAction): JsonResponse
    {
        $validated = $request->validate([
            'machine_id'  => 'required|exists:machines,id',
            'operator_id' => 'nullable|exists:users,id',
        ]);

        try {
            $updatedJob = $assignMachineAction->execute(
                job: $job,
                machine: (int) $validated['machine_id'],
                operatorId: $validated['operator_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => "Machine [{$updatedJob->machine->name}] assigned successfully.",
                'status'  => is_object($updatedJob->status) ? $updatedJob->status->value : $updatedJob->status,
            ]);
        } catch (MachineOccupiedException | RuntimeException $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/jobs/{id}/transition
     * Advance workflow status via API.
     */
    public function transition(Request $request, Job $job, TransitionJobStatusAction $transitionAction): JsonResponse
    {
        $validated = $request->validate([
            'status'        => 'required|string',
            'rack_location' => 'nullable|string|max:255',
            'user_id'       => 'nullable|exists:users,id',
        ]);

        $targetStatus = JobStatus::tryFrom($validated['status']) ?? $validated['status'];

        try {
            $updatedJob = $transitionAction->execute(
                job: $job,
                targetStatus: $targetStatus,
                userId: $validated['user_id'] ?? null,
                rackLocation: $validated['rack_location'] ?? null
            );

            $statusVal = is_object($updatedJob->status) ? $updatedJob->status->value : $updatedJob->status;

            return response()->json([
                'success' => true,
                'message' => "Order transitioned to {$statusVal}.",
                'status'  => $statusVal,
            ]);
        } catch (InvalidStatusTransitionException | RuntimeException $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/jobs/{id}/payments
     * Record an M-Pesa payment transaction via API.
     */
    public function recordPayment(Request $request, Job $job, RecordPaymentAction $recordPaymentAction): JsonResponse
    {
        $validated = $request->validate([
            'amount'                => 'required|numeric|min:1',
            'payment_method'        => 'nullable|string',
            'transaction_reference' => 'nullable|string|max:255',
            'notes'                 => 'nullable|string|max:500',
        ]);

        $amountInCents = (int) round(((float)$validated['amount']) * 100);

        $payment = $recordPaymentAction->execute(
            job: $job,
            amountInCents: $amountInCents,
            paymentMethod: $validated['payment_method'] ?? 'mpesa',
            transactionReference: $validated['transaction_reference'] ?? null,
            notes: $validated['notes'] ?? null
        );

        $job->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'payment' => [
                'amount'    => 'KSh ' . number_format($amountInCents / 100, 2),
                'method'    => $payment->payment_method,
                'reference' => $payment->transaction_reference ?? $payment->payment_reference ?? null,
            ],
            'financials' => [
                'total'        => $job->formatted_total ?? ('KSh ' . number_format($job->total_amount_cents / 100, 2)),
                'paid'         => $job->formatted_paid ?? ('KSh ' . number_format($job->amount_paid_cents / 100, 2)),
                'balance_due'  => $job->formatted_balance ?? ('KSh ' . number_format($job->balance_due_cents / 100, 2)),
                'is_fully_paid'=> method_exists($job, 'isFullyPaid') ? $job->isFullyPaid() : ($job->balance_due <= 0),
            ],
        ]);
    }
}
