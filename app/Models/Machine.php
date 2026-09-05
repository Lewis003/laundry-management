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

    // Which columns are mass-assignable
    protected $fillable = [
        'name',
        'is_available',
        'is_active',
    ];

    //  boolean columns are treated as true/false
    protected $casts = [
        'is_available' => 'boolean',
        'is_active' => 'boolean', // maintainance mode: if false, machine is offline and cannot be used for new jobs
    ];

    /**
     * Relationship: 1 Machine has MANY historical laundry jobs (1:N)
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Relationship: has 1 active job currently spinning in this machine
     */
    public function currentJob(): HasOne
    {
        return $this->hasOne(Job::class)
            ->where('status', JobStatus::IN_PROGRESS);
    }

    /**
     * Check if machine is free and online
     */
    public function isAvailable(): bool
    {
        return $this->is_available && $this->is_active;
    }
}
