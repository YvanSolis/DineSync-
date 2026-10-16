<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineSync+ KDS</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        .kds-bg {
            background:
                linear-gradient(135deg, rgba(8, 13, 24, 0.10), rgba(15, 23, 42, 0.18)),
                url('{{ asset('images/customer-menu/kds-background.png') }}');
            background-size: cover;
            background-position: center;
            background-attachment: scroll;
            position: relative;
        }

        .kds-bg::before {
            content: none;
        }

        .kds-page {
            position: relative;
            z-index: 1;
            height: 100dvh;
            padding: 12px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .kds-topbar {
            flex: 0 0 auto;
            margin-bottom: 12px;
        }

        .kds-board-grid {
            flex: 1 1 auto;
            min-height: 0;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-rows: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .kds-column-card {
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: rgba(2, 6, 23, 0.94);
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
            contain: layout paint style;
        }

        .kds-column-header {
            flex: 0 0 auto;
            padding: 10px 14px;
        }

        .kds-column-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 8px;
            background: rgba(2, 6, 23, 0.72);
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            -webkit-overflow-scrolling: touch;
            will-change: scroll-position;
        }

        .kds-column-body::-webkit-scrollbar {
            width: 7px;
        }

        .kds-column-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .kds-column-body::-webkit-scrollbar-thumb {
            background: rgba(249, 115, 22, 0.35);
            border-radius: 999px;
        }

        .kds-column-body::-webkit-scrollbar-thumb:hover {
            background: rgba(249, 115, 22, 0.55);
        }

        .kds-view-switch {
            display: inline-flex;
            gap: 6px;
            padding: 5px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.06);
        }

        .kds-view-button {
            padding: 8px 14px;
            border-radius: 12px;
            color: rgb(203, 213, 225);
            font-size: 12px;
            font-weight: 800;
            transition: 0.2s ease;
        }

        .kds-view-button:hover {
            color: white;
            background: rgba(255,255,255,0.08);
        }

        .kds-view-button.active {
            color: white;
            background: rgb(249, 115, 22);
            box-shadow: 0 8px 18px rgba(249,115,22,0.22);
        }

        .kds-board-view.hidden {
            display: none;
        }

        .kds-empty-state {
            height: 100%;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgb(148, 163, 184);
            font-size: 14px;
            font-weight: 800;
            text-align: center;
        }


        .order-card,
        .refill-card {
            content-visibility: auto;
            contain-intrinsic-size: 1px 150px;
            contain: layout paint style;
        }

        .kds-column-body > * {
            transform: translateZ(0);
        }

        .kds-page *,
        .kds-page *::before,
        .kds-page *::after {
            scroll-behavior: auto !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .kds-page *,
            .kds-page *::before,
            .kds-page *::after {
                animation: none !important;
                transition: none !important;
            }
        }

        @media (min-width: 768px) and (max-width: 1180px) {
            .kds-page {
                padding: 10px;
            }

            .kds-topbar {
                margin-bottom: 10px;
            }

            .kds-board-grid {
                gap: 10px;
            }

            .kds-column-header {
                padding: 8px 12px;
            }

            .kds-column-body {
                padding: 7px;
            }
        }

        @media (max-width: 767px) {
            html,
            body {
                height: auto;
                min-height: 100%;
                overflow: auto;
            }

            .kds-bg {
                background-attachment: scroll;
            }

            .kds-page {
                height: auto;
                min-height: 100dvh;
                overflow: visible;
                padding: 10px;
            }

            .kds-board-grid {
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: none;
                gap: 12px;
            }

            .kds-column-card {
                min-height: 320px;
            }

            .kds-column-body {
                max-height: 430px;
            }
        }
    </style>
</head>

<body class="kds-bg text-gray-900">

@php
    $pendingOrders = $orders['pending'] ?? collect();
    $preparingOrders = $orders['preparing'] ?? collect();
    $readyOrders = $orders['ready'] ?? collect();
    $servedOrders = $orders['served'] ?? collect();

    $totalPending = $pendingOrders->count();
    $totalPreparing = $preparingOrders->count();
    $totalReady = $readyOrders->count();
    $totalServed = $servedOrders->count();

    $requestedRefills = $refills['requested'] ?? collect();
    $preparingRefills = $refills['preparing'] ?? collect();
    $readyRefills = $refills['ready'] ?? collect();
    $servedRefills = $refills['served'] ?? collect();

    $totalRequestedRefills = $requestedRefills->count();
    $totalPreparingRefills = $preparingRefills->count();
    $totalReadyRefills = $readyRefills->count();
    $totalServedRefills = $servedRefills->count();
