<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Resolves the Unit Price in Kenyan Shillings.
     * Handles both whole shillings (150) and integer cents (15000).
     */
    public function getUnitPriceAttribute(): float
    {
        if (isset($this->attributes['price_in_cents'])) {
            return round((float) $this->attributes['price_in_cents'] / 100, 2);
        }
        if (isset($this->attributes['unit_price_cents'])) {
            return round((float) $this->attributes['unit_price_cents'] / 100, 2);
        }
        if (isset($this->attributes['unit_price_in_cents'])) {
            return round((float) $this->attributes['unit_price_in_cents'] / 100, 2);
        }

        $val = (float) ($this->attributes['unit_price']
            ?? $this->attributes['price']
            ?? 0);

        if ($val >= 10000) {
            return round($val / 100, 2);
        }

        return round($val, 2);
    }

    /**
     * Line subtotal: Unit Price * Quantity (in Shillings).
     */
    public function getSubtotalAttribute(): float
    {
        return round($this->unit_price * (int) ($this->quantity ?? 1), 2);
    }

    public function subtotalCents(): int
    {
        return (int) round($this->subtotal * 100);
    }

    public function unitPriceCents(): int
    {
        return (int) round($this->unit_price * 100);
    }
}
