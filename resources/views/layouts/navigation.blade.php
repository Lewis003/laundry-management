<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <!-- Dashboard -->
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

    <!-- Orders Queue -->
    <x-nav-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')">
        {{ __('Orders') }}
    </x-nav-link>

    <!-- Machines Fleet -->
    <x-nav-link :href="route('machines.index')" :active="request()->routeIs('machines.*')">
        {{ __('Machines') }}
    </x-nav-link>

    <!-- Expenses Tracker -->
    @if(auth()->user()->canManageExpenses())
    <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
        {{ __('Expenses') }}
    </x-nav-link>
    @endif

    <!-- P&L Reports -->
    @if(auth()->user()->canViewRevenue())
    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
        {{ __('Reports & P&L') }}
    </x-nav-link>
    @endif

    <!-- Staff & Roles Permissions Link (Admins Only) -->
    @if(auth()->user()->isAdmin())
    <x-nav-link :href="route('staff.index')" :active="request()->routeIs('staff.*')">
        {{ __('Staff & Roles') }}
    </x-nav-link>
    @endif

    <!-- Services Catalog -->
    @if(Route::has('services.index') && !auth()->user()->isOperator())
    <x-nav-link :href="route('services.index')" :active="request()->routeIs('services.*')">
        {{ __('Services') }}
    </x-nav-link>
    @endif

    <!-- Settings (Admin Only) -->
    @if(auth()->user()->isAdmin())
    <x-nav-link :href="route('settings.edit')" :active="request()->routeIs('settings.*') || request()->routeIs('roles.*')">
        {{ __('Settings') }}
    </x-nav-link>
    @endif
</div>
