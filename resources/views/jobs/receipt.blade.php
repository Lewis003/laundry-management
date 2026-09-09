@php
    $shop = \App\Models\ShopSetting::current();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $job->job_number }} - {{ $shop->shop_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; margin: 0 !important; padding: 0 !important; }
            .receipt-container { box-shadow: none !important; border: none !important; width: 80mm !important; max-width: 80mm !important; padding: 2mm !important; margin: 0 auto !important; }
        }
        .receipt-container { width: 80mm; max-width: 80mm; font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 py-8 min-h-screen flex flex-col items-center">

    <!-- Print Navigation Bar -->
    <div class="no-print mb-6 flex items-center justify-between w-full max-w-[80mm]">
        <a href="{{ route('jobs.show', $job) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
            &larr; Back to Order
        </a>
        <button onclick="window.print()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-xs transition">
            Print M-Pesa Receipt
        </button>
    </div>

    <!-- 80mm POS Thermal Receipt -->
    <div class="receipt-container bg-white p-6 shadow-md border border-slate-200 text-xs space-y-4">

        <!-- Store Brand Header -->
        <div class="text-center space-y-0.5 border-b border-dashed border-slate-300 pb-3">
            <h1 class="text-base font-black tracking-tight uppercase">{{ $shop->shop_name }}</h1>
            <p class="text-[11px] font-semibold text-slate-600 uppercase tracking-wide">{{ $shop->tagline }}</p>
            <p class="text-[10px] text-slate-500">{{ $shop->location }}</p>
            <p class="text-[10px] text-slate-500">Tel: {{ $shop->phone }}</p>
            @if($shop->mpesa_till_or_paybill)
                <p class="text-[10px] font-mono font-bold text-slate-700">M-Pesa Till / Paybill: {{ $shop->mpesa_till_or_paybill }}</p>
            @endif

            <p class="text-[10px] font-black text-emerald-700 bg-emerald-50 py-0.5 mt-1 border border-emerald-200 rounded">
                100% CASHLESS: LIPA NA M-PESA ONLY
            </p>
        </div>

        <!-- Ticket & Customer Metadata -->
        <div class="space-y-1 text-[11px] border-b border-dashed border-slate-300 pb-3">
            <div class="flex justify-between">
                <span class="font-bold">TICKET #:</span>
                <span class="font-mono font-bold">{{ $job->job_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Intake Date:</span>
                <span>{{ $job->created_at->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Customer:</span>
                <span class="font-bold truncate max-w-[150px]">{{ $job->customer?->name ?? 'Walk-in' }}</span>
            </div>
            <div class="flex justify-between">
                <span>M-Pesa Phone:</span>
                <span class="font-mono">{{ $job->customer?->phone ?? '—' }}</span>
            </div>
            @if($job->rack_location)
            <div class="flex justify-between font-bold text-emerald-800">
                <span>RACK / SHELF:</span>
                <span class="font-mono">{{ $job->rack_location }}</span>
            </div>
            @endif
            <div class="flex justify-between text-slate-500">
                <span>Cashier:</span>
                <span>{{ auth()->user()->name ?? 'Front Desk' }}</span>
            </div>
        </div>

        <!-- Garment Items Breakdown -->
        <div class="border-b border-dashed border-slate-300 pb-3">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase">
                        <th class="pb-1">Item</th>
                        <th class="pb-1 text-center">Qty</th>
                        <th class="pb-1 text-right">Price</th>
                        <th class="pb-1 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[11px]">
                    @foreach($job->items as $item)
                        <tr>
                            <td class="py-1.5 pr-1 font-medium">{{ $item->service?->name ?? 'Laundry' }}</td>
                            <td class="py-1.5 text-center">{{ $item->quantity }}</td>
                            <td class="py-1.5 text-right font-mono">{{ number_format($item->unit_price, 0) }}</td>
                            <td class="py-1.5 text-right font-mono font-bold">{{ number_format($item->subtotal, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Order Financial Summary (Printed ETR Tax Breakdown) -->
        <div class="space-y-1.5 text-xs border-b border-dashed border-slate-300 pb-3">
            <div class="flex justify-between text-slate-600">
                <span>Base Cost (Net):</span>
                <span class="font-mono font-bold">KSh {{ number_format($job->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>16% VAT (Inclusive):</span>
                <span class="font-mono font-bold">KSh {{ number_format($job->tax, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-900 font-black text-sm pt-1.5 border-t border-slate-200">
                <span>TOTAL AMOUNT (VAT INCL.):</span>
                <span class="font-mono">KSh {{ number_format($job->total_price, 2) }}</span>
            </div>
        </div>

        <!-- M-Pesa Installment History with Timestamps -->
        <div class="border-b border-dashed border-slate-300 pb-3 space-y-2">
            <div class="text-[10px] font-black uppercase text-slate-500 tracking-wider">
                M-Pesa Installment History:
            </div>

            @forelse($job->payments as $index => $payment)
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 space-y-0.5">
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="font-bold text-slate-800">
                            M-Pesa Installment #{{ $index + 1 }}
                        </span>
                        <span class="font-mono font-black text-emerald-700">
                            +KSh {{ number_format($payment->amount, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-[10px] text-slate-500 font-mono">
                        <span>{{ $payment->created_at->format('d/m/Y h:i A') }}</span>
                        <span>{{ $payment->reference }}</span>
                    </div>
                </div>
            @empty
                <div class="text-[11px] text-slate-400 italic">No M-Pesa payments recorded yet.</div>
            @endforelse
        </div>

        <!-- Balance Due & Status -->
        <div class="space-y-1 text-xs border-b border-dashed border-slate-300 pb-3">
            <div class="flex justify-between text-slate-600">
                <span>TOTAL M-PESA PAID:</span>
                <span class="font-mono font-bold text-emerald-700">KSh {{ number_format($job->paid_amount, 2) }}</span>
            </div>
            <div class="flex justify-between font-black text-sm pt-1 border-t border-slate-200">
                <span>BALANCE DUE:</span>
                <span class="font-mono {{ $job->balance_due > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    KSh {{ number_format($job->balance_due, 2) }}
                </span>
            </div>
            <div class="text-center pt-2 font-black tracking-wider text-[11px] uppercase {{ $job->isFullyPaid() ? 'text-emerald-700' : 'text-amber-700' }}">
                [{{ $job->isFullyPaid() ? 'M-PESA SETTLED' : 'PARTIAL / UNPAID' }}]
            </div>
        </div>

        <!-- Online Tracking Kiosk & Company Policy -->
        <div class="text-center space-y-1 text-[10px] text-slate-500 pt-1">
            <p>Track status online at:</p>
            <p class="font-mono font-bold text-slate-800 text-[11px]">{{ route('track', ['ticket' => $job->job_number]) }}</p>
            <p class="pt-2 italic font-semibold text-slate-700">{{ $shop->receipt_footer }}</p>
            <p class="text-[9px]">Strictly cashless. All transactions processed via Safaricom M-Pesa.</p>
        </div>

    </div>

</body>
</html>
