<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $shop = \App\Models\ShopSetting::current();
    @endphp

    <title>{{ $shop->shop_name }} — {{ $shop->tagline }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 800: '#1e293b', 900: '#0f172a', 950: '#020617' }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50" x-data="{ mobileNav: false }">
    <div class="flex h-screen overflow-hidden">

        <!-- Responsive Mobile Backdrop -->
        <div x-show="mobileNav" @click="mobileNav = false" class="fixed inset-0 bg-slate-950/70 z-40 lg:hidden"></div>

        <!-- ==================== PERSISTENT SIDEBAR ==================== -->
        <aside :class="mobileNav ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-navy-900 text-slate-300 flex flex-col flex-shrink-0 transition-transform duration-200 ease-in-out lg:static shadow-2xl border-r border-slate-800">

            <!-- Brand: Unified Single Business Name -->
            <div class="h-16 flex items-center justify-between px-5 bg-navy-950 border-b border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 text-white font-black tracking-tight min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-slate-950 font-black text-sm shadow shrink-0">
                        🧺
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-black tracking-wide text-slate-100 uppercase truncate">{{ $shop->shop_name }}</div>
                        <div class="text-[9px] text-slate-400 font-semibold truncate">{{ $shop->location }}</div>
                    </div>
                </a>
                <button @click="mobileNav = false" class="lg:hidden text-slate-400 hover:text-white p-1 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Persistent Sidebar Navigation Links in Exact Order -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-1 text-xs font-semibold">

                <!-- 1. Dashboard (All authenticated staff) -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Dashboard</span>
                </a>

                <!-- 2. Orders (All staff; operators process queue, cashiers create intake) -->
                <a href="{{ route('jobs.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('jobs.*') || request()->routeIs('orders.*') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Orders</span>
                </a>

                <!-- 3. Customers (Cashier, Manager, Admin only) -->
                @if(auth()->user()->canManageCustomers())
                <a href="{{ route('customers.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('customers.*') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Customers</span>
                </a>
                @endif

                <!-- 4. Services (Pricing view for Cashier, Manager, Admin) -->
                @if(Route::has('services.index') && !auth()->user()->isOperator())
                <a href="{{ route('services.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('services.*') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Services</span>
                </a>
                @endif

                <!-- 5. Machines (Operator, Manager, Admin, or with manage_machines permission) -->
                @if(Route::has('machines.index') && (auth()->user()->canManageMachines() || auth()->user()->isOperator() || auth()->user()->isAdmin() || auth()->user()->isManager()))
                <a href="{{ route('machines.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('machines.*') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Machines</span>
                </a>
                @endif

                <!-- 6. Payments (Cashier, Manager, Admin) -->
                @if(Route::has('payments.index') && !auth()->user()->isOperator())
                <a href="{{ route('payments.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('payments.*') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Payments</span>
                </a>
                @endif

                <!-- 7. Expenses (Controlled by manage_expenses permission or Manager/Admin) -->
                @if(Route::has('expenses.index') && auth()->user()->canManageExpenses())
                <a href="{{ route('expenses.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('expenses.*') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Expenses</span>
                </a>
                @endif

                <!-- 8. Reports (Controlled by view_revenue permission or Manager/Admin) -->
                @if(Route::has('reports.index') && auth()->user()->canViewRevenue())
                <a href="{{ route('reports.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('reports.*') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Reports</span>
                </a>
                @endif

                <!-- 9. Staff (Manager and Admin only) -->
                @if(Route::has('staff.index') && auth()->user()->canManageStaff())
                <a href="{{ route('staff.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('staff.*') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Staff</span>
                </a>
                @endif

                <!-- 10. Settings & Governance Hierarchy (Admin Only) -->
                @if(auth()->user()->isAdmin())
                <div x-data="{ openSettings: {{ request()->routeIs('settings.*') || request()->routeIs('staff.*') || request()->routeIs('roles.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="openSettings = !openSettings" type="button"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('settings.*') || request()->routeIs('staff.*') || request()->routeIs('roles.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Settings</span>
                        </div>
                        <svg :class="openSettings ? 'rotate-90' : ''" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Settings Submenu Items -->
                    <div x-show="openSettings" class="pl-6 pr-1 py-1 space-y-1 text-[11px]">
                        <!-- Settings ➔ Shop & Location -->
                        <a href="{{ route('settings.edit') }}"
                           class="flex items-center space-x-2.5 px-3 py-1.5 rounded-lg transition {{ request()->routeIs('settings.edit') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.edit') ? 'bg-white' : 'bg-slate-500' }}"></span>
                            <span>Shop & Location</span>
                        </a>

                        <!-- Settings ➔ Role Management -->
                        <a href="{{ route('settings.roles') }}"
                           class="flex items-center space-x-2.5 px-3 py-1.5 rounded-lg transition {{ request()->routeIs('settings.roles') || request()->routeIs('roles.*') ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.roles') || request()->routeIs('roles.*') ? 'bg-white' : 'bg-slate-500' }}"></span>
                            <span>Role Management</span>
                        </a>

                        <!-- Settings ➔ User Roles / Permissions -->
                        <a href="{{ route('settings.permissions') }}"
                           class="flex items-center space-x-2.5 px-3 py-1.5 rounded-lg transition {{ request()->routeIs('settings.permissions') ? 'bg-amber-600 text-white font-bold shadow-xs' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.permissions') ? 'bg-white' : 'bg-slate-500' }}"></span>
                            <span>User Roles / Permissions</span>
                        </a>

                        <!-- Settings ➔ Staff / Team Management -->
                        <a href="{{ route('settings.staff') }}"
                           class="flex items-center space-x-2.5 px-3 py-1.5 rounded-lg transition {{ request()->routeIs('settings.staff') || request()->routeIs('staff.index') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.staff') || request()->routeIs('staff.index') ? 'bg-white' : 'bg-slate-500' }}"></span>
                            <span>Staff / Team Management</span>
                        </a>
                    </div>
                </div>
                @elseif(Route::has('profile.edit'))
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('profile.*') ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>My Profile</span>
                </a>
                @endif

            </nav>

            <!-- User & Logout -->
            <div class="p-4 bg-navy-950 border-t border-slate-800 flex items-center justify-between">
                <div class="flex items-center space-x-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-200">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-bold text-slate-200 truncate">{{ auth()->user()->name ?? 'Admin' }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">{{ strtoupper(auth()->user()->role ?? 'Cashier') }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ==================== MAIN DETAILS CANVAS ==================== -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
            <!-- Top White Header -->
            <header class="h-16 bg-white border-b border-slate-200/80 flex items-center justify-between px-6 lg:px-8 shadow-sm flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <button @click="mobileNav = true" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-sm font-black text-xs">
                            {{ strtoupper(substr($shop->shop_name, 0, 2)) }}
                        </div>
                        <span class="text-sm font-bold text-slate-900 tracking-tight">{{ $shop->shop_name }} <span class="hidden sm:inline font-normal text-slate-400">— {{ $shop->tagline }}</span></span>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Shopfloor Synced
                    </span>
                </div>
            </header>

            <!-- Scrollable Content Canvas -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>

    </div>
</body>
</html>
