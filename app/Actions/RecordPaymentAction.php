<?php

namespace App\Actions;

use App\Models\Job;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Single Responsibility: Record an immutable payment transaction against an order.
 */
class RecordPaymentAction
{
    /**
     * Log a partial deposit or final settlement into the payments ledger.
     */
    public function execute(
        Job $job,
        int $amountInCents,
        string $paymentMethod = 'cash',
        ?string $transactionReference = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($job, $amountInCents, $paymentMethod, $transactionReference, $notes) {

            // Insert a permanent new transaction record into the payments ledger
            $payment = Payment::create([
                'job_id' => $job->id,
                'amount_in_cents' => $amountInCents,
                'payment_method' => $paymentMethod,
                'transaction_reference' => $transactionReference,
                'notes' => $notes,
            ]);

            return $payment;
        });
    }
}
