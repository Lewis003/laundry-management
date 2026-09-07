<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(JobItem::class);
    }

    /**
     * Dynamically resolve price in Shillings.
     */
    public function getPriceAttribute(): float
    {
        if (isset($this->attributes['price_in_cents'])) {
            return round((float) $this->attributes['price_in_cents'] / 100, 2);
        }

        if (isset($this->attributes['price_cents'])) {
            return round((float) $this->attributes['price_cents'] / 100, 2);
        }

        if (isset($this->attributes['price'])) {
            return (float) $this->attributes['price'];
        }

        return 0.0;
    }
}
