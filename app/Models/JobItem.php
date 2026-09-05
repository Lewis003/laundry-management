<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobItem extends Model
{
    use HasFactory;

    // Mass-assignable columns
    protected $fillable = [
        'job_id',
        'service_id',
        'quantity',
        'price_in_cents', // Historical price snapshot in integer cents
    ];

    // Ensure numbers are treated as integers
    protected $casts = [
        'quantity' => 'integer',
        'price_in_cents' => 'integer',
    ];

    /**
     * Relationship: Each line item belongs to 1 specific Job order (N:1)
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Relationship: Each line item links to 1 Service catalog entry (N:1)
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Helper Method: Calculate Subtotal in Cents (Quantity x Snapshot Unit Price)
     * Example: 3 Shirts x 10000 cents = 30000 cents (KSh 300.00)
     */
    public function subtotalCents(): int
    {
        return (int) ($this->quantity * $this->price_in_cents);
    }

    /**
     * Accessor: Format unit snapshot price (e.g. 10000 -> "KSh 100.00")
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'KSh ' . number_format($this->price_in_cents / 100, 2);
    }

    /**
     * Accessor: Format line item subtotal (e.g. 30000 -> "KSh 300.00")
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'KSh ' . number_format($this->subtotalCents() / 100, 2);
    }
}
