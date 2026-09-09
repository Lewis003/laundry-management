<?php

namespace App\Models;

use App\Enums\JobStatus;
use App\Enums\MachineStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'capacity_kg',
        'status',
        'is_available',
        'is_active',
        'last_maintenance_date',
        'next_maintenance_date',
        'notes',
    ];

    protected $casts = [
        'status'                => MachineStatus::class,
        'capacity_kg'           => 'float',
        'is_available'          => 'boolean',
        'is_active'             => 'boolean',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
    ];

    // ==================== RELATIONSHIPS ====================

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function currentJob(): HasOne
    {
        return $this->hasOne(Job::class)
            ->where('status', JobStatus::IN_PROGRESS)
            ->latestOfMany();
    }

    // ==================== SCOPES ====================

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', MachineStatus::AVAILABLE->value)
              ->orWhere('is_available', true);
        })->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ==================== DERIVED ACCESSORS & HELPERS ====================

    public function getFormattedCapacityAttribute(): string
    {
        return number_format($this->capacity_kg ?? 15, 1) . ' kg';
    }

    public function isMaintenanceOverdue(): bool
    {
        return $this->next_maintenance_date && $this->next_maintenance_date->isPast();
    }

    public function isMaintenanceDueSoon(): bool
    {
        return $this->next_maintenance_date &&
               $this->next_maintenance_date->isFuture() &&
               $this->next_maintenance_date->diffInDays(Carbon::now()) <= 7;
    }
}
