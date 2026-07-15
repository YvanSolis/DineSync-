@extends('layouts.service')

@section('page-title', 'Payments')
@section('page-subtitle', 'Manage counter payments, pay later balances, and digital payment status')

@section('content')


<style>
    .service-premium-shell {
        position: relative;
    }

    .service-premium-panel {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.94);
        border-radius: 1.5rem;
        box-shadow: 0 18px 46px rgba(15, 23, 42, 0.065);
        backdrop-filter: blur(14px);
    }

    .service-premium-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(254, 215, 170, 0.88);
        border-radius: 1.75rem;
        background:
            radial-gradient(circle at top right, rgba(251, 146, 60, 0.18), transparent 30%),
            linear-gradient(135deg, rgba(255, 247, 237, 0.98), rgba(255, 255, 255, 0.98) 55%, rgba(255, 251, 235, 0.98));
        box-shadow: 0 20px 50px rgba(249, 115, 22, 0.08);
    }

    .service-premium-stat {
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid rgba(226, 232, 240, 0.94);
        border-radius: 1.35rem;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.055);
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            border-color 0.2s ease;
    }

    .service-premium-stat:hover {
        transform: translateY(-2px);
        border-color: rgba(251, 146, 60, 0.32);
        box-shadow: 0 20px 42px rgba(15, 23, 42, 0.08);
    }

    .service-premium-card {
        border-radius: 1.35rem !important;
        border-color: rgba(226, 232, 240, 0.92) !important;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05) !important;
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            border-color 0.2s ease;
    }

    .service-premium-card:hover {
        transform: translateY(-1px);
        border-color: rgba(251, 146, 60, 0.28) !important;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.075) !important;
    }

    .service-premium-shell table thead {
        background: linear-gradient(to right, #fff7ed, #ffffff, #fffbeb) !important;
    }

    .service-premium-shell table tbody tr {
        transition: background-color 0.18s ease;
    }

    .service-premium-shell table tbody tr:hover {
        background: rgba(255, 247, 237, 0.66) !important;
    }

    .service-premium-shell input,
    .service-premium-shell select,
    .service-premium-shell textarea {
        border-radius: 0.9rem !important;
        border-color: #e2e8f0 !important;
        box-shadow: none !important;
    }

    .service-premium-shell input:focus,
    .service-premium-shell select:focus,
    .service-premium-shell textarea:focus {
        border-color: #fb923c !important;
        box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.12) !important;
    }

    .service-premium-shell button,
    .service-premium-shell a {
        -webkit-tap-highlight-color: transparent;
    }

    .service-premium-shell .premium-section-heading {
        font-weight: 900;
        letter-spacing: -0.02em;
        color: #0f172a;
    }

    .service-premium-shell .premium-muted {
        color: #64748b;
    }

    @media (max-width: 640px) {
        .service-premium-hero {
            border-radius: 1.35rem;
        }

        .service-premium-panel {
            border-radius: 1.25rem;
        }
    }

    /* Admin-style thermal receipt */
    @keyframes receiptModalIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .receipt-modal-panel { animation: receiptModalIn .22s ease both; }

    .thermal-receipt {
        width: 100%;
        max-width: 380px;
        margin: 0 auto;
        background: #fff;
        color: #111827;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 12px;
        line-height: 1.45;
    }

    .receipt-rule {
        border-top: 1px dashed #111827;
        margin: 12px 0;
    }

    .receipt-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .receipt-row > :last-child {
        text-align: right;
        flex-shrink: 0;
    }

    .receipt-label { color: #4b5563; }
    .receipt-total-row { font-size: 16px; font-weight: 900; }

    @media (max-width: 640px) {
        #receiptModal {
            align-items: stretch !important;
            justify-content: stretch !important;
            padding: 0 !important;
        }

        #receiptModal > div {
            width: 100% !important;
            max-width: 100% !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            border-radius: 0 !important;
        }
    }

    @media print {
        body * { visibility: hidden !important; }
        #receiptModal, #receiptModal * { visibility: visible !important; }
        #receiptModal {
            position: absolute !important;
            inset: 0 !important;
            display: block !important;
            background: #fff !important;
            padding: 0 !important;
        }
        #receiptModal > div {
            width: 80mm !important;
            max-width: 80mm !important;
            height: auto !important;
            max-height: none !important;
            margin: 0 auto !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            overflow: visible !important;
        }
        .receipt-screen-only { display: none !important; }
        #receiptPrintArea {
            overflow: visible !important;
            background: #fff !important;
            padding: 0 !important;
        }
        #receiptContent {
            width: 80mm !important;
            max-width: 80mm !important;
            box-shadow: none !important;
            padding: 7mm 5mm !important;
        }
    }

</style>


