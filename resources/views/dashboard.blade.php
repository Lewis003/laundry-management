<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto">

        <!-- ==================== EXECUTIVE HEADER ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                        Welcome back, {{ auth()->user()->name }}
                    </h1>
                    @php
                        $role = auth()->user()->role ?? 'cashier';
                        $roleColors = [
                            'admin'    => 'bg-purple-50 text-purple-700 border-purple-200',
                            'manager'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'cashier'  => 'bg-blue-50 text-blue-700 border-blue-200',
                            'operator' => 'bg-amber-50 text-amber-700 border-amber-200',
                        ];
                        $roleBadgeColor = $roleColors[$role] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                    @endphp
                    <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase rounded-full border {{ $roleBadgeColor }}">
                        {{ $role }}
                    </span>
                </div>
                @php
                    $shop = \App\Models\ShopSetting::current();
                @endphp
                <p class="text-xs text-slate-500 font-medium">
                    Commercial Operations Dashboard • {{ $shop->shop_name }} ({{ $shop->location }})
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if(auth()->user()->canCreateIntake())
                <a href="{{ route('jobs.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition gap-1.5 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ New Order Intake</span>
                </a>
                @elseif(auth()->user()->isOperator() && Route::has('machines.index'))
                <a href="{{ route('machines.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition gap-1.5 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Wash Bay Machines</span>
                </a>
                @endif
                <span class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold text-slate-600 shadow-inner">
                    📅 {{ date('D, d M Y') }}
                </span>
            </div>
        </div>

        <!-- ==================== ROLE-TAILORED KPI METRICS ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            @if(auth()->user()->isOperator())
                <!-- OPERATOR METRIC 1: FLEET TOTAL -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Fleet</div>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalMachines }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Commercial Units</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-lg">
                        ⚙️
                    </div>
                </div>

                <!-- OPERATOR METRIC 2: AVAILABLE MACHINES -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Available Now</div>
                        <div class="text-2xl font-black text-emerald-600 mt-1">{{ $availableMachines }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Ready for Assignment</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-lg">
                        ✓
                    </div>
                </div>

                <!-- OPERATOR METRIC 3: IN USE -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Cycles Running</div>
                        <div class="text-2xl font-black text-indigo-600 mt-1">{{ $inUseMachines }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Wash / Dry in-progress</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-lg">
                        🔄
                    </div>
                </div>

                <!-- OPERATOR METRIC 4: PENDING QUEUE -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Pending Orders</div>
                        <div class="text-2xl font-black text-amber-600 mt-1">{{ $pendingTransactions }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Awaiting Washing</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-lg">
                        🧺
                    </div>
                </div>

            @elseif(!auth()->user()->canViewRevenue())
                <!-- CASHIER METRIC 1: TODAY'S ORDERS -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Today's Intake</div>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ $todayOrdersCount }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Orders recorded today</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-lg">
                        📝
                    </div>
                </div>

                <!-- CASHIER METRIC 2: CLIENT CRM -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Registered Clients</div>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ $customersCount }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Customer phone directory</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-black text-lg">
                        👥
                    </div>
                </div>

                <!-- CASHIER METRIC 3: IN PROGRESS -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Pending Orders</div>
                        <div class="text-2xl font-black text-amber-600 mt-1">{{ $pendingTransactions }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Under wash / dry cycle</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-lg">
                        ⏳
                    </div>
                </div>

                <!-- CASHIER METRIC 4: READY FOR PICKUP -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Ready for Pickup</div>
                        <div class="text-2xl font-black text-emerald-600 mt-1">{{ $readyOrdersCount }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Awaiting customer collection</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-lg">
                        ✓
                    </div>
                </div>

            @else
                <!-- EXECUTIVE / MANAGER METRIC 1: TOTAL VOLUME -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Orders</div>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalTransactions }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $pendingTransactions }} currently in progress</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-lg">
                        🧾
                    </div>
                </div>

                <!-- EXECUTIVE / MANAGER METRIC 2: CLIENT BASE -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Customers</div>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ $customersCount }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Registered accounts</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-black text-lg">
                        👥
                    </div>
                </div>

                <!-- EXECUTIVE / MANAGER METRIC 3: REVENUE (THIS YEAR) -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Income ({{ date('Y') }})</div>
                        <div class="text-2xl font-black text-emerald-700 mt-1">KSh {{ number_format($incomeThisYear, 2) }}</div>
                        <div class="text-xs text-emerald-600 font-semibold mt-0.5">100% Lipa Na M-Pesa</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-lg">
                        💰
                    </div>
                </div>

                <!-- EXECUTIVE / MANAGER METRIC 4: NET PROFIT -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Net Profit ({{ date('Y') }})</div>
                        <div class="text-2xl font-black {{ $profitThisYear >= 0 ? 'text-indigo-700' : 'text-rose-600' }} mt-1">
                            KSh {{ number_format($profitThisYear, 2) }}
                        </div>
                        <div class="text-xs font-bold {{ $profitThisYear >= 0 ? 'text-indigo-600' : 'text-rose-600' }} mt-0.5">
                            {{ $profitThisYear >= 0 ? '✓ Profitable Margin' : '⚠️ Operating Loss' }}
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl {{ $profitThisYear >= 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center font-black text-lg">
                        📈
                    </div>
                </div>
            @endif

        </div>

        <!-- ==================== OPERATIONS & FINANCIAL LEDGER TABLE ==================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Operations & Departmental Ledger</h2>
                    <p class="text-xs text-slate-500">Live operational department indicators and real-time status summary</p>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full self-start sm:self-auto">
                    Year {{ date('Y') }} Consolidated
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-5">Metric Description</th>
                            <th class="py-3.5 px-5">Department</th>
                            <th class="py-3.5 px-5 text-right font-black">Live Value</th>
                            <th class="py-3.5 px-5">Operational Status</th>
                            <th class="py-3.5 px-5 text-center">Quick Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">

                        <!-- 1. Registered Staff (Visible only to Admin/Manager) -->
                        @if(auth()->user()->canManageStaff())
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5 font-bold text-slate-900 flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                                Registered Staff
                            </td>
                            <td class="py-4 px-5 text-slate-500">Human Resources</td>
                            <td class="py-4 px-5 text-right font-black text-slate-900 text-sm">
                                {{ $registeredEmployees }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-800">
                                    Active Employees
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <a href="{{ route('staff.index') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                    Manage Staff &rarr;
                                </a>
                            </td>
                        </tr>
                        @endif

                        <!-- 2. Total Customers (Visible to Cashier, Manager, Admin) -->
                        @if(auth()->user()->canManageCustomers())
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5 font-bold text-slate-900 flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                                Total Customers
                            </td>
                            <td class="py-4 px-5 text-slate-500">Client CRM</td>
                            <td class="py-4 px-5 text-right font-black text-slate-900 text-sm">
                                {{ $customersCount }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700">
                                    Client Accounts
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <a href="{{ route('customers.index') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                    View Customers &rarr;
                                </a>
                            </td>
                        </tr>
                        @endif

                        <!-- 3. Total Transactions (Visible to all) -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5 font-bold text-slate-900 flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                Total Transactions
                            </td>
                            <td class="py-4 px-5 text-slate-500">Orders Desk</td>
                            <td class="py-4 px-5 text-right font-black text-slate-900 text-sm">
                                {{ $totalTransactions }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700">
                                    Tickets Issued
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <a href="{{ route('jobs.index') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                    Open Orders &rarr;
                                </a>
                            </td>
                        </tr>

                        <!-- 4. Pending Queue (Visible to all) -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5 font-bold text-slate-900 flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                Pending Queue
                            </td>
                            <td class="py-4 px-5 text-slate-500">Wash Bay Operations</td>
                            <td class="py-4 px-5 text-right font-black text-amber-700 text-sm">
                                {{ $pendingTransactions }}
                            </td>
                            <td class="py-4 px-5">
                                @if($pendingTransactions > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                    ⚠️ In-Process / Awaiting Pickup
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                    ✓ Queue Cleared
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-center">
                                <a href="{{ route('jobs.index', ['status' => 'received']) }}" class="font-bold text-amber-600 hover:text-amber-700 hover:underline">
                                    Process Queue &rarr;
                                </a>
                            </td>
                        </tr>

                        <!-- 5. Income (This Year) - ONLY for users with canViewRevenue() -->
                        @if(auth()->user()->canViewRevenue())
                        <tr class="hover:bg-slate-50 transition bg-emerald-50/20">
                            <td class="py-4 px-5 font-bold text-slate-900 flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                Income (This Year)
                            </td>
                            <td class="py-4 px-5 text-slate-500">Revenue Ledger</td>
                            <td class="py-4 px-5 text-right font-black text-emerald-700 text-sm">
                                KSh {{ number_format($incomeThisYear, 2) }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                    100% Lipa Na M-Pesa
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <a href="{{ route('reports.index') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                    Sales Report &rarr;
                                </a>
                            </td>
                        </tr>
                        @endif

                        <!-- 6. Expenditure (This Year) - ONLY for users with canViewRevenue() or canManageExpenses() -->
                        @if(auth()->user()->canViewRevenue() || auth()->user()->canManageExpenses())
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5 font-bold text-slate-900 flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                Expenditure (This Year)
                            </td>
                            <td class="py-4 px-5 text-slate-500">Cost Accounting</td>
                            <td class="py-4 px-5 text-right font-black text-rose-600 text-sm">
                                KSh {{ number_format($expenditureThisYear, 2) }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700">
                                    Water, Power, Wages & Supplies
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <a href="{{ route('expenses.index') }}" class="font-bold text-rose-600 hover:text-rose-700 hover:underline">
                                    View Expenses &rarr;
                                </a>
                            </td>
                        </tr>
                        @endif

                        <!-- 7. Net Profit (This Year) - ONLY for users with canViewRevenue() -->
                        @if(auth()->user()->canViewRevenue())
                        <tr class="hover:bg-slate-50 transition font-black bg-slate-50/80">
                            <td class="py-4 px-5 font-black text-slate-900 flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                Net Profit (This Year)
                            </td>
                            <td class="py-4 px-5 text-slate-500 font-semibold">P&L Margin</td>
                            <td class="py-4 px-5 text-right font-black {{ $profitThisYear >= 0 ? 'text-indigo-700' : 'text-rose-600' }} text-base">
                                KSh {{ number_format($profitThisYear, 2) }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $profitThisYear >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $profitThisYear >= 0 ? '✓ Profitable Margin' : '⚠️ Operating Loss' }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <a href="{{ route('reports.index') }}" class="font-bold text-indigo-600 hover:text-indigo-700 hover:underline">
                                    P&L Statement &rarr;
                                </a>
                            </td>
                        </tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== QUICK SHORTCUT ACTIONS GRID ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @if(auth()->user()->isOperator())
                <!-- OPERATOR SHORTCUT 1 -->
                <a href="{{ route('machines.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-blue-300 hover:shadow-md transition flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-xl group-hover:bg-blue-600 group-hover:text-white transition">
                        ⚙️
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Machine Fleet</div>
                        <div class="text-xs text-slate-500">View wash bay equipment & cycles</div>
                    </div>
                </a>

                <!-- OPERATOR SHORTCUT 2 -->
                <a href="{{ route('jobs.index', ['status' => 'received']) }}" class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-amber-300 hover:shadow-md transition flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-xl group-hover:bg-amber-600 group-hover:text-white transition">
                        🧺
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Wash Bay Queue</div>
                        <div class="text-xs text-slate-500">Pick next job awaiting wash</div>
                    </div>
                </a>

                <!-- OPERATOR SHORTCUT 3 -->
                <a href="{{ route('jobs.index', ['status' => 'ready']) }}" class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-emerald-300 hover:shadow-md transition flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-xl group-hover:bg-emerald-600 group-hover:text-white transition">
                        ✓
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Ready for Collection</div>
                        <div class="text-xs text-slate-500">Jobs washed, dried & pressed</div>
                    </div>
                </a>

            @else
                <!-- CASHIER / MANAGER SHORTCUT 1 -->
                @if(auth()->user()->canCreateIntake())
                <a href="{{ route('jobs.create') }}" class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-emerald-300 hover:shadow-md transition flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-xl group-hover:bg-emerald-600 group-hover:text-white transition">
                        +
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">New Order Intake</div>
                        <div class="text-xs text-slate-500">Accept drop-off & print ticket</div>
                    </div>
                </a>
                @endif

                <!-- CASHIER / MANAGER SHORTCUT 2 -->
                <a href="{{ route('jobs.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-blue-300 hover:shadow-md transition flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-xl group-hover:bg-blue-600 group-hover:text-white transition">
                        📋
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Orders Queue</div>
                        <div class="text-xs text-slate-500">Track orders & collection status</div>
                    </div>
                </a>

                <!-- CASHIER / MANAGER SHORTCUT 3 -->
                @if(auth()->user()->canManageCustomers())
                <a href="{{ route('customers.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-sky-300 hover:shadow-md transition flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-black text-xl group-hover:bg-sky-600 group-hover:text-white transition">
                        👥
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Customer Directory</div>
                        <div class="text-xs text-slate-500">View client phone & order histories</div>
                    </div>
                </a>
                @endif

            @endif
        </div>

    </div>
</x-app-layout>
