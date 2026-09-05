<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    // Which columns are safe to save from forms
    protected $fillable = [
        'name',
        'phone',
    ];

    /**
     * Relationship: 1 Customer can have MANY laundry orders (1:N)
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
