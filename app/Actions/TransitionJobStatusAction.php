<?php

namespace App\Actions;

use App\Enums\JobStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransitionJobStatusAction
{
    /**
     * Transition job status, update rack location, and release machines when entering ready/pickup.
     */
    public function execute(
        Job|int $job,
        JobStatus|string $targetStatus,
        ?int $userId = null,
        ?string $rackLocation = null
    ): Job {
        $jobModel = $job instanceof Job ? $job : Job::findOrFail($job);
        $newStatus = $targetStatus instanceof JobStatus ? $targetStatus : (JobStatus::tryFrom($targetStatus) ?? $targetStatus);

        $currentVal = is_string($jobModel->status) ? $jobModel->status : ($jobModel->status->value ?? 'received');
        $targetVal = $newStatus instanceof JobStatus ? $newStatus->value : (string) $newStatus;

        if ($currentVal === $targetVal) {
            return $jobModel;
        }

        $allowedTransitions = [
            'received'    => ['in_progress', 'cancelled'],
            'in_progress' => ['ready', 'cancelled'],
            'ready'       => ['collected', 'picked_up', 'completed', 'cancelled'],
            'collected'   => [],
            'picked_up'   => [],
            'completed'   => [],
            'cancelled'   => [],
        ];

        if (isset($allowedTransitions[$currentVal]) && !in_array($targetVal, $allowedTransitions[$currentVal])) {
            throw new InvalidStatusTransitionException($currentVal, $targetVal);
        }

        return DB::transaction(function () use ($jobModel, $newStatus, $rackLocation) {
            $statusVal = $newStatus instanceof JobStatus ? $newStatus->value : (string) $newStatus;

            // When garments are READY or COLLECTED, vacate and unlock the equipment
            if (in_array($statusVal, ['ready', 'collected', 'picked_up', 'completed'])) {
                if ($jobModel->machine) {
                    $machineUpdates = [];
                    if (Schema::hasColumn('machines', 'is_available')) {
                        $machineUpdates['is_available'] = true;
                    }
                    if (Schema::hasColumn('machines', 'status')) {
                        $machineUpdates['status'] = 'available';
                    }
                    if (!empty($machineUpdates)) {
                        $jobModel->machine->update($machineUpdates);
                    }
                }
            }

            $jobUpdates = [
                'status' => $newStatus,
            ];

            if ($rackLocation && Schema::hasColumn('jobs', 'rack_location')) {
                $jobUpdates['rack_location'] = $rackLocation;
            }

            $jobModel->update($jobUpdates);

            return $jobModel->fresh(['machine', 'items.service', 'customer']);
        });
    }
}
