<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'notes',
    ];

    /**
     * Relationship: An order belongs to this customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Job::class, 'customer_id')->latest();
    }

    /**
     * Derived Accessor: Total orders count (computed live, never stored).
     */
    public function getTotalOrdersCountAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Derived Accessor: Total spent in integer cents (computed live from line items, never stored).
     */
    public function getTotalSpentCentsAttribute(): int
    {
        return (int) $this->orders()
            ->with('items')
            ->get()
            ->sum(fn ($order) => $order->total_cents ?? 0);
    }
}
