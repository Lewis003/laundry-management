<div class="border-b border-slate-200/80 mb-6">
    <nav class="-mb-px flex space-x-2 sm:space-x-4 overflow-x-auto text-xs font-bold" aria-label="Settings Tabs">
        <!-- 1. Shop & Location Settings -->
        <a href="{{ route('settings.edit') }}"
           class="whitespace-nowrap py-3 px-3.5 border-b-2 rounded-t-lg transition flex items-center gap-2 {{ request()->routeIs('settings.edit') ? 'border-emerald-600 text-emerald-700 bg-emerald-50/50' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('settings.edit') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Shop & Location</span>
        </a>

        <!-- 2. Role Management -->
        <a href="{{ route('settings.roles') }}"
           class="whitespace-nowrap py-3 px-3.5 border-b-2 rounded-t-lg transition flex items-center gap-2 {{ request()->routeIs('settings.roles') || request()->routeIs('roles.*') ? 'border-indigo-600 text-indigo-700 bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('settings.roles') || request()->routeIs('roles.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>Role Management</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ request()->routeIs('settings.roles') || request()->routeIs('roles.*') ? 'bg-indigo-200/80 text-indigo-900' : 'bg-slate-100 text-slate-600' }}">
                {{ \App\Models\Role::count() }}
            </span>
        </a>

        <!-- 3. User Roles / Permissions -->
        <a href="{{ route('settings.permissions') }}"
           class="whitespace-nowrap py-3 px-3.5 border-b-2 rounded-t-lg transition flex items-center gap-2 {{ request()->routeIs('settings.permissions') ? 'border-amber-600 text-amber-700 bg-amber-50/50' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('settings.permissions') ? 'text-amber-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            <span>User Roles / Permissions</span>
        </a>

        <!-- 4. Staff / Team Management -->
        <a href="{{ route('settings.staff') }}"
           class="whitespace-nowrap py-3 px-3.5 border-b-2 rounded-t-lg transition flex items-center gap-2 {{ request()->routeIs('settings.staff') || (request()->routeIs('staff.index') && !request()->routeIs('settings.permissions')) ? 'border-emerald-600 text-emerald-700 bg-emerald-50/50' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('settings.staff') || request()->routeIs('staff.index') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Staff / Team Management</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ request()->routeIs('settings.staff') || request()->routeIs('staff.index') ? 'bg-emerald-200/80 text-emerald-900' : 'bg-slate-100 text-slate-600' }}">
                {{ \App\Models\User::count() }}
            </span>
        </a>
    </nav>
</div>

