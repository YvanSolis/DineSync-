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
                linear-gradient(135deg, rgba(255, 247, 237, 0.94), rgba(255, 255, 255, 0.96)),
                url('https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1800&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .kds-glass {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .kds-panel {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .kds-brand-card {
            background:
                linear-gradient(135deg, rgba(249, 115, 22, 0.96), rgba(251, 146, 60, 0.88)),
                url('https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="kds-bg min-h-screen text-gray-900">

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

<div class="min-h-screen p-4 lg:p-6">

    {{-- TOP BAR --}}
    <div class="kds-glass border border-orange-100/80 rounded-3xl px-5 py-4 mb-5 shadow-xl shadow-orange-100/40">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">

            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white border border-orange-100 flex items-center justify-center overflow-hidden shadow-sm shrink-0">
                    <img
                        src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                        alt="Chef Oppa Logo"
                        class="w-full h-full object-cover"
                    >
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-orange-500">
                        Chef Oppa Kitchen
                    </p>

                    <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight text-gray-900">
                        Kitchen Display System
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Live order preparation board for kitchen staff
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex items-center gap-2 bg-green-50 text-green-600 border border-green-100 px-4 py-3 rounded-2xl text-sm font-bold shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                    Online
                </div>

                <div class="bg-white/85 border border-orange-100 px-5 py-3 rounded-2xl shadow-sm">
                    <p class="text-[11px] text-gray-400">Current Time</p>
                    <p id="currentTime" class="text-base font-extrabold text-gray-800">--:-- --</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="bg-red-500 hover:bg-red-600 px-5 py-3 rounded-2xl text-sm font-bold text-white transition shadow-md shadow-red-100">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
        <div class="bg-white border border-orange-100 rounded-2xl px-5 py-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 font-semibold">New Orders</p>
                <span class="w-3 h-3 rounded-full bg-orange-400"></span>
            </div>

            <h2 class="text-4xl font-extrabold text-orange-500 mt-2">
                <span id="pending-count">{{ $totalPending }}</span>
            </h2>

            <p class="text-xs text-gray-400 mt-1">Waiting to start</p>
        </div>

        <div class="bg-white border border-yellow-100 rounded-2xl px-5 py-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 font-semibold">Preparing</p>
                <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
            </div>

            <h2 class="text-4xl font-extrabold text-yellow-500 mt-2">
                <span id="preparing-count">{{ $totalPreparing }}</span>
            </h2>

            <p class="text-xs text-gray-400 mt-1">Currently in kitchen</p>
        </div>

        <div class="bg-white border border-green-100 rounded-2xl px-5 py-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 font-semibold">Ready</p>
                <span class="w-3 h-3 rounded-full bg-green-400"></span>
            </div>

            <h2 class="text-4xl font-extrabold text-green-500 mt-2">
                <span id="ready-count">{{ $totalReady }}</span>
            </h2>

            <p class="text-xs text-gray-400 mt-1">Waiting for service</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 font-semibold">Completed</p>
                <span class="w-3 h-3 rounded-full bg-gray-400"></span>
            </div>

            <h2 class="text-4xl font-extrabold text-gray-700 mt-2">
                <span id="served-count">{{ $totalServed }}</span>
            </h2>

            <p class="text-xs text-gray-400 mt-1">Served orders</p>
        </div>
    </div>

    {{-- KDS BOARD --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

        {{-- NEW --}}
        <div class="kds-panel border border-orange-100 rounded-3xl overflow-hidden shadow-xl shadow-orange-100/40">
            <div class="border-t-4 border-orange-500 px-5 py-4 bg-orange-50 flex justify-between items-center">
                <div>
                    <h2 class="text-base font-extrabold tracking-wide text-gray-900 uppercase">New</h2>
                    <p class="text-xs text-gray-500">Waiting to start</p>
                </div>

                <span id="pending-header-count" class="bg-orange-100 text-orange-600 border border-orange-200 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalPending }}
                </span>
            </div>

            <div id="pending-column" class="p-4 h-[430px] overflow-y-auto space-y-3 bg-orange-50/30">
                @forelse ($pendingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Start Preparing',
                        'nextStatus' => 'preparing',
                        'buttonClass' => 'bg-yellow-500 hover:bg-yellow-600',
                        'columnType' => 'pending'
                    ])
                @empty
                    <div class="h-full min-h-[220px] flex items-center justify-center text-gray-400 text-base font-bold">
                        No new orders
                    </div>
                @endforelse
            </div>
        </div>

        {{-- PREPARING --}}
        <div class="kds-panel border border-yellow-100 rounded-3xl overflow-hidden shadow-xl shadow-orange-100/40">
            <div class="border-t-4 border-yellow-500 px-5 py-4 bg-yellow-50 flex justify-between items-center">
                <div>
                    <h2 class="text-base font-extrabold tracking-wide text-gray-900 uppercase">Preparing</h2>
                    <p class="text-xs text-gray-500">Currently in kitchen</p>
                </div>

                <span id="preparing-header-count" class="bg-yellow-100 text-yellow-700 border border-yellow-200 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalPreparing }}
                </span>
            </div>

            <div id="preparing-column" class="p-4 h-[430px] overflow-y-auto space-y-3 bg-yellow-50/30">
                @forelse ($preparingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Mark Ready',
                        'nextStatus' => 'ready',
                        'buttonClass' => 'bg-green-500 hover:bg-green-600',
                        'columnType' => 'preparing'
                    ])
                @empty
                    <div class="h-full min-h-[220px] flex items-center justify-center text-gray-400 text-base font-bold">
                        No preparing orders
                    </div>
                @endforelse
            </div>
        </div>

        {{-- READY --}}
        <div class="kds-panel border border-green-100 rounded-3xl overflow-hidden shadow-xl shadow-orange-100/40">
            <div class="border-t-4 border-green-500 px-5 py-4 bg-green-50 flex justify-between items-center">
                <div>
                    <h2 class="text-base font-extrabold tracking-wide text-gray-900 uppercase">Ready</h2>
                    <p class="text-xs text-gray-500">Waiting for service</p>
                </div>

                <span id="ready-header-count" class="bg-green-100 text-green-700 border border-green-200 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalReady }}
                </span>
            </div>

            <div id="ready-column" class="p-4 h-[430px] overflow-y-auto space-y-3 bg-green-50/30">
                @forelse ($readyOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Complete',
                        'nextStatus' => 'served',
                        'buttonClass' => 'bg-gray-700 hover:bg-gray-800',
                        'columnType' => 'ready'
                    ])
                @empty
                    <div class="h-full min-h-[220px] flex items-center justify-center text-gray-400 text-base font-bold">
                        No ready orders
                    </div>
                @endforelse
            </div>
        </div>

        {{-- COMPLETED --}}
        <div class="kds-panel border border-gray-200 rounded-3xl overflow-hidden shadow-xl shadow-orange-100/40">
            <div class="border-t-4 border-gray-400 px-5 py-4 bg-gray-50 flex justify-between items-center">
                <div>
                    <h2 class="text-base font-extrabold tracking-wide text-gray-900 uppercase">Completed</h2>
                    <p class="text-xs text-gray-500">Served orders</p>
                </div>

                <span id="served-header-count" class="bg-gray-100 text-gray-700 border border-gray-200 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalServed }}
                </span>
            </div>

            <div id="served-column" class="p-4 h-[430px] overflow-y-auto space-y-3 bg-gray-50/60">
                @forelse ($servedOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => null,
                        'nextStatus' => null,
                        'buttonClass' => '',
                        'columnType' => 'served'
                    ])
                @empty
                    <div class="h-full min-h-[220px] flex items-center justify-center text-gray-400 text-base font-bold">
                        No completed orders
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <p class="text-center text-gray-400 text-xs mt-4">
        Board updates automatically every few seconds.
    </p>
</div>

<script>
    let isUpdatingOrder = false;

    function updateClock() {
        const now = new Date();

        document.getElementById('currentTime').textContent = now.toLocaleTimeString([], {
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
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

            document.getElementById('pending-count').textContent = data.counts.pending;
            document.getElementById('preparing-count').textContent = data.counts.preparing;
            document.getElementById('ready-count').textContent = data.counts.ready;
            document.getElementById('served-count').textContent = data.counts.served;

            document.getElementById('pending-header-count').textContent = data.counts.pending;
            document.getElementById('preparing-header-count').textContent = data.counts.preparing;
            document.getElementById('ready-header-count').textContent = data.counts.ready;
            document.getElementById('served-header-count').textContent = data.counts.served;

        } catch (error) {
            console.log('Silent refresh failed.');
        }
    }

    setInterval(silentRefreshBoard, 5000);
</script>

</body>
</html>