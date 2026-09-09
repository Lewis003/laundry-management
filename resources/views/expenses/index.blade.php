<x-app-layout>
    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-xl text-xs font-bold flex items-center justify-between">
                <span>✓ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-900 font-black">&times;</button>
            </div>
            @endif

            <!-- Top Bar -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Operational Expense Tracker</h1>
                    <p class="text-xs text-slate-500 font-medium">Log detergents, utilities, electricity, wages, and maintenance costs</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('expenses.export', request()->query()) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-emerald-400 text-xs font-bold rounded-lg transition shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV
                    </a>
                    <button onclick="document.getElementById('addExpenseModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        + Log Expense
                    </button>
                </div>
            </div>

            <!-- Expense Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-[11px] font-bold text-slate-500 uppercase">This Month Expenses</span>
                    <div class="text-2xl font-black text-rose-600 mt-1">KSh {{ number_format($thisMonthTotal, 2) }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Today's Spend</span>
                    <div class="text-2xl font-black text-slate-900 mt-1">KSh {{ number_format($todayTotal, 2) }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Highest Cost Center</span>
                    <div class="text-2xl font-black text-amber-600 mt-1">
                        {{ $topCategory ? ucfirst($topCategory->category) : 'None' }}
                    </div>
                </div>
            </div>

            <!-- Search, Filters, Bulk Actions & DataTable -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <form method="GET" action="{{ route('expenses.index') }}" class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Search expense title or receipt code..."
                               class="w-full text-xs font-medium bg-white border border-slate-300 rounded-lg p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <select name="category" class="text-xs font-semibold bg-white border border-slate-300 rounded-lg p-2.5">
                        <option value="">All Categories</option>
                        <option value="water" {{ request('category') === 'water' ? 'selected' : '' }}>💧 Water & Borehole Delivery</option>
                        <option value="electricity" {{ request('category') === 'electricity' ? 'selected' : '' }}>⚡ Electricity & Power (KPLC)</option>
                        <option value="salaries" {{ request('category') === 'salaries' ? 'selected' : '' }}>👷 Staff Salaries & Wages</option>
                        <option value="supplies" {{ request('category') === 'supplies' ? 'selected' : '' }}>🧴 Detergents & Washing Supplies</option>
                        <option value="maintenance" {{ request('category') === 'maintenance' ? 'selected' : '' }}>🔧 Machine Repairs & Maintenance</option>
                        <option value="rent" {{ request('category') === 'rent' ? 'selected' : '' }}>🏢 Premises / Plant Rent</option>
                        <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>📦 Other Expenses</option>
                    </select>
                    <select name="date_filter" class="text-xs font-semibold bg-white border border-slate-300 rounded-lg p-2.5">
                        <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    </select>
                    <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800">Filter</button>
                    <a href="{{ route('expenses.index') }}" class="px-3 py-2.5 text-xs font-semibold text-slate-500 hover:text-slate-900">Reset</a>
                </form>

                <form method="POST" action="{{ route('expenses.bulk-action') }}">
                    @csrf
                    <div class="px-4 py-2.5 bg-slate-100 border-b border-slate-200 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-slate-700">Bulk Actions:</span>
                            <select name="bulk_action" class="text-xs bg-white border border-slate-300 rounded p-1.5 font-medium">
                                <option value="">Select Action</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <button type="submit" onclick="return confirm('Delete selected expense logs?')" class="px-3 py-1.5 bg-rose-700 text-white rounded font-bold hover:bg-rose-800">Apply</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                                    <th class="py-3 px-4 w-6"><input type="checkbox" onclick="document.querySelectorAll('.row-checkbox').forEach(c => c.checked = this.checked)"></th>
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Expense Title</th>
                                    <th class="py-3 px-4">Category</th>
                                    <th class="py-3 px-4">Payment & Ref</th>
                                    <th class="py-3 px-4 text-right">Amount</th>
                                    <th class="py-3 px-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @forelse($expenses as $expense)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-4"><input type="checkbox" name="selected_ids[]" value="{{ $expense->id }}" class="row-checkbox"></td>
                                    <td class="py-3 px-4 font-mono text-slate-600">{{ $expense->expense_date->format('d M Y') }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $expense->title }}</td>
                                    <td class="py-3 px-4">
                                        @php
                                            $catBadges = [
                                                'water'       => ['label' => 'Water & Borehole', 'class' => 'bg-cyan-50 text-cyan-700 border-cyan-200', 'icon' => '💧'],
                                                'electricity' => ['label' => 'Electricity / Power', 'class' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => '⚡'],
                                                'salaries'    => ['label' => 'Staff Salaries', 'class' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => '👷'],
                                                'supplies'    => ['label' => 'Detergents & Supplies', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => '🧴'],
                                                'maintenance' => ['label' => 'Maintenance & Repairs', 'class' => 'bg-orange-50 text-orange-700 border-orange-200', 'icon' => '🔧'],
                                                'rent'        => ['label' => 'Premises Rent', 'class' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => '🏢'],
                                                'other'       => ['label' => 'Other Costs', 'class' => 'bg-slate-100 text-slate-700 border-slate-200', 'icon' => '📦'],
                                                'utilities'   => ['label' => 'Water & Electricity', 'class' => 'bg-teal-50 text-teal-700 border-teal-200', 'icon' => '💡'],
                                            ];
                                            $catInfo = $catBadges[$expense->category] ?? ['label' => ucfirst($expense->category), 'class' => 'bg-slate-100 text-slate-700 border-slate-200', 'icon' => '🏷️'];
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $catInfo['class'] }}">
                                            <span>{{ $catInfo['icon'] }}</span>
                                            <span>{{ $catInfo['label'] }}</span>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 uppercase font-mono text-[11px]">
                                        {{ $expense->payment_method }} {{ $expense->reference_code ? '• ' . $expense->reference_code : '' }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-rose-600">
                                        KSh {{ number_format($expense->amount, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" onclick="if(confirm('Delete this expense?')) document.getElementById('delete-expense-{{ $expense->id }}').submit();" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Delete</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 font-medium">No expenses logged for this filter.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>

                @if($expenses->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $expenses->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Hidden Delete Forms -->
    @foreach($expenses as $expense)
    <form id="delete-expense-{{ $expense->id }}" action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    @endforeach

    <!-- Add Expense Modal -->
    <div id="addExpenseModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-base font-bold text-slate-900">Record Operational Expense</h3>
                <button onclick="document.getElementById('addExpenseModal').classList.add('hidden')" class="text-slate-400 font-bold text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('expenses.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Expense Description</label>
                    <input type="text" name="title" required placeholder="e.g. 50L Liquid Industrial Detergent" class="w-full rounded-lg border-slate-300 p-2.5">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Category</label>
                        <select name="category" class="w-full rounded-lg border-slate-300 p-2.5">
                            <option value="water">💧 Water & Borehole Delivery</option>
                            <option value="electricity">⚡ Electricity & Power (KPLC)</option>
                            <option value="salaries">👷 Staff Salaries & Wages</option>
                            <option value="supplies">🧴 Detergents & Washing Supplies</option>
                            <option value="maintenance">🔧 Machine Repairs & Maintenance</option>
                            <option value="rent">🏢 Premises / Plant Rent</option>
                            <option value="other">📦 Other Operations</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Amount (KSh)</label>
                        <input type="number" step="1" name="amount" required placeholder="4500" class="w-full rounded-lg border-slate-300 p-2.5">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Date</label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border-slate-300 p-2.5">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full rounded-lg border-slate-300 p-2.5">
                            <option value="mpesa">Lipa Na M-Pesa</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash / Petty</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">M-Pesa / Invoice Reference</label>
                    <input type="text" name="reference_code" placeholder="e.g. QHD728919" class="w-full rounded-lg border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Vendor details or extra remarks..." class="w-full rounded-lg border-slate-300 p-2.5"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="document.getElementById('addExpenseModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 font-bold rounded-lg text-slate-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 font-bold rounded-lg text-white">Record Expense</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
