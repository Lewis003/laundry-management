<x-app-layout>
    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Top Header & Period Filter -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Financial Reports & P&L Statement</h1>
                    <p class="text-xs text-slate-500 font-medium">Sales Revenue vs Operational Expenses & Net Margin Analysis</p>
                </div>
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('reports.index') }}" class="flex items-center gap-2">
                        <select name="period" onchange="this.form.submit()" class="text-xs font-semibold bg-slate-50 border border-slate-300 rounded-lg p-2.5">
                            <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </form>
                    <a href="{{ route('reports.export-pnl') }}" class="px-4 py-2 bg-slate-900 text-emerald-400 hover:bg-slate-800 text-xs font-bold rounded-lg transition shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export P&L CSV
                    </a>
                </div>
            </div>

            <!-- P&L 3-Card Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gross Sales Revenue</span>
                    <div class="text-2xl font-black text-emerald-600 mt-2">KSh {{ number_format($totalRevenue, 2) }}</div>
                    <p class="text-xs text-slate-500 mt-1 font-medium">{{ $ordersCount }} Total Orders Intake</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Operational Expenses</span>
                    <div class="text-2xl font-black text-rose-600 mt-2">KSh {{ number_format($totalExpenses, 2) }}</div>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Detergents, Power, Wages & Repairs</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Net Operating Profit</span>
                    <div class="text-2xl font-black {{ $netProfit >= 0 ? 'text-indigo-700' : 'text-rose-600' }} mt-2">
                        KSh {{ number_format($netProfit, 2) }}
                    </div>
                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-[11px] font-bold {{ $profitMargin >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $profitMargin }}% Profit Margin
                    </span>
                </div>
            </div>

            <!-- Visual Breakdown Section: "Where Does Company Money Go?" -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Expenses by Category Table -->
                <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Where Does the Company Money Go?</h2>
                            <p class="text-[11px] text-slate-500 font-medium">Categorical operational outflow breakdown for {{ $periodLabel }}</p>
                        </div>
                        <span class="text-xs font-mono font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                            Total: KSh {{ number_format($totalExpenses, 2) }}
                        </span>
                    </div>

                    <div class="space-y-3.5">
                        @php
                            $catMeta = [
                                'water'       => ['label' => 'Water & Borehole Delivery', 'color' => 'bg-cyan-500', 'textColor' => 'text-cyan-700', 'bgLight' => 'bg-cyan-50 border-cyan-200', 'icon' => '💧'],
                                'electricity' => ['label' => 'Electricity & Power (KPLC)', 'color' => 'bg-amber-500', 'textColor' => 'text-amber-700', 'bgLight' => 'bg-amber-50 border-amber-200', 'icon' => '⚡'],
                                'salaries'    => ['label' => 'Staff Salaries & Wages', 'color' => 'bg-purple-600', 'textColor' => 'text-purple-700', 'bgLight' => 'bg-purple-50 border-purple-200', 'icon' => '👷'],
                                'supplies'    => ['label' => 'Detergents & Washing Supplies', 'color' => 'bg-emerald-500', 'textColor' => 'text-emerald-700', 'bgLight' => 'bg-emerald-50 border-emerald-200', 'icon' => '🧴'],
                                'maintenance' => ['label' => 'Machine Maintenance & Repairs', 'color' => 'bg-orange-500', 'textColor' => 'text-orange-700', 'bgLight' => 'bg-orange-50 border-orange-200', 'icon' => '🔧'],
                                'rent'        => ['label' => 'Premises / Plant Rent', 'color' => 'bg-blue-600', 'textColor' => 'text-blue-700', 'bgLight' => 'bg-blue-50 border-blue-200', 'icon' => '🏢'],
                                'other'       => ['label' => 'Other Costs', 'color' => 'bg-slate-500', 'textColor' => 'text-slate-700', 'bgLight' => 'bg-slate-100 border-slate-200', 'icon' => '📦'],
                                'utilities'   => ['label' => 'Water & Electricity (Combined)', 'color' => 'bg-teal-500', 'textColor' => 'text-teal-700', 'bgLight' => 'bg-teal-50 border-teal-200', 'icon' => '💡'],
                            ];
                        @endphp

                        @forelse($expenseBreakdown as $exp)
                        @php
                            $raw = strtolower($exp['raw_category'] ?? strtolower($exp['category']));
                            $meta = $catMeta[$raw] ?? ['label' => $exp['category'], 'color' => 'bg-slate-600', 'textColor' => 'text-slate-700', 'bgLight' => 'bg-slate-100 border-slate-200', 'icon' => '🏷️'];
                            $pctExpense = $totalExpenses > 0 ? round(($exp['total'] / $totalExpenses) * 100, 1) : 0;
                            $pctRevenue = $totalRevenue > 0 ? round(($exp['total'] / $totalRevenue) * 100, 1) : 0;
                        @endphp
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 font-bold text-slate-800">
                                    <span class="text-sm">{{ $meta['icon'] }}</span>
                                    <span>{{ $meta['label'] }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono font-black text-slate-900">KSh {{ number_format($exp['total'], 2) }}</span>
                                    <span class="text-[10px] font-bold text-slate-500 ml-1">({{ $pctExpense }}% of spend)</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                <div class="{{ $meta['color'] }} h-2 rounded-full transition-all duration-500" style="width: {{ $pctExpense }}%"></div>
                            </div>
                            <div class="flex justify-between text-[10px] text-slate-400 font-medium">
                                <span>Share of total operational cost</span>
                                <span>{{ $pctRevenue }}% of Gross Revenue</span>
                            </div>
                        </div>
                        @empty
                        <div class="py-8 text-center text-slate-400 text-xs font-medium">
                            No operational expense records logged for {{ $periodLabel }}.
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Executive P&L Snapshot -->
                <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between space-y-5">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-3">Executive P&L Summary</h2>
                        <div class="divide-y divide-slate-100 text-xs space-y-2">
                            <div class="pt-2 flex justify-between items-center">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="font-bold text-slate-700">Gross Sales Revenue</span>
                                </div>
                                <span class="font-mono font-black text-emerald-600 text-sm">KSh {{ number_format($totalRevenue, 2) }}</span>
                            </div>
                            <div class="pt-2 flex justify-between items-center">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span class="font-bold text-slate-700">Less: Total Company Outflows</span>
                                </div>
                                <span class="font-mono font-bold text-rose-600">- KSh {{ number_format($totalExpenses, 2) }}</span>
                            </div>

                            <div class="pt-3">
                                <div class="p-3.5 rounded-xl {{ $netProfit >= 0 ? 'bg-emerald-50 border border-emerald-200' : 'bg-rose-50 border border-rose-200' }} flex justify-between items-center">
                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-wider {{ $netProfit >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">Net Retained Profit</div>
                                        <div class="text-base font-black {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }} font-mono">
                                            KSh {{ number_format($netProfit, 2) }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black {{ $profitMargin >= 0 ? 'bg-emerald-200 text-emerald-900' : 'bg-rose-200 text-rose-900' }}">
                                            {{ $profitMargin }}% Margin
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-[11px] text-slate-500 leading-relaxed">
                        💡 <strong>Where does company money go?</strong> All revenue originates from cashless Lipa Na M-Pesa order settlements. Operational expenditures are deducted into separate utility, wage, supply, rent, and repair cost centers to maintain audit integrity.
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
