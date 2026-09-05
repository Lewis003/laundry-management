<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    // Mass-assignable columns
    protected $fillable = [
        'job_id',
        'amount_in_cents',
        'payment_method',
        'transaction_reference',
        'notes',
    ];

    // Ensure amount is strictly an integer in cents
    protected $casts = [
        'amount_in_cents' => 'integer',
    ];

    /**
     * Relationship: Each Payment transaction belongs to 1 specific Job order (N:1)
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }


    public function getFormattedAmountAttribute(): string
    {
        return 'KSh ' . number_format($this->amount_in_cents / 100, 2);
    }
}
