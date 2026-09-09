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

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Customer Management</h1>
                <p class="text-xs text-slate-500 font-medium">Search client profiles, contact information, and order frequency</p>
            </div>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                + Register Customer
            </a>
        </div>

        <!-- GET-based Filter Bar (Eloquent ->when pattern) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <form method="GET" action="{{ route('customers.index') }}" class="p-4 bg-slate-50/60 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Search customer name, phone, or email..."
                           class="bg-white border border-slate-300 rounded-lg p-2 text-xs w-full sm:w-72 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">

                    <select name="sort" class="bg-white border border-slate-300 rounded-lg p-2 text-xs font-medium focus:ring-emerald-500 shadow-sm">
                        <option value="">Sort: Newest First</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                        <option value="orders_desc" {{ request('sort') === 'orders_desc' ? 'selected' : '' }}>Most Orders</option>
                    </select>

                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'sort']))
                    <a href="{{ route('customers.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-900 font-semibold">
                        Reset
                    </a>
                    @endif
                </div>
                <div class="text-slate-500 font-mono text-[11px]">
                    Total: {{ $customers->total() }} Customers
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-4">Customer Name</th>
                            <th class="py-3.5 px-4">Phone Number</th>
                            <th class="py-3.5 px-4">Email</th>
                            <th class="py-3.5 px-4 text-center">Orders Count</th>
                            <th class="py-3.5 px-4 text-center">Member Since</th>
                            <th class="py-3.5 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <a href="{{ route('customers.show', $customer) }}" class="text-emerald-600 hover:underline">
                                    {{ $customer->name }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700 font-bold">{{ $customer->phone }}</td>
                            <td class="py-3.5 px-4 text-slate-500">{{ $customer->email ?? '—' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700">
                                    {{ $customer->orders_count ?? 0 }} Orders
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                                {{ $customer->created_at ? $customer->created_at->format('d M Y') : '—' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('customers.show', $customer) }}" class="font-bold text-slate-700 hover:text-emerald-600">
                                        View
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('customers.edit', $customer) }}" class="font-bold text-blue-600 hover:text-blue-800">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 font-medium">No customers found matching your search.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
