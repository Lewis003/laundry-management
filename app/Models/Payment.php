<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Resolves payment reference string safely.
     */
    public function getReferenceAttribute(): string
    {
        return $this->attributes['payment_reference']
            ?? $this->attributes['reference']
            ?? $this->attributes['transaction_reference']
            ?? ('PAY-' . str_pad((string) ($this->id ?? 1), 5, '0', STR_PAD_LEFT));
    }

    /**
     * Resolves payment amount in Kenyan Shillings.
     */
    public function getAmountAttribute(): float
    {
        if (isset($this->attributes['amount_in_cents'])) {
            return round((float) $this->attributes['amount_in_cents'] / 100, 2);
        }
        if (isset($this->attributes['amount_cents'])) {
            return round((float) $this->attributes['amount_cents'] / 100, 2);
        }

        $val = (float) ($this->attributes['amount'] ?? 0);

        if ($val >= 10000) {
            return round($val / 100, 2);
        }

        return round($val, 2);
    }
}
