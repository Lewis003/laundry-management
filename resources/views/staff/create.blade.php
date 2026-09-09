<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6 pb-20">

        <!-- Top Breadcrumb -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('staff.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Staff Roster
                </a>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Register New Staff Member</h2>
                <p class="text-xs text-slate-500 mt-0.5">Create team credentials and set role permissions.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs font-bold shadow-2xs">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('staff.store') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 p-8 shadow-2xs space-y-6">
            @csrf

            <!-- Full Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. John Kamau" required
                       class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 py-2.5">
            </div>

            <!-- Email Address -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Work Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="john@safishwa.co.ke" required
                       class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 py-2.5">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Initial Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" placeholder="Minimum 6 characters" required minlength="6"
                       class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 py-2.5">
            </div>

            <!-- Role Selection -->
            <div x-data="{ selectedRole: '{{ old('role', 'operator') }}' }">
                <label class="block text-xs font-bold text-slate-700 mb-2">Assign Operational Role <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                    <!-- Operator -->
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="operator" x-model="selectedRole" class="sr-only">
                        <div :class="selectedRole === 'operator' ? 'border-amber-500 bg-amber-50/70 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50'"
                             class="p-4 rounded-2xl border-2 transition space-y-1">
                            <div class="font-black text-xs text-amber-900">Operator</div>
                            <p class="text-[11px] text-slate-500 leading-tight">Wash bay execution, machine locking, and cycle tracking. Cannot collect payments.</p>
                        </div>
                    </label>

                    <!-- Cashier -->
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="cashier" x-model="selectedRole" class="sr-only">
                        <div :class="selectedRole === 'cashier' ? 'border-blue-500 bg-blue-50/70 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50'"
                             class="p-4 rounded-2xl border-2 transition space-y-1">
                            <div class="font-black text-xs text-blue-900">Cashier</div>
                            <p class="text-[11px] text-slate-500 leading-tight">Front desk order intake, customer registration, and M-Pesa payment collection.</p>
                        </div>
                    </label>

                    <!-- Admin -->
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="admin" x-model="selectedRole" class="sr-only">
                        <div :class="selectedRole === 'admin' ? 'border-purple-500 bg-purple-50/70 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50'"
                             class="p-4 rounded-2xl border-2 transition space-y-1">
                            <div class="font-black text-xs text-purple-900">Administrator</div>
                            <p class="text-[11px] text-slate-500 leading-tight">Full access across staff rosters, financial reports, pricing catalog, and bay equipment.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('staff.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    Create Staff Member
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
