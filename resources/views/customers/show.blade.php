<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('customers.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">&larr; Customers</a>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">{{ $customer->name }}</h1>
                <p class="text-xs text-slate-500 font-mono">Phone: {{ $customer->phone }} • Email: {{ $customer->email ?? 'N/A' }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('customers.edit', $customer) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                    Edit Profile
                </a>
                <a href="{{ route('jobs.create', ['customer_id' => $customer->id]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                    + New Order Intake
                </a>
            </div>
        </div>

        <!-- Chronological Order History Ledger -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Order History Ledger</h2>
                    <p class="text-xs text-slate-500">Live ledger of all historical orders and payment settlements</p>
                </div>
                <span class="text-xs font-bold bg-slate-100 text-slate-700 px-3 py-1 rounded-full">
                    {{ $customer->orders->count() }} Lifetime Orders
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-4">Ticket Number</th>
                            <th class="py-3.5 px-4">Date</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Order Total (KSh)</th>
                            <th class="py-3.5 px-4 text-right">Paid (KSh)</th>
                            <th class="py-3.5 px-4 text-right">Balance Due (KSh)</th>
                            <th class="py-3.5 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($customer->orders as $order)
                        @php
                            $totalCents = $order->total_cents ?? ($order->price_in_cents ?? 0);
                            $paidCents = method_exists($order, 'payments') ? $order->payments->sum('amount_in_cents') : 0;
                            $balanceCents = max(0, $totalCents - $paidCents);
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $order->ticket_number }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                {{ number_format($totalCents / 100, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right text-emerald-600 font-bold">
                                {{ number_format($paidCents / 100, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-black {{ $balanceCents > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                {{ number_format($balanceCents / 100, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('jobs.show', $order->id) }}" class="font-bold text-emerald-600 hover:underline">
                                    Details &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 font-medium">No order history found for this customer.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
