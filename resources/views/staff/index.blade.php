<x-app-layout>
    @php
        $staffList = $staff ?? $users ?? \App\Models\User::with('roleDefinition')->orderBy('name', 'asc')->get();
        $rolesList = $roles ?? \App\Models\Role::withCount('users')->orderBy('id', 'asc')->get();
    @endphp

    <div class="space-y-8 max-w-7xl mx-auto pb-12">

        <!-- Alerts -->
        @if(session('success'))
        <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-xl text-xs font-bold flex items-center justify-between shadow-sm">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-emerald-900 font-black text-sm hover:opacity-75">&times;</button>
        </div>
        @endif
        @if(session('error'))
        <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-xl text-xs font-bold flex items-center justify-between shadow-sm">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-rose-900 font-black text-sm hover:opacity-75">&times;</button>
        </div>
        @endif

        <!-- Main Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Role-Based Access Control</h1>
                        <p class="text-xs text-slate-500 font-medium">Staff & Operations Activity Tracker &bull; Configure roles, primary permission actions, and access levels</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button onclick="openAddRoleModal()" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    + Add New Role
                </button>
                <button onclick="document.getElementById('addStaffModal').classList.remove('hidden')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    + Register Staff
                </button>
            </div>
        </div>

        <!-- SECTION 1: ROLE-BASED PERMISSION ACTIONS MATRIX TABLE -->
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        Role & Primary Permissions Matrix
                    </h2>
                    <p class="text-xs text-slate-500">Overview of system roles, primary permission capabilities, and functional access tiers</p>
                </div>
                <span class="text-xs font-semibold bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">
                    {{ count($rolesList) }} Defined Roles
                </span>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                                <th class="py-3.5 px-5">Role</th>
                                <th class="py-3.5 px-5">Primary Permission Actions</th>
                                <th class="py-3.5 px-5">Access Level</th>
                                <th class="py-3.5 px-5 text-center">Assigned Staff</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($rolesList as $roleItem)
                            @php
                                $accessClass = match(strtolower(trim($roleItem->access_level))) {
                                    'full access' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'operational authority' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                    'customer facing' => 'bg-sky-100 text-sky-800 border-sky-200',
                                    'back-end processing' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'logistics only' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                                $actionsList = is_array($roleItem->primary_actions) ? $roleItem->primary_actions : (json_decode($roleItem->primary_actions, true) ?? []);
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <!-- Role Name & Details -->
                                <td class="py-4 px-5 align-top">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 text-sm">{{ $roleItem->display_name }}</span>
                                        @if($roleItem->is_system)
                                            <span class="text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded border border-slate-200">System</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ $roleItem->name }}</div>
                                    @if($roleItem->description)
                                        <p class="text-[11px] text-slate-500 mt-1 max-w-xs leading-relaxed">{{ $roleItem->description }}</p>
                                    @endif
                                </td>

                                <!-- Primary Permission Actions -->
                                <td class="py-4 px-5 align-top">
                                    <div class="flex flex-wrap gap-1.5 max-w-md">
                                        @foreach($actionsList as $action)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono font-medium bg-slate-100 text-slate-700 border border-slate-200/80">
                                                <svg class="w-2.5 h-2.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $action }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>

                                <!-- Access Level Badge -->
                                <td class="py-4 px-5 align-top">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border shadow-xs {{ $accessClass }}">
                                        {{ $roleItem->access_level }}
                                    </span>
                                </td>

                                <!-- Assigned Staff Count -->
                                <td class="py-4 px-5 align-top text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold {{ $roleItem->users_count > 0 ? 'bg-slate-100 text-slate-800' : 'bg-slate-50 text-slate-400' }}">
                                        {{ $roleItem->users_count }} staff
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-5 align-top text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                onclick="openEditRoleModal({{ json_encode($roleItem) }})"
                                                class="px-2.5 py-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition">
                                            Edit Role
                                        </button>
                                        @if(!$roleItem->is_system && $roleItem->users_count == 0)
                                        <form method="POST" action="{{ route('roles.destroy', $roleItem->id) }}" onsubmit="return confirm('Permanently delete role {{ $roleItem->display_name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 text-xs font-bold text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition">
                                                Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">No roles configured. Click "+ Add New Role" to create one.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION 2: STAFF MEMBERS ROSTER & ASSIGNMENTS -->
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Active Staff Members Roster & Role Assignments
                    </h2>
                    <p class="text-xs text-slate-500">Assign roles to employee accounts and manage granular permission overrides</p>
                </div>
                <span class="text-xs font-semibold bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">
                    {{ count($staffList) }} Registered Employees
                </span>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                                <th class="py-3.5 px-4">Staff Member</th>
                                <th class="py-3.5 px-4">Assigned Role</th>
                                <th class="py-3.5 px-4 text-center">Admin Flag</th>
                                <th class="py-3.5 px-4 text-center">👁️ View Revenue</th>
                                <th class="py-3.5 px-4 text-center">💸 Log Expenses</th>
                                <th class="py-3.5 px-4 text-center">⚙️ Machine Bay</th>
                                <th class="py-3.5 px-4 text-center">🗑️ Delete Orders</th>
                                <th class="py-3.5 px-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($staffList as $member)
                            <tr class="hover:bg-slate-50 transition">
                                <!-- Staff Info -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $member->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $member->email }}</div>
                                </td>

                                <!-- Role Selector -->
                                <td class="py-3.5 px-4">
                                    <form method="POST" action="{{ route('staff.update-role', $member->id) }}">
                                        @csrf
                                        <select name="role" onchange="this.form.submit()" class="text-xs font-semibold bg-white border border-slate-300 rounded-lg p-1.5 shadow-sm cursor-pointer focus:ring-emerald-500">
                                            @foreach($rolesList as $rOption)
                                                <option value="{{ $rOption->name }}" {{ $member->role === $rOption->name ? 'selected' : '' }}>
                                                    {{ $rOption->display_name }} ({{ $rOption->access_level }})
                                                </option>
                                            @endforeach
                                            @if(!$rolesList->contains('name', $member->role))
                                                <option value="{{ $member->role }}" selected>{{ ucfirst($member->role) }}</option>
                                            @endif
                                        </select>
                                    </form>
                                </td>

                                <!-- Toggle Admin Status -->
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('staff.toggle-admin', $member->id) }}">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition shadow-sm {{ $member->isAdmin() ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                            {{ $member->isAdmin() ? '✓ ADMIN' : 'STAFF' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Toggle: View Revenue -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($member->isAdmin())
                                        <span class="text-[11px] font-bold text-emerald-600">Always (Admin)</span>
                                    @else
                                        <form method="POST" action="{{ route('staff.toggle-permission', $member->id) }}">
                                            @csrf
                                            <input type="hidden" name="permission" value="view_revenue">
                                            <button type="submit" class="w-10 h-5 inline-flex items-center rounded-full p-0.5 transition-colors {{ $member->canViewRevenue() ? 'bg-emerald-500 justify-end' : 'bg-slate-300 justify-start' }}">
                                                <span class="w-4 h-4 rounded-full bg-white shadow-md transform"></span>
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <!-- Toggle: Manage Expenses -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($member->isAdmin())
                                        <span class="text-[11px] font-bold text-emerald-600">Always (Admin)</span>
                                    @else
                                        <form method="POST" action="{{ route('staff.toggle-permission', $member->id) }}">
                                            @csrf
                                            <input type="hidden" name="permission" value="manage_expenses">
                                            <button type="submit" class="w-10 h-5 inline-flex items-center rounded-full p-0.5 transition-colors {{ $member->canManageExpenses() ? 'bg-emerald-500 justify-end' : 'bg-slate-300 justify-start' }}">
                                                <span class="w-4 h-4 rounded-full bg-white shadow-md transform"></span>
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <!-- Toggle: Manage Machines -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($member->isAdmin())
                                        <span class="text-[11px] font-bold text-emerald-600">Always (Admin)</span>
                                    @else
                                        <form method="POST" action="{{ route('staff.toggle-permission', $member->id) }}">
                                            @csrf
                                            <input type="hidden" name="permission" value="manage_machines">
                                            <button type="submit" class="w-10 h-5 inline-flex items-center rounded-full p-0.5 transition-colors {{ $member->canManageMachines() ? 'bg-emerald-500 justify-end' : 'bg-slate-300 justify-start' }}">
                                                <span class="w-4 h-4 rounded-full bg-white shadow-md transform"></span>
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <!-- Toggle: Delete Orders -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($member->isAdmin())
                                        <span class="text-[11px] font-bold text-emerald-600">Always (Admin)</span>
                                    @else
                                        <form method="POST" action="{{ route('staff.toggle-permission', $member->id) }}">
                                            @csrf
                                            <input type="hidden" name="permission" value="delete_orders">
                                            <button type="submit" class="w-10 h-5 inline-flex items-center rounded-full p-0.5 transition-colors {{ $member->canDeleteOrders() ? 'bg-emerald-500 justify-end' : 'bg-slate-300 justify-start' }}">
                                                <span class="w-4 h-4 rounded-full bg-white shadow-md transform"></span>
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <!-- Remove Staff -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($member->id !== auth()->id())
                                    <form method="POST" action="{{ route('staff.destroy', $member->id) }}" onsubmit="return confirm('Remove staff member {{ $member->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Remove</button>
                                    </form>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Active Self</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL 1: ADD NEW ROLE -->
    <div id="addRoleModal" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Add New System Role</h3>
                    <p class="text-xs text-slate-500">Define role identifier, access tier, and primary permission actions</p>
                </div>
                <button onclick="document.getElementById('addRoleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('roles.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Role Display Name *</label>
                        <input type="text" name="display_name" required placeholder="e.g. Quality Auditor" class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Slug Identifier *</label>
                        <input type="text" name="name" required placeholder="e.g. quality_auditor" class="w-full rounded-lg border-slate-300 p-2.5 text-xs font-mono focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Access Level *</label>
                    <select name="access_level" required class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-indigo-500">
                        <option value="Full Access">Full Access (Unrestricted)</option>
                        <option value="Operational Authority">Operational Authority (Management)</option>
                        <option value="Customer Facing">Customer Facing (Front Desk/Sales)</option>
                        <option value="Back-end Processing">Back-end Processing (Floor Operations)</option>
                        <option value="Logistics Only">Logistics Only (Delivery/Dispatch)</option>
                        <option value="Limited Access">Limited Access (Auditing/Viewing)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Primary Permission Actions *</label>
                    <textarea name="primary_actions" rows="2" required placeholder="e.g. audit:check, report:view, order:flag (comma-separated)"
                              class="w-full rounded-lg border-slate-300 p-2.5 text-xs font-mono focus:ring-indigo-500"></textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Separate action slugs with commas (e.g. order:create, tag:generate, payment:collect)</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Description / Responsibilities</label>
                    <textarea name="description" rows="2" placeholder="Brief summary of duties and authorities for this role..."
                              class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('addRoleModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 font-bold rounded-lg text-slate-700 hover:bg-slate-200">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 font-bold rounded-lg text-white shadow">Create Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EDIT ROLE -->
    <div id="editRoleModal" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit Role & Permissions</h3>
                    <p class="text-xs text-slate-500" id="editRoleSubheading">Update role attributes and permission actions</p>
                </div>
                <button onclick="document.getElementById('editRoleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
            </div>
            <form id="editRoleForm" method="POST" action="" class="space-y-3.5 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Display Name *</label>
                    <input type="text" id="edit_display_name" name="display_name" required class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Access Level *</label>
                    <select id="edit_access_level" name="access_level" required class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-indigo-500">
                        <option value="Full Access">Full Access</option>
                        <option value="Operational Authority">Operational Authority</option>
                        <option value="Customer Facing">Customer Facing</option>
                        <option value="Back-end Processing">Back-end Processing</option>
                        <option value="Logistics Only">Logistics Only</option>
                        <option value="Limited Access">Limited Access</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Primary Permission Actions *</label>
                    <textarea id="edit_primary_actions" name="primary_actions" rows="3" required
                              class="w-full rounded-lg border-slate-300 p-2.5 text-xs font-mono focus:ring-indigo-500"></textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Comma-separated permission action slugs (e.g. user:manage, invoice:void, payment:collect)</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Description / Responsibilities</label>
                    <textarea id="edit_description" name="description" rows="2"
                              class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('editRoleModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 font-bold rounded-lg text-slate-700 hover:bg-slate-200">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 font-bold rounded-lg text-white shadow">Update Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: ADD NEW STAFF -->
    <div id="addStaffModal" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-base font-bold text-slate-900">Register New Staff Member</h3>
                <button onclick="document.getElementById('addStaffModal').classList.add('hidden')" class="text-slate-400 font-bold text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('staff.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Mary Atieno" class="w-full rounded-lg border-slate-300 p-2.5 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email (Login ID)</label>
                    <input type="email" name="email" required placeholder="mary@taisonlaundry.com" class="w-full rounded-lg border-slate-300 p-2.5 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Temporary Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full rounded-lg border-slate-300 p-2.5 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Assign Role</label>
                    <select name="role" class="w-full rounded-lg border-slate-300 p-2.5 text-xs">
                        @foreach($rolesList as $rOption)
                            <option value="{{ $rOption->name }}">{{ $rOption->display_name }} ({{ $rOption->access_level }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="document.getElementById('addStaffModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 font-bold rounded-lg text-slate-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 font-bold rounded-lg text-white">Create Staff</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Helper for Role Modals -->
    <script>
        function openAddRoleModal() {
            document.getElementById('addRoleModal').classList.remove('hidden');
        }

        function openEditRoleModal(role) {
            const form = document.getElementById('editRoleForm');
            form.action = '/roles/' + role.id;

            document.getElementById('editRoleSubheading').innerText = 'Editing ' + role.display_name + ' (' + role.name + ')';
            document.getElementById('edit_display_name').value = role.display_name || '';
            document.getElementById('edit_access_level').value = role.access_level || '';

            let actions = role.primary_actions;
            if (Array.isArray(actions)) {
                actions = actions.join(', ');
            }
            document.getElementById('edit_primary_actions').value = actions || '';
            document.getElementById('edit_description').value = role.description || '';

            document.getElementById('editRoleModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
