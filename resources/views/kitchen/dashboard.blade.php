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
            background-attachment: fixed;
            position: relative;
        }

        .kds-bg::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(249, 115, 22, 0.08), transparent 26%),
                radial-gradient(circle at bottom right, rgba(249, 115, 22, 0.10), transparent 30%),
                linear-gradient(180deg, rgba(5, 8, 15, 0.10), rgba(5, 8, 15, 0.18));
            pointer-events: none;
            z-index: 0;
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
            background: rgba(2, 6, 23, 0.82);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 14px 32px rgba(0,0,0,0.26);
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
            background: rgba(2, 6, 23, 0.42);
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
@endphp

<div class="kds-page">

    {{-- TOP BAR --}}
    <div class="kds-topbar rounded-3xl px-4 py-3 border border-white/10 bg-slate-950/82 backdrop-blur-xl shadow-[0_14px_32px_rgba(0,0,0,0.28)]">
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
                <div class="hidden sm:inline-flex items-center gap-2 bg-green-500/10 text-green-300 border border-green-400/20 px-3 py-2 rounded-2xl text-xs lg:text-sm font-bold shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse"></span>
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

    {{-- KDS BOARD --}}
    <div class="kds-board-grid">

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
</div>

<script>
    let isUpdatingOrder = false;

    function updateClock() {
        const now = new Date();

        document.getElementById('currentTime').textContent = new Intl.DateTimeFormat('en-PH', {
            timeZone: 'Asia/Manila',
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        }).format(now);
    }

    updateClock();
    setInterval(updateClock, 1000);

    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('.kds-status-form');

        if (!form) {
            return;
        }

        event.preventDefault();
        isUpdatingOrder = true;

        const button = form.querySelector('button');
        const card = form.closest('.order-card');
        const nextStatus = form.dataset.nextStatus;
        const formData = new FormData(form);
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = 'Updating...';
        button.classList.add('opacity-70', 'cursor-not-allowed');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error('Update failed');
            }

            moveOrderCard(card, nextStatus);

            isUpdatingOrder = false;
            silentRefreshBoard();

        } catch (error) {
            alert('Failed to update order status.');

            isUpdatingOrder = false;
            button.disabled = false;
            button.textContent = originalText;
            button.classList.remove('opacity-70', 'cursor-not-allowed');
        }
    });

    function moveOrderCard(card, status) {
        const targetColumn = document.getElementById(status + '-column');

        if (!targetColumn) {
            return;
        }

        const emptyMessage = targetColumn.querySelector('.kds-empty-state, .h-full, .h-40');

        if (emptyMessage) {
            emptyMessage.remove();
        }

        targetColumn.prepend(card);
        updateCardButton(card, status);
    }

    function updateCardButton(card, status) {
        const form = card.querySelector('.kds-status-form');

        if (!form) {
            return;
        }

        const input = form.querySelector('input[name="status"]');
        const button = form.querySelector('button');

        button.disabled = false;
        button.classList.remove('opacity-70', 'cursor-not-allowed');

        if (status === 'preparing') {
            form.dataset.nextStatus = 'ready';
            input.value = 'ready';
            button.textContent = 'Mark Ready';
            button.className = 'w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-xl font-extrabold text-[11px] uppercase tracking-wide transition active:scale-[0.98] shadow-sm';
        }

        if (status === 'ready') {
            form.dataset.nextStatus = 'served';
            input.value = 'served';
            button.textContent = 'Complete';
            button.className = 'w-full bg-gray-700 hover:bg-gray-800 text-white py-2 rounded-xl font-extrabold text-[11px] uppercase tracking-wide transition active:scale-[0.98] shadow-sm';
        }

        if (status === 'served') {
            form.outerHTML = `
                <div class="w-full rounded-xl border border-gray-200 bg-gray-100 py-2 text-center text-[11px] font-extrabold text-gray-500 uppercase">
                    Completed
                </div>
            `;
        }
    }

    function setTextIfExists(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = value;
        }
    }

    async function silentRefreshBoard() {
        if (isUpdatingOrder || document.hidden) {
            return;
        }

        try {
            const response = await fetch("{{ route('kitchen.orders.fetch') }}", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            document.getElementById('pending-column').innerHTML = data.html.pending;
            document.getElementById('preparing-column').innerHTML = data.html.preparing;
            document.getElementById('ready-column').innerHTML = data.html.ready;
            document.getElementById('served-column').innerHTML = data.html.served;

            setTextIfExists('pending-count', data.counts.pending);
            setTextIfExists('preparing-count', data.counts.preparing);
            setTextIfExists('ready-count', data.counts.ready);
            setTextIfExists('served-count', data.counts.served);

            setTextIfExists('pending-header-count', data.counts.pending);
            setTextIfExists('preparing-header-count', data.counts.preparing);
            setTextIfExists('ready-header-count', data.counts.ready);
            setTextIfExists('served-header-count', data.counts.served);

        } catch (error) {
            console.log('Silent refresh failed.');
        }
    }

    setInterval(silentRefreshBoard, 5000);
</script>

</body>
</html>