@endphp

<div class="kds-page">

    {{-- TOP BAR --}}
    <div class="kds-topbar rounded-3xl px-4 py-3 border border-white/10 bg-slate-950/95 shadow-md">
        <div class="flex items-center justify-between gap-3">

            <div class="flex items-center gap-3 min-w-0">
                <div class="w-12 h-12 lg:w-14 lg:h-14 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 p-1 shadow-lg shadow-orange-900/30 shrink-0">
                    <div class="w-full h-full rounded-xl bg-white flex items-center justify-center overflow-hidden">
                        <img
                            src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                            alt="Chef Oppa Logo"
                            class="w-full h-full object-cover"
                        >
                    </div>
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                        <p class="text-[10px] lg:text-xs font-extrabold uppercase tracking-[0.20em] text-orange-400 truncate">
                            Chef Oppa Kitchen
                        </p>
                    </div>

                    <h1 class="text-xl lg:text-2xl font-extrabold tracking-tight text-white leading-tight truncate">
                        Kitchen Display System
                    </h1>

                    <p class="hidden sm:block text-xs lg:text-sm text-slate-300 mt-0.5 truncate">
                        Live order preparation board for kitchen staff
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <div class="kds-view-switch">
                    <button
                        type="button"
                        id="ordersViewButton"
                        class="kds-view-button active"
                        onclick="switchKdsView('orders')"
                    >
                        Orders
                    </button>

                    <button
                        type="button"
                        id="refillsViewButton"
                        class="kds-view-button"
                        onclick="switchKdsView('refills')"
                    >
                        Refills
                        <span id="refill-total-badge" class="ml-1 rounded-full bg-white/15 px-2 py-0.5 text-[10px]">
                            {{ $totalRequestedRefills + $totalPreparingRefills + $totalReadyRefills }}
                        </span>
                    </button>
                </div>

                <div class="hidden sm:inline-flex items-center gap-2 bg-green-500/10 text-green-300 border border-green-400/20 px-3 py-2 rounded-2xl text-xs lg:text-sm font-bold shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-400 "></span>
                    Online
                </div>

                <div class="bg-white/8 border border-white/10 px-3 lg:px-4 py-2 rounded-2xl shadow-sm">
                    <p class="text-[10px] text-slate-400">Current Time</p>
                    <p id="currentTime" class="text-xs lg:text-base font-extrabold text-white whitespace-nowrap">--:-- --</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="bg-red-500 hover:bg-red-600 px-3 lg:px-4 py-2 rounded-2xl text-xs lg:text-sm font-bold text-white transition shadow-lg shadow-red-950/30">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ORDERS BOARD --}}
    <div id="ordersBoard" class="kds-board-grid kds-board-view">

        {{-- NEW --}}
        <div class="kds-column-card border border-orange-400/20 rounded-3xl">
            <div class="kds-column-header border-t-4 border-orange-500 bg-orange-500/10 flex justify-between items-center">
                <div>
                    <h2 class="text-sm lg:text-base font-extrabold tracking-wide text-orange-300 uppercase">New Orders</h2>
                    <p class="text-[11px] text-slate-300">Waiting to start</p>
                </div>

                <span id="pending-header-count" class="bg-orange-500/15 text-orange-300 border border-orange-400/25 px-3 py-1 rounded-full text-xs lg:text-sm font-bold">
                    {{ $totalPending }}
                </span>
            </div>

            <div id="pending-column" class="kds-column-body space-y-2">
                @forelse ($pendingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Start Preparing',
                        'nextStatus' => 'preparing',
                        'buttonClass' => 'bg-yellow-500 hover:bg-yellow-600',
                        'columnType' => 'pending'
                    ])
                @empty
                    <div class="kds-empty-state">No new orders</div>
                @endforelse
            </div>
        </div>

        {{-- PREPARING --}}
        <div class="kds-column-card border border-yellow-400/20 rounded-3xl">
            <div class="kds-column-header border-t-4 border-yellow-500 bg-yellow-500/10 flex justify-between items-center">
                <div>
                    <h2 class="text-sm lg:text-base font-extrabold tracking-wide text-yellow-300 uppercase">Preparing</h2>
                    <p class="text-[11px] text-slate-300">Currently in kitchen</p>
                </div>

                <span id="preparing-header-count" class="bg-yellow-500/15 text-yellow-300 border border-yellow-400/25 px-3 py-1 rounded-full text-xs lg:text-sm font-bold">
                    {{ $totalPreparing }}
                </span>
            </div>

            <div id="preparing-column" class="kds-column-body space-y-2">
                @forelse ($preparingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Mark Ready',
                        'nextStatus' => 'ready',
                        'buttonClass' => 'bg-green-500 hover:bg-green-600',
                        'columnType' => 'preparing'
                    ])
                @empty
                    <div class="kds-empty-state">No preparing orders</div>
                @endforelse
            </div>
        </div>

        {{-- READY --}}
        <div class="kds-column-card border border-green-400/20 rounded-3xl">
            <div class="kds-column-header border-t-4 border-green-500 bg-green-500/10 flex justify-between items-center">
                <div>
                    <h2 class="text-sm lg:text-base font-extrabold tracking-wide text-green-300 uppercase">Ready</h2>
                    <p class="text-[11px] text-slate-300">Waiting for service</p>
                </div>

                <span id="ready-header-count" class="bg-green-500/15 text-green-300 border border-green-400/25 px-3 py-1 rounded-full text-xs lg:text-sm font-bold">
                    {{ $totalReady }}
                </span>
            </div>

            <div id="ready-column" class="kds-column-body space-y-2">
                @forelse ($readyOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Complete',
                        'nextStatus' => 'served',
                        'buttonClass' => 'bg-gray-700 hover:bg-gray-800',
                        'columnType' => 'ready'
                    ])
                @empty
                    <div class="kds-empty-state">No ready orders</div>
                @endforelse
            </div>
        </div>

        {{-- COMPLETED --}}
        <div class="kds-column-card border border-white/10 rounded-3xl">
            <div class="kds-column-header border-t-4 border-slate-400 bg-white/5 flex justify-between items-center">
                <div>
                    <h2 class="text-sm lg:text-base font-extrabold tracking-wide text-slate-200 uppercase">Completed</h2>
                    <p class="text-[11px] text-slate-300">Served orders today</p>
                </div>

                <span id="served-header-count" class="bg-white/10 text-slate-200 border border-white/15 px-3 py-1 rounded-full text-xs lg:text-sm font-bold">
                    {{ $totalServed }}
                </span>
            </div>

            <div id="served-column" class="kds-column-body space-y-2">
                @forelse ($servedOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => null,
                        'nextStatus' => null,
                        'buttonClass' => '',
                        'columnType' => 'served'
                    ])
                @empty
                    <div class="kds-empty-state">No completed orders today</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- REFILLS BOARD --}}
    <div id="refillsBoard" class="kds-board-grid kds-board-view hidden">

        {{-- REQUESTED REFILLS --}}
        <div class="kds-column-card border border-blue-400/20 rounded-3xl">
            <div class="kds-column-header border-t-4 border-blue-500 bg-blue-500/10 flex justify-between items-center">
                <div>
                    <h2 class="text-sm lg:text-base font-extrabold tracking-wide text-blue-300 uppercase">Refill Requests</h2>
                    <p class="text-[11px] text-slate-300">Waiting to start</p>
                </div>

                <span id="requested-refill-header-count" class="bg-blue-500/15 text-blue-300 border border-blue-400/25 px-3 py-1 rounded-full text-xs lg:text-sm font-bold">
                    {{ $totalRequestedRefills }}
                </span>
            </div>

            <div id="requested-refill-column" class="kds-column-body space-y-2">
                @forelse ($requestedRefills as $refill)
                    @include('kitchen.partials.refill-card', [
                        'refill' => $refill,
                        'buttonText' => 'Start Preparing',
                        'nextStatus' => 'preparing',
                        'buttonClass' => 'bg-blue-600 hover:bg-blue-700'
                    ])
                @empty
                    <div class="kds-empty-state">No refill requests</div>
                @endforelse
            </div>
        </div>

        {{-- PREPARING REFILLS --}}
        <div class="kds-column-card border border-yellow-400/20 rounded-3xl">
            <div class="kds-column-header border-t-4 border-yellow-500 bg-yellow-500/10 flex justify-between items-center">
                <div>
                    <h2 class="text-sm lg:text-base font-extrabold tracking-wide text-yellow-300 uppercase">Preparing Refills</h2>
                    <p class="text-[11px] text-slate-300">Currently in kitchen</p>
                </div>

                <span id="preparing-refill-header-count" class="bg-yellow-500/15 text-yellow-300 border border-yellow-400/25 px-3 py-1 rounded-full text-xs lg:text-sm font-bold">
                    {{ $totalPreparingRefills }}
                </span>
            </div>

            <div id="preparing-refill-column" class="kds-column-body space-y-2">
                @forelse ($preparingRefills as $refill)
                    @include('kitchen.partials.refill-card', [
                        'refill' => $refill,
                        'buttonText' => 'Mark Ready',
                        'nextStatus' => 'ready',
                        'buttonClass' => 'bg-green-600 hover:bg-green-700'
                    ])
                @empty
                    <div class="kds-empty-state">No refills preparing</div>
                @endforelse
            </div>
        </div>

        {{-- READY REFILLS --}}
        <div class="kds-column-card border border-green-400/20 rounded-3xl">
            <div class="kds-column-header border-t-4 border-green-500 bg-green-500/10 flex justify-between items-center">
                <div>
                    <h2 class="text-sm lg:text-base font-extrabold tracking-wide text-green-300 uppercase">Ready Refills</h2>
                    <p class="text-[11px] text-slate-300">Waiting for service</p>
                </div>

                <span id="ready-refill-header-count" class="bg-green-500/15 text-green-300 border border-green-400/25 px-3 py-1 rounded-full text-xs lg:text-sm font-bold">
                    {{ $totalReadyRefills }}
                </span>
            </div>

            <div id="ready-refill-column" class="kds-column-body space-y-2">
                @forelse ($readyRefills as $refill)
                    @include('kitchen.partials.refill-card', [
                        'refill' => $refill,
                        'buttonText' => 'Mark Served',
                        'nextStatus' => 'served',
                        'buttonClass' => 'bg-gray-800 hover:bg-black'
                    ])
                @empty
                    <div class="kds-empty-state">No refills ready</div>
                @endforelse
            </div>
        </div>

        {{-- SERVED REFILLS --}}
        <div class="kds-column-card border border-white/10 rounded-3xl">
            <div class="kds-column-header border-t-4 border-slate-400 bg-white/5 flex justify-between items-center">
                <div>
                    <h2 class="text-sm lg:text-base font-extrabold tracking-wide text-slate-200 uppercase">Served Refills</h2>
                    <p class="text-[11px] text-slate-300">Completed today</p>
                </div>

                <span id="served-refill-header-count" class="bg-white/10 text-slate-200 border border-white/15 px-3 py-1 rounded-full text-xs lg:text-sm font-bold">
                    {{ $totalServedRefills }}
                </span>
            </div>

            <div id="served-refill-column" class="kds-column-body space-y-2">
                @forelse ($servedRefills as $refill)
                    @include('kitchen.partials.refill-card', [
                        'refill' => $refill,
                        'buttonText' => null,
                        'nextStatus' => null,
                        'buttonClass' => ''
                    ])
                @empty
                    <div class="kds-empty-state">No served refills today</div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<script>
    let isUpdatingOrder = false;
    let isUpdatingRefill = false;
    let isRefreshingOrders = false;
    let isRefreshingRefills = false;
    let activeKdsView = localStorage.getItem('kdsActiveView') || 'orders';

    const columnIds = [
        'pending-column',
        'preparing-column',
        'ready-column',
        'served-column',
        'requested-refill-column',
        'preparing-refill-column',
        'ready-refill-column',
        'served-refill-column'
    ];

    const clockFormatter = new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });

    function switchKdsView(view) {
        activeKdsView = view === 'refills' ? 'refills' : 'orders';
        localStorage.setItem('kdsActiveView', activeKdsView);

        document.getElementById('ordersBoard')?.classList.toggle('hidden', activeKdsView !== 'orders');
        document.getElementById('refillsBoard')?.classList.toggle('hidden', activeKdsView !== 'refills');

        document.getElementById('ordersViewButton')?.classList.toggle('active', activeKdsView === 'orders');
        document.getElementById('refillsViewButton')?.classList.toggle('active', activeKdsView === 'refills');

        if (activeKdsView === 'orders') {
            silentRefreshOrders();
        } else {
            silentRefreshRefills();
        }
    }

    switchKdsView(activeKdsView);

    function updateClock() {
        const clock = document.getElementById('currentTime');
        if (clock) {
            clock.textContent = clockFormatter.format(new Date());
        }
    }

    updateClock();
    setInterval(updateClock, 1000);

    function captureColumnScrollPositions() {
        const positions = {};
        columnIds.forEach((id) => {
            const element = document.getElementById(id);
            if (element) positions[id] = element.scrollTop;
        });
        return positions;
    }

    function restoreColumnScrollPositions(positions) {
        requestAnimationFrame(() => {
            Object.entries(positions).forEach(([id, scrollTop]) => {
                const element = document.getElementById(id);
                if (element) element.scrollTop = scrollTop;
            });
        });
    }

    function replaceHtmlOnlyWhenChanged(elementId, nextHtml) {
        const element = document.getElementById(elementId);
        if (!element || typeof nextHtml !== 'string') return false;

        const normalizedCurrent = element.innerHTML.trim();
        const normalizedNext = nextHtml.trim();

        if (normalizedCurrent === normalizedNext) return false;

        element.innerHTML = nextHtml;
        return true;
    }

    document.addEventListener('submit', async function (event) {
        const orderForm = event.target.closest('.kds-status-form');
        const refillForm = event.target.closest('.kds-refill-status-form');

        if (!orderForm && !refillForm) return;

        event.preventDefault();

        const form = orderForm || refillForm;
        const isRefill = Boolean(refillForm);
        const button = form.querySelector('button');
        const formData = new FormData(form);
        const originalText = button?.textContent || '';

        if (isRefill) isUpdatingRefill = true;
        else isUpdatingOrder = true;

        if (button) {
            button.disabled = true;
            button.textContent = 'Updating...';
            button.classList.add('opacity-70', 'cursor-not-allowed');
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Update failed.');
            }

            if (isRefill) await silentRefreshRefills(true);
            else await silentRefreshOrders(true);
        } catch (error) {
            alert(error.message || 'Failed to update status.');

            if (button) {
                button.disabled = false;
                button.textContent = originalText;
                button.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        } finally {
            if (isRefill) isUpdatingRefill = false;
            else isUpdatingOrder = false;
        }
    });

    function setTextIfExists(id, value) {
        const element = document.getElementById(id);
        if (element) element.textContent = value ?? 0;
    }

    async function silentRefreshOrders(force = false) {
        if (isRefreshingOrders || (!force && isUpdatingOrder) || document.hidden) return;
        if (!force && activeKdsView !== 'orders') return;

        isRefreshingOrders = true;
        const scrollPositions = captureColumnScrollPositions();

        try {
            const response = await fetch("{{ route('kitchen.orders.fetch') }}", {
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            let changed = false;

            changed = replaceHtmlOnlyWhenChanged('pending-column', data.html.pending) || changed;
            changed = replaceHtmlOnlyWhenChanged('preparing-column', data.html.preparing) || changed;
            changed = replaceHtmlOnlyWhenChanged('ready-column', data.html.ready) || changed;
            changed = replaceHtmlOnlyWhenChanged('served-column', data.html.served) || changed;

            setTextIfExists('pending-header-count', data.counts.pending);
            setTextIfExists('preparing-header-count', data.counts.preparing);
            setTextIfExists('ready-header-count', data.counts.ready);
            setTextIfExists('served-header-count', data.counts.served);

            if (changed) restoreColumnScrollPositions(scrollPositions);
        } catch (error) {
            console.log('Order refresh failed.');
        } finally {
            isRefreshingOrders = false;
        }
    }

    async function silentRefreshRefills(force = false) {
        if (isRefreshingRefills || (!force && isUpdatingRefill) || document.hidden) return;
        if (!force && activeKdsView !== 'refills') return;

        isRefreshingRefills = true;
        const scrollPositions = captureColumnScrollPositions();

        try {
            const response = await fetch("{{ route('kitchen.refills.fetch') }}", {
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            let changed = false;

            changed = replaceHtmlOnlyWhenChanged('requested-refill-column', data.html.requested) || changed;
            changed = replaceHtmlOnlyWhenChanged('preparing-refill-column', data.html.preparing) || changed;
            changed = replaceHtmlOnlyWhenChanged('ready-refill-column', data.html.ready) || changed;
            changed = replaceHtmlOnlyWhenChanged('served-refill-column', data.html.served) || changed;

            setTextIfExists('requested-refill-header-count', data.counts.requested);
            setTextIfExists('preparing-refill-header-count', data.counts.preparing);
            setTextIfExists('ready-refill-header-count', data.counts.ready);
            setTextIfExists('served-refill-header-count', data.counts.served);

            const activeRefillCount =
                Number(data.counts.requested || 0)
                + Number(data.counts.preparing || 0)
                + Number(data.counts.ready || 0);

            setTextIfExists('refill-total-badge', activeRefillCount);

            if (changed) restoreColumnScrollPositions(scrollPositions);
        } catch (error) {
            console.log('Refill refresh failed.');
        } finally {
            isRefreshingRefills = false;
        }
    }

    function refreshActiveBoard() {
        if (document.hidden) return;

        if (activeKdsView === 'orders') {
            silentRefreshOrders();
        } else {
            silentRefreshRefills();
        }
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) refreshActiveBoard();
    });

    // Reduced from 5 seconds and refreshes only the visible board.
    setInterval(refreshActiveBoard, 5000);
</script>

</body>
</html>