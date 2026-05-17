<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-950 min-h-screen text-white">

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

<div class="min-h-screen p-4">

    {{-- TOP BAR --}}
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 mb-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>
                <h1 class="text-3xl font-black tracking-tight">Kitchen Display System</h1>
                <p class="text-slate-400">Live kitchen order monitoring</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 px-5 py-3 rounded-2xl font-black">
                    ONLINE
                </div>

                <div class="bg-slate-800 px-5 py-3 rounded-2xl">
                    <p class="text-xs text-slate-400">Current Time</p>
                    <p id="currentTime" class="text-xl font-black">--:-- --</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-red-600 hover:bg-red-700 px-6 py-4 rounded-2xl font-black active:scale-95 transition">
                        Logout
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="bg-blue-600 rounded-3xl p-5">
            <p class="text-blue-100 font-bold">New Orders</p>
            <h2 class="text-5xl font-black"><span id="pending-count">{{ $totalPending }}</span></h2>
        </div>

        <div class="bg-orange-500 rounded-3xl p-5">
            <p class="text-orange-100 font-bold">Preparing</p>
            <h2 class="text-5xl font-black"><span id="preparing-count">{{ $totalPreparing }}</span></h2>
        </div>

        <div class="bg-emerald-600 rounded-3xl p-5">
            <p class="text-emerald-100 font-bold">Ready</p>
            <h2 class="text-5xl font-black"><span id="ready-count">{{ $totalReady }}</span></h2>
        </div>

        <div class="bg-slate-700 rounded-3xl p-5">
            <p class="text-slate-300 font-bold">Completed</p>
            <h2 class="text-5xl font-black"><span id="served-count">{{ $totalServed }}</span></h2>
        </div>
    </div>

    {{-- KDS BOARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- NEW --}}
        <div class="bg-slate-900 border border-blue-500/30 rounded-3xl overflow-hidden">
            <div class="bg-blue-600 px-5 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-black">NEW</h2>
                <span id="pending-header-count" class="bg-white/20 px-4 py-1 rounded-full font-black">{{ $totalPending }}</span>
            </div>

            <div id="pending-column" class="p-4 h-[620px] overflow-y-auto space-y-4">
                @forelse ($pendingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'START COOKING',
                        'nextStatus' => 'preparing',
                        'buttonClass' => 'bg-orange-500 hover:bg-orange-600',
                        'columnType' => 'pending'
                    ])
                @empty
                    <div class="h-40 flex items-center justify-center text-slate-500 font-bold">No new orders</div>
                @endforelse
            </div>
        </div>

        {{-- PREPARING --}}
        <div class="bg-slate-900 border border-orange-500/30 rounded-3xl overflow-hidden">
            <div class="bg-orange-500 px-5 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-black">PREPARING</h2>
                <span id="preparing-header-count" class="bg-white/20 px-4 py-1 rounded-full font-black">{{ $totalPreparing }}</span>
            </div>

            <div id="preparing-column" class="p-4 h-[620px] overflow-y-auto space-y-4">
                @forelse ($preparingOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'MARK READY',
                        'nextStatus' => 'ready',
                        'buttonClass' => 'bg-emerald-600 hover:bg-emerald-700',
                        'columnType' => 'preparing'
                    ])
                @empty
                    <div class="h-40 flex items-center justify-center text-slate-500 font-bold">No preparing orders</div>
                @endforelse
            </div>
        </div>

        {{-- READY --}}
        <div class="bg-slate-900 border border-emerald-500/30 rounded-3xl overflow-hidden">
            <div class="bg-emerald-600 px-5 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-black">READY</h2>
                <span id="ready-header-count" class="bg-white/20 px-4 py-1 rounded-full font-black">{{ $totalReady }}</span>
            </div>

            <div id="ready-column" class="p-4 h-[620px] overflow-y-auto space-y-4">
                @forelse ($readyOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => 'COMPLETE',
                        'nextStatus' => 'served',
                        'buttonClass' => 'bg-slate-600 hover:bg-slate-700',
                        'columnType' => 'ready'
                    ])
                @empty
                    <div class="h-40 flex items-center justify-center text-slate-500 font-bold">No ready orders</div>
                @endforelse
            </div>
        </div>

        {{-- COMPLETED --}}
        <div class="bg-slate-900 border border-slate-700 rounded-3xl overflow-hidden">
            <div class="bg-slate-700 px-5 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-black">DONE</h2>
                <span id="served-header-count" class="bg-white/20 px-4 py-1 rounded-full font-black">{{ $totalServed }}</span>
            </div>

            <div id="served-column" class="p-4 h-[620px] overflow-y-auto space-y-4">
                @forelse ($servedOrders as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'buttonText' => null,
                        'nextStatus' => null,
                        'buttonClass' => '',
                        'columnType' => 'served'
                    ])
                @empty
                    <div class="h-40 flex items-center justify-center text-slate-500 font-bold">No completed orders</div>
                @endforelse
            </div>
        </div>

    </div>

    <p class="text-center text-slate-500 text-sm mt-4">
        Updates automatically without refreshing the page
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
        button.textContent = 'UPDATING...';
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
            button.textContent = 'UPDATE';
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
            button.textContent = 'MARK READY';
            button.className = 'w-full bg-emerald-600 hover:bg-emerald-700 text-white py-5 rounded-2xl font-black text-xl active:scale-95 transition';
        }

        if (status === 'ready') {
            form.dataset.nextStatus = 'served';
            input.value = 'served';
            button.textContent = 'COMPLETE';
            button.className = 'w-full bg-slate-600 hover:bg-slate-700 text-white py-5 rounded-2xl font-black text-xl active:scale-95 transition';
        }

        if (status === 'served') {
            form.outerHTML = `
                <div class="w-full bg-slate-800 text-slate-400 py-5 rounded-2xl font-black text-center">
                    COMPLETED
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