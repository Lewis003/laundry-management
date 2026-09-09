<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Machine;
use App\Services\MachineService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MachineController extends Controller
{
    public function __construct(
        protected MachineService $machineService
    ) {}

    /**
     * Equipment fleet listing with live filters and status badges.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'type', 'status', 'maintenance']);
        $machines = $this->machineService->getFilteredMachines($filters, 10);
        $counts = $this->machineService->getSummaryCounts();

        // Retrieve unassigned/pending jobs ready for equipment assignment
        $pendingJobs = Job::where('status', 'received')
            ->whereNull('machine_id')
            ->with('customer')
            ->latest()
            ->get();

        return view('machines.index', array_merge([
            'machines'    => $machines,
            'filters'     => $filters,
            'pendingJobs' => $pendingJobs,
        ], $counts));
    }

    /**
     * Add a new machine unit to the fleet.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->canManageMachines(), 403, 'Unauthorized. Adding equipment is restricted.');

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:100', 'unique:machines,name'],
            'type'                  => ['required', 'in:washer,dryer,iron'],
            'capacity_kg'           => ['required', 'numeric', 'min:1', 'max:100'],
            'status'                => ['required', 'in:available,in_use,maintenance,retired'],
            'last_maintenance_date' => ['nullable', 'date'],
            'next_maintenance_date' => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string', 'max:500'],
        ]);

        $machine = $this->machineService->createMachine($validated);

        return redirect()->route('machines.index')->with('success', "Equipment '{$machine->name}' added to fleet.");
    }

    /**
     * Update machine unit specifications or maintenance schedule.
     */
    public function update(Request $request, Machine $machine): RedirectResponse
    {
        abort_unless(auth()->user()->canManageMachines(), 403, 'Unauthorized. Updating equipment is restricted.');

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:100', 'unique:machines,name,' . $machine->id],
            'type'                  => ['required', 'in:washer,dryer,iron'],
            'capacity_kg'           => ['required', 'numeric', 'min:1', 'max:100'],
            'status'                => ['required', 'in:available,in_use,maintenance,retired'],
            'last_maintenance_date' => ['nullable', 'date'],
            'next_maintenance_date' => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->machineService->updateMachine($machine, $validated);
            return redirect()->route('machines.index')->with('success', "Equipment '{$machine->name}' updated.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Assign equipment to a pending laundry job atomically.
     */
    public function assign(Request $request, Machine $machine): RedirectResponse
    {
        abort_if(auth()->user()->isCashier(), 403, 'Front desk cashiers cannot allocate or change machine statuses.');
        abort_unless(auth()->user()->canOperateMachines(), 403, 'Unauthorized to allocate equipment.');

        $validated = $request->validate([
            'job_id' => ['required', 'exists:jobs,id'],
        ]);

        try {
            $job = Job::findOrFail($validated['job_id']);
            $this->machineService->assignMachineToJob($machine, $job, auth()->id());
            return redirect()->route('machines.index')->with('success', "Order #{$job->job_number} assigned to {$machine->name} and started.");
        } catch (RuntimeException | DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Release a machine back into available rotation.
     */
    public function release(Machine $machine): RedirectResponse
    {
        abort_if(auth()->user()->isCashier(), 403, 'Front desk cashiers cannot allocate or change machine statuses.');
        abort_unless(auth()->user()->canOperateMachines(), 403, 'Unauthorized to release equipment.');

        $this->machineService->releaseMachine($machine);
        return redirect()->route('machines.index')->with('success', "Equipment '{$machine->name}' marked available.");
    }

    /**
     * Remove machine from system.
     */
    public function destroy(Machine $machine): RedirectResponse
    {
        abort_unless(auth()->user()->canManageMachines(), 403, 'Unauthorized. Deleting equipment is restricted.');

        try {
            $name = $machine->name;
            $this->machineService->deleteMachine($machine);
            return redirect()->route('machines.index')->with('success', "Equipment '{$name}' removed from fleet.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export machine fleet roster to CSV.
     */
    public function export(): StreamedResponse
    {
        $fileName = 'safishwa_machines_' . date('Y_m_d_His') . '.csv';
        $machines = Machine::all();

        return response()->streamDownload(function () use ($machines) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Machine Name', 'Type', 'Capacity (kg)', 'Status', 'Next Maintenance', 'Notes']);

            foreach ($machines as $machine) {
                $statusVal = is_object($machine->status) ? $machine->status->value : $machine->status;
                fputcsv($handle, [
                    $machine->id,
                    $machine->name,
                    strtoupper($machine->type),
                    $machine->capacity_kg,
                    strtoupper($statusVal),
                    $machine->next_maintenance_date ? $machine->next_maintenance_date->format('Y-m-d') : 'N/A',
                    $machine->notes ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
