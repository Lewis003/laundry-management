<x-app-layout>
    <div class="max-w-xl mx-auto space-y-6">
        <div class="flex items-center space-x-2">
            <a href="{{ route('customers.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">&larr; Back to Customers</a>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h1 class="text-lg font-bold text-slate-900">Register New Customer</h1>
                <p class="text-xs text-slate-500">Add a client profile to track orders and receipts</p>
            </div>

            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. David Mwangi"
                           class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    @error('name') <span class="text-rose-600 text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number (Unique) *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="e.g. 0712345678"
                           class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                    @error('phone') <span class="text-rose-600 text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="e.g. david@example.com"
                           class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    @error('email') <span class="text-rose-600 text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Special Notes / Garment Preferences</label>
                    <textarea name="notes" rows="3" placeholder="e.g. Starch shirts lightly, allergic to scented fabric softeners..."
                              class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-emerald-500 focus:border-emerald-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <a href="{{ route('customers.index') }}" class="px-4 py-2 bg-slate-100 font-bold rounded-lg text-slate-700 hover:bg-slate-200">Cancel</a>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 font-bold rounded-lg text-white shadow">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
