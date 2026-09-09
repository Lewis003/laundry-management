<x-app-layout>
    <div class="space-y-6 max-w-4xl mx-auto pb-12">

        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('settings.edit') }}" class="hover:text-slate-800">Settings</a>
            <span>&rsaquo;</span>
            <span class="text-slate-900 font-bold">Shop & Location</span>
        </div>

        <!-- Shared Settings Navigation Tabs -->
        @include('settings.partials.tabs')

        <!-- Alerts -->
        @if(session('success'))
        <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
            <span>✓ {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-900 font-black">&times;</button>
        </div>
        @endif

        @if($errors->any())
        <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl text-xs font-bold space-y-1 shadow-sm">
            @foreach($errors->all() as $error)
                <div>⚠️ {{ $error }}</div>
            @endforeach
        </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Shop & Location Settings</h1>
                <p class="text-xs text-slate-500 font-medium">Configure business identity, location address, and payment information for any branch or owner</p>
            </div>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-xl self-start sm:self-auto">
                Admin Control
            </span>
        </div>

        <!-- Form Card -->
        <form method="POST" action="{{ route('settings.update') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Business Identity & Branding -->
            <div class="space-y-4">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Business Identity & Branding</h2>
                    <p class="text-xs text-slate-500">This business name and tagline appear consistently across the sidebar, header, customer receipts, and tracking kiosks.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Shop / Business Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="shop_name" value="{{ old('shop_name', $setting->shop_name) }}" required
                               class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tagline / Subtitle <span class="text-rose-500">*</span></label>
                        <input type="text" name="tagline" value="{{ old('tagline', $setting->tagline) }}" required
                               placeholder="e.g. Commercial Laundry & Dry Cleaning"
                               class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500 shadow-sm">
                    </div>
                </div>
            </div>

            <!-- Section 2: Location & Contact Information -->
            <div class="space-y-4">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Branch Location & Contacts</h2>
                    <p class="text-xs text-slate-500">Specify physical premises location and customer support lines.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Physical Location / Address <span class="text-rose-500">*</span></label>
                        <input type="text" name="location" value="{{ old('location', $setting->location) }}" required
                               placeholder="e.g. Industrial Area, Plant A, Nairobi"
                               class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Official Phone Number <span class="text-rose-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $setting->phone) }}" required
                               placeholder="e.g. +254 700 000 001"
                               class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Official Support Email</label>
                        <input type="email" name="email" value="{{ old('email', $setting->email) }}"
                               placeholder="e.g. info@safishwa.co.ke"
                               class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500 shadow-sm">
                    </div>
                </div>
            </div>

            <!-- Section 3: Payments & Billing -->
            <div class="space-y-4">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Payments & Fiscal Configuration</h2>
                    <p class="text-xs text-slate-500">Configure M-Pesa Till/Paybill number printed on receipts and applied tax rate.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">M-Pesa Till / Paybill Number</label>
                        <input type="text" name="mpesa_till_or_paybill" value="{{ old('mpesa_till_or_paybill', $setting->mpesa_till_or_paybill) }}"
                               placeholder="e.g. 542310"
                               class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Currency Prefix <span class="text-rose-500">*</span></label>
                        <input type="text" name="currency" value="{{ old('currency', $setting->currency) }}" required
                               placeholder="e.g. KSh"
                               class="w-full text-xs font-bold bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Standard VAT Rate (%) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_percent" value="{{ old('tax_percent', $setting->tax_percent) }}" required
                               placeholder="e.g. 16.00"
                               class="w-full text-xs font-bold bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500 shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Receipt Disclaimer / Terms Footer</label>
                    <textarea name="receipt_footer" rows="2"
                              placeholder="Notes printed at the bottom of customer intake tickets..."
                              class="w-full text-xs font-medium bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500 shadow-sm">{{ old('receipt_footer', $setting->receipt_footer) }}</textarea>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm active:scale-95">
                    Save Changes
                </button>
            </div>
        </form>

    </div>
</x-app-layout>

