<?php

namespace App\Actions;

use App\Enums\JobStatus;
use App\Models\Job;
use App\Models\Machine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class AssignMachineAction
{
    /**
     * Assign machine, lock equipment, and advance status to IN_PROGRESS.
     * Includes self-healing logic for stale locks.
     */
    public function execute(Job|int $job, Machine|int $machine, ?int $operatorId = null): Job
    {
        $jobModel = $job instanceof Job ? $job : Job::findOrFail($job);
        $machineId = $machine instanceof Machine ? $machine->id : (int) $machine;

        return DB::transaction(function () use ($jobModel, $machineId, $operatorId) {
            $lockedMachine = Machine::where('id', $machineId)->lockForUpdate()->firstOrFail();

            // Check if an ACTUAL job is currently washing in this machine
            $activeRunningJob = Job::where('machine_id', $machineId)
                ->where('status', JobStatus::IN_PROGRESS)
                ->where('id', '!=', $jobModel->id)
                ->first();

            if ($activeRunningJob) {
                throw new RuntimeException("Equipment '{$lockedMachine->name}' is currently in use by Order #{$activeRunningJob->job_number}.");
            }

            // Lock the machine for this job
            $machineUpdates = [];
            if (Schema::hasColumn('machines', 'is_available')) {
                $machineUpdates['is_available'] = false;
            }
            if (Schema::hasColumn('machines', 'status')) {
                $machineUpdates['status'] = 'in_use';
            }
            if (!empty($machineUpdates)) {
                $lockedMachine->update($machineUpdates);
            }

            // Advance Job Status to IN_PROGRESS
            $jobUpdates = [
                'machine_id' => $machineId,
                'status'     => JobStatus::IN_PROGRESS,
            ];

            if ($operatorId) {
                if (Schema::hasColumn('jobs', 'operator_id')) {
                    $jobUpdates['operator_id'] = $operatorId;
                } elseif (Schema::hasColumn('jobs', 'user_id')) {
                    $jobUpdates['user_id'] = $operatorId;
                } elseif (Schema::hasColumn('jobs', 'assigned_to')) {
                    $jobUpdates['assigned_to'] = $operatorId;
                }
            }

            $jobModel->update($jobUpdates);

            return $jobModel->fresh(['machine', 'items.service', 'customer']);
        });
    }
}
