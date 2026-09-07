<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_available',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Active job currently occupying this machine (washing or queued in bay).
     */
    public function currentJob(): HasOne
    {
        return $this->hasOne(Job::class)
            ->whereIn('status', [
                JobStatus::IN_PROGRESS,
                JobStatus::IN_PROGRESS->value,
                JobStatus::RECEIVED,
                JobStatus::RECEIVED->value
            ]);
    }

    /**
     * Is the machine active and free for a new load.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->is_available && $this->currentJob()->doesntExist();
    }

    /**
     * Dynamic status accessor matching workflow expectations ('available', 'in_use', 'maintenance').
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'maintenance';
        }

        // If marked unavailable OR has an active running job, it is strictly IN USE
        if (!$this->is_available || $this->currentJob()->exists()) {
            return 'in_use';
        }

        return 'available';
    }

    /**
     * Set status attribute cleanly mapping to is_available and is_active booleans.
     */
    public function setStatusAttribute(?string $value): void
    {
        if ($value === 'available') {
            $this->attributes['is_available'] = true;
            $this->attributes['is_active'] = true;
        } elseif ($value === 'in_use') {
            $this->attributes['is_available'] = false;
        } elseif ($value === 'maintenance') {
            $this->attributes['is_active'] = false;
        }
    }
}
