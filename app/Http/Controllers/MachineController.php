<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MachineController extends Controller
{
    /**
     * Display equipment listing wrapped in the master app layout.
     */
    public function index()
    {
        $machines = Machine::query()->orderBy('name')->get();

        // Check which machines currently have an active job washing
        $activeMachineIds = [];
        if (class_exists(Job::class) && Schema::hasColumn('jobs', 'machine_id')) {
            $activeMachineIds = Job::whereIn('status', ['IN_PROGRESS', 'PROCESSING', 'WASHING', 'DRYING'])
                ->whereNotNull('machine_id')
                ->pluck('machine_id')
                ->map(fn($id) => (int)$id)
                ->toArray();
        }

        $machines->transform(function ($machine) use ($activeMachineIds) {
            $machine->has_active_job = in_array((int)$machine->id, $activeMachineIds);
            return $machine;
        });

        $stats = [
            'total'       => $machines->count(),
            'available'   => $machines->filter(fn($m) => ($m->is_active ?? true) && !$m->has_active_job)->count(),
            'running'     => $machines->filter(fn($m) => ($m->is_active ?? true) && $m->has_active_job)->count(),
            'maintenance' => $machines->filter(fn($m) => !($m->is_active ?? true))->count(),
        ];

        return view('machines.index', compact('machines', 'stats'));
    }

    /**
     * Store newly registered equipment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'type'        => 'nullable|string|max:50',
            'capacity_kg' => 'nullable|numeric|min:1',
        ]);

        $machine = new Machine();
        $machine->name = $validated['name'];

        if (Schema::hasColumn('machines', 'type') && isset($validated['type'])) {
            $machine->type = $validated['type'];
        }

        if (Schema::hasColumn('machines', 'capacity_kg') && isset($validated['capacity_kg'])) {
            $machine->capacity_kg = $validated['capacity_kg'];
        }

        if (Schema::hasColumn('machines', 'is_active')) {
            $machine->is_active = true;
        }

        if (Schema::hasColumn('machines', 'is_available')) {
            $machine->is_available = true;
        }

        if (Schema::hasColumn('machines', 'status')) {
            $machine->status = 'available';
        }

        $machine->save();

        return redirect()->route('machines.index')->with('success', "Machine {$machine->name} added successfully.");
    }

    /**
     * Toggle machine maintenance mode ON/OFF.
     * Updates is_active, is_available, and status in unison.
     */
    public function toggle(Machine $machine)
    {
        $currentlyActive = true;
        if (isset($machine->is_active)) {
            $currentlyActive = (bool) $machine->is_active;
        } elseif (isset($machine->is_available)) {
            $currentlyActive = (bool) $machine->is_available;
        } elseif (isset($machine->status)) {
            $currentlyActive = strtolower((string)$machine->status) !== 'maintenance';
        }

        $newActiveState = !$currentlyActive;

        if (Schema::hasColumn('machines', 'is_active')) {
            $machine->is_active = $newActiveState;
        }

        if (Schema::hasColumn('machines', 'is_available')) {
            $machine->is_available = $newActiveState;
        }

        if (Schema::hasColumn('machines', 'status')) {
            $machine->status = $newActiveState ? 'available' : 'maintenance';
        }

        $machine->save();

        $message = $newActiveState
            ? "Machine {$machine->name} is now Active & Ready for batches."
            : "Machine {$machine->name} is now switched to Under Maintenance.";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete equipment.
     */
    public function destroy(Machine $machine)
    {
        $name = $machine->name;
        $machine->delete();

        return redirect()->route('machines.index')->with('success', "Machine {$name} removed successfully.");
    }
}
