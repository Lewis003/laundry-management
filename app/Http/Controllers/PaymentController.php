<?php

namespace App\Http\Controllers;

use App\Actions\RecordPaymentAction;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Record a new deposit or balance payment against an order.
     */
    public function store(Request $request, Job $job, RecordPaymentAction $recordPaymentAction): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1', // Amount in KSh entered by cashier (e.g. 500.00)
            'payment_method' => 'required|in:mpesa,cash,card',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Convert user-entered KSh to integer cents (e.g. 500.00 -> 50000 cents)
        $amountInCents = (int) round($validated['amount'] * 100);

        // Execute payment record inside our immutable ledger action
        $recordPaymentAction->execute(
            job: $job,
            amountInCents: $amountInCents,
            paymentMethod: $validated['payment_method'],
            transactionReference: $validated['transaction_reference'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return back()->with('success', 'Payment of KSh ' . number_format($validated['amount'], 2) . ' recorded successfully!');
    }

    /**
     * Render the printable 80mm POS Thermal Receipt view.
     */
    public function receipt(Job $job): View
    {
        $job->load(['customer', 'items.service', 'payments', 'assignedWorker']);

        return view('jobs.receipt', compact('job'));
    }
}
