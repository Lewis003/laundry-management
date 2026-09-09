<x-app-layout>
    @php
        $staffList = $staff ?? $users ?? \App\Models\User::with('roleDefinition')->orderBy('name', 'asc')->get();
        $rolesList = $roles ?? \App\Models\Role::withCount('users')->orderBy('id', 'asc')->get();
    @endphp

    <div class="space-y-6 max-w-7xl mx-auto pb-12">

        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('settings.edit') }}" class="hover:text-slate-800">Settings</a>
            <span>&rsaquo;</span>
            <span class="text-slate-900 font-bold">User Roles / Permissions</span>
        </div>

        <!-- Shared Settings Navigation Tabs -->
        @include('settings.partials.tabs')

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

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </span>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">User Roles & Granular Permissions</h1>
                        <p class="text-xs text-slate-500 font-medium">Toggle administrative access, financial visibility, machine bay controls, and order deletion privileges</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('settings.roles') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Manage Role Definitions &rarr;
            </a>
        </div>

        <!-- Permission Matrix Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">User Permission Toggles</h2>
                    <p class="text-xs text-slate-500">Live granular switches per user account. Green switches indicate permission enabled.</p>
                </div>
                <span class="text-xs font-semibold bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">
                    {{ count($staffList) }} Staff Accounts
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-4">User</th>
                            <th class="py-3.5 px-4">Role Assignment</th>
                            <th class="py-3.5 px-4 text-center">Admin Flag</th>
                            <th class="py-3.5 px-4 text-center">👁️ View Revenue</th>
                            <th class="py-3.5 px-4 text-center">💸 Log Expenses</th>
                            <th class="py-3.5 px-4 text-center">⚙️ Machine Bay</th>
                            <th class="py-3.5 px-4 text-center">🗑️ Delete Orders</th>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
