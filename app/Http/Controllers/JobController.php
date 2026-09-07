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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class JobController extends Controller
{
    /**
     * Orders queue listing.
     */
    public function index(Request $request)
    {
        $query = Job::with(['customer', 'machine', 'items.service', 'payments'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
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
     * Show order intake terminal (Operators strictly forbidden).
     */
    public function create()
    {
        $user = Auth::user();

        // Operators are stationed in the wash bay and cannot create orders
        if ($user && ($user->role === 'operator' || (method_exists($user, 'isOperator') && $user->isOperator()))) {
            abort(403, 'Wash bay operators are not authorized to create intake orders. Intake is handled exclusively by Front Desk Cashiers.');
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
     * Store intake order (Operators strictly forbidden).
     */
    public function store(Request $request, CreateJobAction $createJobAction)
    {
        $user = Auth::user();

        // Operators are stationed in the wash bay and cannot create orders
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

        // Strictly M-Pesa
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
     * Mark order ready for pickup.
     */
    public function markReady(Job $job, TransitionJobStatusAction $transitionAction)
    {
        $transitionAction->execute($job, JobStatus::READY, Auth::id());

        return back()->with('success', "Order marked as Ready for pickup. Equipment released.");
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
     * M-Pesa Payment Intake (Operators forbidden).
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
            'payment_method' => 'mpesa', // Strictly M-Pesa
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
}
