<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- 🧺 Prominent Customer Self-Service Tracking Card -->
    <div class="mb-6 p-5 bg-blue-50/80 border-2 border-blue-200 rounded-2xl shadow-2xs">
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 font-bold text-sm shadow-xs">
                🧺
            </div>
            <div class="flex-1">
                <h3 class="text-xs font-black text-blue-950 uppercase tracking-wider">Are you a customer tracking an order?</h3>
                <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                    No login required! Check your live washing stage, rack location, and ready status instantly.
                </p>
                <a href="{{ route('track') }}" class="mt-3 inline-flex items-center justify-center w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition space-x-1.5">
                    <span>Track Your Laundry Order Here</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Divider -->
    <div class="relative flex py-2 items-center mb-5">
        <div class="grow border-t border-slate-200"></div>
        <span class="shrink mx-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Staff Operations Portal</span>
        <div class="grow border-t border-slate-200"></div>
    </div>

    <!-- Staff Workstation Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Staff Work Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full text-xs font-semibold border-slate-200 rounded-xl py-2.5 px-3 focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. admin@taisonlaundry.com">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
            <input type="password" name="password" required class="w-full text-xs font-semibold border-slate-200 rounded-xl py-2.5 px-3 focus:border-blue-500 focus:ring-blue-500" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between text-xs pt-1">
            <label class="inline-flex items-center text-slate-600 font-medium">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2">Remember workstation</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-black text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition">
                Sign In to Workstation
            </button>
        </div>
    </form>
</x-guest-layout>
