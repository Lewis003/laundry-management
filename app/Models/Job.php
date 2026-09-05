<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;

    // Mass-assignable columns
    protected $fillable = [
        'job_number',
        'customer_id',
        'machine_id',
        'status',
        'assigned_worker_id',
        'rack_location',
        'notes',
    ];


    protected $casts = [
        'status' => JobStatus::class,
    ];

    /**
     * Relationship: Each Job belongs to 1 Customer (N:1)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship: Each Job is assigned to 1 Machine while washing (N:1)
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Relationship: Each Job is handled by 1 Staff Member / Washer (N:1)
     */
    public function assignedWorker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_worker_id');
    }

    /**
     * Relationship: 1 Job has MANY garment line items (1:N)
     */
    public function items(): HasMany
    {
        return $this->hasMany(JobItem::class);
    }

    /**
     * Relationship: 1 Job has MANY payment ledger records (1:N)
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }


    /**
     * 1. Dynamic Total: Sum of all garment line item subtotals
     */
    public function totalCents(): int
    {
        return (int) $this->items->sum(function ($item) {
            return $item->subtotalCents();
        });
    }

    /**
     *  Sum of all transactions in the payments ledger
     */
    public function paidCents(): int
    {
        return (int) $this->payments->sum('amount_in_cents');
    }

    /**
     * 3. Dynamic Balance Due: Total minus Paid (Never drops below zero)
     */
    public function balanceCents(): int
    {
        return max(0, $this->totalCents() - $this->paidCents());
    }

    /**
     * Check if the order is 100% paid in full
     */
    public function isFullyPaid(): bool
    {
        return $this->totalCents() > 0 && $this->balanceCents() === 0;
    }



    public function getFormattedTotalAttribute(): string
    {
        return 'KSh ' . number_format($this->totalCents() / 100, 2);
    }

    public function getFormattedPaidAttribute(): string
    {
        return 'KSh ' . number_format($this->paidCents() / 100, 2);
    }


    public function getFormattedBalanceAttribute(): string
    {
        return 'KSh ' . number_format($this->balanceCents() / 100, 2);
    }
}
