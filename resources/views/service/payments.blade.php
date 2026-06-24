@extends('layouts.service')

@section('page-title', 'Payments')
@section('page-subtitle', 'Manage counter payments, pay later balances, and digital payment status')

@section('content')

@php
    $selectedDateLabel = \Carbon\Carbon::parse($selectedDate)->format('M d, Y');

    $ordersForModal = $orders->getCollection()->mapWithKeys(function ($order) {
        $tableNumber = $order->source_table_number
            ?? $order->table_number
            ?? $order->table?->table_number
            ?? $order->table?->name
            ?? null;

        return [
            $order->id => [
                'id' => $order->id,
                'order_number' => $order->order_number ?? ('Order #' . $order->id),
                'table_number' => $tableNumber ? 'Table ' . $tableNumber : 'No table',
                'total_amount' => number_format($order->total_amount ?? 0, 2),
                'action_url' => route('service.orders.process-payment', $order),
                'items' => $order->items->map(function ($item) {
                    return [
                        'quantity' => $item->quantity,
                        'name' => $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item',
                        'price' => number_format(($item->price ?? 0) * ($item->quantity ?? 1), 2),
                    ];
                })->values(),
            ],
        ];
    });
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
        <div>
            <p class="text-xs font-black text-orange-500 uppercase tracking-[0.28em] mb-2">
                Service Payments
            </p>

            <h1 class="text-3xl font-black text-gray-950 tracking-tight">
                Payment Records
            </h1>

            <p class="text-gray-500 mt-2 max-w-3xl">
                Settle counter payments, Pay Later balances, and QR PH payments through Xendit.
            </p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 w-full xl:w-auto">
            <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm min-w-[155px]">
                <p class="text-xs font-semibold text-gray-500">Counter Waiting</p>
                <p class="text-2xl font-black text-orange-500 mt-1">{{ $stats['awaiting_counter'] ?? 0 }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm min-w-[155px]">
                <p class="text-xs font-semibold text-gray-500">Pay Later</p>
                <p class="text-2xl font-black text-purple-500 mt-1">{{ $stats['pay_later_unpaid'] ?? 0 }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm min-w-[155px]">
                <p class="text-xs font-semibold text-gray-500">Digital Pending</p>
                <p class="text-2xl font-black text-blue-500 mt-1">{{ $stats['digital_pending'] ?? 0 }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm min-w-[155px]">
                <p class="text-xs font-semibold text-gray-500">Paid</p>
                <p class="text-2xl font-black text-green-500 mt-1">{{ $stats['paid'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- FLASH --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl text-sm font-bold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm font-bold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- ACTIVE TABLES --}}
    <div class="bg-white/95 border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-lg font-black text-gray-950">Active Tables</h2>
            <p class="text-sm text-gray-500">Tables currently occupied in service monitoring.</p>
        </div>

        <div class="p-5">
            @if ($activeTables->count())
                <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3">
                    @foreach ($activeTables as $table)
                        <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-4">
                            <p class="text-xs font-bold text-orange-600 uppercase tracking-wide">Table</p>
                            <p class="text-2xl font-black text-gray-950 mt-1">{{ $table->table_number }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $table->current_guest_count ?? 0 }} guest{{ ($table->current_guest_count ?? 0) > 1 ? 's' : '' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <h3 class="font-black text-gray-900">No active tables</h3>
                    <p class="text-sm text-gray-500 mt-1">Occupied tables will appear here.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- PAYMENT RECORDS --}}
    <div class="bg-white/95 border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-gray-950">
                    Payment Records
                </h2>
                <p class="text-sm text-gray-500">
                    Showing {{ $mode === 'all' ? 'all payment records' : 'records for ' . $selectedDateLabel }}.
                </p>
            </div>

            <form method="GET" action="{{ route('service.payments') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2 w-full xl:w-auto">
                <input
                    type="date"
                    name="date"
                    value="{{ $selectedDate }}"
                    class="w-full rounded-xl border-gray-200 text-sm font-semibold text-gray-700 focus:border-orange-400 focus:ring-orange-200"
                >

                <button
                    type="submit"
                    class="rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-black px-5 py-3 transition">
                    View Date
                </button>

                <a
                    href="{{ route('service.payments', ['date' => now('Asia/Manila')->toDateString()]) }}"
                    class="rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-black px-5 py-3 text-center transition">
                    Today
                </a>

                <a
                    href="{{ route('service.payments', ['mode' => 'all']) }}"
                    class="rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-black px-5 py-3 text-center transition">
                    All
                </a>
            </form>
        </div>

        {{-- RESPONSIVE CARD VIEW --}}
        <div class="xl:hidden p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($orders as $order)
                @php
                    $rawMethod = trim($order->payment_method ?? '');
                    $normalizedMethod = strtolower(str_replace(['_', '-'], ' ', $rawMethod));

                    $paymentMethod = match (true) {
                        str_contains($normalizedMethod, 'digital') => 'Digital Payment',
                        str_contains($normalizedMethod, 'qr') => 'Digital Payment',
                        str_contains($normalizedMethod, 'xendit') => 'Digital Payment',
                        str_contains($normalizedMethod, 'later') => 'Pay Later',
                        str_contains($normalizedMethod, 'counter') => 'Pay at Counter',
                        str_contains($normalizedMethod, 'cash') => 'Cash',
                        default => $rawMethod ?: 'Pay at Counter',
                    };

                    $paymentStatus = strtolower(trim($order->payment_status ?? 'pending'));
                    $orderStatus = strtolower(trim($order->status ?? 'pending'));

                    $isPaid = in_array($paymentStatus, ['paid', 'verified'], true);
                    $isAwaitingPayment = $orderStatus === 'awaiting_payment';

                    $methodClass = match ($paymentMethod) {
                        'Digital Payment' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'Pay Later' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'Pay at Counter', 'Cash' => 'bg-orange-50 text-orange-700 border-orange-200',
                        default => 'bg-gray-50 text-gray-700 border-gray-200',
                    };

                    $paymentClass = $isPaid
                        ? 'bg-green-50 text-green-700 border-green-200'
                        : 'bg-red-50 text-red-700 border-red-200';

                    $tableNumber = $order->source_table_number
                        ?? $order->table_number
                        ?? $order->table?->table_number
                        ?? $order->table?->name
                        ?? null;

                    $digitalPaymentUrl = $order->xendit_invoice_url
                        ?? $order->invoice_url
                        ?? null;
                @endphp

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-black text-gray-950 truncate">
                                {{ $order->order_number ?? 'No order number' }}
                            </p>

                            <p class="text-xs text-gray-500 mt-1">
                                Created: {{ $order->created_at ? $order->created_at->diffForHumans() : 'No date' }}
                            </p>
                        </div>

                        @if ($tableNumber)
                            <span class="shrink-0 inline-flex px-3 py-1 rounded-full bg-orange-50 text-orange-700 border border-orange-200 text-xs font-black">
                                Table {{ $tableNumber }}
                            </span>
                        @else
                            <span class="shrink-0 inline-flex px-3 py-1 rounded-full bg-gray-50 text-gray-500 border border-gray-200 text-xs font-black">
                                No table
                            </span>
                        @endif
                    </div>

                    <div class="p-4 space-y-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400 mb-2">
                                Items
                            </p>

                            <div class="space-y-1">
                                @forelse ($order->items as $item)
                                    <p class="text-sm text-gray-800">
                                        <span class="font-bold">{{ $item->quantity }}x</span>
                                        {{ $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item' }}
                                    </p>
                                @empty
                                    <p class="text-sm text-gray-400">No items</p>
                                @endforelse
                            </div>

                            <p class="text-sm font-black text-orange-500 mt-2">
                                Total: ₱{{ number_format($order->total_amount ?? 0, 2) }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-3">
                                <p class="text-xs font-black uppercase tracking-wide text-gray-400 mb-2">
                                    Payment Method
                                </p>

                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $methodClass }}">
                                    {{ $paymentMethod }}
                                </span>

                                @if ($paymentMethod === 'Digital Payment')
                                    <p class="text-xs text-gray-500 mt-2">
                                        Xendit QR PH checkout
                                    </p>
                                @endif
                            </div>

                            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-3">
                                <p class="text-xs font-black uppercase tracking-wide text-gray-400 mb-2">
                                    Payment Status
                                </p>

                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $paymentClass }}">
                                    {{ $isPaid ? 'Paid' : 'Pending Payment' }}
                                </span>

                                @if ($order->paid_at)
                                    <p class="text-xs text-gray-500 mt-2">
                                        Paid: {{ \Carbon\Carbon::parse($order->paid_at)->format('M d, h:i A') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 border border-gray-100 p-3">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400 mb-2">
                                Kitchen Flow
                            </p>

                            @if ($isPaid)
                                <p class="text-sm font-black text-green-600">
                                    Sent to kitchen
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Status: {{ ucfirst($orderStatus) }}
                                </p>
                            @elseif ($paymentMethod === 'Digital Payment')
                                <p class="text-sm font-black text-blue-600">
                                    Waiting for QR payment
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Not yet sent to KDS.
                                </p>
                            @elseif ($isAwaitingPayment)
                                <p class="text-sm font-black text-orange-600">
                                    Waiting for counter payment
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Not yet sent to KDS.
                                </p>
                            @else
                                <p class="text-sm font-black text-green-600">
                                    Sent to kitchen
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Status: {{ ucfirst($orderStatus) }}
                                </p>
                            @endif
                        </div>

                        <div>
                            @if ($isPaid)
                                <div class="w-full rounded-xl bg-gray-100 text-gray-500 text-sm font-black px-4 py-3 text-center">
                                    Paid
                                </div>
                            @elseif (in_array($paymentMethod, ['Pay at Counter', 'Pay Later', 'Cash'], true))
                                <button
                                    type="button"
                                    onclick="openPaymentModal('{{ $order->id }}')"
                                    class="w-full rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-black px-4 py-3 transition">
                                    Settle Payment
                                </button>
                            @elseif ($paymentMethod === 'Digital Payment')
                                @if ($digitalPaymentUrl)
                                    <a
                                        href="{{ $digitalPaymentUrl }}"
                                        target="_blank"
                                        class="block w-full rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-black px-4 py-3 text-center transition">
                                        View QR
                                    </a>
                                @else
                                    <div class="w-full rounded-xl bg-blue-50 text-blue-700 text-sm font-black px-4 py-3 text-center">
                                        Waiting Xendit
                                    </div>
                                @endif
                            @else
                                <div class="w-full rounded-xl bg-gray-100 text-gray-500 text-sm font-black px-4 py-3 text-center">
                                    No Action
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 px-5 py-14 text-center">
                    <h3 class="font-black text-gray-900">No payment records found</h3>
                    <p class="text-sm text-gray-500 mt-1">Payment-related orders will appear here.</p>
                </div>
            @endforelse
        </div>

        {{-- DESKTOP TABLE VIEW --}}
        <div class="hidden xl:block overflow-x-auto">
            <table class="w-full min-w-[1180px] text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-5 py-4 font-bold">Order</th>
                        <th class="px-5 py-4 font-bold">Table</th>
                        <th class="px-5 py-4 font-bold">Items</th>
                        <th class="px-5 py-4 font-bold">Payment Method</th>
                        <th class="px-5 py-4 font-bold">Payment Status</th>
                        <th class="px-5 py-4 font-bold">Kitchen Flow</th>
                        <th class="px-5 py-4 font-bold text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        @php
                            $rawMethod = trim($order->payment_method ?? '');
                            $normalizedMethod = strtolower(str_replace(['_', '-'], ' ', $rawMethod));

                            $paymentMethod = match (true) {
                                str_contains($normalizedMethod, 'digital') => 'Digital Payment',
                                str_contains($normalizedMethod, 'qr') => 'Digital Payment',
                                str_contains($normalizedMethod, 'xendit') => 'Digital Payment',
                                str_contains($normalizedMethod, 'later') => 'Pay Later',
                                str_contains($normalizedMethod, 'counter') => 'Pay at Counter',
                                str_contains($normalizedMethod, 'cash') => 'Cash',
                                default => $rawMethod ?: 'Pay at Counter',
                            };

                            $paymentStatus = strtolower(trim($order->payment_status ?? 'pending'));
                            $orderStatus = strtolower(trim($order->status ?? 'pending'));

                            $isPaid = in_array($paymentStatus, ['paid', 'verified'], true);
                            $isAwaitingPayment = $orderStatus === 'awaiting_payment';

                            $methodClass = match ($paymentMethod) {
                                'Digital Payment' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'Pay Later' => 'bg-purple-50 text-purple-700 border-purple-200',
                                'Pay at Counter', 'Cash' => 'bg-orange-50 text-orange-700 border-orange-200',
                                default => 'bg-gray-50 text-gray-700 border-gray-200',
                            };

                            $paymentClass = $isPaid
                                ? 'bg-green-50 text-green-700 border-green-200'
                                : 'bg-red-50 text-red-700 border-red-200';

                            $tableNumber = $order->source_table_number
                                ?? $order->table_number
                                ?? $order->table?->table_number
                                ?? $order->table?->name
                                ?? null;

                            $digitalPaymentUrl = $order->xendit_invoice_url
                                ?? $order->invoice_url
                                ?? null;
                        @endphp

                        <tr class="align-top hover:bg-gray-50 transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-black text-gray-950">
                                    {{ $order->order_number ?? 'No order number' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Created: {{ $order->created_at ? $order->created_at->diffForHumans() : 'No date' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                @if ($tableNumber)
                                    <span class="inline-flex px-3 py-1 rounded-full bg-orange-50 text-orange-700 border border-orange-200 text-xs font-black">
                                        Table {{ $tableNumber }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">No table</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 w-[300px]">
                                @forelse ($order->items as $item)
                                    <p class="text-sm text-gray-800 mb-1">
                                        <span class="font-bold">{{ $item->quantity }}x</span>
                                        {{ $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item' }}
                                    </p>
                                @empty
                                    <span class="text-sm text-gray-400">No items</span>
                                @endforelse

                                <p class="text-xs font-black text-orange-500 mt-2">
                                    Total: ₱{{ number_format($order->total_amount ?? 0, 2) }}
                                </p>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $methodClass }}">
                                    {{ $paymentMethod }}
                                </span>

                                @if ($paymentMethod === 'Digital Payment')
                                    <p class="text-xs text-gray-500 mt-2">
                                        Xendit QR PH checkout
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $paymentClass }}">
                                    {{ $isPaid ? 'Paid' : 'Pending Payment' }}
                                </span>

                                @if ($order->paid_at)
                                    <p class="text-xs text-gray-500 mt-2">
                                        Paid: {{ \Carbon\Carbon::parse($order->paid_at)->format('M d, h:i A') }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 w-[220px]">
                                @if ($isPaid)
                                    <p class="text-sm font-black text-green-600">
                                        Sent to kitchen
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Status: {{ ucfirst($orderStatus) }}
                                    </p>
                                @elseif ($paymentMethod === 'Digital Payment')
                                    <p class="text-sm font-black text-blue-600">
                                        Waiting for QR payment
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Not yet sent to KDS.
                                    </p>
                                @elseif ($isAwaitingPayment)
                                    <p class="text-sm font-black text-orange-600">
                                        Waiting for counter payment
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Not yet sent to KDS.
                                    </p>
                                @else
                                    <p class="text-sm font-black text-green-600">
                                        Sent to kitchen
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Status: {{ ucfirst($orderStatus) }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                @if ($isPaid)
                                    <span class="inline-flex px-4 py-2.5 rounded-xl bg-gray-100 text-gray-500 text-xs font-black">
                                        Paid
                                    </span>
                                @elseif (in_array($paymentMethod, ['Pay at Counter', 'Pay Later', 'Cash'], true))
                                    <button
                                        type="button"
                                        onclick="openPaymentModal('{{ $order->id }}')"
                                        class="inline-flex px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-black transition">
                                        Settle Payment
                                    </button>
                                @elseif ($paymentMethod === 'Digital Payment')
                                    @if ($digitalPaymentUrl)
                                        <a
                                            href="{{ $digitalPaymentUrl }}"
                                            target="_blank"
                                            class="inline-flex px-4 py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-black transition">
                                            View QR
                                        </a>
                                    @else
                                        <span class="inline-flex px-4 py-2.5 rounded-xl bg-blue-50 text-blue-700 text-xs font-black">
                                            Waiting Xendit
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex px-4 py-2.5 rounded-xl bg-gray-100 text-gray-500 text-xs font-black">
                                        No Action
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">
                                <h3 class="font-black text-gray-900">No payment records found</h3>
                                <p class="text-sm text-gray-500 mt-1">Payment-related orders will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="p-5 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

{{-- PAYMENT MODAL --}}
<div
    id="paymentModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 px-4 py-6">
    <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black text-orange-500 uppercase tracking-[0.25em]">
                    Settle Payment
                </p>
                <h2 id="modalOrderNumber" class="text-2xl font-black text-gray-950 mt-1">
                    Order
                </h2>
                <p id="modalTableNumber" class="text-sm text-gray-500 mt-1">
                    Table
                </p>
            </div>

            <button
                type="button"
                onclick="closePaymentModal()"
                class="w-10 h-10 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-black">
                ×
            </button>
        </div>

        <div class="p-6 space-y-5">
            <div class="rounded-2xl border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-black text-gray-900">
                        Order Summary
                    </p>
                </div>

                <div id="modalItems" class="divide-y divide-gray-100">
                </div>

                <div class="px-4 py-4 bg-orange-50 flex items-center justify-between">
                    <span class="text-sm font-black text-gray-800">
                        Total Amount
                    </span>
                    <span id="modalTotal" class="text-2xl font-black text-orange-500">
                        ₱0.00
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 p-4">
                <p class="text-sm font-black text-gray-950 mb-3">
                    Choose Payment Method
                </p>

                <form id="paymentForm" method="POST" action="#">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="payment_method" id="selectedPaymentMethod">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button
                            type="button"
                            onclick="submitPaymentMethod('cash')"
                            class="rounded-2xl border border-green-200 bg-green-50 hover:bg-green-100 px-5 py-4 text-left transition">
                            <p class="text-lg font-black text-green-700">
                                Cash
                            </p>
                            <p class="text-sm text-green-700/80 mt-1">
                                Confirm cash payment and send order to KDS.
                            </p>
                        </button>

                        <button
                            type="button"
                            onclick="submitPaymentMethod('qrph')"
                            class="rounded-2xl border border-blue-200 bg-blue-50 hover:bg-blue-100 px-5 py-4 text-left transition">
                            <p class="text-lg font-black text-blue-700">
                                QR PH
                            </p>
                            <p class="text-sm text-blue-700/80 mt-1">
                                Open Xendit QR PH checkout for scanning.
                            </p>
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3">
                <p class="text-sm text-orange-800 font-bold">
                    QR PH payment is handled by Xendit. Customers scan the QR code from the Xendit checkout page using supported banking or e-wallet apps.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    window.paymentOrders = @json($ordersForModal);

    function openPaymentModal(orderId) {
        const order = window.paymentOrders[orderId];

        if (!order) {
            alert('Order data not found.');
            return;
        }

        document.getElementById('modalOrderNumber').textContent = order.order_number;
        document.getElementById('modalTableNumber').textContent = order.table_number;
        document.getElementById('modalTotal').textContent = '₱' + order.total_amount;

        const itemsContainer = document.getElementById('modalItems');
        itemsContainer.innerHTML = '';

        if (!order.items || order.items.length === 0) {
            itemsContainer.innerHTML = `
                <div class="px-4 py-4 text-sm text-gray-400">
                    No items
                </div>
            `;
        } else {
            order.items.forEach((item) => {
                const row = document.createElement('div');
                row.className = 'px-4 py-3 flex items-center justify-between gap-4';

                row.innerHTML = `
                    <div>
                        <p class="text-sm font-black text-gray-900">
                            ${item.quantity}x ${item.name}
                        </p>
                    </div>
                    <p class="text-sm font-black text-orange-500">
                        ₱${item.price}
                    </p>
                `;

                itemsContainer.appendChild(row);
            });
        }

        const form = document.getElementById('paymentForm');
        form.action = order.action_url;

        document.getElementById('paymentModal').classList.remove('hidden');
        document.getElementById('paymentModal').classList.add('flex');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('paymentModal').classList.remove('flex');
    }

    function submitPaymentMethod(method) {
        const message = method === 'cash'
            ? 'Confirm cash payment for this order?'
            : 'Open Xendit QR PH checkout for this order?';

        if (!confirm(message)) {
            return;
        }

        document.getElementById('selectedPaymentMethod').value = method;
        document.getElementById('paymentForm').submit();
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePaymentModal();
        }
    });
</script>

@endsection