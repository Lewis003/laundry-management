<?php

namespace App\Services;

use App\Enums\JobStatus;
use App\Enums\MachineStatus;
use App\Models\Job;
use App\Models\Machine;
use Carbon\Carbon;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class MachineService
{
    /**
     * Retrieve paginated and filtered list of machines using GET query parameters.
     */
    public function getFilteredMachines(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Machine::query()
            ->with(['currentJob.customer'])
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = trim($filters['search']);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['type']) && $filters['type'] !== 'all', function ($query) use ($filters) {
                $query->where('type', $filters['type']);
            })
            ->when(!empty($filters['status']) && $filters['status'] !== 'all', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(!empty($filters['maintenance']), function ($query) use ($filters) {
                if ($filters['maintenance'] === 'overdue') {
                    $query->where('next_maintenance_date', '<', Carbon::today());
                } elseif ($filters['maintenance'] === 'upcoming') {
                    $query->whereBetween('next_maintenance_date', [Carbon::today(), Carbon::today()->addDays(7)]);
                }
            })
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get operational summary counts for equipment fleet.
     *
     * @return array<string, int>
     */
    public function getSummaryCounts(): array
    {
        return [
            'totalCount'       => Machine::count(),
            'availableCount'   => Machine::where('status', MachineStatus::AVAILABLE->value)->where('is_active', true)->count(),
            'inUseCount'       => Machine::where('status', MachineStatus::IN_USE->value)->count(),
            'maintenanceCount' => Machine::where('status', MachineStatus::MAINTENANCE->value)->orWhere('is_active', false)->count(),
            'overdueCount'     => Machine::where('next_maintenance_date', '<', Carbon::today())->count(),
        ];
    }

    /**
     * Create a new machine unit.
     */
    public function createMachine(array $data): Machine
    {
        $status = isset($data['status'])
            ? ($data['status'] instanceof MachineStatus ? $data['status'] : MachineStatus::from($data['status']))
            : MachineStatus::AVAILABLE;

        return Machine::create([
            'name'                  => $data['name'],
            'type'                  => $data['type'] ?? 'washer',
            'capacity_kg'           => $data['capacity_kg'] ?? 15.00,
            'status'                => $status,
            'is_available'          => ($status === MachineStatus::AVAILABLE),
            'is_active'             => $data['is_active'] ?? true,
            'last_maintenance_date' => $data['last_maintenance_date'] ?? null,
            'next_maintenance_date' => $data['next_maintenance_date'] ?? null,
            'notes'                 => $data['notes'] ?? null,
        ]);
    }

    /**
     * Update an existing machine unit and validate status transition.
     */
    public function updateMachine(Machine $machine, array $data): Machine
    {
        if (isset($data['status'])) {
            $newStatus = $data['status'] instanceof MachineStatus
                ? $data['status']
                : MachineStatus::from($data['status']);

            $currentStatus = $machine->status instanceof MachineStatus
                ? $machine->status
                : MachineStatus::from($machine->status ?? 'available');

            if ($currentStatus !== $newStatus && !$currentStatus->canTransitionTo($newStatus)) {
                throw new DomainException("Invalid machine status transition from '{$currentStatus->label()}' to '{$newStatus->label()}'.");
            }

            $data['status'] = $newStatus;
            $data['is_available'] = ($newStatus === MachineStatus::AVAILABLE);
        }

        $machine->update($data);
        return $machine;
    }

    /**
     * Assign equipment atomically with pessimistic locking to prevent race conditions.
     */
    public function assignMachineToJob(int|Machine $machine, int|Job $job, ?int $operatorId = null): Job
    {
        $machineId = $machine instanceof Machine ? $machine->id : (int) $machine;
        $jobModel  = $job instanceof Job ? $job : Job::findOrFail($job);

        return DB::transaction(function () use ($machineId, $jobModel, $operatorId) {
            // Pessimistic write lock on the machine record
            $lockedMachine = Machine::where('id', $machineId)->lockForUpdate()->firstOrFail();

            if (!$lockedMachine->is_active) {
                throw new RuntimeException("Equipment '{$lockedMachine->name}' is offline / inactive.");
            }

            // Check if another job is actively running in this machine
            $activeRunningJob = Job::where('machine_id', $machineId)
                ->where('status', JobStatus::IN_PROGRESS)
                ->where('id', '!=', $jobModel->id)
                ->first();

            if ($activeRunningJob) {
                throw new RuntimeException("Equipment '{$lockedMachine->name}' is currently in use by Order #{$activeRunningJob->job_number}.");
            }

            // Update machine status atomically
            $lockedMachine->update([
                'status'       => MachineStatus::IN_USE,
                'is_available' => false,
            ]);

            // Update job details
            $jobUpdates = [
                'machine_id' => $machineId,
                'status'     => JobStatus::IN_PROGRESS,
            ];

            if ($operatorId) {
                if (Schema::hasColumn('jobs', 'assigned_to')) {
                    $jobUpdates['assigned_to'] = $operatorId;
                } elseif (Schema::hasColumn('jobs', 'user_id')) {
                    $jobUpdates['user_id'] = $operatorId;
                }
            }

            $jobModel->update($jobUpdates);

            return $jobModel;
        });
    }

    /**
     * Release a machine back to available status.
     */
    public function releaseMachine(int|Machine $machine): Machine
    {
        $machineId = $machine instanceof Machine ? $machine->id : (int) $machine;

        return DB::transaction(function () use ($machineId) {
            $lockedMachine = Machine::where('id', $machineId)->lockForUpdate()->firstOrFail();

            $lockedMachine->update([
                'status'       => MachineStatus::AVAILABLE,
                'is_available' => true,
            ]);

            return $lockedMachine;
        });
    }

    /**
     * Delete machine if not currently in use.
     */
    public function deleteMachine(Machine $machine): bool
    {
        $hasActiveJobs = Job::where('machine_id', $machine->id)
            ->where('status', JobStatus::IN_PROGRESS)
            ->exists();

        if ($hasActiveJobs) {
            throw new DomainException("Cannot delete machine '{$machine->name}' while an order is actively washing.");
        }

        return (bool) $machine->delete();
    }
}

