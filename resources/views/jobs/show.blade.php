<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-6 pb-24">

        <!-- Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('jobs.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-900 transition mb-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Order Queue
                </a>
                <div class="flex items-center space-x-3">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Order #{{ $job->job_number }}</h2>
                    <span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2.5 py-0.5 rounded-lg">
                        {{ $job->created_at->format('M d, Y h:i A') }}
                    </span>
                </div>
            </div>
            @if(!auth()->user()->isOperator())
            <div class="flex items-center space-x-2">
                <a href="{{ route('jobs.receipt', $job) }}" target="_blank"
                   class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print 80mm Receipt
                </a>
            </div>
            @endif
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-bold flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs font-bold flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @php
            $statusVal = is_string($job->status) ? $job->status : ($job->status->value ?? 'received');
            $steps = ['received' => 1, 'in_progress' => 2, 'ready' => 3, 'collected' => 4];
            $currentStep = $steps[$statusVal] ?? 1;
            $percentPaid = $job->total_price > 0 ? min(100, round(($job->paid_amount / $job->total_price) * 100)) : 100;
        @endphp

        <!-- Visual Stepper -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
            <div class="grid grid-cols-4 gap-2 text-center">
                <div class="space-y-1">
                    <div class="h-2 rounded-full {{ $currentStep >= 1 ? 'bg-blue-600' : 'bg-slate-200' }}"></div>
                    <span class="text-[11px] font-black {{ $currentStep >= 1 ? 'text-blue-600' : 'text-slate-400' }}">1. Received</span>
                </div>
                <div class="space-y-1">
                    <div class="h-2 rounded-full {{ $currentStep >= 2 ? 'bg-amber-500' : 'bg-slate-200' }}"></div>
                    <span class="text-[11px] font-black {{ $currentStep >= 2 ? 'text-amber-500' : 'text-slate-400' }}">2. Washing</span>
                </div>
                <div class="space-y-1">
                    <div class="h-2 rounded-full {{ $currentStep >= 3 ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                    <span class="text-[11px] font-black {{ $currentStep >= 3 ? 'text-emerald-600' : 'text-slate-400' }}">3. Ready</span>
                </div>
                <div class="space-y-1">
                    <div class="h-2 rounded-full {{ $currentStep >= 4 ? 'bg-slate-800' : 'bg-slate-200' }}"></div>
                    <span class="text-[11px] font-black {{ $currentStep >= 4 ? 'text-slate-800' : 'text-slate-400' }}">4. Collected</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN: WORKFLOW & INTAKE ITEMS -->
            <div class="lg:col-span-6 space-y-6">

                <!-- WORKFLOW EXECUTION -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Wash Bay Workflow</h3>
                        <span class="text-[11px] font-bold text-slate-400">Step {{ $currentStep }} of 4</span>
                    </div>

                    @if($statusVal === 'received')
                        <!-- Step 1: Assign Equipment -->
                        <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3">
                            <div>
                                <div class="text-xs font-black text-slate-800">Assign Machine & Start Cycle</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Select an available commercial washer or dryer to lock and start washing.</p>
                            </div>
                            @if(auth()->user()->canOperateMachines())
                            <form action="{{ route('jobs.assign-machine', $job) }}" method="POST" class="space-y-3">
                                @csrf
                                <select name="machine_id" required class="w-full rounded-xl border-slate-200 text-xs font-bold bg-white focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Choose Equipment --</option>
                                    @foreach($machines as $machine)
                                        @php
                                            $isAvail = (bool) ($machine->is_available ?? ($machine->status === 'available'));
                                        @endphp
                                        <option value="{{ $machine->id }}">
                                            {{ $machine->name }} ({{ $machine->capacity_kg ?? 18 }}kg) — {{ $isAvail ? 'Available' : 'Busy' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span>Lock Equipment & Start Wash</span>
                                </button>
                            </form>
                            @else
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 font-semibold flex items-center gap-2">
                                <span>⚠️ Machine assignment is executed by wash bay operators.</span>
                            </div>
                            @endif
                        </div>
                    @elseif($statusVal === 'in_progress')
                        <!-- Step 2: Wash In Progress -->
                        <div class="p-5 bg-amber-50/80 rounded-2xl border border-amber-200 space-y-4">
                            <div class="flex items-center space-x-3">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                </span>
                                <div>
                                    <div class="text-xs font-black text-amber-900">Cycle Active in {{ $job->machine?->name ?? 'Assigned Equipment' }}</div>
                                    <div class="text-[11px] text-amber-700">Machine is locked and cannot be double-booked.</div>
                                </div>
                            </div>

                            <form action="{{ route('jobs.mark-ready', $job) }}" method="POST" class="space-y-3 pt-2">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Shelving Rack / Storage Location <span class="text-amber-600 font-normal">(optional, e.g. Rack A-04)</span>
                                    </label>
                                    <input type="text" name="rack_location" value="{{ old('rack_location', $job->rack_location) }}" placeholder="e.g. Rack A-04, Shelf 2B"
                                           class="w-full text-xs font-bold font-mono rounded-xl border-slate-300 p-2.5 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                </div>
                                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Unlock Machine, Package & Mark Ready</span>
                                </button>
                            </form>
                        </div>
                    @elseif($statusVal === 'ready')
                        <!-- Step 3: Ready for Pickup -->
                        <div class="p-5 bg-emerald-50/80 rounded-2xl border border-emerald-200 space-y-4">
                            <div>
                                <div class="text-xs font-black text-emerald-900">Garments Cleaned & Ready for Pickup</div>
                                <p class="text-[11px] text-emerald-700 mt-0.5">Awaiting customer collection at front counter.</p>
                            </div>

                            @if($job->rack_location)
                                <div class="p-3.5 bg-white rounded-xl border border-emerald-300 flex items-center justify-between shadow-2xs">
                                    <div class="flex items-center space-x-2.5">
                                        <span class="text-base">📍</span>
                                        <div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase">Packaged Shelf Location</div>
                                            <div class="font-mono font-black text-emerald-800 text-sm">{{ $job->rack_location }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Shelved</span>
                                </div>
                            @endif

                            @if(!$job->isFullyPaid())
                                <div class="p-3 bg-white rounded-xl border border-amber-300 text-xs text-amber-800 font-bold flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Remaining M-Pesa balance of KSh {{ number_format($job->balance_due, 2) }} must be received before handover.</span>
                                </div>
                            @else
                                <form action="{{ route('jobs.mark-collected', $job) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Confirm Handover & Complete Order</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                        <!-- Step 4: Finished -->
                        <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200 text-center space-y-2">
                            <div class="text-xs font-black text-slate-800">Order Completed & Collected</div>
                            <p class="text-[11px] text-slate-500">Garments handed over to customer. M-Pesa ledger reconciled.</p>
                            @if($job->rack_location)
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-700">
                                    <span>📍 Retrieved from:</span>
                                    <span class="text-emerald-700">{{ $job->rack_location }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- INTAKE ITEMS -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Garments Intake List</h3>
                        <span class="text-[11px] font-bold text-slate-500">{{ $job->items->sum('quantity') }} items</span>
                    </div>

                    <div class="divide-y divide-slate-100 text-xs">
                        @foreach($job->items as $item)
                            <div class="py-3 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-slate-600 text-xs">
                                        {{ $item->quantity }}&times;
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $item->service?->name ?? 'Laundry Service' }}</div>
                                        @if(!auth()->user()->isOperator())
                                            <div class="text-[11px] text-slate-400">@ KSh {{ number_format($item->unit_price, 2) }} each</div>
                                        @else
                                            <div class="text-[11px] text-slate-400">Wash Bay Processing Item</div>
                                        @endif
                                    </div>
                                </div>
                                @if(!auth()->user()->isOperator())
                                <div class="font-mono font-bold text-slate-900">
                                    KSh {{ number_format($item->subtotal, 2) }}
                                </div>
                                @else
                                <div class="text-xs font-bold text-slate-400">
                                    Qty: {{ $item->quantity }}
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($job->notes)
                        <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200 text-xs text-slate-600">
                            <span class="font-bold text-slate-800">Garment Tag / Notes:</span> {{ $job->notes }}
                        </div>
                    @endif
                </div>

                <!-- CUSTOMER CARD (Masked for Laundry Operators) -->
                @if(!auth()->user()->isOperator())
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Customer Details</h3>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ $job->customer?->name ?? 'Walk-in' }}</div>
                            <div class="font-mono text-xs text-slate-500 mt-0.5">{{ $job->customer?->phone ?? 'No phone registered' }}</div>
                        </div>
                        <a href="tel:{{ $job->customer?->phone }}" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-slate-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </a>
                    </div>
                </div>
                @endif

            </div>

            <!-- RIGHT COLUMN: FINANCIAL LEDGER & M-PESA TERMINAL (OR OPERATOR WORKBENCH) -->
            <div class="lg:col-span-6 space-y-6">

                @if(!auth()->user()->isOperator())
                <!-- FINANCIAL SUMMARY CARD -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-slate-400">Order Financials</span>
                            <div class="text-lg font-black text-slate-900 mt-0.5">KSh {{ number_format($job->total_price, 2) }}</div>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full {{ $job->isFullyPaid() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ strtoupper($job->payment_status) }}
                        </span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-xs font-bold">
                            <span class="text-slate-500">Payment Progress</span>
                            <span class="text-slate-900 font-mono">{{ $percentPaid }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $percentPaid }}%"></div>
                        </div>
                    </div>

                    <!-- Breakdown Table -->
                    <div class="space-y-2 text-xs divide-y divide-slate-50">
                        <div class="flex justify-between py-1 text-slate-600">
                            <span>Taxable Base (Net Cost)</span>
                            <span class="font-mono font-bold text-slate-900">KSh {{ number_format($job->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-slate-600">
                            <span>16% VAT (Inclusive)</span>
                            <span class="font-mono font-bold text-slate-900">
                                {{ $job->tax > 0 ? 'KSh ' . number_format($job->tax, 2) : 'Exempt (KSh 0.00)' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1 text-slate-700 font-bold">
                            <span>Total Payable (VAT Incl.)</span>
                            <span class="font-mono font-black text-slate-900">KSh {{ number_format($job->total_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-slate-600">
                            <span>Total M-Pesa Paid</span>
                            <span class="font-mono font-bold text-emerald-600">KSh {{ number_format($job->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-sm font-black bg-slate-50 p-3 rounded-2xl">
                            <span class="text-slate-700">Remaining Balance</span>
                            <span class="font-mono {{ $job->balance_due > 0 ? 'text-amber-600 text-base' : 'text-emerald-600' }}">
                                KSh {{ number_format($job->balance_due, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- PAYMENT TRANSACTION HISTORY -->
                @if($job->payments->count() > 0)
                    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">M-Pesa Payment History</h3>
                        <div class="divide-y divide-slate-100 text-xs">
                            @foreach($job->payments as $payment)
                                <div class="py-2.5 flex items-center justify-between">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="p-2 rounded-xl bg-emerald-50 text-emerald-700 font-black text-[10px]">
                                            M-PESA
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800">{{ $payment->reference }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $payment->created_at->format('d M, h:i A') }}</div>
                                        </div>
                                    </div>
                                    <div class="font-mono font-black text-emerald-600 text-sm">
                                        +KSh {{ number_format($payment->amount, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- DEDICATED M-PESA RECORDING TERMINAL -->
                @if($job->balance_due > 0)
                    <div x-data="{ amount: {{ $job->balance_due }} }"
                         class="bg-white p-6 rounded-3xl border-2 border-emerald-500 shadow-sm space-y-5">

                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Lipa Na M-Pesa Console</h3>
                            </div>
                            <span class="text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-md">
                                Cashless Policy
                            </span>
                        </div>

                        <form action="{{ route('jobs.collect-payment', $job) }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="payment_method" value="mpesa">

                            <!-- Prompt for amount -->
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-[11px] font-bold text-slate-600">M-Pesa Amount to Record</label>
                                    <button type="button" @click="amount = {{ $job->balance_due }}"
                                            class="text-[10px] font-bold text-emerald-600 hover:underline">
                                        Exact Balance (KSh {{ number_format($job->balance_due, 2) }})
                                    </button>
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-2.5 font-bold text-slate-400 text-sm">KSh</span>
                                    <input type="number" step="0.01" min="1" max="{{ $job->balance_due }}" name="amount"
                                           x-model.number="amount" required
                                           class="w-full pl-14 pr-4 py-2.5 rounded-2xl border-slate-200 text-base font-mono font-black focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50">
                                </div>
                            </div>

                            <!-- Submit M-Pesa Record -->
                            <button type="submit"
                                    :disabled="amount <= 0 || amount > {{ $job->balance_due }}"
                                    class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-black text-xs rounded-2xl shadow-md transition flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>Confirm M-Pesa Receipt (KSh <span x-text="Number(amount || 0).toFixed(2)"></span>)</span>
                            </button>
                        </form>
                    </div>
                @elseif($job->isFullyPaid())
                    <!-- Settled Seal -->
                    <div class="p-6 bg-emerald-50 rounded-3xl border border-emerald-200 text-center space-y-2">
                        <div class="inline-flex p-3 rounded-full bg-emerald-100 text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-black text-emerald-950">M-Pesa Settled in Full</h4>
                        <p class="text-xs text-emerald-700">No outstanding balance due. Ready for collection.</p>
                    </div>
                @endif
                @else
                <!-- OPERATIONAL BAY WORKBENCH FOR LAUNDRY OPERATORS -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-slate-400">Wash Bay Processing</span>
                            <div class="text-lg font-black text-slate-900 mt-0.5">Order #{{ $job->job_number }}</div>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                            {{ strtoupper($statusVal) }}
                        </span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                            <div class="font-bold text-slate-800">Equipment Allocation:</div>
                            <div class="text-slate-700 font-mono text-sm font-black">
                                {{ $job->machine ? '⚡ ' . $job->machine->name . ' (' . ($job->machine->capacity_kg ?? 18) . 'kg)' : '⚠️ Not yet allocated' }}
                            </div>
                        </div>

                        @if($job->rack_location)
                        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 space-y-1">
                            <div class="font-bold text-emerald-900">Shelved Rack Location:</div>
                            <div class="font-mono font-black text-emerald-700 text-sm">📍 {{ $job->rack_location }}</div>
                        </div>
                        @endif

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                            <div class="font-bold text-slate-800">Total Garment Items:</div>
                            <div class="text-slate-900 font-black text-lg">{{ $job->items->sum('quantity') }} items</div>
                        </div>

                        @if($job->notes)
                        <div class="p-4 bg-amber-50/80 rounded-2xl border border-amber-200 space-y-1">
                            <div class="font-bold text-amber-900">Special Handling Instructions:</div>
                            <div class="text-amber-800 text-xs">{{ $job->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>

        </div>

    </div>
</x-app-layout>
