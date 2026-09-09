<x-app-layout>
    @php
        // Normalize service prices into true Kenyan Shillings
        $normalizedServices = $services->map(function($s) {
            $rawPrice = $s->price_in_cents ?? $s->price_cents ?? $s->price ?? 0;
            $price = ($rawPrice >= 10000) ? ($rawPrice / 100) : $rawPrice;
            return [
                'id'    => $s->id,
                'name'  => $s->name,
                'price' => (float)$price,
                'unit'  => $s->unit ?? 'Unit'
            ];
        });
    @endphp

    <div class="max-w-6xl mx-auto space-y-6 pb-20"
         x-data="{
            services: {{ Js::from($normalizedServices) }},
            items: [
                { service_id: '{{ $services->first()?->id ?? '' }}', quantity: 1 }
            ],
            paid_amount: 0,
            isNewCustomer: true,
            addItem() {
                this.items.push({
                    service_id: this.services[0]?.id ?? '',
                    quantity: 1
                });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            getItemPrice(serviceId) {
                let s = this.services.find(x => x.id == serviceId);
                return s ? Number(s.price) : 0;
            },
            getItemUnit(serviceId) {
                let s = this.services.find(x => x.id == serviceId);
                return s ? s.unit : 'Unit';
            },
            getItemTotal(item) {
                return this.getItemPrice(item.service_id) * Number(item.quantity || 0);
            },
            getSubtotal() {
                return this.items.reduce((sum, item) => sum + this.getItemTotal(item), 0);
            },
            setFullPayment() {
                this.paid_amount = this.getSubtotal();
            },
            setZeroPayment() {
                this.paid_amount = 0;
            }
         }">

        <!-- Top Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('jobs.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Orders Queue
                </a>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Commercial Laundry Order Intake</h2>
                <p class="text-xs text-slate-400 mt-0.5">Front Desk Cashier POS Terminal &bull; Industrial Area, Plant A</p>
            </div>
            <span class="text-xs bg-white text-slate-600 px-3.5 py-2 rounded-xl border border-slate-200 font-semibold shadow-2xs">
                📅 {{ now()->format('l, d M Y') }}
            </span>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs font-bold shadow-2xs">
                <div class="flex items-center space-x-2 mb-1">
                    <span class="w-4 h-4 bg-rose-200 text-rose-800 rounded-full flex items-center justify-center text-[10px]">!</span>
                    <span>Please correct the following:</span>
                </div>
                <ul class="list-disc pl-5 space-y-0.5 font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('jobs.store') }}" method="POST">
            @csrf

            <!-- Spacious 2-Column POS Layout (No Squeezing) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- LEFT COLUMN: Customer & Garment Services (7 Columns) -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- 1. Customer Identification Card -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-black text-xs">👤</span>
                                <h3 class="text-sm font-black text-slate-900">Customer Identification</h3>
                            </div>
                            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-xl">
                                <button type="button" @click="isNewCustomer = true"
                                    :class="isNewCustomer ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                    class="px-3 py-1 rounded-lg transition text-[11px]">
                                    New Walk-in
                                </button>
                                <button type="button" @click="isNewCustomer = false"
                                    :class="!isNewCustomer ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                    class="px-3 py-1 rounded-lg transition text-[11px]">
                                    Existing Client
                                </button>
                            </div>
                        </div>

                        <!-- Existing Client Select -->
                        <div x-show="!isNewCustomer" style="display: none;">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Client</label>
                            <select name="customer_id" class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 py-2.5 px-3">
                                <option value="">-- Choose registered customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} &bull; {{ $customer->phone ?? 'No phone' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- New Walk-in Inputs -->
                        <div x-show="isNewCustomer" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Customer Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Gamma Operations / George Maina"
                                       class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 py-2.5 px-3">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">M-Pesa Mobile Number <span class="text-rose-500">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0712345678"
                                       class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 py-2.5 px-3">
                                <p class="text-[10px] text-slate-400 font-semibold mt-1">Used for live SMS status updates and M-Pesa receipt verification.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Services & Garments (Spacious Card-based Rows) -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-black text-xs">🧺</span>
                                <h3 class="text-sm font-black text-slate-900">Garments & Services Intake</h3>
                            </div>
                            <button type="button" @click="addItem()"
                                class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center space-x-1.5">
                                <span>+ Add Another Service</span>
                            </button>
                        </div>

                        <!-- Roomy Service Cards List -->
                        <div class="space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-4 space-y-3 relative hover:border-slate-300 transition">

                                    <!-- Full Width Service Dropdown -->
                                    <div>
                                        <div class="flex justify-between items-center mb-1.5">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400" x-text="'Service Line #' + (index + 1)"></span>
                                            <button type="button" @click="removeItem(index)"
                                                class="text-xs font-bold text-rose-500 hover:text-rose-700 transition"
                                                x-show="items.length > 1">
                                                Remove Item
                                            </button>
                                        </div>
                                        <select :name="'items[' + index + '][service_id]'" x-model="item.service_id"
                                            class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-800 focus:border-blue-500 focus:ring-blue-500 bg-white py-3 px-3 shadow-xs">
                                            <template x-for="srv in services" :key="srv.id">
                                                <option :value="srv.id" x-text="srv.name + ' — KSh ' + srv.price.toFixed(2) + ' / ' + srv.unit"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Stepper & Line Price Row -->
                                    <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                                        <!-- Quantity Stepper Controls -->
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-bold text-slate-600">Quantity:</span>
                                            <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl shadow-2xs">
                                                <button type="button" @click="if (item.quantity > 1) item.quantity--"
                                                    class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-slate-900 font-bold hover:bg-slate-50 rounded-l-xl transition">
                                                    &minus;
                                                </button>
                                                <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity"
                                                    class="w-14 text-center font-mono font-black text-xs border-0 focus:ring-0 py-1 bg-transparent text-slate-900">
                                                <button type="button" @click="item.quantity++"
                                                    class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-slate-900 font-bold hover:bg-slate-50 rounded-r-xl transition">
                                                    &#43;
                                                </button>
                                            </div>
                                            <span class="text-[11px] font-semibold text-slate-400" x-text="getItemUnit(item.service_id)"></span>
                                        </div>

                                        <!-- Line Total -->
                                        <div class="text-right">
                                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Subtotal</span>
                                            <span class="font-mono font-black text-slate-900 text-sm">
                                                KSh <span x-text="getItemTotal(item).toFixed(2)"></span>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>

                        <!-- Special Instructions -->
                        <div class="pt-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Care & Stain Notes</label>
                            <input type="text" name="notes" placeholder="e.g. Delicate steam press &bull; Heavy stain on cuffs &bull; Return on plastic hangers"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 py-2.5 px-3">
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Sticky Lipa Na M-Pesa Checkout (5 Columns) -->
                <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">

                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-2xs space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-xs">📱</span>
                                <h3 class="text-sm font-black text-slate-900">Lipa Na M-Pesa Billing</h3>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                100% Cashless
                            </span>
                        </div>

                        <!-- Grand Total Display -->
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Amount Due</span>
                            <div class="text-3xl font-black text-slate-900 font-mono mt-1">
                                KSh <span x-text="getSubtotal().toFixed(2)"></span>
                            </div>
                            <div class="text-[11px] text-slate-500 font-medium mt-1">
                                Customer thermal receipt will reflect all line items.
                            </div>
                        </div>

                        <!-- Payment Input Section -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700">M-Pesa Intake Deposit</label>
                                <div class="space-x-1.5">
                                    <button type="button" @click="setFullPayment()" class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded hover:bg-blue-100 transition">
                                        Full Paid
                                    </button>
                                    <button type="button" @click="setZeroPayment()" class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded hover:bg-slate-200 transition">
                                        Pay on Pickup
                                    </button>
                                </div>
                            </div>

                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-xs font-bold text-slate-400">KSh</span>
                                <input type="number" step="0.01" min="0" name="paid_amount" x-model="paid_amount"
                                    class="w-full pl-12 rounded-xl border-slate-200 text-sm font-mono font-bold focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 py-2.5">
                            </div>

                            <!-- Remaining Balance Breakdown -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1 text-xs">
                                <div class="flex justify-between font-medium text-slate-600">
                                    <span>Total Items Cost:</span>
                                    <span class="font-mono font-bold">KSh <span x-text="getSubtotal().toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between font-medium text-emerald-600">
                                    <span>M-Pesa Deposit:</span>
                                    <span class="font-mono font-bold">&minus; KSh <span x-text="Number(paid_amount || 0).toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between font-bold pt-1.5 border-t border-slate-200 text-sm"
                                     :class="(getSubtotal() - paid_amount) > 0 ? 'text-amber-600' : 'text-emerald-700'">
                                    <span>Balance Due:</span>
                                    <span class="font-mono" x-text="'KSh ' + Math.max(0, getSubtotal() - paid_amount).toFixed(2)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Order Button -->
                        <div class="pt-2">
                            <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider rounded-2xl shadow-sm transition flex items-center justify-center space-x-2">
                                <span>Create Order &bull; Print Ticket</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </form>

    </div>
</x-app-layout>
