@extends('layouts.service')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Monitor restaurant service operations')

@section('content')
@php
    $pendingReservations = $reservationStats['pending'] ?? 0;
    $readyOrders = $orderStats['ready'] ?? 0;
    $seatedCustomers = $reservationStats['seated'] ?? 0;

    $normalizeOrderStatus = function ($order) {
        $status = strtolower(trim($order->display_status ?? $order->status ?? 'pending'));

        return in_array($status, ['new', 'placed', 'confirmed'], true)
            ? 'pending'
            : $status;
    };

    $orderStatusClass = function ($status) {
        return match($status) {
            'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
            'preparing' => 'border-blue-200 bg-blue-50 text-blue-700',
            'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'served' => 'border-slate-200 bg-slate-50 text-slate-600',
            'cancelled' => 'border-red-200 bg-red-50 text-red-700',
            default => 'border-slate-200 bg-slate-50 text-slate-600',
        };
    };

    $reservationStatusClass = function ($status) {
        return match($status) {
            'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
            'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'declined' => 'border-red-200 bg-red-50 text-red-700',
            'arrived' => 'border-blue-200 bg-blue-50 text-blue-700',
            'seated' => 'border-violet-200 bg-violet-50 text-violet-700',
            'completed' => 'border-slate-200 bg-slate-50 text-slate-700',
            'cancelled', 'canceled' => 'border-slate-200 bg-slate-50 text-slate-600',
            default => 'border-slate-200 bg-slate-50 text-slate-600',
        };
    };

    $paymentStatusClass = function ($status) {
        return match($status) {
            'paid', 'verified', 'settled', 'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'rejected', 'failed' => 'border-red-200 bg-red-50 text-red-700',
            'expired' => 'border-amber-200 bg-amber-50 text-amber-700',
            default => 'border-amber-200 bg-amber-50 text-amber-700',
        };
    };
@endphp

<style>
    .service-dashboard-shell { position: relative; }
    .service-dashboard-shell::before {
        content: '';
        position: absolute;
        inset: -24px -20px auto;
        height: 250px;
        border-radius: 34px;
        background:
            radial-gradient(circle at 10% 10%, rgba(251, 146, 60, .18), transparent 38%),
            radial-gradient(circle at 88% 8%, rgba(250, 204, 21, .12), transparent 34%);
        pointer-events: none;
        z-index: 0;
    }
    .service-dashboard-content { position: relative; z-index: 1; }
    .premium-panel {
        background: rgba(255, 255, 255, .94);
        border: 1px solid rgba(226, 232, 240, .9);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .06);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }
    .metric-card {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }
    .metric-card::after {
        content: '';
        position: absolute;
        right: -28px;
        top: -28px;
        width: 110px;
        height: 110px;
        border-radius: 999px;
        background: currentColor;
        opacity: .055;
        z-index: -1;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 48px rgba(15, 23, 42, .09);
    }
</style>

