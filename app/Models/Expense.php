<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'amount_in_cents',
        'expense_date',
        'payment_method',
        'reference_code',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount_in_cents' => 'integer',
    ];

    public function getAmountAttribute(): float
    {
        return round($this->amount_in_cents / 100, 2);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
