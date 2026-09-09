<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-20"
         x-data="{
            selectedJobs: [],
            selectAll: false,
            toggleAll() {
                if (this.selectAll) {
                    this.selectedJobs = Array.from(document.querySelectorAll('.job-checkbox')).map(cb => cb.value);
                } else {
                    this.selectedJobs = [];
                }
            }
         }">

        <!-- Top Title Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Active Orders Queue</h2>
                <p class="text-xs text-slate-400 mt-0.5">Commercial laundry workflow from intake to M-Pesa collection.</p>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Export to CSV Button -->
                <a href="{{ route('jobs.export', request()->query()) }}"
                   class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition inline-flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export CSV</span>
                </a>

                <!-- Place New Order Button (Cashier & Admin Only) -->
                @if(Auth::user()?->isAdmin() || Auth::user()?->isCashier())
                    <a href="{{ route('jobs.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center space-x-1.5">
                        <span class="text-base font-black leading-none">+</span>
                        <span>Place New Order</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Flash Notifications -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
                <div class="flex items-center space-x-3">
                    <span class="w-6 h-6 rounded-full bg-emerald-200 text-emerald-800 flex items-center justify-center text-xs font-black">✓</span>
                    <span class="text-xs font-bold">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold uppercase">Dismiss</button>
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-2xs">
                <div class="flex items-center space-x-3">
                    <span class="w-6 h-6 rounded-full bg-rose-200 text-rose-800 flex items-center justify-center text-xs font-black">!</span>
                    <span class="text-xs font-bold">{{ session('error') ?? $errors->first() }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-xs font-bold uppercase">Dismiss</button>
            </div>
        @endif

        <!-- FILTER & SEARCH TOOLBAR -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
            <form method="GET" action="{{ route('jobs.index') }}" class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">

                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[260px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by Ticket #, Customer Name, or Phone..."
                               class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 text-xs font-bold focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Date Range Dropdown -->
                    <div class="w-44">
                        <select name="date_range" onchange="this.form.submit()"
                                class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 py-2">
                            <option value="">All Time</option>
                            <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>📅 Today</option>
                            <option value="this_week" {{ request('date_range') === 'this_week' ? 'selected' : '' }}>📅 This Week</option>
                            <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>📅 This Month</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition">
                            Apply Filter
                        </button>
                        @if(request()->hasAny(['search', 'status', 'date_range']))
                            <a href="{{ route('jobs.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                                Reset
                            </a>
                        @endif
                    </div>

                </div>

                <!-- Workflow Status Pill Filters -->
                <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-slate-100">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mr-1.5">Status:</span>
                    @php
                        $currentStatus = request('status', 'all');
                        $statusTabs = [
                            'all'         => 'All Orders',
                            'received'    => 'Intake / Received',
                            'in_progress' => 'Washing Bay',
                            'ready'       => 'Ready for Pickup',
                            'collected'   => 'Collected'
                        ];
                    @endphp
                    @foreach($statusTabs as $val => $label)
                        <a href="{{ route('jobs.index', array_merge(request()->query(), ['status' => $val])) }}"
                           class="px-3 py-1 rounded-xl text-xs font-bold transition {{ $currentStatus === $val ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </form>
        </div>

        <!-- BULK ACTIONS FLOATING TOOLBAR -->
        <div x-show="selectedJobs.length > 0" style="display: none;"
             class="p-4 bg-slate-900 text-white rounded-2xl shadow-xl flex flex-wrap items-center justify-between gap-4 transition">
            <div class="flex items-center space-x-3">
                <span class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-black font-mono" x-text="selectedJobs.length"></span>
                <span class="text-xs font-bold">Orders Selected</span>
            </div>

            <form action="{{ route('jobs.bulk-action') }}" method="POST" class="flex items-center space-x-2">
                @csrf
                <template x-for="id in selectedJobs" :key="id">
                    <input type="hidden" name="job_ids[]" :value="id">
                </template>

                <select name="action" required class="bg-slate-800 border-slate-700 text-white text-xs font-bold rounded-xl py-1.5 px-3">
                    <option value="">-- Choose Bulk Action --</option>
                    <option value="mark_ready">Mark Selected as Ready (Vacates Equipment)</option>
                    <option value="export_selected">Export Selected to CSV</option>
                </select>

                <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    Execute
                </button>
            </form>
        </div>

        <!-- Orders Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-black text-xs">📋</span>
                    <h3 class="text-sm font-black text-slate-900">Orders Roster</h3>
                </div>
                <span class="text-[11px] bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md font-semibold">
                    Showing {{ $jobs->firstItem() ?? 0 }} - {{ $jobs->lastItem() ?? 0 }} of {{ $jobs->total() }} Total
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                            <th class="py-3 px-4 w-10 text-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                       class="rounded text-blue-600 focus:ring-0 border-slate-300">
                            </th>
                            <th class="py-3 px-4">Ticket #</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Equipment</th>
                            <th class="py-3 px-4">Financial Ledger</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($jobs as $job)
                            @php
                                $total = $job->total_price;
                                $paid = $job->paid_amount;
                                $balance = $job->balance_due;
                                $isPaid = $job->isFullyPaid();

                                $rawStatus = is_object($job->status)
                                    ? ($job->status->value ?? $job->status->name ?? 'RECEIVED')
                                    : ($job->status ?? 'RECEIVED');
                                $status = strtoupper((string)$rawStatus);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 text-center">
                                    <input type="checkbox" value="{{ $job->id }}" x-model="selectedJobs"
                                           class="job-checkbox rounded text-blue-600 focus:ring-0 border-slate-300">
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-black text-blue-600">{{ $job->job_number }}</span>
                                    <div class="text-[10px] text-slate-400">{{ $job->created_at?->format('d M, h:i A') }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $job->customer?->name ?? 'Walk-in Client' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $job->customer?->phone ?? '—' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($job->machine)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                            ⚡ {{ $job->machine->name }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-slate-900">KSh {{ number_format($total, 2) }}</div>
                                    <div class="text-[10px] font-semibold {{ $isPaid ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $isPaid ? '✓ M-Pesa Cleared' : 'Due: KSh ' . number_format($balance, 2) }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($status === 'COLLECTED')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-700">Collected</span>
                                    @elseif($status === 'READY')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800">Ready</span>
                                    @elseif(in_array($status, ['IN_PROGRESS', 'PROCESSING', 'WASHING', 'DRYING']))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-800 animate-pulse">Washing Bay</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800">Intake</span>
                                    @endif

                                    @if($job->rack_location && in_array($status, ['READY', 'COLLECTED']))
                                        <div class="mt-1 text-[10px] font-mono font-bold text-emerald-700 flex items-center gap-0.5">
                                            <span>📍</span>
                                            <span>{{ $job->rack_location }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('jobs.show', $job) }}" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition inline-block">
                                        View
                                    </a>
                                    <a href="{{ route('jobs.receipt', $job) }}" target="_blank" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-600 transition inline-block">
                                        Receipt
                                    </a>

                                    @if(Auth::user()?->isAdmin())
                                        <form action="{{ route('jobs.destroy', $job) }}" method="POST" class="inline-block"
                                              onsubmit="return confirm('Are you sure you want to delete order #{{ $job->job_number }}? Equipment will be released.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-600 transition inline-block">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400 text-xs font-semibold">
                                    No orders match your search and filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Clean Pagination Links -->
            @if($jobs->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $jobs->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
