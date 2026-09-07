<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Laundry jobs assigned to this staff member / wash bay operator.
     */
    public function assignedJobs(): HasMany
    {
        $foreignKey = 'user_id';

        if (Schema::hasTable('jobs')) {
            if (Schema::hasColumn('jobs', 'operator_id')) {
                $foreignKey = 'operator_id';
            } elseif (Schema::hasColumn('jobs', 'assigned_to')) {
                $foreignKey = 'assigned_to';
            }
        }

        return $this->hasMany(Job::class, $foreignKey);
    }

    /**
     * Laundry jobs created / checked in by this user.
     */
    public function jobs(): HasMany
    {
        $foreignKey = 'user_id';

        if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'created_by')) {
            $foreignKey = 'created_by';
        }

        return $this->hasMany(Job::class, $foreignKey);
    }

    /**
     * Payments collected / processed by this user.
     */
    public function payments(): HasMany
    {
        $foreignKey = 'user_id';

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'received_by')) {
            $foreignKey = 'received_by';
        }

        return $this->hasMany(Payment::class, $foreignKey);
    }

    /**
     * Check if user is an administrator.
     */
    public function isAdmin(): bool
    {
        return strtolower($this->role ?? '') === 'admin';
    }

    /**
     * Check if user is a front desk cashier.
     */
    public function isCashier(): bool
    {
        return strtolower($this->role ?? '') === 'cashier';
    }

    /**
     * Check if user is a wash bay operator.
     */
    public function isOperator(): bool
    {
        return strtolower($this->role ?? '') === 'operator';
    }
}
