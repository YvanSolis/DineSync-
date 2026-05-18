<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 min-h-screen text-gray-100">

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
    <div class="bg-gray-900 border border-gray-800 rounded-2xl px-5 py-4 mb-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Kitchen Display System
                </h1>
                <p class="text-sm text-gray-400 mt-1">
                    Live kitchen order monitoring and preparation tracking
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex items-center gap-2 bg-green-500/10 text-green-300 border border-green-500/20 px-4 py-2 rounded-xl text-sm font-semibold">
                    <span class="w-2 h-2 rounded-full bg-green-400"></span>
                    Online
                </div>

                <div class="bg-gray-800 border border-gray-700 px-4 py-2 rounded-xl">
                    <p class="text-[11px] text-gray-400">Current Time</p>
                    <p id="currentTime" class="text-base font-bold text-white">--:-- --</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-red-600 hover:bg-red-700 px-5 py-3 rounded-xl text-sm font-semibold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-400">New Orders</p>
                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
            </div>
            <h2 class="text-2xl font-semibold text-white mt-1">
                <span id="pending-count">{{ $totalPending }}</span>
            </h2>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-400">Preparing</p>
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            </div>
            <h2 class="text-2xl font-semibold text-white mt-1">
                <span id="preparing-count">{{ $totalPreparing }}</span>
            </h2>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-400">Ready</p>
                <span class="w-2 h-2 rounded-full bg-green-400"></span>
            </div>
            <h2 class="text-2xl font-semibold text-white mt-1">
                <span id="ready-count">{{ $totalReady }}</span>
            </h2>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-400">Completed</p>
                <span class="w-2 h-2 rounded-full bg-gray-500"></span>
            </div>
            <h2 class="text-2xl font-semibold text-white mt-1">
                <span id="served-count">{{ $totalServed }}</span>
            </h2>
        </div>
    </div>

    {{-- KDS BOARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- NEW --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <div class="border-t-4 border-blue-500 px-4 py-3 bg-gray-900 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold tracking-wide text-white uppercase">New</h2>
                    <p class="text-xs text-gray-500">Waiting to start</p>
                </div>
                <span id="pending-header-count" class="bg-blue-500/10 text-blue-300 border border-blue-500/20 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalPending }}
                </span>
            </div>

            <div id="pending-column" class="p-3 h-[620px] overflow-y-auto space-y-3 bg-gray-950/40">
                @forelse ($pendingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Start Preparing',
                        'nextStatus' => 'preparing',
                        'buttonClass' => 'bg-amber-500 hover:bg-amber-600',
                        'columnType' => 'pending'
                    ])
                @empty
                    <div class="h-40 flex items-center justify-center text-gray-500 text-sm font-medium">
                        No new orders
                    </div>
                @endforelse
            </div>
        </div>

        {{-- PREPARING --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <div class="border-t-4 border-amber-500 px-4 py-3 bg-gray-900 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold tracking-wide text-white uppercase">Preparing</h2>
                    <p class="text-xs text-gray-500">Currently in kitchen</p>
                </div>
                <span id="preparing-header-count" class="bg-amber-500/10 text-amber-300 border border-amber-500/20 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalPreparing }}
                </span>
            </div>

            <div id="preparing-column" class="p-3 h-[620px] overflow-y-auto space-y-3 bg-gray-950/40">
                @forelse ($preparingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Mark Ready',
                        'nextStatus' => 'ready',
                        'buttonClass' => 'bg-green-600 hover:bg-green-700',
                        'columnType' => 'preparing'
                    ])
                @empty
                    <div class="h-40 flex items-center justify-center text-gray-500 text-sm font-medium">
                        No preparing orders
                    </div>
                @endforelse
            </div>
        </div>

        {{-- READY --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <div class="border-t-4 border-green-500 px-4 py-3 bg-gray-900 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold tracking-wide text-white uppercase">Ready</h2>
                    <p class="text-xs text-gray-500">Waiting for service</p>
                </div>
                <span id="ready-header-count" class="bg-green-500/10 text-green-300 border border-green-500/20 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalReady }}
                </span>
            </div>

            <div id="ready-column" class="p-3 h-[620px] overflow-y-auto space-y-3 bg-gray-950/40">
                @forelse ($readyOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'Complete',
                        'nextStatus' => 'served',
                        'buttonClass' => 'bg-gray-700 hover:bg-gray-600',
                        'columnType' => 'ready'
                    ])
                @empty
                    <div class="h-40 flex items-center justify-center text-gray-500 text-sm font-medium">
                        No ready orders
                    </div>
                @endforelse
            </div>
        </div>

        {{-- COMPLETED --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <div class="border-t-4 border-gray-600 px-4 py-3 bg-gray-900 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold tracking-wide text-white uppercase">Completed</h2>
                    <p class="text-xs text-gray-500">Served orders</p>
                </div>
                <span id="served-header-count" class="bg-gray-700 text-gray-300 border border-gray-600 px-3 py-1 rounded-full text-sm font-bold">
                    {{ $totalServed }}
                </span>
            </div>

            <div id="served-column" class="p-3 h-[620px] overflow-y-auto space-y-3 bg-gray-950/40">
                @forelse ($servedOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => null,
                        'nextStatus' => null,
                        'buttonClass' => '',
                        'columnType' => 'served'
                    ])
                @empty
                    <div class="h-40 flex items-center justify-center text-gray-500 text-sm font-medium">
                        No completed orders
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <p class="text-center text-gray-600 text-xs mt-4">
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
        if (!form) return;

        event.preventDefault();
        isUpdatingOrder = true;

        const button = form.querySelector('button');
        const card = form.closest('.order-card');
        const nextStatus = form.dataset.nextStatus;
        const formData = new FormData(form);

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

            if (!response.ok) throw new Error('Update failed');

            moveOrderCard(card, nextStatus);
            isUpdatingOrder = false;
            silentRefreshBoard();

        } catch (error) {
            alert('Failed to update order status.');
            isUpdatingOrder = false;
            button.disabled = false;
            button.textContent = 'Update';
            button.classList.remove('opacity-70', 'cursor-not-allowed');
        }
    });

    function moveOrderCard(card, status) {
        const targetColumn = document.getElementById(status + '-column');
        if (!targetColumn) return;

        const emptyMessage = targetColumn.querySelector('.h-40');
        if (emptyMessage) emptyMessage.remove();

        targetColumn.prepend(card);
        updateCardButton(card, status);
    }

    function updateCardButton(card, status) {
        const form = card.querySelector('.kds-status-form');
        if (!form) return;

        const input = form.querySelector('input[name="status"]');
        const button = form.querySelector('button');

        button.disabled = false;
        button.classList.remove('opacity-70', 'cursor-not-allowed');

        if (status === 'preparing') {
            form.dataset.nextStatus = 'ready';
            input.value = 'ready';
            button.textContent = 'Mark Ready';
            button.className = 'w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold text-sm transition';
        }

        if (status === 'ready') {
            form.dataset.nextStatus = 'served';
            input.value = 'served';
            button.textContent = 'Complete';
            button.className = 'w-full bg-gray-700 hover:bg-gray-600 text-white py-3 rounded-xl font-semibold text-sm transition';
        }

        if (status === 'served') {
            form.outerHTML = `
                <div class="w-full bg-gray-800 text-gray-400 py-3 rounded-xl font-semibold text-center text-sm">
                    Completed
                </div>
            `;
        }
    }

    async function silentRefreshBoard() {
        if (isUpdatingOrder) return;

        try {
            const response = await fetch("{{ route('kitchen.orders.fetch') }}", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response.ok) return;

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