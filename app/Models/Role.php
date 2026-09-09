<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'access_level',
        'primary_actions',
        'description',
        'is_system',
    ];

    protected $casts = [
        'primary_actions' => 'array',
        'is_system' => 'boolean',
    ];

    /**
     * Users assigned to this role (users.role = roles.name).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    /**
     * Scope default system roles.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }
}

