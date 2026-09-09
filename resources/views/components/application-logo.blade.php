@php
    $shop = \App\Models\ShopSetting::current();
    $words = explode(' ', trim($shop->shop_name));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $initials .= mb_substr($w, 0, 1);
    }
    $initials = strtoupper($initials ?: 'CW');
@endphp
<div class="flex items-center space-x-3">
    <div class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center text-white font-black text-base shadow-md shrink-0">
        {{ $initials }}
    </div>
    <div class="text-left min-w-0">
        <span class="font-black text-slate-900 text-base tracking-tight leading-none block uppercase truncate">{{ $shop->shop_name }}</span>
        <span class="text-[11px] text-slate-400 font-bold tracking-wider block uppercase mt-1 truncate">{{ $shop->tagline }}</span>
    </div>
</div>
