<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Job extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status'    => JobStatus::class,
        'apply_vat' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function assignedWorker(): BelongsTo
    {
        $foreignKey = 'user_id';
        if (Schema::hasTable('jobs')) {
            if (Schema::hasColumn('jobs', 'operator_id')) {
                $foreignKey = 'operator_id';
            } elseif (Schema::hasColumn('jobs', 'assigned_to')) {
                $foreignKey = 'assigned_to';
            }
        }
        return $this->belongsTo(User::class, $foreignKey);
    }

    public function operator(): BelongsTo { return $this->assignedWorker(); }
    public function worker(): BelongsTo { return $this->assignedWorker(); }
    public function user(): BelongsTo { return $this->assignedWorker(); }

    public function creator(): BelongsTo
    {
        $foreignKey = 'user_id';
        if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'created_by')) {
            $foreignKey = 'created_by';
        }
        return $this->belongsTo(User::class, $foreignKey);
    }

    // -------------------------------------------------------------
    // Financial Calculations in Kenyan Shillings (VAT-Inclusive Mode)
    // -------------------------------------------------------------

    /**
     * Grand Total in Shillings: Sum of your clean, advertised target prices.
     * (e.g., If the user selects a Duvet, this returns exactly 850.00)
     */
    public function getTotalPriceAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->subtotal; // This assumes your items store the final target retail prices
        });
    }

    public function getTotalAttribute(): float
    {
        return $this->total_price;
    }

    /**
     * Subtotal in Shillings: Your true 100% net business revenue baseline.
     * (e.g., 850.00 / 1.16 = 732.76)
     */
    public function getSubtotalAttribute(): float
    {
        // If tax is explicitly off or exempt, your subtotal remains the full price
        if (isset($this->attributes['apply_vat']) && !$this->attributes['apply_vat']) {
            return $this->total_price;
        }

        if (str_contains($this->notes ?? '', '[VAT_EXEMPT]')) {
            return $this->total_price;
        }

        return round($this->total_price / 1.16, 2);
    }

    /**
     * 16% VAT Tax in Shillings extracted from inside the total price.
     * (e.g., 850.00 - 732.76 = 117.24)
     */
    public function getTaxAttribute(): float
    {
        if (isset($this->attributes['apply_vat']) && !$this->attributes['apply_vat']) {
            return 0.0;
        }

        if (str_contains($this->notes ?? '', '[VAT_EXEMPT]')) {
            return 0.0;
        }

        // Always subtract your subtotal from total to prevent rounding-penny bugs
        return round($this->total_price - $this->subtotal, 2);
    }

    /**
     * Total amount paid so far in Shillings (e.g., M-Pesa Callback updates this).
     */
    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments->sum(function ($p) {
            return $p->amount;
        });
    }

    public function getPaidAttribute(): float
    {
        return $this->paid_amount;
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->paid_amount;
    }

    /**
     * Outstanding Balance Due in Shillings.
     */
    public function getBalanceDueAttribute(): float
    {
        $due = $this->total_price - $this->paid_amount;
        return max(0.0, round($due, 2));
    }

    public function getBalanceAttribute(): float
    {
        return $this->balance_due;
    }


    public function getPaymentStatusAttribute(): string
    {
        if ($this->isFullyPaid()) {
            return 'paid';
        }
        if ($this->paid_amount > 0) {
            return 'partial';
        }
        return 'unpaid';
    }

    public function isFullyPaid(): bool
    {
        return $this->balance_due <= 0.001;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->paid_amount > 0 && $this->balance_due > 0.001;
    }

    public function isUnpaid(): bool
    {
        return $this->paid_amount <= 0.001;
    }

    public function hasBalanceDue(): bool
    {
        return $this->balance_due > 0.001;
    }

    // Method aliases for backward compatibility
    public function totalCents(): int { return (int) round($this->total_price * 100); }
    public function subtotalCents(): int { return (int) round($this->subtotal * 100); }
    public function taxCents(): int { return (int) round($this->tax * 100); }
    public function paidCents(): int { return (int) round($this->paid_amount * 100); }
    public function balanceCents(): int { return (int) round($this->balance_due * 100); }
    public function paidAmountCents(): int { return $this->paidCents(); }
    public function balanceDueCents(): int { return $this->balanceCents(); }
}