<div class="service-dashboard-shell">
    <div class="service-dashboard-content space-y-6">

        <section class="premium-panel overflow-hidden rounded-[30px]">
            <div class="relative px-5 py-6 sm:px-7 sm:py-7">
                <div class="absolute inset-0 bg-gradient-to-r from-orange-50 via-white to-amber-50"></div>
                <div class="absolute -right-12 -top-16 h-48 w-48 rounded-full bg-orange-100/60"></div>
                <div class="absolute -bottom-20 left-1/3 h-44 w-44 rounded-full bg-amber-100/40"></div>

                <div class="relative flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="min-w-0">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white/90 px-3 py-1 text-[11px] font-black uppercase tracking-[.16em] text-orange-600">
                                <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                                Service Operations
                            </span>
                            <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-black text-emerald-700">
                                Live Monitoring
                            </span>
                        </div>

                        <h1 class="text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                            Service Staff Dashboard
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 sm:text-base">
                            Monitor kitchen-ready orders, reservations, and table activity from one clean workspace.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <a href="{{ route('service.active-orders') }}"
                           class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-200 transition hover:bg-orange-600">
                            View Active Orders
                        </a>

                        <a href="{{ route('service.table-monitoring') }}"
                           class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:border-orange-200 hover:text-orange-600">
                            Monitor Tables
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-3">
            <a href="{{ route('service.reservations') }}"
               class="metric-card premium-panel rounded-[26px] p-5 text-violet-600 transition duration-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[.14em] text-slate-400">Pending Reservations</p>
                        <p class="mt-3 text-4xl font-black tracking-tight text-violet-600">{{ $pendingReservations }}</p>
                        <p class="mt-2 text-sm text-slate-500">Needs payment confirmation or approval</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-violet-100 bg-violet-50 text-sm font-black text-violet-600">RSV</div>
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-xs font-bold text-slate-400">Reservation queue</span>
                    <span class="text-xs font-black text-violet-600">Open</span>
                </div>
            </a>

            <a href="{{ route('service.active-orders') }}"
               class="metric-card premium-panel rounded-[26px] p-5 text-emerald-600 transition duration-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[.14em] text-slate-400">Ready to Serve</p>
                        <p class="mt-3 text-4xl font-black tracking-tight text-emerald-600">
                            <span id="ready-to-serve-count">{{ $readyOrders }}</span>
                        </p>
                        <p class="mt-2 text-sm text-slate-500">Kitchen orders waiting for delivery</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-sm font-black text-emerald-600">RDY</div>
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-xs font-bold text-slate-400">Kitchen queue</span>
                    <span class="inline-flex items-center gap-2 text-xs font-black text-emerald-600">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>Live
                    </span>
                </div>
            </a>

            <a href="{{ route('service.table-monitoring') }}"
               class="metric-card premium-panel rounded-[26px] p-5 text-blue-600 transition duration-200 sm:col-span-2 2xl:col-span-1">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[.14em] text-slate-400">Seated Customers</p>
                        <p class="mt-3 text-4xl font-black tracking-tight text-blue-600">{{ $seatedCustomers }}</p>
                        <p class="mt-2 text-sm text-slate-500">Customers currently seated at tables</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-sm font-black text-blue-600">TBL</div>
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-xs font-bold text-slate-400">Dining floor</span>
                    <span class="text-xs font-black text-blue-600">Monitor</span>
                </div>
            </a>
        </section>

        <section class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <article class="premium-panel overflow-hidden rounded-[28px]">
                <header class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span>
                            <h2 class="text-lg font-black text-slate-950">Recent Orders</h2>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">Latest kitchen orders tracked by service staff.</p>
                    </div>

                    <a href="{{ route('service.active-orders') }}"
                       class="inline-flex min-h-[42px] items-center justify-center rounded-2xl border border-orange-100 bg-orange-50 px-4 py-2 text-sm font-black text-orange-600 transition hover:bg-orange-100">
                        View all orders
                    </a>
                </header>

                <div class="divide-y divide-slate-100">
                    @forelse ($recentOrders as $order)
                        @php
                            $currentStatus = $normalizeOrderStatus($order);
                            $statusClass = $orderStatusClass($currentStatus);
                            $tableNumber = $order->source_table_number
                                ?? $order->table_number
                                ?? $order->table?->table_number
                                ?? $order->table?->name
                                ?? null;
                        @endphp

                        <div class="p-5 transition hover:bg-slate-50/70 sm:p-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="truncate font-black text-slate-950">{{ $order->order_number ?? 'No order number' }}</p>
                                        <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-black {{ $statusClass }}">
                                            {{ ucfirst($currentStatus) }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span>{{ $order->created_at ? $order->created_at->diffForHumans() : 'No date recorded' }}</span>
                                        <span class="text-slate-300">•</span>
                                        @if ($tableNumber)
                                            <span class="font-bold text-orange-600">Table {{ $tableNumber }}</span>
                                        @else
                                            <span>No table assigned</span>
                                        @endif
                                    </div>
                                </div>

                                <p class="shrink-0 text-lg font-black text-orange-500">
                                    ₱{{ number_format($order->total_amount ?? 0, 2) }}
                                </p>
                            </div>

                            <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[.14em] text-slate-400">Order Items</p>
                                <div class="mt-3 space-y-2">
                                    @forelse ($order->items->take(3) as $item)
                                        <p class="text-sm text-slate-700">
                                            <span class="font-black text-slate-950">{{ $item->quantity }}x</span>
                                            {{ $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item' }}
                                        </p>
                                    @empty
                                        <p class="text-sm text-slate-400">No items</p>
                                    @endforelse

                                    @if ($order->items->count() > 3)
                                        <p class="text-xs font-bold text-slate-400">+{{ $order->items->count() - 3 }} more item(s)</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-3">
                                <p class="text-xs font-bold text-slate-400">Status controlled by KDS</p>
                                @if ($currentStatus === 'ready')
                                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-3 py-1 text-xs font-black text-white shadow-sm">
                                        <span class="h-2 w-2 rounded-full bg-white"></span>Ready for service
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-14 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xs font-black text-slate-400">ORD</div>
                            <h3 class="mt-4 font-black text-slate-950">No recent orders</h3>
                            <p class="mt-1 text-sm text-slate-500">Kitchen orders will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="premium-panel overflow-hidden rounded-[28px]">
                <header class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-violet-500"></span>
                            <h2 class="text-lg font-black text-slate-950">Recent Reservations</h2>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">Latest customer reservation requests.</p>
                    </div>

                    <a href="{{ route('service.reservations') }}"
                       class="inline-flex min-h-[42px] items-center justify-center rounded-2xl border border-violet-100 bg-violet-50 px-4 py-2 text-sm font-black text-violet-600 transition hover:bg-violet-100">
                        View all reservations
                    </a>
                </header>

                <div class="divide-y divide-slate-100">
                    @forelse ($recentReservations as $reservation)
                        @php
                            $paymentStatus = strtolower(trim($reservation->payment_status ?? 'pending'));
                            $reservationStatus = strtolower(trim($reservation->status ?? 'pending'));

                            $paymentLabel = match($paymentStatus) {
                                'paid', 'verified', 'settled', 'completed' => 'Paid',
                                'rejected' => 'Rejected',
                                'expired' => 'Expired',
                                'failed' => 'Failed',
                                default => 'Pending',
                            };

                            $paymentClass = $paymentStatusClass($paymentStatus);
                            $statusClass = $reservationStatusClass($reservationStatus);
                        @endphp

                        <div class="p-5 transition hover:bg-slate-50/70 sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate font-black text-slate-950">{{ $reservation->customer_name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $reservation->customer_phone }}</p>
                                </div>

                                <span class="inline-flex shrink-0 rounded-full border px-3 py-1 text-[11px] font-black {{ $statusClass }}">
                                    {{ ucfirst($reservationStatus) }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                    <p class="text-[11px] font-black uppercase tracking-[.14em] text-slate-400">Schedule</p>
                                    <p class="mt-2 font-black text-slate-950">
                                        {{ $reservation->reservation_date ? $reservation->reservation_date->format('M d, Y') : 'No date' }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $reservation->reservation_time ? \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') : 'No time' }}
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                    <p class="text-[11px] font-black uppercase tracking-[.14em] text-slate-400">Payment</p>
                                    <span class="mt-3 inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $paymentClass }}">
                                        {{ $paymentLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-14 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xs font-black text-slate-400">RSV</div>
                            <h3 class="mt-4 font-black text-slate-950">No recent reservations</h3>
                            <p class="mt-1 text-sm text-slate-500">Reservations will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</div>

<script>
    let lastReadyCount = {{ $readyOrders }};
    let hasLoadedReadyChecker = false;

    function showReadyOrderToast(orderNumber) {
        document.getElementById('ready-order-toast')?.remove();

        const toast = document.createElement('div');
        toast.id = 'ready-order-toast';
        toast.className = 'fixed right-4 top-5 z-[100] w-[calc(100vw-2rem)] max-w-sm overflow-hidden rounded-[22px] border border-emerald-200 bg-white shadow-2xl sm:right-5';

        toast.innerHTML = `
            <div class="flex items-start gap-3 bg-gradient-to-r from-emerald-50 to-white p-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-xs font-black text-white">RDY</div>
                <div class="min-w-0 flex-1">
                    <p class="font-black text-slate-950">Order Ready to Serve</p>
                    <p class="mt-1 text-sm leading-5 text-slate-600">
                        ${orderNumber ? `Order ${orderNumber} is ready for service.` : 'A kitchen order is ready for service.'}
                    </p>
                </div>
                <button type="button"
                    onclick="this.closest('#ready-order-toast').remove()"
                    class="text-xl font-black leading-none text-slate-400 hover:text-slate-700">&times;</button>
            </div>
        `;

        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 7000);
    }

    function playReadySound() {
        const audio = new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg');
        audio.volume = 0.5;
        audio.play().catch(() => {});
    }

    async function checkReadyOrders() {
        try {
            const response = await fetch("{{ route('service.ready-order-count') }}", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            const currentReadyCount = Number(data.ready_count ?? 0);
            const countElement = document.getElementById('ready-to-serve-count');

            if (countElement) countElement.textContent = currentReadyCount;

            if (hasLoadedReadyChecker && currentReadyCount > lastReadyCount) {
                showReadyOrderToast(data.latest_order_number);
                playReadySound();

                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('Order Ready to Serve', {
                        body: data.latest_order_number
                            ? `Order ${data.latest_order_number} is ready.`
                            : 'A kitchen order is ready.'
                    });
                }
            }

            lastReadyCount = currentReadyCount;
            hasLoadedReadyChecker = true;
        } catch (error) {
            console.error('Ready order check failed:', error);
        }
    }

    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    checkReadyOrders();
    setInterval(checkReadyOrders, 10000);
</script>
@endsection
