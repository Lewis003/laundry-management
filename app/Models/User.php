<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_admin',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'permissions' => 'array',
    ];

    public function assignedJobs()
    {
        return $this->hasMany(Job::class, 'assigned_to');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'user_id');
    }

    public function roleDefinition()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    // Role Checkers
    public function isAdmin(): bool
    {
        return $this->is_admin || $this->role === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isRider(): bool
    {
        return $this->role === 'rider';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $perms = $this->permissions ?? [];
        if (is_array($perms) && array_key_exists($permission, $perms)) {
            return (bool) $perms[$permission];
        }

        // Check assigned Role definition's primary actions
        if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            $roleRecord = $this->roleDefinition;
            if ($roleRecord && is_array($roleRecord->primary_actions) && in_array($permission, $roleRecord->primary_actions)) {
                return true;
            }
        }

        // Default role permissions if not explicitly configured in JSON
        if ($this->isManager()) {
            return in_array($permission, ['view_revenue', 'manage_expenses', 'manage_machines', 'price:override', 'rewash:approve', 'route:assign', 'report:export']);
        }

        if ($this->isCashier()) {
            return in_array($permission, ['order:create', 'order:edit', 'payment:collect', 'tag:generate']);
        }

        if ($this->isOperator()) {
            return in_array($permission, ['manage_machines', 'status:update', 'weight:log', 'machine:allocate']);
        }

        if ($this->isRider()) {
            return in_array($permission, ['delivery:confirm', 'bag:audit']);
        }

        return false;
    }

    public function canViewRevenue(): bool
    {
        return $this->hasPermission('view_revenue');
    }

    public function canManageExpenses(): bool
    {
        return $this->hasPermission('manage_expenses');
    }

    public function canManageMachines(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canOperateMachines(): bool
    {
        return $this->isAdmin() || $this->isManager() || $this->isOperator();
    }

    public function canOverridePrice(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canApproveRewash(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canDeleteOrders(): bool
    {
        return $this->hasPermission('delete_orders');
    }

    public function canCreateIntake(): bool
    {
        return !$this->isOperator() && !$this->isRider();
    }

    public function canManageStaff(): bool
    {
        return $this->isAdmin();
    }

    public function canManageServices(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canManageCustomers(): bool
    {
        return !$this->isOperator() && !$this->isRider();
    }
}
