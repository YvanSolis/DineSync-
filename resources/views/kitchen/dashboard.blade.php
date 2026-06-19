<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
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
        }

        .kds-glass {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .kds-panel {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .kds-column-body::-webkit-scrollbar {
            width: 8px;
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
    </style>
</head>

<body class="kds-bg min-h-screen text-gray-900 overflow-hidden">

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

<div class="kds-page h-screen p-4 lg:p-5 flex flex-col overflow-hidden">

    {{-- TOP BAR --}}
    <div class="rounded-3xl px-5 py-4 mb-4 shrink-0 border border-white/10 bg-slate-950/82 backdrop-blur-xl shadow-[0_18px_45px_rgba(0,0,0,0.30)]">
        <div class="flex items-center justify-between gap-4">

            <div class="flex items-center gap-4 min-w-0">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 p-1 shadow-lg shadow-orange-900/30 shrink-0">
                    <div class="w-full h-full rounded-xl bg-white flex items-center justify-center overflow-hidden">
                        <img
                            src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                            alt="Chef Oppa Logo"
                            class="w-full h-full object-cover"
                        >
                    </div>
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                        <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-orange-400">
                            Chef Oppa Kitchen
                        </p>
                    </div>

                    <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight text-white leading-tight">
                        Kitchen Display System
                    </h1>

                    <p class="text-sm text-slate-300 mt-1">
                        Live order preparation board for kitchen staff
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="inline-flex items-center gap-2 bg-green-500/10 text-green-300 border border-green-400/20 px-4 py-3 rounded-2xl text-sm font-bold shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse"></span>
                    Online
                </div>

                <div class="bg-white/8 border border-white/10 px-5 py-3 rounded-2xl shadow-sm">
                    <p class="text-[11px] text-slate-400">Current Time</p>
                    <p id="currentTime" class="text-base font-extrabold text-white">--:-- --</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="bg-red-500 hover:bg-red-600 px-5 py-3 rounded-2xl text-sm font-bold text-white transition shadow-lg shadow-red-950/30">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- KDS BOARD - FIT TO ONE SCREEN --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 flex-1 min-h-0">

        {{-- NEW --}}
        <div class="border border-orange-400/20 rounded-3xl overflow-hidden shadow-[0_18px_45px_rgba(0,0,0,0.30)] flex flex-col min-h-0 bg-slate-950/82 backdrop-blur-xl">
            <div class="border-t-4 border-orange-500 px-5 py-3 bg-orange-500/10 flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-base font-extrabold tracking-wide text-orange-300 uppercase">New Orders</h2>
                    <p class="text-xs text-slate-300">Waiting to start</p>
                </div>

                <span id="pending-header-count" class="bg-orange-500/15 text-orange-300 border border-orange-400/25 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalPending }}
                </span>
            </div>

            <div id="pending-column" class="kds-column-body p-2.5 overflow-y-auto space-y-2 bg-slate-950/40 flex-1 min-h-0">
                @forelse ($pendingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Start Preparing',
                        'nextStatus' => 'preparing',
                        'buttonClass' => 'bg-yellow-500 hover:bg-yellow-600',
                        'columnType' => 'pending'
                    ])
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 text-base font-bold">
                        <div class="text-4xl mb-3 opacity-40">🍽️</div>
                        No new orders
                    </div>
                @endforelse
            </div>
        </div>

        {{-- PREPARING --}}
        <div class="border border-yellow-400/20 rounded-3xl overflow-hidden shadow-[0_18px_45px_rgba(0,0,0,0.30)] flex flex-col min-h-0 bg-slate-950/82 backdrop-blur-xl">
            <div class="border-t-4 border-yellow-500 px-5 py-3 bg-yellow-500/10 flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-base font-extrabold tracking-wide text-yellow-300 uppercase">Preparing</h2>
                    <p class="text-xs text-slate-300">Currently in kitchen</p>
                </div>

                <span id="preparing-header-count" class="bg-yellow-500/15 text-yellow-300 border border-yellow-400/25 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalPreparing }}
                </span>
            </div>

            <div id="preparing-column" class="kds-column-body p-2 overflow-y-auto space-y-1.5 bg-slate-950/40 flex-1 min-h-0">
                @forelse ($preparingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Mark Ready',
                        'nextStatus' => 'ready',
                        'buttonClass' => 'bg-green-500 hover:bg-green-600',
                        'columnType' => 'preparing'
                    ])
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 text-base font-bold">
                        <div class="text-4xl mb-3 opacity-40">👨‍🍳</div>
                        No preparing orders
                    </div>
                @endforelse
            </div>
        </div>

        {{-- READY --}}
        <div class="border border-green-400/20 rounded-3xl overflow-hidden shadow-[0_18px_45px_rgba(0,0,0,0.30)] flex flex-col min-h-0 bg-slate-950/82 backdrop-blur-xl">
            <div class="border-t-4 border-green-500 px-5 py-3 bg-green-500/10 flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-base font-extrabold tracking-wide text-green-300 uppercase">Ready</h2>
                    <p class="text-xs text-slate-300">Waiting for service</p>
                </div>

                <span id="ready-header-count" class="bg-green-500/15 text-green-300 border border-green-400/25 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalReady }}
                </span>
            </div>

            <div id="ready-column" class="kds-column-body p-4 overflow-y-auto space-y-3 bg-slate-950/40 flex-1 min-h-0">
                @forelse ($readyOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Complete',
                        'nextStatus' => 'served',
                        'buttonClass' => 'bg-gray-700 hover:bg-gray-800',
                        'columnType' => 'ready'
                    ])
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 text-base font-bold">
                        <div class="text-4xl mb-3 opacity-40">✅</div>
                        No ready orders
                    </div>
                @endforelse
            </div>
        </div>

        {{-- COMPLETED --}}
        <div class="border border-white/10 rounded-3xl overflow-hidden shadow-[0_18px_45px_rgba(0,0,0,0.30)] flex flex-col min-h-0 bg-slate-950/82 backdrop-blur-xl">
            <div class="border-t-4 border-slate-400 px-5 py-3 bg-white/5 flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-base font-extrabold tracking-wide text-slate-200 uppercase">Completed</h2>
                    <p class="text-xs text-slate-300">Served orders today</p>
                </div>

                <span id="served-header-count" class="bg-white/10 text-slate-200 border border-white/15 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalServed }}
                </span>
            </div>

            <div id="served-column" class="kds-column-body p-4 overflow-y-auto space-y-3 bg-slate-950/40 flex-1 min-h-0">
                @forelse ($servedOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => null,
                        'nextStatus' => null,
                        'buttonClass' => '',
                        'columnType' => 'served'
                    ])
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 text-base font-bold">
                        <div class="text-4xl mb-3 opacity-40">📋</div>
                        No completed orders
                    </div>
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

        const emptyMessage = targetColumn.querySelector('.h-full, .h-40');

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
            button.className = 'w-full bg-green-500 hover:bg-green-600 text-white py-4 rounded-2xl font-extrabold text-sm transition shadow-md shadow-green-100 active:scale-[0.98]';
        }

        if (status === 'ready') {
            form.dataset.nextStatus = 'served';
            input.value = 'served';
            button.textContent = 'Complete';
            button.className = 'w-full bg-gray-700 hover:bg-gray-800 text-white py-4 rounded-2xl font-extrabold text-sm transition shadow-md shadow-gray-200 active:scale-[0.98]';
        }

        if (status === 'served') {
            form.outerHTML = `
                <div class="w-full bg-gray-100 text-gray-500 py-4 rounded-2xl font-extrabold text-center text-sm border border-gray-200">
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