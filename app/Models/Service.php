<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_cents',
        'price_in_cents',
        'duration_minutes',
        'category',
        'is_active',
        'description',
    ];

    protected $casts = [
        'price_cents'      => 'integer',
        'price_in_cents'   => 'integer',
        'duration_minutes' => 'integer',
        'is_active'        => 'boolean',
    ];

    public function setPriceCentsAttribute($value): void
    {
        $cents = (int) $value;
        $this->attributes['price_cents'] = $cents;
        $this->attributes['price_in_cents'] = $cents;
    }

    public function setPriceInCentsAttribute($value): void
    {
        $cents = (int) $value;
        $this->attributes['price_cents'] = $cents;
        $this->attributes['price_in_cents'] = $cents;
    }

    public function getPriceCentsAttribute(): int
    {
        return (int) ($this->attributes['price_cents'] ?? $this->attributes['price_in_cents'] ?? 0);
    }

    public function getPriceInCentsAttribute(): int
    {
        return (int) ($this->attributes['price_in_cents'] ?? $this->attributes['price_cents'] ?? 0);
    }

    /**
     * Derived Accessor: Display price in KSh (derived from integer cents, never stored as float).
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_cents / 100, 2);
    }

    public function getPriceAttribute(): float
    {
        return round($this->price_cents / 100, 2);
    }
}
