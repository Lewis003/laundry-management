<x-app-layout>
    <div class="max-w-xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('customers.show', $customer) }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">&larr; Back to Customer Details</a>
            <a href="{{ route('customers.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-600">All Customers</a>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold text-slate-900">Edit Customer Profile</h1>
                    <p class="text-xs text-slate-500">Update client contact details and garment preferences</p>
                </div>
                <span class="text-xs font-mono font-bold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg">ID: #{{ $customer->id }}</span>
            </div>

            <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required placeholder="e.g. David Mwangi"
                           class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    @error('name') <span class="text-rose-600 text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number (Unique) *</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required placeholder="e.g. 0712345678"
                           class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                    @error('phone') <span class="text-rose-600 text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" placeholder="e.g. david@example.com"
                           class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    @error('email') <span class="text-rose-600 text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Special Notes / Garment Preferences</label>
                    <textarea name="notes" rows="3" placeholder="e.g. Starch shirts lightly, allergic to scented fabric softeners..."
                              class="w-full rounded-lg border-slate-300 p-2.5 text-xs focus:ring-emerald-500 focus:border-emerald-500">{{ old('notes', $customer->notes) }}</textarea>
                    @error('notes') <span class="text-rose-600 text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <a href="{{ route('customers.show', $customer) }}" class="px-4 py-2 bg-slate-100 font-bold rounded-lg text-slate-700 hover:bg-slate-200">Cancel</a>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 font-bold rounded-lg text-white shadow">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

