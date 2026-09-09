<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto">

        <!-- Alerts -->
        @if(session('success'))
        <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-xl text-xs font-bold flex items-center justify-between shadow-sm">
            <span>✓ {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-900 font-black">&times;</button>
        </div>
        @endif
        @if(session('error'))
        <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-xl text-xs font-bold flex items-center justify-between shadow-sm">
            <span>⚠️ {{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-rose-900 font-black">&times;</button>
        </div>
        @endif

        <!-- ==================== HEADER ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Machine Fleet Management</h1>
                <p class="text-xs text-slate-500 font-medium">Monitor commercial washers, dryers, status & service cycles</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('machines.export', request()->query()) }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-200 text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export CSV</span>
                </a>
                @if(auth()->user()->canManageMachines())
                <button onclick="document.getElementById('addMachineModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-1.5 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add Machine</span>
                </button>
                @endif
            </div>
        </div>

        <!-- ==================== FLEET KPIS ==================== -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Fleet</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalCount }} Units</div>
                <div class="text-xs text-slate-500 mt-0.5">Commercial Inventory</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Available / Idle</span>
                <div class="text-2xl font-black text-emerald-700 mt-1">{{ $availableCount }} Units</div>
                <div class="text-xs text-emerald-600/80 font-medium mt-0.5">Ready for intake</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Running Cycles</span>
                <div class="text-2xl font-black text-blue-700 mt-1">{{ $inUseCount }} Units</div>
                <div class="text-xs text-blue-600/80 font-medium mt-0.5">Active laundry cycle</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Maintenance</span>
                <div class="text-2xl font-black text-amber-700 mt-1">
                    {{ $maintenanceCount }}
                    @if($overdueCount > 0)
                    <span class="text-xs font-bold text-rose-600">({{ $overdueCount }} Overdue)</span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 mt-0.5">Service attention</div>
            </div>
        </div>

        <!-- ==================== SEARCH, FILTERS & DATATABLE ==================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <!-- Search & Filters Form -->
            <form method="GET" action="{{ route('machines.index') }}" class="p-4 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Search machine name, type, or notes..."
                           class="w-full text-xs font-medium bg-white border border-slate-300 rounded-xl p-2.5 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
                </div>
                <select name="type" class="text-xs font-semibold bg-white border border-slate-300 rounded-xl p-2.5 shadow-sm">
                    <option value="all">All Types</option>
                    <option value="washer" {{ request('type') === 'washer' ? 'selected' : '' }}>Washer</option>
                    <option value="dryer" {{ request('type') === 'dryer' ? 'selected' : '' }}>Dryer</option>
                    <option value="iron" {{ request('type') === 'iron' ? 'selected' : '' }}>Iron / Steamer</option>
                </select>
                <select name="status" class="text-xs font-semibold bg-white border border-slate-300 rounded-xl p-2.5 shadow-sm">
                    <option value="all">All Statuses</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>🟢 Available</option>
                    <option value="in_use" {{ request('status') === 'in_use' ? 'selected' : '' }}>🔵 In Use</option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>🟡 Maintenance</option>
                    <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>⚪ Retired</option>
                </select>
                <select name="maintenance" class="text-xs font-semibold bg-white border border-slate-300 rounded-xl p-2.5 shadow-sm">
                    <option value="">All Maintenance</option>
                    <option value="overdue" {{ request('maintenance') === 'overdue' ? 'selected' : '' }}>⚠️ Overdue Service</option>
                    <option value="upcoming" {{ request('maintenance') === 'upcoming' ? 'selected' : '' }}>🗓️ Next 7 Days</option>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'type', 'status', 'maintenance']))
                <a href="{{ route('machines.index') }}" class="px-3 py-2.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
                    Reset
                </a>
                @endif
            </form>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-4">Machine Unit</th>
                            <th class="py-3.5 px-4">Type & Capacity</th>
                            <th class="py-3.5 px-4">Current Status</th>
                            <th class="py-3.5 px-4">Active Order</th>
                            <th class="py-3.5 px-4">Next Service</th>
                            <th class="py-3.5 px-4 text-center">Equipment Control</th>
                            @if(auth()->user()->canManageMachines())
                            <th class="py-3.5 px-4 text-center">Manage</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($machines as $machine)
                        @php
                            $rawStatus = is_object($machine->status) ? $machine->status->value : ($machine->status ?? 'available');
                            $statusClasses = [
                                'available'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'in_use'      => 'bg-blue-50 text-blue-700 border-blue-200',
                                'maintenance' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'retired'     => 'bg-slate-100 text-slate-600 border-slate-200',
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div>{{ $machine->name }}</div>
                                @if($machine->notes)
                                <div class="text-[11px] text-slate-400 font-normal truncate max-w-xs">{{ $machine->notes }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="capitalize font-semibold text-slate-800">{{ $machine->type }}</span>
                                <span class="text-slate-400 font-mono text-[11px]">({{ $machine->formatted_capacity }})</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $statusClasses[$rawStatus] ?? 'bg-slate-100' }}">
                                    {{ ucfirst(str_replace('_', ' ', $rawStatus)) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($machine->currentJob)
                                    <a href="{{ route('jobs.show', $machine->currentJob) }}" class="font-bold text-blue-600 hover:underline">
                                        #{{ $machine->currentJob->job_number }}
                                    </a>
                                    <div class="text-[11px] text-slate-400 truncate">{{ $machine->currentJob->customer?->name ?? 'Walk-in' }}</div>
                                @else
                                    <span class="text-slate-400 italic">None (Idle)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($machine->next_maintenance_date)
                                    <span class="{{ $machine->isMaintenanceOverdue() ? 'text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200' : 'text-slate-600' }}">
                                        {{ $machine->next_maintenance_date->format('d M Y') }}
                                        @if($machine->isMaintenanceOverdue()) ⚠️ Overdue @endif
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Unscheduled</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($rawStatus === 'available')
                                    <button type="button" onclick="openAssignModal({{ $machine->id }}, '{{ addslashes($machine->name) }}')" class="px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 rounded-lg font-bold text-xs transition">
                                        + Assign Job
                                    </button>
                                @elseif($rawStatus === 'in_use')
                                    <form method="POST" action="{{ route('machines.release', $machine->id) }}" onsubmit="return confirm('Release {{ $machine->name }} back to available status?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300 rounded-lg font-bold text-xs transition">
                                            Vacate / Release
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-400 text-xs font-semibold">—</span>
                                @endif
                            </td>
                            @if(auth()->user()->canManageMachines())
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" onclick="editMachine({{ json_encode([
                                        'id'                    => $machine->id,
                                        'name'                  => $machine->name,
                                        'type'                  => $machine->type,
                                        'capacity_kg'           => $machine->capacity_kg,
                                        'status'                => $rawStatus,
                                        'last_maintenance_date' => $machine->last_maintenance_date ? $machine->last_maintenance_date->format('Y-m-d') : '',
                                        'next_maintenance_date' => $machine->next_maintenance_date ? $machine->next_maintenance_date->format('Y-m-d') : '',
                                        'notes'                 => $machine->notes,
                                    ]) }})" class="text-blue-600 hover:text-blue-800 font-bold text-xs">Edit</button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" onclick="if(confirm('Remove {{ $machine->name }} from fleet?')) document.getElementById('delete-machine-{{ $machine->id }}').submit();" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Remove</button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                                No machines found matching filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($machines->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $machines->links() }}
            </div>
            @endif
        </div>

    </div>

    <!-- Hidden Delete Forms -->
    @foreach($machines as $machine)
    <form id="delete-machine-{{ $machine->id }}" action="{{ route('machines.destroy', $machine->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    @endforeach

    <!-- Assign Modal -->
    <div id="assignModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Assign Job to <span id="assignMachineName" class="text-blue-600"></span></h3>
                <button onclick="document.getElementById('assignModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
            </div>
            <form id="assignForm" method="POST" action="" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Select Received Order Awaiting Washing</label>
                    <select name="job_id" required class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-blue-500">
                        <option value="">-- Choose Order --</option>
                        @foreach($pendingJobs as $pendingJob)
                        <option value="{{ $pendingJob->id }}">
                            #{{ $pendingJob->job_number }} — {{ $pendingJob->customer?->name ?? 'Walk-in' }} ({{ $pendingJob->created_at->diffForHumans() }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('assignModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 font-bold rounded-xl text-slate-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 font-bold rounded-xl text-white shadow-sm">Start Cycle & Assign</button>
                </div>
            </form>
        </div>
    </div>

    @if(auth()->user()->canManageMachines())
    <!-- Add Machine Modal -->
    <div id="addMachineModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Add New Laundry Unit</h3>
                <button onclick="document.getElementById('addMachineModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('machines.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Machine Name / Code</label>
                    <input type="text" name="name" required placeholder="e.g. Commercial Washer 03 (15kg)" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Type</label>
                        <select name="type" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-emerald-500">
                            <option value="washer">Washer</option>
                            <option value="dryer">Dryer</option>
                            <option value="iron">Iron / Steamer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Capacity (KG)</label>
                        <input type="number" step="0.5" name="capacity_kg" value="15.00" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-emerald-500">
                        <option value="available">🟢 Available / Ready</option>
                        <option value="in_use">🔵 In Use / Running</option>
                        <option value="maintenance">🟡 Maintenance</option>
                        <option value="retired">⚪ Retired</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Last Service</label>
                        <input type="date" name="last_maintenance_date" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Next Service</label>
                        <input type="date" name="next_maintenance_date" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Model info, belt serial, warranty..." class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-emerald-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('addMachineModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 font-bold rounded-xl text-slate-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 font-bold rounded-xl text-white shadow-sm">Save Machine</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Machine Modal -->
    <div id="editMachineModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Edit Machine Unit</h3>
                <button onclick="document.getElementById('editMachineModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
            </div>
            <form id="editMachineForm" method="POST" action="" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Machine Name</label>
                    <input type="text" id="edit_name" name="name" required class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Type</label>
                        <select id="edit_type" name="type" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-blue-500">
                            <option value="washer">Washer</option>
                            <option value="dryer">Dryer</option>
                            <option value="iron">Iron / Steamer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Capacity (KG)</label>
                        <input type="number" step="0.5" id="edit_capacity_kg" name="capacity_kg" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select id="edit_status" name="status" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-blue-500">
                        <option value="available">🟢 Available</option>
                        <option value="in_use">🔵 In Use</option>
                        <option value="maintenance">🟡 Maintenance</option>
                        <option value="retired">⚪ Retired</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Last Service</label>
                        <input type="date" id="edit_last_maintenance_date" name="last_maintenance_date" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Next Service</label>
                        <input type="date" id="edit_next_maintenance_date" name="next_maintenance_date" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Notes</label>
                    <textarea id="edit_notes" name="notes" rows="2" class="w-full rounded-xl border-slate-300 p-2.5 font-medium shadow-sm focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('editMachineModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 font-bold rounded-xl text-slate-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 font-bold rounded-xl text-white shadow-sm">Update Machine</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        function openAssignModal(machineId, machineName) {
            document.getElementById('assignMachineName').textContent = machineName;
            document.getElementById('assignForm').action = '/machines/' + machineId + '/assign';
            document.getElementById('assignModal').classList.remove('hidden');
        }

        function editMachine(machine) {
            document.getElementById('editMachineForm').action = '/machines/' + machine.id;
            document.getElementById('edit_name').value = machine.name;
            document.getElementById('edit_type').value = machine.type;
            document.getElementById('edit_capacity_kg').value = machine.capacity_kg;
            document.getElementById('edit_status').value = machine.status;
            document.getElementById('edit_last_maintenance_date').value = machine.last_maintenance_date || '';
            document.getElementById('edit_next_maintenance_date').value = machine.next_maintenance_date || '';
            document.getElementById('edit_notes').value = machine.notes || '';
            document.getElementById('editMachineModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
