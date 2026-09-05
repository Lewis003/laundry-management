<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    // Mass-assignable columns
    protected $fillable = [
        'name',
        'price_in_cents',
        'duration_minutes',
        'description',
    ];


    protected $casts = [
        'price_in_cents' => 'integer',
        'duration_minutes' => 'integer',
    ];

    /**
     * Relationship: Service package is used across MANY order line items (1:N)
     */
    public function items(): HasMany
    {
        return $this->hasMany(JobItem::class);
    }

    /**
     * Helper Accessor: Format price in Kenyan Shillings
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'KSh ' . number_format($this->price_in_cents / 100, 2);
    }
}
