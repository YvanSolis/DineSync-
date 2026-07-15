@extends('layouts.service')

@section('page-title', 'Table Monitoring')
@section('page-subtitle', 'Monitor tables, walk-ins, table status, and payment status')

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
</style>

<div class="service-premium-shell space-y-5 sm:space-y-6">

    <section class="service-premium-hero px-5 py-6 sm:px-7">
        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-orange-600">
                    Dining Floor Control
                </span>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    Table Monitoring
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    View table availability, walk-in assignments, tablet status, and active payments.
                </p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/90 px-4 py-3 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Floor Capacity</p>
                <p class="mt-1 text-lg font-black text-orange-600">{{ $tables->count() }} tables</p>
            </div>
        </div>
    </section>


    {{-- FLASH MESSAGES --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 sm:px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 sm:px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    {{-- STATS --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="service-premium-stat px-4 sm:px-5 py-4">
            <p class="text-xs sm:text-sm text-gray-500">Total Tables</p>
            <p class="text-xl sm:text-2xl font-bold text-orange-500">{{ $tables->count() }}</p>
        </div>

        <div class="service-premium-stat px-4 sm:px-5 py-4">
            <p class="text-xs sm:text-sm text-gray-500">Available</p>
            <p class="text-xl sm:text-2xl font-bold text-green-500">{{ $tableStats['available'] ?? 0 }}</p>
        </div>

        <div class="service-premium-stat px-4 sm:px-5 py-4">
            <p class="text-xs sm:text-sm text-gray-500">Occupied</p>
            <p class="text-xl sm:text-2xl font-bold text-blue-500">{{ $tableStats['occupied'] ?? 0 }}</p>
        </div>

        <div class="service-premium-stat px-4 sm:px-5 py-4">
            <p class="text-xs sm:text-sm text-gray-500">Reserved</p>
            <p class="text-xl sm:text-2xl font-bold text-purple-500">{{ $tableStats['reserved'] ?? 0 }}</p>
        </div>

        <div class="service-premium-stat px-4 sm:px-5 py-4">
            <p class="text-xs sm:text-sm text-gray-500">Cleaning</p>
            <p class="text-xl sm:text-2xl font-bold text-yellow-500">{{ $tableStats['cleaning'] ?? 0 }}</p>
        </div>
    </div>

    {{-- TABLE CARDS --}}
    <div class="service-premium-panel overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100">
            <h2 class="text-base sm:text-lg font-bold text-gray-900">
                Restaurant Tables
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                View table status, active order payment status, and manage walk-in customers.
            </p>
        </div>

        <div class="p-4 sm:p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">

                @forelse ($tables as $table)
                    @php
                        $tableStatusClass = match($table->status) {
                            'available' => 'bg-green-50 text-green-700 border-green-200',
                            'occupied' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'reserved' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'cleaning' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                        };

                        $cardBorderClass = match($table->status) {
                            'available' => 'border-green-100',
                            'occupied' => 'border-blue-100',
                            'reserved' => 'border-purple-100',
                            'cleaning' => 'border-yellow-100',
                            default => 'border-gray-200',
                        };

                        $tabletAccount = $tabletAccounts[$table->table_number] ?? null;
                        $tabletStatus = $tabletAccount?->display_status ?? 'offline';

                        $tabletStatusClass = match($tabletStatus) {
                            'online' => 'bg-green-50 text-green-700 border-green-200',
                            'inactive' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                        };

                        $tabletDotClass = match($tabletStatus) {
                            'online' => 'bg-green-500',
                            'inactive' => 'bg-yellow-500',
                            default => 'bg-gray-400',
                        };

                        $tabletLabel = match($tabletStatus) {
                            'online' => 'On',
                            'inactive' => 'Idle',
                            default => 'Off',
                        };

                        $activeOrder = null;

                        if ($table->status === 'occupied' && !empty($table->current_order_id)) {
                            $activeOrder = $activeOrders[$table->table_number] ?? null;
                        }

                        $activeOrderStatus = $activeOrder
                            ? strtolower(trim($activeOrder->display_status ?? $activeOrder->status ?? 'pending'))
                            : null;

                        if (in_array($activeOrderStatus, ['new', 'placed', 'confirmed'])) {
                            $activeOrderStatus = 'pending';
                        }

                        $activeOrderStatusClass = match($activeOrderStatus) {
                            'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'preparing' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'ready' => 'bg-green-50 text-green-700 border-green-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                        };

                        $rawPaymentMethod = trim($activeOrder->payment_method ?? '');
                        $normalizedPaymentMethod = strtolower(str_replace(['_', '-'], ' ', $rawPaymentMethod));

                        $paymentMethod = match (true) {
                            str_contains($normalizedPaymentMethod, 'qr') => 'QR PH',
                            str_contains($normalizedPaymentMethod, 'later') => 'Pay Later',
                            str_contains($normalizedPaymentMethod, 'counter') => 'Pay at Counter',
                            str_contains($normalizedPaymentMethod, 'cash') => 'Pay at Counter',
                            $rawPaymentMethod === '' => 'Pay at Counter',
                            default => $rawPaymentMethod,
                        };

                        $paymentStatus = strtolower(trim($activeOrder->payment_status ?? 'pending'));

                        $paymentMethodClass = match($paymentMethod) {
                            'QR PH' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'Pay Later' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'Pay at Counter' => 'bg-orange-50 text-orange-700 border-orange-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                        };

                        $paymentStatusLabel = match($paymentStatus) {
                            'paid', 'verified' => 'Paid',
                            'expired' => 'Expired',
                            'failed', 'rejected' => 'Failed',
                            default => 'Pending Payment',
                        };

                        $paymentStatusClass = match($paymentStatus) {
                            'paid', 'verified' => 'bg-green-50 text-green-700 border-green-200',
                            'expired' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'failed', 'rejected' => 'bg-red-50 text-red-700 border-red-200',
                            default => 'bg-red-50 text-red-700 border-red-200',
                        };

                        $needsPayment = $activeOrder && !in_array($paymentStatus, ['paid', 'verified']);
                    @endphp

                    <div class="service-premium-card border {{ $cardBorderClass }} rounded-2xl bg-white overflow-hidden min-w-0">

                        {{-- CARD HEADER --}}
                        <div class="p-4 sm:p-5 border-b border-gray-100">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 truncate">
                                        Table {{ $table->table_number }}
                                    </h3>

                                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                                        Capacity: {{ $table->capacity }} guest{{ $table->capacity > 1 ? 's' : '' }}
                                    </p>
                                </div>

                                <div class="flex flex-col items-end gap-2 shrink-0">
                                    <span class="inline-flex px-2.5 sm:px-3 py-1 rounded-full border text-[11px] sm:text-xs font-semibold {{ $tableStatusClass }}">
                                        {{ ucfirst($table->status) }}
                                    </span>

                                    <span class="inline-flex items-center gap-2 px-2.5 sm:px-3 py-1 rounded-full border text-[11px] sm:text-xs font-semibold {{ $tabletStatusClass }}">
                                        <span class="w-2 h-2 rounded-full {{ $tabletDotClass }}"></span>
                                        {{ $tabletLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 sm:p-5 space-y-4">

                            {{-- TABLE DETAILS --}}
                            <div class="text-xs sm:text-sm text-gray-600 space-y-1.5">
                                @if ($table->status === 'occupied')
                                    <p>
                                        <span class="font-semibold text-gray-900">Guests:</span>
                                        {{ $table->current_guest_count ?? 'N/A' }}
                                    </p>

                                    <p>
                                        <span class="font-semibold text-gray-900">Occupied:</span>
                                        {{ $table->occupied_at ? $table->occupied_at->diffForHumans() : 'No time recorded' }}
                                    </p>

                                    <p class="break-words">
                                        <span class="font-semibold text-gray-900">Notes:</span>
                                        {{ $table->notes ?? 'None' }}
                                    </p>
                                @elseif ($table->status === 'reserved')
                                    <p>
                                        <span class="font-semibold text-gray-900">Reservation:</span>
                                        {{ $table->reservation ? $table->reservation->customer_name : 'Reserved' }}
                                    </p>

                                    <p>
                                        <span class="font-semibold text-gray-900">Guests:</span>
                                        {{ $table->current_guest_count ?? 'N/A' }}
                                    </p>
                                @elseif ($table->status === 'cleaning')
                                    <p>
                                        This table needs cleaning before it can be used again.
                                    </p>
                                @else
                                    <p>
                                        Ready for walk-in customers.
                                    </p>
                                @endif
                            </div>

                            {{-- ACTIVE ORDER PAYMENT INFO --}}
                            @if ($activeOrder)
                                <div class="rounded-2xl border border-orange-100 bg-orange-50/60 p-3 sm:p-4 space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-3">
                                        <div class="min-w-0">
                                            <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-orange-500">
                                                Active Order
                                            </p>

                                            <p class="font-extrabold text-gray-900 truncate mt-1 text-sm sm:text-base">
                                                {{ $activeOrder->order_number ?? 'No order number' }}
                                            </p>
                                        </div>

                                        <span class="inline-flex w-fit px-3 py-1 rounded-full border text-xs font-semibold {{ $activeOrderStatusClass }}">
                                            {{ ucfirst($activeOrderStatus) }}
                                        </span>
                                    </div>

                                    <div class="space-y-2">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex px-3 py-1 rounded-full border text-[11px] sm:text-xs font-bold {{ $paymentMethodClass }}">
                                                {{ $paymentMethod }}
                                            </span>

                                            <span class="inline-flex px-3 py-1 rounded-full border text-[11px] sm:text-xs font-bold {{ $paymentStatusClass }}">
                                                {{ $paymentStatusLabel }}
                                            </span>
                                        </div>

                                        <p class="text-sm font-bold text-orange-600">
                                            Total: ₱{{ number_format($activeOrder->total_amount ?? 0, 2) }}
                                        </p>

                                        @if ($needsPayment)
                                            <div class="rounded-xl border border-red-100 bg-red-50 px-3 py-2">
                                                <p class="text-xs font-bold text-red-600">
                                                    Needs payment
                                                </p>

                                                @if ($paymentMethod === 'Pay at Counter')
                                                    <p class="text-xs text-red-500 mt-0.5">
                                                        Customer should pay at the counter.
                                                    </p>
                                                @elseif ($paymentMethod === 'Pay Later')
                                                    <p class="text-xs text-red-500 mt-0.5">
                                                        Payment will be settled later.
                                                    </p>
                                                @elseif ($paymentMethod === 'QR PH')
                                                    <p class="text-xs text-red-500 mt-0.5">
                                                        Waiting for QR PH payment confirmation.
                                                    </p>
                                                @endif

                                                @if (in_array($paymentMethod, ['Pay at Counter', 'Pay Later']))
                                                    <form method="POST" action="{{ route('service.orders.mark-paid', $activeOrder) }}" class="mt-3">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button
                                                            type="submit"
                                                            onclick="return confirm('Mark this order as paid? This will only update payment status, not kitchen status.')"
                                                            class="w-full px-3 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-bold">
                                                            Mark as Paid
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                            <div class="rounded-xl border border-green-100 bg-green-50 px-3 py-2">
                                                <p class="text-xs font-bold text-green-600">
                                                    Payment settled
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3">
                                    <p class="text-xs font-bold text-gray-500">
                                        No active order for this table.
                                    </p>
                                </div>
                            @endif

                            {{-- SERVICE CONTROLS --}}
                            <div class="border-t border-gray-100 pt-4">
                                @if ($table->status === 'available')
                                    <form method="POST" action="{{ route('service.table-monitoring.walk-in', $table) }}" class="space-y-3">
                                        @csrf
                                        @method('PATCH')

                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">
                                                Walk-in guest count
                                            </label>
                                            <input
                                                type="number"
                                                name="guest_count"
                                                min="1"
                                                max="{{ $table->capacity }}"
                                                required
                                                placeholder="Max {{ $table->capacity }}"
                                                class="w-full rounded-xl border-gray-200 text-sm focus:border-orange-300 focus:ring-orange-200">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">
                                                Notes
                                            </label>
                                            <input
                                                type="text"
                                                name="notes"
                                                placeholder="Optional"
                                                class="w-full rounded-xl border-gray-200 text-sm focus:border-orange-300 focus:ring-orange-200">
                                        </div>

                                        <button
                                            type="submit"
                                            class="w-full px-4 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold">
                                            Assign Walk-in
                                        </button>
                                    </form>
                                @elseif ($table->status === 'occupied')
                                    <form method="POST" action="{{ route('service.table-monitoring.cleaning', $table) }}">
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="w-full px-4 py-3 rounded-xl bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold">
                                            Mark for Cleaning
                                        </button>
                                    </form>
                                @elseif ($table->status === 'cleaning')
                                    <form method="POST" action="{{ route('service.table-monitoring.available', $table) }}">
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="w-full px-4 py-3 rounded-xl bg-green-500 hover:bg-green-600 text-white text-sm font-semibold">
                                            Mark Available
                                        </button>
                                    </form>
                                @else
                                    <span class="block text-center px-4 py-3 rounded-xl bg-gray-100 text-gray-500 text-sm font-semibold">
                                        No table action available
                                    </span>
                                @endif
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <h3 class="font-bold text-gray-900">No tables found</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Add restaurant tables first before using table monitoring.
                        </p>
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</div>

<script>
    const scrollStorageKey = 'serviceTableMonitoringScrollY';

    window.addEventListener('load', function () {
        const savedScrollY = sessionStorage.getItem(scrollStorageKey);

        if (savedScrollY !== null) {
            setTimeout(() => {
                window.scrollTo(0, parseInt(savedScrollY, 10));
            }, 50);
        }
    });

    function saveCurrentScrollPosition() {
        sessionStorage.setItem(scrollStorageKey, window.scrollY.toString());
    }

    window.addEventListener('beforeunload', saveCurrentScrollPosition);

    setInterval(() => {
        if (document.hidden) {
            return;
        }

        const activeElement = document.activeElement;
        const isTyping =
            activeElement &&
            (
                activeElement.tagName === 'INPUT' ||
                activeElement.tagName === 'TEXTAREA' ||
                activeElement.tagName === 'SELECT'
            );

        if (isTyping) {
            return;
        }

        saveCurrentScrollPosition();
        window.location.reload();
    }, 15000);
</script>
@endsection