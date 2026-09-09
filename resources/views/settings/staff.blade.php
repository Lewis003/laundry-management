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
            <span class="text-slate-900 font-bold">Staff / Team Management</span>
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
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Staff & Team Management</h1>
                        <p class="text-xs text-slate-500 font-medium">Register employee accounts, assign organizational roles, and maintain staff roster</p>
                    </div>
                </div>
            </div>
            <button onclick="document.getElementById('addStaffModal').classList.remove('hidden')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                + Register Staff Member
            </button>
        </div>

        <!-- Staff Roster Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Active Staff Accounts</h2>
                    <p class="text-xs text-slate-500">All registered employees with role assignments</p>
                </div>
                <span class="text-xs font-semibold bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">
                    {{ count($staffList) }} Total Staff
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-5">Staff Member</th>
                            <th class="py-3.5 px-5">Assigned Role</th>
                            <th class="py-3.5 px-5 text-center">Admin Flag</th>
                            <th class="py-3.5 px-5">Registered Date</th>
                            <th class="py-3.5 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($staffList as $member)
                        <tr class="hover:bg-slate-50 transition">
                            <!-- Staff Info -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-black text-slate-700 text-xs shrink-0">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $member->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Selector -->
                            <td class="py-4 px-5">
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
                            <td class="py-4 px-5 text-center">
                                <form method="POST" action="{{ route('staff.toggle-admin', $member->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition shadow-sm {{ $member->isAdmin() ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                        {{ $member->isAdmin() ? '✓ ADMIN' : 'STAFF' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Registered Date -->
                            <td class="py-4 px-5 text-slate-500 font-medium">
                                {{ $member->created_at ? $member->created_at->format('M d, Y') : 'N/A' }}
                            </td>

                            <!-- Remove Staff -->
                            <td class="py-4 px-5 text-right">
                                @if($member->id !== auth()->id())
                                <form method="POST" action="{{ route('staff.destroy', $member->id) }}" onsubmit="return confirm('Permanently remove staff member {{ $member->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-lg transition border border-rose-200">
                                        Remove Staff
                                    </button>
                                </form>
                                @else
                                    <span class="text-slate-400 italic text-[11px] px-2 py-1">Logged-in User</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL: REGISTER STAFF MEMBER -->
    <div id="addStaffModal" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-base font-bold text-slate-900">Register New Staff Member</h3>
                <button onclick="document.getElementById('addStaffModal').classList.add('hidden')" class="text-slate-400 font-bold text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('staff.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Mary Atieno" class="w-full rounded-lg border-slate-300 p-2.5 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email (Login ID) *</label>
                    <input type="email" name="email" required placeholder="mary@taisonlaundry.com" class="w-full rounded-lg border-slate-300 p-2.5 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Temporary Password *</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full rounded-lg border-slate-300 p-2.5 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Assign Role *</label>
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
</x-app-layout>
