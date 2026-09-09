<?php

namespace App\Http\Controllers;

use App\Actions\AssignMachineAction;
use App\Actions\CreateJobAction;
use App\Actions\TransitionJobStatusAction;
use App\Enums\JobStatus;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Payment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobController extends Controller
{
    /**
     * Orders queue listing with multi-field search, status pills, and date filters.
     */
    public function index(Request $request)
    {
        $query = Job::with(['customer', 'machine', 'items.service', 'payments'])
            ->latest();

        // 1. Filter by Workflow Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 2. Filter by Date Range
        if ($request->filled('date_range')) {
            if ($request->date_range === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($request->date_range === 'this_week') {
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($request->date_range === 'this_month') {
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
            }
        }

        // 3. Search by Ticket #, Customer Name, or M-Pesa Phone
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $jobs = $query->paginate(15)->withQueryString();
        $machines = Machine::all();
        $customers = Customer::orderBy('name')->get();

        return view('jobs.index', compact('jobs', 'machines', 'customers'));
    }

    /**
     * Export filtered orders directly to CSV format.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Job::with(['customer', 'machine', 'items.service', 'payments'])->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_range')) {
            if ($request->date_range === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($request->date_range === 'this_week') {
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($request->date_range === 'this_month') {
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $jobs = $query->get();
        $filename = 'safishwa_orders_' . date('Y-m-d_His') . '.csv';

        return response()->stream(function () use ($jobs) {
            $handle = fopen('php://output', 'w');

            // CSV Header Row
            fputcsv($handle, [
                'Ticket #',
                'Intake Date',
                'Customer Name',
                'Customer Phone',
                'Workflow Status',
                'Assigned Equipment',
                'Total Amount (KES)',
                'Net Taxable Base (KES)',
                '16% VAT (KES)',
                'Paid Amount (KES)',
                'Balance Due (KES)',
                'Settlement Status'
            ]);

            // Data Rows
            foreach ($jobs as $job) {
                $rawStatus = is_object($job->status) ? ($job->status->value ?? $job->status->name ?? 'received') : $job->status;

                fputcsv($handle, [
                    $job->job_number,
                    $job->created_at->format('Y-m-d H:i:s'),
                    $job->customer?->name ?? 'Walk-in',
                    $job->customer?->phone ?? '—',
                    strtoupper($rawStatus),
                    $job->machine?->name ?? 'Unassigned',
                    number_format($job->total_price, 2, '.', ''),
                    number_format($job->subtotal, 2, '.', ''),
                    number_format($job->tax, 2, '.', ''),
                    number_format($job->paid_amount, 2, '.', ''),
                    number_format($job->balance_due, 2, '.', ''),
                    $job->isFullyPaid() ? 'CLEARED' : 'PENDING'
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Handle bulk actions (Bulk Mark Ready, Bulk Export).
     */
    public function bulkAction(Request $request, TransitionJobStatusAction $transitionAction)
    {
        $validated = $request->validate([
            'action'  => ['required', 'string', 'in:mark_ready,export_selected'],
            'job_ids' => ['required', 'array', 'min:1'],
            'job_ids.*' => ['exists:jobs,id'],
        ]);

        $jobIds = $validated['job_ids'];

        if ($validated['action'] === 'mark_ready') {
            $count = 0;
            foreach ($jobIds as $id) {
                $job = Job::find($id);
                $statusVal = is_object($job->status) ? ($job->status->value ?? $job->status) : $job->status;
                if ($statusVal === 'in_progress') {
                    $transitionAction->execute($job, JobStatus::READY, Auth::id());
                    $count++;
                }
            }

            return back()->with('success', "Bulk Action Complete: {$count} order(s) marked as Ready for pickup and equipment vacated.");
        }

        if ($validated['action'] === 'export_selected') {
            $jobs = Job::with(['customer', 'machine'])->whereIn('id', $jobIds)->get();
            $filename = 'selected_orders_' . date('Y-m-d_His') . '.csv';

            return response()->stream(function () use ($jobs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Ticket #', 'Customer', 'Phone', 'Status', 'Total', 'Paid', 'Due']);
                foreach ($jobs as $job) {
                    fputcsv($handle, [
                        $job->job_number,
                        $job->customer?->name ?? 'Walk-in',
                        $job->customer?->phone ?? '',
                        is_object($job->status) ? $job->status->value : $job->status,
                        $job->total_price,
                        $job->paid_amount,
                        $job->balance_due
                    ]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return back();
    }

    /**
     * Show order intake terminal.
     */
    public function create()
    {
        $user = Auth::user();
        if ($user && ($user->role === 'operator' || (method_exists($user, 'isOperator') && $user->isOperator()))) {
            abort(403, 'Wash bay operators are not authorized to create intake orders.');
        }

        $customers = Customer::orderBy('name')->get();
        $servicesQuery = Service::query();
        if (Schema::hasTable('services') && Schema::hasColumn('services', 'is_active')) {
            $servicesQuery->where('is_active', true);
        }
        $services = $servicesQuery->orderBy('name')->get();

        return view('jobs.create', compact('customers', 'services'));
    }

    /**
     * Store intake order.
     */
    public function store(Request $request, CreateJobAction $createJobAction)
    {
        $user = Auth::user();
        if ($user && ($user->role === 'operator' || (method_exists($user, 'isOperator') && $user->isOperator()))) {
            abort(403, 'Wash bay operators are not authorized to create intake orders.');
        }

        $validated = $request->validate([
            'customer_id'        => ['nullable', 'exists:customers,id'],
            'name'               => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'phone'              => ['required_without:customer_id', 'nullable', 'string', 'max:20'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', 'exists:services,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'notes'              => ['nullable', 'string'],
            'paid_amount'        => ['nullable', 'numeric', 'min:0'],
            'apply_vat'          => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['customer_id'])) {
            $customer = Customer::findOrFail($validated['customer_id']);
        } else {
            $customer = Customer::create([
                'name'  => $validated['name'],
                'phone' => $validated['phone'],
            ]);
        }

        $depositCents = isset($validated['paid_amount']) ? (int) round(((float) $validated['paid_amount']) * 100) : 0;
        $applyVat = $request->has('apply_vat') ? $request->boolean('apply_vat') : true;

        $job = $createJobAction->execute(
            $customer,
            $validated['items'],
            $validated['notes'] ?? null,
            $depositCents,
            'mpesa',
            Auth::id(),
            $applyVat
        );

        return redirect()->route('jobs.show', $job)->with('success', "Order #{$job->job_number} created successfully.");
    }

    /**
     * Show order details.
     */
    public function show(Job $job)
    {
        $job->load(['customer', 'items.service', 'payments', 'machine']);

        $machinesQuery = Machine::query();
        if (Schema::hasTable('machines') && Schema::hasColumn('machines', 'is_active')) {
            $machinesQuery->where('is_active', true);
        }
        $machines = $machinesQuery->get();

        return view('jobs.show', compact('job', 'machines'));
    }

    /**
     * Assign equipment in wash bay.
     */
    public function assignMachine(Request $request, Job $job, AssignMachineAction $assignMachineAction)
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
        ]);

        try {
            $assignMachineAction->execute($job, (int) $validated['machine_id'], Auth::id());
        } catch (RuntimeException $e) {
            return back()->withErrors(['machine_id' => $e->getMessage()]);
        }

        return back()->with('success', "Machine assigned successfully. Wash cycle started.");
    }

    /**
     * Mark order ready for pickup and record shelved rack location.
     */
    public function markReady(Request $request, Job $job, TransitionJobStatusAction $transitionAction)
    {
        $validated = $request->validate([
            'rack_location' => ['nullable', 'string', 'max:100'],
        ]);

        $rackLocation = $validated['rack_location'] ?? $request->input('rack_location') ?? $job->rack_location;

        $transitionAction->execute($job, JobStatus::READY, Auth::id(), $rackLocation);

        $msg = "Order marked as Ready for pickup. Equipment released";
        if ($rackLocation) {
            $msg .= " and shelved at {$rackLocation}.";
        } else {
            $msg .= ".";
        }

        return back()->with('success', $msg);
    }

    /**
     * Mark order collected by customer.
     */
    public function markCollected(Job $job, TransitionJobStatusAction $transitionAction)
    {
        if ($job->balance_due > 0) {
            return back()->withErrors(['error' => 'Cannot mark as collected while there is an outstanding balance due.']);
        }

        $transitionAction->execute($job, JobStatus::COLLECTED, Auth::id());

        return back()->with('success', "Order marked as Collected. Job completed.");
    }

    /**
     * M-Pesa Payment Intake.
     */
    public function collectPayment(Request $request, Job $job)
    {
        $user = Auth::user();
        if ($user && ($user->role === 'operator' || (method_exists($user, 'isOperator') && $user->isOperator()))) {
            abort(403, 'Wash bay operators are not authorized to collect payments.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amountCents = (int) round(((float) $validated['amount']) * 100);

        $paymentPayload = [
            'job_id'         => $job->id,
            'payment_method' => 'mpesa',
        ];

        $ref = 'PAY-' . strtoupper(Str::random(6));
        if (Schema::hasColumn('payments', 'payment_reference')) {
            $paymentPayload['payment_reference'] = $ref;
        } elseif (Schema::hasColumn('payments', 'reference')) {
            $paymentPayload['reference'] = $ref;
        } elseif (Schema::hasColumn('payments', 'transaction_reference')) {
            $paymentPayload['transaction_reference'] = $ref;
        }

        if (Schema::hasColumn('payments', 'amount_in_cents')) {
            $paymentPayload['amount_in_cents'] = $amountCents;
        }
        if (Schema::hasColumn('payments', 'amount_cents')) {
            $paymentPayload['amount_cents'] = $amountCents;
        }
        if (Schema::hasColumn('payments', 'amount')) {
            $paymentPayload['amount'] = $amountCents / 100;
        }

        if ($user) {
            if (Schema::hasColumn('payments', 'received_by')) {
                $paymentPayload['received_by'] = $user->id;
            } elseif (Schema::hasColumn('payments', 'user_id')) {
                $paymentPayload['user_id'] = $user->id;
            }
        }

        Payment::create($paymentPayload);

        return back()->with('success', 'M-Pesa payment recorded successfully.');
    }

    /**
     * 80mm Multi-timestamp thermal receipt.
     */
    public function receipt(Job $job)
    {
        $job->load(['customer', 'items.service', 'payments', 'machine']);

        return view('jobs.receipt', compact('job'));
    }

    /**
     * Delete an order (Admin Only Guard).
     */
    public function destroy(Job $job)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only administrators can delete orders.');
        }

        DB::transaction(function () use ($job) {
            // If machine was locked, free it
            if ($job->machine) {
                $job->machine->update(['is_available' => true, 'status' => 'available']);
            }

            // Delete associated items and payments
            $job->items()->delete();
            $job->payments()->delete();
            $job->delete();
        });

        return redirect()->route('jobs.index')->with('success', "Order #{$job->job_number} deleted successfully.");
    }
}

