<?php

namespace App\Actions;

use App\Enums\JobStatus;
use App\Models\Customer;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateJobAction
{
    /**
     * Execute job creation with intake items, optional deposit, and VAT settings.
     * All parameters after $customer and $items have safe default values.
     */
    public function execute(
        Customer|int $customer,
        array $items,
        ?string $notes = null,
        int $paidAmountCents = 0,
        string $paymentMethod = 'mpesa',
        ?int $creatorId = null,
        bool $applyVat = true,
        ...$extra
    ): Job {
        $customerId = $customer instanceof Customer ? $customer->id : (int) $customer;
        if ($paidAmountCents === 0 && isset($extra['paidAmount'])) {
            $paidAmountCents = (int) $extra['paidAmount'];
        }

        return DB::transaction(function () use (
            $customerId,
            $items,
            $notes,
            $paidAmountCents,
            $paymentMethod,
            $creatorId,
            $applyVat
        ) {
            $jobNumber = 'AUR-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $jobData = [
                'job_number'  => $jobNumber,
                'customer_id' => $customerId,
                'status'      => JobStatus::RECEIVED ?? 'received',
                'notes'       => $notes,
            ];

            if ($creatorId) {
                if (Schema::hasColumn('jobs', 'user_id')) {
                    $jobData['user_id'] = $creatorId;
                } elseif (Schema::hasColumn('jobs', 'created_by')) {
                    $jobData['created_by'] = $creatorId;
                }
            }

            if (Schema::hasColumn('jobs', 'apply_vat')) {
                $jobData['apply_vat'] = $applyVat;
            } elseif (!$applyVat && !empty($notes)) {
                $jobData['notes'] = trim($notes . ' [VAT_EXEMPT]');
            }

            /** @var Job $job */
            $job = Job::create($jobData);

            foreach ($items as $itemData) {
                $service = Service::findOrFail($itemData['service_id']);
                $qty = max(1, (int) ($itemData['quantity'] ?? 1));

                $cents = $service->price_in_cents
                    ?? $service->price_cents
                    ?? (int) round(((float) ($service->price ?? 0)) * 100);

                $itemPayload = [
                    'job_id'     => $job->id,
                    'service_id' => $service->id,
                    'quantity'   => $qty,
                ];

                if (Schema::hasColumn('job_items', 'price_in_cents')) {
                    $itemPayload['price_in_cents'] = $cents;
                }
                if (Schema::hasColumn('job_items', 'unit_price_cents')) {
                    $itemPayload['unit_price_cents'] = $cents;
                }
                if (Schema::hasColumn('job_items', 'unit_price_in_cents')) {
                    $itemPayload['unit_price_in_cents'] = $cents;
                }
                if (Schema::hasColumn('job_items', 'unit_price')) {
                    $itemPayload['unit_price'] = $cents / 100;
                }
                if (Schema::hasColumn('job_items', 'price')) {
                    $itemPayload['price'] = $cents / 100;
                }

                JobItem::create($itemPayload);
            }

            if ($paidAmountCents > 0) {
                $paymentPayload = [
                    'job_id'         => $job->id,
                    'payment_method' => $paymentMethod ?: 'mpesa',
                ];

                $ref = 'DEP-' . strtoupper(Str::random(6));
                if (Schema::hasColumn('payments', 'payment_reference')) {
                    $paymentPayload['payment_reference'] = $ref;
                } elseif (Schema::hasColumn('payments', 'reference')) {
                    $paymentPayload['reference'] = $ref;
                } elseif (Schema::hasColumn('payments', 'transaction_reference')) {
                    $paymentPayload['transaction_reference'] = $ref;
                }

                if (Schema::hasColumn('payments', 'amount_in_cents')) {
                    $paymentPayload['amount_in_cents'] = $paidAmountCents;
                }
                if (Schema::hasColumn('payments', 'amount_cents')) {
                    $paymentPayload['amount_cents'] = $paidAmountCents;
                }
                if (Schema::hasColumn('payments', 'amount')) {
                    $paymentPayload['amount'] = $paidAmountCents / 100;
                }

                if ($creatorId) {
                    if (Schema::hasColumn('payments', 'received_by')) {
                        $paymentPayload['received_by'] = $creatorId;
                    } elseif (Schema::hasColumn('payments', 'user_id')) {
                        $paymentPayload['user_id'] = $creatorId;
                    }
                }

                Payment::create($paymentPayload);
            }

            return $job->fresh(['customer', 'items.service', 'payments']);
        });
    }
}
