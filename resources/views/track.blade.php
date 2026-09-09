@php
    $shop = \App\Models\ShopSetting::current();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Garment Status — {{ $shop->shop_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-lg space-y-6">

        <!-- Brand Header -->
        <div class="text-center space-y-1">
            <h1 class="text-2xl font-black tracking-tight text-slate-900">{{ strtoupper($shop->shop_name) }}</h1>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $shop->tagline }}</p>
        </div>

        <!-- Search Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="text-xs font-bold text-slate-700">Check Your Laundry Status Online</div>
            <form action="{{ route('track') }}" method="GET" class="flex gap-2">
                <input type="text" name="ticket" value="{{ $ticket ?? '' }}" placeholder="Enter Ticket # (e.g. AUR-20260907-XXXX)"
                       required class="flex-1 rounded-xl border-slate-200 text-xs font-mono font-bold focus:border-blue-500 focus:ring-blue-500 bg-slate-50">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    Track
                </button>
            </form>
        </div>

        <!-- Result Box -->
        @if ($searched ?? false)
            @if ($job)
                @php
                    $statusVal = is_string($job->status) ? $job->status : ($job->status->value ?? 'received');
                    $steps = ['received' => 1, 'in_progress' => 2, 'ready' => 3, 'collected' => 4];
                    $currentStep = $steps[$statusVal] ?? 1;
                @endphp

                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Ticket Number</span>
                            <div class="font-mono font-black text-slate-900 text-sm">{{ $job->job_number }}</div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Customer</span>
                            <div class="font-bold text-slate-800 text-xs">{{ $job->customer?->name ?? 'Valued Customer' }}</div>
                        </div>
                    </div>

                    <!-- Visual Progress -->
                    <div class="grid grid-cols-4 gap-2 text-center">
                        <div class="space-y-1">
                            <div class="h-2 rounded-full {{ $currentStep >= 1 ? 'bg-blue-600' : 'bg-slate-200' }}"></div>
                            <span class="text-[10px] font-bold {{ $currentStep >= 1 ? 'text-blue-600' : 'text-slate-400' }}">Received</span>
                        </div>
                        <div class="space-y-1">
                            <div class="h-2 rounded-full {{ $currentStep >= 2 ? 'bg-amber-500' : 'bg-slate-200' }}"></div>
                            <span class="text-[10px] font-bold {{ $currentStep >= 2 ? 'text-amber-500' : 'text-slate-400' }}">Washing</span>
                        </div>
                        <div class="space-y-1">
                            <div class="h-2 rounded-full {{ $currentStep >= 3 ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                            <span class="text-[10px] font-bold {{ $currentStep >= 3 ? 'text-emerald-600' : 'text-slate-400' }}">Ready</span>
                        </div>
                        <div class="space-y-1">
                            <div class="h-2 rounded-full {{ $currentStep >= 4 ? 'bg-slate-800' : 'bg-slate-200' }}"></div>
                            <span class="text-[10px] font-bold {{ $currentStep >= 4 ? 'text-slate-800' : 'text-slate-400' }}">Collected</span>
                        </div>
                    </div>

                    <!-- Current Status Notice -->
                    <div class="p-4 rounded-2xl text-xs font-semibold text-center
                        {{ $statusVal === 'ready' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' :
                          ($statusVal === 'in_progress' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-800 border border-blue-200') }}">
                        @if($statusVal === 'ready')
                            Your garments are cleaned, pressed, and ready for collection!
                        @elseif($statusVal === 'in_progress')
                            Your garments are currently in the washing & drying cycle.
                        @elseif($statusVal === 'collected')
                            This order was completed and picked up. Thank you!
                        @else
                            Order received at front desk intake. Waiting for wash bay assignment.
                        @endif
                    </div>

                    @if($job->rack_location && in_array($statusVal, ['ready', 'collected']))
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2">
                                <span class="text-base">📍</span>
                                <span class="text-slate-600 font-bold">Counter Shelf / Storage Bay:</span>
                            </div>
                            <span class="font-mono font-black text-emerald-800 bg-white px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">{{ $job->rack_location }}</span>
                        </div>
                    @endif

                    <!-- Financial Summary -->
                    <div class="border-t border-slate-100 pt-3 text-xs flex justify-between items-center text-slate-600 font-mono">
                        <span>Balance Due:</span>
                        <span class="font-bold {{ $job->balance_due > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                            KSh {{ number_format($job->balance_due, 2) }}
                        </span>
                    </div>
                </div>
            @else
                <div class="p-5 bg-rose-50 border border-rose-200 rounded-2xl text-center text-xs text-rose-700 font-bold">
                    No order found matching ticket "{{ $ticket }}". Please verify your ticket number.
                </div>
            @endif
        @endif

        <div class="text-center text-[11px] text-slate-400">
            {{ $shop->location }} &bull; Tel: {{ $shop->phone }}
        </div>
    </div>

</body>
</html>