@php
    $selectedDateLabel = \Carbon\Carbon::parse($selectedDate, 'Asia/Manila')->format('M d, Y');

    $formatServiceDateTime = function ($value) {
        if (!$value) {
            return 'No date';
        }

        return \Carbon\Carbon::parse($value)
            ->timezone('Asia/Manila')
            ->format('M d, Y h:i A');
    };

    $formatServiceDiff = function ($value) {
        if (!$value) {
            return 'No date';
        }

        return \Carbon\Carbon::parse($value)
            ->timezone('Asia/Manila')
            ->diffForHumans();
    };

    $getDiscountMeta = function ($order) {
        $type = strtolower(trim($order->discount_type ?? 'none'));

        return match ($type) {
            'senior' => [
                'label' => 'Senior Citizen Discount',
                'short' => 'Senior',
                'class' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            'pwd' => [
                'label' => 'PWD Discount',
                'short' => 'PWD',
                'class' => 'bg-purple-50 text-purple-700 border-purple-200',
            ],
            default => [
                'label' => 'No Discount',
                'short' => 'None',
                'class' => 'bg-gray-50 text-gray-600 border-gray-200',
            ],
        };
    };

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
                'total_amount_raw' => (float) ($order->total_amount ?? 0),
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

    $receiptsForModal = $orders->getCollection()->mapWithKeys(function ($order) {
        $tableNumber = $order->source_table_number
            ?? $order->table_number
            ?? $order->table?->table_number
            ?? $order->table?->name
            ?? null;

        $discountType = strtolower(trim($order->discount_type ?? 'none'));
        $hasGovernmentDiscount = in_array($discountType, ['senior', 'pwd'], true);
        $subtotal = (float) ($order->total_amount ?? 0);
        $finalAmount = (float) ($order->final_amount ?? $order->total_amount ?? 0);

        return [
            $order->id => [
                'id' => $order->id,
                'transaction_id' => $order->xendit_invoice_id
                    ?? $order->transaction_id
                    ?? ('PAY-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)),
                'order_number' => $order->order_number ?? ('Order #' . $order->id),
                'table_number' => $tableNumber ? 'Table ' . $tableNumber : 'No table',
                'processed_by' => auth()->user()->name ?? 'Service Staff',
                'payment_method' => $order->payment_method ?? 'Cash',
                'payment_status' => $order->payment_status ?? 'paid',
                'paid_at' => optional($order->paid_at ?? $order->updated_at)?->toIso8601String(),
                'subtotal' => $subtotal,
                'final_amount' => $finalAmount,
                'discount_type' => $discountType,
                'qualified_diners' => (int) ($order->qualified_diners ?? 0),
                'total_diners' => (int) ($order->total_diners ?? 0),
                'discount_holder_name' => $order->discount_holder_name ?? '',
                'discount_id_number' => $order->discount_id_number ?? '',
                'vat_exempt_amount' => (float) ($order->vat_exempt_amount ?? 0),
                'discount_amount' => (float) ($order->discount_amount ?? 0),
                'has_government_discount' => $hasGovernmentDiscount,
                'items' => $order->items->map(function ($item) {
                    $quantity = (float) ($item->quantity ?? 1);
                    $unitPrice = (float) ($item->price ?? 0);
                    return [
                        'quantity' => $quantity,
                        'name' => $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item',
                        'unit_price' => $unitPrice,
                        'line_total' => $unitPrice * $quantity,
                    ];
                })->values(),
            ],
        ];
    });

@endphp

<div class="service-premium-shell space-y-6">

    <section class="service-premium-hero px-5 py-6 sm:px-7">
        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-orange-600">
                    Payment Operations
                </span>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    Payments
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Handle counter payments, pay-later orders, and QR PH payment monitoring.
                </p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/90 px-4 py-3 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Selected View</p>
                <p class="mt-1 text-sm font-black text-slate-900">
                    {{ $mode === 'all' ? 'All records' : $selectedDateLabel }}
                </p>
            </div>
        </div>
    </section>


    {{-- STATS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 w-full xl:w-auto">
        <div class="service-premium-stat px-5 py-4 min-w-[155px]">
            <p class="text-xs font-semibold text-gray-500">Counter Waiting</p>
            <p class="text-2xl font-black text-orange-500 mt-1">{{ $stats['awaiting_counter'] ?? 0 }}</p>
        </div>

        <div class="service-premium-stat px-5 py-4 min-w-[155px]">
            <p class="text-xs font-semibold text-gray-500">Pay Later</p>
            <p class="text-2xl font-black text-purple-500 mt-1">{{ $stats['pay_later_unpaid'] ?? 0 }}</p>
        </div>

        <div class="service-premium-stat px-5 py-4 min-w-[155px]">
            <p class="text-xs font-semibold text-gray-500">Digital Pending</p>
            <p class="text-2xl font-black text-blue-500 mt-1">{{ $stats['digital_pending'] ?? 0 }}</p>
        </div>

        <div class="service-premium-stat px-5 py-4 min-w-[155px]">
            <p class="text-xs font-semibold text-gray-500">Paid</p>
            <p class="text-2xl font-black text-green-500 mt-1">{{ $stats['paid'] ?? 0 }}</p>
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
    <div class="service-premium-panel overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="premium-section-heading text-lg">Active Tables</h2>
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
    <div class="service-premium-panel overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div>
                <h2 class="premium-section-heading text-lg">
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

                    $discountMeta = $getDiscountMeta($order);
                    $discountType = strtolower(trim($order->discount_type ?? 'none'));
                    $hasGovernmentDiscount = in_array($discountType, ['senior', 'pwd'], true);

                    $originalAmount = (float) ($order->total_amount ?? 0);
                    $vatExemptAmount = (float) ($order->vat_exempt_amount ?? 0);
                    $discountAmount = (float) ($order->discount_amount ?? 0);
                    $finalAmount = (float) (
                        $order->final_amount
                        ?? $order->total_amount
                        ?? 0
                    );
                @endphp

                <div class="service-premium-card rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-black text-gray-950 truncate">
                                {{ $order->order_number ?? 'No order number' }}
                            </p>

                            <p class="text-xs text-gray-500 mt-1">
                                Created: {{ $formatServiceDiff($order->created_at) }}
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

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex px-3 py-1 rounded-full border text-[11px] font-black {{ $discountMeta['class'] }}">
                                    {{ $discountMeta['label'] }}
                                </span>

                                <span class="text-sm font-black text-orange-500">
                                    {{ $hasGovernmentDiscount ? 'Final' : 'Total' }}:
                                    ₱{{ number_format($finalAmount, 2) }}
                                </span>
                            </div>

                            @if ($hasGovernmentDiscount)
                                <div class="mt-3 rounded-2xl border border-orange-100 bg-orange-50/70 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-xs font-black uppercase tracking-wide text-orange-600">
                                            Discount Breakdown
                                        </p>

                                        <span class="text-[10px] font-black text-orange-700">
                                            {{ $order->qualified_diners ?? 0 }}/{{ $order->total_diners ?? 0 }} qualified
                                        </span>
                                    </div>

                                    <div class="mt-3 space-y-2 text-xs">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-500">Original Total</span>
                                            <span class="font-black text-gray-900">
                                                ₱{{ number_format($originalAmount, 2) }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-500">VAT Exemption</span>
                                            <span class="font-black text-blue-600">
                                                -₱{{ number_format($vatExemptAmount, 2) }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-500">20% Discount</span>
                                            <span class="font-black text-green-600">
                                                -₱{{ number_format($discountAmount, 2) }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between gap-3 border-t border-orange-100 pt-2">
                                            <span class="font-black text-gray-900">Final Payable</span>
                                            <span class="text-base font-black text-orange-600">
                                                ₱{{ number_format($finalAmount, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-3 border-t border-orange-100 pt-3 text-[11px] leading-5 text-gray-500">
                                        <p>
                                            <span class="font-black text-gray-700">Holder:</span>
                                            {{ $order->discount_holder_name ?: 'Not recorded' }}
                                        </p>

                                        <p>
                                            <span class="font-black text-gray-700">ID:</span>
                                            {{ $order->discount_id_number ?: 'Not recorded' }}
                                        </p>

                                        <p>
                                            <span class="font-black text-gray-700">Verified:</span>
                                            {{ $order->discount_verified_at ? $formatServiceDateTime($order->discount_verified_at) : 'Not recorded' }}
                                        </p>
                                    </div>
                                </div>
                            @endif
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
                                        Paid: {{ $formatServiceDateTime($order->paid_at) }}
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
                                <button
                                    type="button"
                                    onclick="openReceiptModal('{{ $order->id }}')"
                                    class="w-full rounded-xl bg-orange-500 px-4 py-3 text-center text-sm font-black text-white shadow-sm transition hover:bg-orange-600">
                                    View Receipt
                                </button>
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
            <table class="w-full min-w-[1360px] text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-5 py-4 font-bold">Order</th>
                        <th class="px-5 py-4 font-bold">Table</th>
                        <th class="px-5 py-4 font-bold">Items</th>
                        <th class="px-5 py-4 font-bold">Payment Method</th>
                        <th class="px-5 py-4 font-bold">Payment Status</th>
                        <th class="px-5 py-4 font-bold">Discount</th>
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

                            $discountMeta = $getDiscountMeta($order);
                            $discountType = strtolower(trim($order->discount_type ?? 'none'));
                            $hasGovernmentDiscount = in_array($discountType, ['senior', 'pwd'], true);

                            $originalAmount = (float) ($order->total_amount ?? 0);
                            $vatExemptAmount = (float) ($order->vat_exempt_amount ?? 0);
                            $discountAmount = (float) ($order->discount_amount ?? 0);
                            $finalAmount = (float) (
                                $order->final_amount
                                ?? $order->total_amount
                                ?? 0
                            );
                        @endphp

                        <tr class="align-top hover:bg-gray-50 transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-black text-gray-950">
                                    {{ $order->order_number ?? 'No order number' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Created: {{ $formatServiceDiff($order->created_at) }}
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
                                    {{ $hasGovernmentDiscount ? 'Final' : 'Total' }}:
                                    ₱{{ number_format($finalAmount, 2) }}
                                </p>

                                @if ($hasGovernmentDiscount)
                                    <p class="text-[10px] text-gray-400 mt-1">
                                        Original: ₱{{ number_format($originalAmount, 2) }}
                                    </p>
                                @endif
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
                                        Paid: {{ $formatServiceDateTime($order->paid_at) }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 w-[240px]">
                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $discountMeta['class'] }}">
                                    {{ $discountMeta['short'] }}
                                </span>

                                @if ($hasGovernmentDiscount)
                                    <div class="mt-3 space-y-1 text-xs">
                                        <p class="text-gray-500">
                                            Qualified:
                                            <span class="font-black text-gray-800">
                                                {{ $order->qualified_diners ?? 0 }}/{{ $order->total_diners ?? 0 }}
                                            </span>
                                        </p>

                                        <p class="text-gray-500">
                                            VAT exempt:
                                            <span class="font-black text-blue-600">
                                                ₱{{ number_format($vatExemptAmount, 2) }}
                                            </span>
                                        </p>

                                        <p class="text-gray-500">
                                            Discount:
                                            <span class="font-black text-green-600">
                                                ₱{{ number_format($discountAmount, 2) }}
                                            </span>
                                        </p>

                                        <p class="text-gray-500">
                                            Final:
                                            <span class="font-black text-orange-600">
                                                ₱{{ number_format($finalAmount, 2) }}
                                            </span>
                                        </p>

                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-[10px] font-black text-orange-600">
                                                View ID details
                                            </summary>

                                            <div class="mt-2 rounded-xl bg-gray-50 p-2 text-[10px] leading-4 text-gray-500">
                                                <p>
                                                    <span class="font-black text-gray-700">Holder:</span>
                                                    {{ $order->discount_holder_name ?: 'Not recorded' }}
                                                </p>
                                                <p>
                                                    <span class="font-black text-gray-700">ID:</span>
                                                    {{ $order->discount_id_number ?: 'Not recorded' }}
                                                </p>
                                                <p>
                                                    <span class="font-black text-gray-700">Verified:</span>
                                                    {{ $order->discount_verified_at ? $formatServiceDateTime($order->discount_verified_at) : 'Not recorded' }}
                                                </p>
                                            </div>
                                        </details>
                                    </div>
                                @else
                                    <p class="text-xs text-gray-400 mt-2">
                                        Regular payment
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
                                    <button
                                        type="button"
                                        onclick="openReceiptModal('{{ $order->id }}')"
                                        class="inline-flex items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-orange-600">
                                        View Receipt
                                    </button>
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
                            <td colspan="8" class="px-5 py-14 text-center">
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


{{-- ADMIN-STYLE THERMAL RECEIPT MODAL --}}
<div id="receiptModal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-950/70 p-3 backdrop-blur-sm sm:p-5" aria-hidden="true">
    <div class="receipt-modal-panel flex max-h-[94vh] w-full max-w-md flex-col overflow-hidden rounded-[28px] border border-orange-100 bg-white shadow-2xl">
        <div class="receipt-screen-only flex shrink-0 items-center justify-between gap-3 border-b bg-gradient-to-r from-orange-50 via-white to-amber-50 px-5 py-4">
            <div>
                <h3 class="text-lg font-black text-gray-950">Payment Receipt</h3>
                <p class="text-xs text-gray-500">Customer copy · Thermal receipt format</p>
            </div>
            <button type="button" onclick="closeReceiptModal()" class="flex h-10 w-10 items-center justify-center rounded-full border border-orange-100 bg-white text-xl text-gray-500 shadow-sm transition hover:bg-orange-50 hover:text-orange-700">&times;</button>
        </div>

        <div id="receiptPrintArea" class="min-h-0 flex-1 overflow-y-auto bg-gray-100 p-3 sm:p-5">
            <div id="receiptContent" class="thermal-receipt rounded-sm bg-white px-5 py-6 shadow-sm">
                <header class="text-center">
                    <img src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}" alt="Chef Oppa Logo" class="mx-auto mb-2 h-16 w-16 rounded-full object-cover grayscale">
                    <h2 class="text-[20px] font-black uppercase tracking-wide">CHEF OPPA</h2>
                    <p class="mt-0.5 font-bold uppercase">Main Branch</p>
                    <p class="mt-1 leading-4 text-gray-700">DineSync+ Restaurant</p>
                    <p class="leading-4 text-gray-700">dinesync.shop</p>
                </header>

                <div class="receipt-rule"></div>
                <p class="text-center text-[13px] font-black uppercase tracking-[0.18em]">Payment Receipt</p>
                <p class="text-center text-[10px] uppercase text-gray-500">Customer Copy</p>
                <div class="receipt-rule"></div>

                <section class="space-y-1">
                    <div class="receipt-row"><span class="receipt-label">Receipt No.</span><strong id="receiptTransactionId">-</strong></div>
                    <div class="receipt-row"><span class="receipt-label">Order No.</span><strong id="receiptOrderId">-</strong></div>
                    <div class="receipt-row"><span class="receipt-label">Date / Time</span><span id="receiptDate">-</span></div>
                    <div class="receipt-row"><span class="receipt-label">Table</span><span id="receiptTable">-</span></div>
                    <div class="receipt-row"><span class="receipt-label">Processed By</span><span id="receiptProcessedBy">Service Staff</span></div>
                    <div class="receipt-row"><span class="receipt-label">Payment</span><span id="receiptMethod">-</span></div>
                    <div class="receipt-row"><span class="receipt-label">Status</span><strong id="receiptStatusText">PAID</strong></div>
                </section>

                <div class="receipt-rule"></div>
                <section>
                    <div class="receipt-row mb-2 font-black uppercase"><span>Qty / Item</span><span>Amount</span></div>
                    <div id="receiptItems" class="space-y-2"></div>
                </section>

                <div class="receipt-rule"></div>
                <section class="space-y-1">
                    <div class="receipt-row"><span>Subtotal</span><strong id="receiptSubtotal">₱0.00</strong></div>
                    <div id="receiptDiscountSection" class="hidden">
                        <div class="my-2 border-t border-dotted border-gray-400"></div>
                        <p class="mb-1 font-black uppercase">Government Discount</p>
                        <div class="receipt-row"><span>Type</span><strong id="receiptDiscountType">-</strong></div>
                        <div class="receipt-row"><span>Qualified Diners</span><span id="receiptQualifiedDiners">-</span></div>
                        <div class="receipt-row"><span>ID Holder(s)</span><span id="receiptHolderName" class="max-w-[210px] break-words">-</span></div>
                        <div class="receipt-row"><span>ID Number(s)</span><span id="receiptIdNumber" class="max-w-[210px] break-words">-</span></div>
                        <div class="receipt-row"><span>VAT Exemption</span><strong id="receiptVatExempt">-₱0.00</strong></div>
                        <div class="receipt-row"><span>20% Discount</span><strong id="receiptDiscountAmount">-₱0.00</strong></div>
                    </div>
                </section>

                <div class="receipt-rule"></div>
                <div class="receipt-row receipt-total-row"><span>TOTAL</span><span id="receiptBottomTotal">₱0.00</span></div>
                <div class="receipt-rule"></div>

                <footer class="text-center">
                    <p class="font-black uppercase">Thank you for dining with us!</p>
                    <p>Please come again.</p>
                    <p class="mt-2 text-[10px] text-gray-500">This is a system-generated customer payment receipt.</p>
                </footer>
            </div>
        </div>

        <div class="receipt-screen-only flex shrink-0 flex-col-reverse gap-2 border-t bg-white px-4 py-4 sm:flex-row sm:justify-end">
            <button type="button" onclick="closeReceiptModal()" class="rounded-xl bg-gray-100 px-5 py-2.5 font-bold text-gray-700 hover:bg-gray-200">Close</button>
            <button type="button" onclick="printThermalReceipt()" class="rounded-xl bg-orange-500 px-5 py-2.5 font-black text-white hover:bg-orange-600">Print Receipt</button>
        </div>
    </div>
</div>

{{-- PREMIUM PAYMENT MODAL --}}
<div
    id="paymentModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/70 p-3 backdrop-blur-sm sm:p-5"
    aria-hidden="true"
>
    <div
        id="paymentModalCard"
        class="flex max-h-[94vh] w-full max-w-6xl scale-[0.98] flex-col overflow-hidden rounded-[28px] border border-white/70 bg-white opacity-0 shadow-2xl transition duration-200"
        onclick="event.stopPropagation()"
    >
        {{-- Modal Header --}}
        <div class="shrink-0 border-b border-slate-100 bg-white px-5 py-4 sm:px-6 sm:py-5">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-orange-600">
                            Counter Payment
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-700">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Cash Only
                        </span>
                    </div>

                    <h2
                        id="modalOrderNumber"
                        class="mt-2 truncate text-xl font-black tracking-tight text-slate-950 sm:text-2xl"
                    >
                        Order
                    </h2>

                    <p
                        id="modalTableNumber"
                        class="mt-1 text-sm font-semibold text-slate-500"
                    >
                        Table
                    </p>
                </div>

                <button
                    type="button"
                    onclick="closePaymentModal()"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-xl font-bold text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                    aria-label="Close payment modal"
                >
                    &times;
                </button>
            </div>
        </div>

        {{-- Scrollable Modal Body --}}
        <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50/70">
            <form id="paymentForm" method="POST" action="#">
                @csrf
                @method('PATCH')

                <input
                    type="hidden"
                    name="payment_method"
                    id="selectedPaymentMethod"
                    value="cash"
                >

                <div class="grid grid-cols-1 gap-5 p-4 sm:p-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)] lg:p-6">

                    {{-- LEFT COLUMN --}}
                    <div class="space-y-5">

                        {{-- Order Summary --}}
                        <section class="overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-sm">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3.5">
                                <div>
                                    <h3 class="text-sm font-black text-slate-950">
                                        Order Summary
                                    </h3>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Review the items before confirming payment.
                                    </p>
                                </div>

                                <span class="rounded-full bg-orange-100 px-3 py-1 text-[10px] font-black text-orange-700">
                                    Pay at Counter
                                </span>
                            </div>

                            <div
                                id="modalItems"
                                class="max-h-[240px] divide-y divide-slate-100 overflow-y-auto"
                            ></div>

                            <div class="flex items-center justify-between gap-4 border-t border-orange-100 bg-orange-50 px-4 py-4">
                                <span class="text-sm font-black text-slate-800">
                                    Original Total
                                </span>

                                <span
                                    id="modalTotal"
                                    class="text-2xl font-black tracking-tight text-orange-600"
                                >
                                    ₱0.00
                                </span>
                            </div>
                        </section>

                        {{-- Government Discount --}}
                        <section class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                            <div>
                                <h3 class="text-sm font-black text-slate-950">
                                    Government Discount
                                </h3>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Apply only after checking every qualified customer’s valid Senior Citizen or PWD ID.
                                </p>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <label class="discount-option relative cursor-pointer overflow-hidden rounded-2xl border border-orange-300 bg-orange-50 p-4 transition">
                                    <input
                                        type="radio"
                                        name="discount_type"
                                        value="none"
                                        class="sr-only"
                                        checked
                                        onchange="updateDiscountPreview()"
                                    >

                                    <span class="discount-check absolute right-3 top-3 flex h-6 w-6 items-center justify-center rounded-full bg-orange-500 text-xs font-black text-white">
                                        ✓
                                    </span>

                                    <span class="block pr-8 text-sm font-black text-orange-700">
                                        No Discount
                                    </span>

                                    <span class="mt-1 block text-xs leading-5 text-orange-600/80">
                                        Regular cashier payment
                                    </span>
                                </label>

                                <label class="discount-option relative cursor-pointer overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-orange-200 hover:bg-orange-50/50">
                                    <input
                                        type="radio"
                                        name="discount_type"
                                        value="senior"
                                        class="sr-only"
                                        onchange="updateDiscountPreview()"
                                    >

                                    <span class="discount-check absolute right-3 top-3 hidden h-6 w-6 items-center justify-center rounded-full bg-orange-500 text-xs font-black text-white">
                                        ✓
                                    </span>

                                    <span class="block pr-8 text-sm font-black text-slate-800">
                                        Senior Citizen
                                    </span>

                                    <span class="mt-1 block text-xs leading-5 text-slate-500">
                                        VAT exemption + 20%
                                    </span>
                                </label>

                                <label class="discount-option relative cursor-pointer overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-orange-200 hover:bg-orange-50/50">
                                    <input
                                        type="radio"
                                        name="discount_type"
                                        value="pwd"
                                        class="sr-only"
                                        onchange="updateDiscountPreview()"
                                    >

                                    <span class="discount-check absolute right-3 top-3 hidden h-6 w-6 items-center justify-center rounded-full bg-orange-500 text-xs font-black text-white">
                                        ✓
                                    </span>

                                    <span class="block pr-8 text-sm font-black text-slate-800">
                                        PWD
                                    </span>

                                    <span class="mt-1 block text-xs leading-5 text-slate-500">
                                        VAT exemption + 20%
                                    </span>
                                </label>
                            </div>

                            <div
                                id="discountFields"
                                class="mt-5 hidden space-y-4 border-t border-slate-100 pt-5"
                            >
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label>
                                        <span class="mb-1.5 block text-xs font-black text-slate-600">
                                            Total Diners
                                        </span>

                                        <input
                                            type="number"
                                            name="total_diners"
                                            id="totalDiners"
                                            min="1"
                                            max="100"
                                            step="1"
                                            value="1"
                                            class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm font-semibold"
                                            oninput="updateDiscountPreview()"
                                        >
                                    </label>

                                    <label>
                                        <span class="mb-1.5 block text-xs font-black text-slate-600">
                                            Qualified Diners
                                        </span>

                                        <input
                                            type="number"
                                            name="qualified_diners"
                                            id="qualifiedDiners"
                                            min="1"
                                            max="100"
                                            step="1"
                                            value="1"
                                            class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm font-semibold"
                                            oninput="updateDiscountPreview(); updateMultipleHolderHint();"
                                        >
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label>
                                        <span class="mb-1.5 block text-xs font-black text-slate-600">
                                            ID Holder Name(s)
                                        </span>

                                        <textarea
                                            name="discount_holder_name"
                                            id="discountHolderName"
                                            rows="3"
                                            maxlength="1000"
                                            class="w-full resize-none rounded-2xl border-slate-200 px-4 py-3 text-sm"
                                            placeholder="Full name"
                                        ></textarea>
                                    </label>

                                    <label>
                                        <span class="mb-1.5 block text-xs font-black text-slate-600">
                                            ID Number(s)
                                        </span>

                                        <textarea
                                            name="discount_id_number"
                                            id="discountIdNumber"
                                            rows="3"
                                            maxlength="1000"
                                            class="w-full resize-none rounded-2xl border-slate-200 px-4 py-3 text-sm"
                                            placeholder="Senior/PWD ID number"
                                        ></textarea>
                                    </label>
                                </div>

                                <p
                                    id="multipleHolderHint"
                                    class="hidden rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-semibold leading-5 text-blue-700"
                                >
                                    Enter one name and one ID number per qualified diner, separated by commas or new lines.
                                </p>

                                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                    <input
                                        type="checkbox"
                                        name="discount_id_verified"
                                        id="discountIdVerified"
                                        value="1"
                                        class="mt-0.5 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-200"
                                    >

                                    <span>
                                        <span class="block text-sm font-black text-emerald-700">
                                            Valid ID verified
                                        </span>

                                        <span class="mt-0.5 block text-xs leading-5 text-emerald-700/80">
                                            Confirm that all qualified diners’ IDs were checked.
                                        </span>
                                    </span>
                                </label>

                                <p
                                    id="discountValidationMessage"
                                    class="hidden rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-bold text-red-600"
                                ></p>
                            </div>
                        </section>
                    </div>

                    {{-- RIGHT COLUMN --}}
                    <div class="space-y-5 lg:sticky lg:top-0 lg:self-start">

                        {{-- Receipt Breakdown --}}
                        <section class="overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 bg-slate-950 px-5 py-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-orange-300">
                                    Payment Summary
                                </p>

                                <h3 class="mt-1 text-lg font-black text-white">
                                    Amount to Collect
                                </h3>
                            </div>

                            <div class="space-y-3 px-5 py-5 text-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-slate-500">
                                        Original Total
                                    </span>

                                    <span
                                        id="breakdownOriginal"
                                        class="font-black text-slate-900"
                                    >
                                        ₱0.00
                                    </span>
                                </div>

                                <div
                                    id="vatRow"
                                    class="hidden items-center justify-between gap-4"
                                >
                                    <span class="text-slate-500">
                                        VAT Exemption
                                    </span>

                                    <span
                                        id="breakdownVat"
                                        class="font-black text-blue-600"
                                    >
                                        -₱0.00
                                    </span>
                                </div>

                                <div
                                    id="discountRow"
                                    class="hidden items-center justify-between gap-4"
                                >
                                    <span class="text-slate-500">
                                        20% Discount
                                    </span>

                                    <span
                                        id="breakdownDiscount"
                                        class="font-black text-emerald-600"
                                    >
                                        -₱0.00
                                    </span>
                                </div>

                                <div class="border-t border-dashed border-slate-200 pt-4">
                                    <div class="flex items-end justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                                Final Payable
                                            </p>

                                            <p
                                                id="breakdownDiscountLabel"
                                                class="mt-1 text-xs font-semibold text-slate-500"
                                            >
                                                Regular payment
                                            </p>
                                        </div>

                                        <span
                                            id="breakdownFinal"
                                            class="text-3xl font-black tracking-tight text-orange-600"
                                        >
                                            ₱0.00
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- Safety Notice --}}
                        <section class="rounded-[22px] border border-orange-200 bg-orange-50 p-4">
                            <p class="text-sm font-black text-orange-800">
                                Cashier reminder
                            </p>

                            <p class="mt-1 text-xs leading-5 text-orange-700">
                                QR PH is completed from the customer mobile app. This screen is only for cash, pay-at-counter, or pay-later settlement.
                            </p>
                        </section>
                    </div>
                </div>
            </form>
        </div>

        {{-- Sticky Modal Footer --}}
        <div class="shrink-0 border-t border-slate-200 bg-white px-4 py-4 sm:px-6">
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <button
                    type="button"
                    onclick="closePaymentModal()"
                    class="inline-flex min-h-[48px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    id="reviewPaymentButton"
                    type="button"
                    onclick="openPaymentConfirmation()"
                    class="inline-flex min-h-[48px] items-center justify-center rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.99]"
                >
                    Review & Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

{{-- PREMIUM CONFIRMATION MODAL --}}
<div
    id="paymentConfirmModal"
    class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-950/75 p-4 backdrop-blur-sm"
    aria-hidden="true"
>
    <div
        id="paymentConfirmCard"
        class="w-full max-w-md scale-[0.96] rounded-[26px] border border-white/70 bg-white p-5 opacity-0 shadow-2xl transition duration-200 sm:p-6"
        onclick="event.stopPropagation()"
    >
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-xl font-black text-emerald-700">
            ₱
        </div>

        <h3 class="mt-4 text-xl font-black tracking-tight text-slate-950">
            Confirm Cash Payment
        </h3>

        <p
            id="paymentConfirmDescription"
            class="mt-2 text-sm leading-6 text-slate-500"
        >
            Review the payment details before submitting.
        </p>

        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center justify-between gap-4">
                <span class="text-sm font-semibold text-slate-500">
                    Discount
                </span>

                <span
                    id="confirmDiscountType"
                    class="text-sm font-black text-slate-900"
                >
                    None
                </span>
            </div>

            <div class="mt-3 flex items-center justify-between gap-4 border-t border-slate-200 pt-3">
                <span class="text-sm font-black text-slate-900">
                    Final Amount
                </span>

                <span
                    id="confirmFinalAmount"
                    class="text-2xl font-black text-orange-600"
                >
                    ₱0.00
                </span>
            </div>
        </div>

        <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button
                type="button"
                onclick="closePaymentConfirmation()"
                class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 hover:bg-slate-50"
            >
                Go Back
            </button>

            <button
                id="finalSubmitButton"
                type="button"
                onclick="submitPaymentForm()"
                class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70"
            >
                <span id="finalSubmitSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                <span id="finalSubmitText">Confirm Payment</span>
            </button>
        </div>
    </div>
</div>

<script>
    window.paymentOrders = @json($ordersForModal);

    window.receiptOrders = @json($receiptsForModal);

    function receiptMoney(value) {
        return `₱${Number(value || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`;
    }

    function receiptDateTime(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '-';
        return date.toLocaleString('en-US', {
            month: 'short', day: '2-digit', year: 'numeric',
            hour: 'numeric', minute: '2-digit'
        });
    }

    function receiptPaymentLabel(value) {
        const normalized = String(value || '').toLowerCase().replaceAll('_', ' ');
        if (normalized.includes('digital') || normalized.includes('xendit') || normalized.includes('qr')) return 'Digital Payment';
        if (normalized.includes('later')) return 'Pay Later';
        if (normalized.includes('counter')) return 'Pay at Counter';
        if (normalized.includes('cash')) return 'Cash';
        return value || 'Cash';
    }

    function openReceiptModal(orderId) {
        const receipt = window.receiptOrders?.[orderId];
        if (!receipt) return;

        document.getElementById('receiptTransactionId').textContent = receipt.transaction_id || '-';
        document.getElementById('receiptOrderId').textContent = receipt.order_number || '-';
        document.getElementById('receiptDate').textContent = receiptDateTime(receipt.paid_at);
        document.getElementById('receiptTable').textContent = receipt.table_number || '-';
        document.getElementById('receiptProcessedBy').textContent = receipt.processed_by || 'Service Staff';
        document.getElementById('receiptMethod').textContent = receiptPaymentLabel(receipt.payment_method);
        document.getElementById('receiptStatusText').textContent = String(receipt.payment_status || 'paid').toUpperCase();
        document.getElementById('receiptSubtotal').textContent = receiptMoney(receipt.subtotal);
        document.getElementById('receiptBottomTotal').textContent = receiptMoney(receipt.final_amount);

        const items = document.getElementById('receiptItems');
        items.innerHTML = '';
        (receipt.items || []).forEach(item => {
            const row = document.createElement('div');
            row.className = 'receipt-row';
            row.innerHTML = `
                <div class="min-w-0">
                    <div><strong>${item.quantity}x</strong> ${String(item.name || 'Menu item').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]))}</div>
                    <div class="text-[10px] text-gray-500">@ ${receiptMoney(item.unit_price)}</div>
                </div>
                <strong>${receiptMoney(item.line_total)}</strong>`;
            items.appendChild(row);
        });
        if (!receipt.items || receipt.items.length === 0) {
            items.innerHTML = '<p class="text-center text-gray-500">No items recorded</p>';
        }

        const discountSection = document.getElementById('receiptDiscountSection');
        if (receipt.has_government_discount) {
            discountSection.classList.remove('hidden');
            document.getElementById('receiptDiscountType').textContent = receipt.discount_type === 'pwd' ? 'PWD' : 'Senior Citizen';
            document.getElementById('receiptQualifiedDiners').textContent = `${receipt.qualified_diners || 0} / ${receipt.total_diners || 0}`;
            document.getElementById('receiptHolderName').textContent = receipt.discount_holder_name || '-';
            document.getElementById('receiptIdNumber').textContent = receipt.discount_id_number || '-';
            document.getElementById('receiptVatExempt').textContent = '-' + receiptMoney(receipt.vat_exempt_amount);
            document.getElementById('receiptDiscountAmount').textContent = '-' + receiptMoney(receipt.discount_amount);
        } else {
            discountSection.classList.add('hidden');
        }

        const modal = document.getElementById('receiptModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function closeReceiptModal() {
        const modal = document.getElementById('receiptModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    function printThermalReceipt() {
        window.print();
    }

    document.getElementById('receiptModal').addEventListener('mousedown', function (event) {
        if (event.target === this) closeReceiptModal();
    });


    let activePaymentOrder = null;
    let paymentIsSubmitting = false;

    function formatPeso(amount) {
        return '₱' + Number(amount || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getSelectedDiscountType() {
        return document.querySelector(
            'input[name="discount_type"]:checked'
        )?.value || 'none';
    }

    function getDiscountLabel(type) {
        if (type === 'senior') {
            return 'Senior Citizen';
        }

        if (type === 'pwd') {
            return 'PWD';
        }

        return 'No Discount';
    }

    function parseListEntries(value) {
        return String(value || '')
            .split(/[\r\n,]+/)
            .map(item => item.trim())
            .filter(Boolean);
    }

    function showAnimatedModal(overlayId, cardId) {
        const overlay = document.getElementById(overlayId);
        const card = document.getElementById(cardId);

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        overlay.setAttribute('aria-hidden', 'false');

        requestAnimationFrame(() => {
            card.classList.remove('scale-[0.98]', 'scale-[0.96]', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        });
    }

    function hideAnimatedModal(overlayId, cardId, callback = null) {
        const overlay = document.getElementById(overlayId);
        const card = document.getElementById(cardId);

        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-[0.98]', 'opacity-0');

        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            overlay.setAttribute('aria-hidden', 'true');

            if (callback) {
                callback();
            }
        }, 180);
    }

    function refreshDiscountOptionStyles() {
        document.querySelectorAll('.discount-option').forEach(label => {
            const input = label.querySelector(
                'input[name="discount_type"]'
            );

            const selected = Boolean(input?.checked);
            const check = label.querySelector('.discount-check');
            const title = label.querySelector('span.block');

            label.classList.toggle('border-orange-300', selected);
            label.classList.toggle('bg-orange-50', selected);
            label.classList.toggle('shadow-sm', selected);
            label.classList.toggle('border-slate-200', !selected);
            label.classList.toggle('bg-white', !selected);

            if (check) {
                check.classList.toggle('hidden', !selected);
                check.classList.toggle('flex', selected);
            }

            if (title) {
                title.classList.toggle('text-orange-700', selected);
                title.classList.toggle('text-slate-800', !selected);
            }
        });
    }

    function updateMultipleHolderHint() {
        const qualifiedDiners = Number(
            document.getElementById('qualifiedDiners').value || 0
        );

        const hint = document.getElementById('multipleHolderHint');

        hint.classList.toggle('hidden', qualifiedDiners <= 1);
    }

    function calculatePreview() {
        const type = getSelectedDiscountType();
        const original = Number(
            activePaymentOrder?.total_amount_raw || 0
        );

        const totalDiners = Math.max(
            0,
            parseInt(
                document.getElementById('totalDiners').value || '0',
                10
            )
        );

        const qualifiedDiners = Math.max(
            0,
            parseInt(
                document.getElementById('qualifiedDiners').value || '0',
                10
            )
        );

        if (
            type === 'none' ||
            totalDiners < 1 ||
            qualifiedDiners < 1 ||
            qualifiedDiners > totalDiners
        ) {
            return {
                original,
                vatExempt: 0,
                discount: 0,
                finalAmount: original,
                valid: type === 'none'
            };
        }

        const qualifiedGross =
            original * (qualifiedDiners / totalDiners);

        const vatExclusive = qualifiedGross / 1.12;
        const vatExempt = qualifiedGross - vatExclusive;
        const discount = vatExclusive * 0.20;
        const finalAmount =
            original - vatExempt - discount;

        return {
            original,
            vatExempt,
            discount,
            finalAmount,
            valid: true
        };
    }

    function updateDiscountPreview() {
        const type = getSelectedDiscountType();
        const fields = document.getElementById('discountFields');
        const vatRow = document.getElementById('vatRow');
        const discountRow = document.getElementById('discountRow');
        const validation = document.getElementById(
            'discountValidationMessage'
        );

        refreshDiscountOptionStyles();
        updateMultipleHolderHint();

        validation.classList.add('hidden');
        validation.textContent = '';

        if (type === 'none') {
            fields.classList.add('hidden');
            vatRow.classList.add('hidden');
            vatRow.classList.remove('flex');
            discountRow.classList.add('hidden');
            discountRow.classList.remove('flex');
        } else {
            fields.classList.remove('hidden');
            vatRow.classList.remove('hidden');
            vatRow.classList.add('flex');
            discountRow.classList.remove('hidden');
            discountRow.classList.add('flex');
        }

        const totalDiners = Number(
            document.getElementById('totalDiners').value || 0
        );

        const qualifiedDiners = Number(
            document.getElementById('qualifiedDiners').value || 0
        );

        if (type !== 'none') {
            if (totalDiners < 1) {
                validation.textContent =
                    'Total diners must be at least 1.';
                validation.classList.remove('hidden');
            } else if (qualifiedDiners < 1) {
                validation.textContent =
                    'Qualified diners must be at least 1.';
                validation.classList.remove('hidden');
            } else if (qualifiedDiners > totalDiners) {
                validation.textContent =
                    'Qualified diners cannot exceed total diners.';
                validation.classList.remove('hidden');
            }
        }

        const preview = calculatePreview();

        document.getElementById('breakdownOriginal').textContent =
            formatPeso(preview.original);

        document.getElementById('breakdownVat').textContent =
            '-' + formatPeso(preview.vatExempt);

        document.getElementById('breakdownDiscount').textContent =
            '-' + formatPeso(preview.discount);

        document.getElementById('breakdownFinal').textContent =
            formatPeso(preview.finalAmount);

        document.getElementById('breakdownDiscountLabel').textContent =
            type === 'none'
                ? 'Regular payment'
                : `${getDiscountLabel(type)} discount`;
    }

    function resetDiscountForm() {
        document.querySelector(
            'input[name="discount_type"][value="none"]'
        ).checked = true;

        document.getElementById('totalDiners').value = 1;
        document.getElementById('qualifiedDiners').value = 1;
        document.getElementById('discountHolderName').value = '';
        document.getElementById('discountIdNumber').value = '';
        document.getElementById('discountIdVerified').checked = false;

        updateDiscountPreview();
    }

    function openPaymentModal(orderId) {
        const order = window.paymentOrders[orderId];

        if (!order) {
            return;
        }

        activePaymentOrder = order;

        document.getElementById('modalOrderNumber').textContent =
            order.order_number;

        document.getElementById('modalTableNumber').textContent =
            order.table_number;

        document.getElementById('modalTotal').textContent =
            '₱' + order.total_amount;

        const itemsContainer = document.getElementById('modalItems');
        itemsContainer.innerHTML = '';

        if (!order.items || order.items.length === 0) {
            itemsContainer.innerHTML = `
                <div class="px-4 py-6 text-center text-sm font-semibold text-slate-400">
                    No order items found
                </div>
            `;
        } else {
            order.items.forEach(item => {
                const row = document.createElement('div');

                row.className =
                    'flex items-center justify-between gap-4 px-4 py-3.5';

                row.innerHTML = `
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-slate-900">
                            ${item.quantity}x ${item.name}
                        </p>
                    </div>

                    <p class="shrink-0 text-sm font-black text-orange-600">
                        ₱${item.price}
                    </p>
                `;

                itemsContainer.appendChild(row);
            });
        }

        document.getElementById('paymentForm').action =
            order.action_url;

        resetDiscountForm();

        document.body.classList.add('overflow-hidden');

        showAnimatedModal(
            'paymentModal',
            'paymentModalCard'
        );
    }

    function closePaymentModal() {
        if (paymentIsSubmitting) {
            return;
        }

        hideAnimatedModal(
            'paymentModal',
            'paymentModalCard',
            () => {
                activePaymentOrder = null;
                document.body.classList.remove('overflow-hidden');
            }
        );
    }

    function validatePaymentForm() {
        const type = getSelectedDiscountType();

        if (type === 'none') {
            return true;
        }

        const totalDiners = parseInt(
            document.getElementById('totalDiners').value || '0',
            10
        );

        const qualifiedDiners = parseInt(
            document.getElementById('qualifiedDiners').value || '0',
            10
        );

        const names = parseListEntries(
            document.getElementById('discountHolderName').value
        );

        const ids = parseListEntries(
            document.getElementById('discountIdNumber').value
        );

        const verified = document
            .getElementById('discountIdVerified')
            .checked;

        let message = '';

        if (totalDiners < 1) {
            message = 'Total diners must be at least 1.';
        } else if (
            qualifiedDiners < 1 ||
            qualifiedDiners > totalDiners
        ) {
            message =
                'Qualified diners must be between 1 and total diners.';
        } else if (names.length !== qualifiedDiners) {
            message =
                `Enter exactly ${qualifiedDiners} qualified holder name(s).`;
        } else if (ids.length !== qualifiedDiners) {
            message =
                `Enter exactly ${qualifiedDiners} verified ID number(s).`;
        } else if (
            new Set(
                names.map(value => value.toLowerCase())
            ).size !== names.length
        ) {
            message =
                'Duplicate qualified holder names are not allowed.';
        } else if (
            new Set(
                ids.map(value => value.toLowerCase())
            ).size !== ids.length
        ) {
            message =
                'Duplicate ID numbers are not allowed.';
        } else if (!verified) {
            message =
                'Confirm that all valid IDs were verified.';
        }

        const validation = document.getElementById(
            'discountValidationMessage'
        );

        if (message) {
            validation.textContent = message;
            validation.classList.remove('hidden');
            validation.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            return false;
        }

        validation.classList.add('hidden');
        validation.textContent = '';

        return true;
    }

    function openPaymentConfirmation() {
        if (!activePaymentOrder || !validatePaymentForm()) {
            return;
        }

        const type = getSelectedDiscountType();
        const preview = calculatePreview();

        document.getElementById('confirmDiscountType').textContent =
            getDiscountLabel(type);

        document.getElementById('confirmFinalAmount').textContent =
            formatPeso(preview.finalAmount);

        document.getElementById(
            'paymentConfirmDescription'
        ).textContent =
            type === 'none'
                ? 'Confirm this regular cash payment and send the order to the kitchen.'
                : `Confirm this cash payment with the ${getDiscountLabel(type)} discount.`;

        showAnimatedModal(
            'paymentConfirmModal',
            'paymentConfirmCard'
        );
    }

    function closePaymentConfirmation() {
        if (paymentIsSubmitting) {
            return;
        }

        hideAnimatedModal(
            'paymentConfirmModal',
            'paymentConfirmCard'
        );
    }

    function submitPaymentForm() {
        if (paymentIsSubmitting) {
            return;
        }

        paymentIsSubmitting = true;

        const button = document.getElementById(
            'finalSubmitButton'
        );

        button.disabled = true;

        document.getElementById(
            'finalSubmitSpinner'
        ).classList.remove('hidden');

        document.getElementById(
            'finalSubmitText'
        ).textContent = 'Processing Payment...';

        document.getElementById(
            'reviewPaymentButton'
        ).disabled = true;

        document.getElementById(
            'selectedPaymentMethod'
        ).value = 'cash';

        document.getElementById('paymentForm').submit();
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent accidental modal closing and form submission
    |--------------------------------------------------------------------------
    | The modal may only be closed through its explicit Close/Cancel buttons.
    | Enter inside number/text fields must not submit or close the modal.
    */
    document.getElementById('paymentForm').addEventListener(
        'submit',
        function (event) {
            if (!paymentIsSubmitting) {
                event.preventDefault();
            }
        }
    );

    document.getElementById('paymentForm').addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                event.stopPropagation();
            }
        }
    );

    document.getElementById('paymentModal').addEventListener(
        'mousedown',
        function (event) {
            if (event.target === this) {
                event.preventDefault();
            }
        }
    );

    document.getElementById(
        'paymentConfirmModal'
    ).addEventListener('mousedown', function (event) {
        if (event.target === this) {
            event.preventDefault();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape' || paymentIsSubmitting) {
            return;
        }

        const activeElement = document.activeElement;
        const isEditingField = activeElement && (
            activeElement.tagName === 'INPUT' ||
            activeElement.tagName === 'TEXTAREA' ||
            activeElement.tagName === 'SELECT'
        );

        if (isEditingField) {
            return;
        }

        const confirmModal = document.getElementById(
            'paymentConfirmModal'
        );

        if (!confirmModal.classList.contains('hidden')) {
            closePaymentConfirmation();
            return;
        }

        const paymentModal = document.getElementById(
            'paymentModal'
        );

        if (!paymentModal.classList.contains('hidden')) {
            closePaymentModal();
        }
    });
</script>

@endsection
