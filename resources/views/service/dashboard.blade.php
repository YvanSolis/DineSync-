@extends('layouts.service')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Monitor restaurant service operations')

@section('content')
<div class="space-y-6">

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <a href="{{ route('service.active-orders') }}"
           class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm hover:shadow transition">
            <p class="text-sm text-gray-500">Served Today</p>
            <p class="text-2xl font-bold text-gray-700 mt-1">{{ $orderStats['served_today'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Completed orders today</p>
        </a>

        <a href="{{ route('service.reservations') }}"
           class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm hover:border-purple-300 hover:shadow transition">
            <p class="text-sm text-gray-500">Pending Reservations</p>
            <p class="text-2xl font-bold text-purple-500 mt-1">{{ $reservationStats['pending'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Needs payment or approval</p>
        </a>

        <a href="{{ route('service.active-orders') }}"
           class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm hover:border-green-300 hover:shadow transition">
            <p class="text-sm text-gray-500">Ready to Serve</p>
            <p class="text-2xl font-bold text-green-500 mt-1">
                <span id="ready-to-serve-count">{{ $orderStats['ready'] ?? 0 }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">Waiting for service staff</p>
        </a>

        <a href="{{ route('service.reservations') }}"
           class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm hover:shadow transition">
            <p class="text-sm text-gray-500">Seated</p>
            <p class="text-2xl font-bold text-purple-500 mt-1">{{ $reservationStats['seated'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Currently seated customers</p>
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Recent Orders</h2>
                    <p class="text-sm text-gray-500">Latest customer and kiosk orders.</p>
                </div>

                <a href="{{ route('service.active-orders') }}"
                   class="text-sm font-semibold text-orange-500 hover:text-orange-600">
                    View all
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-gray-500">
                            <th class="px-5 py-4 font-semibold">Order</th>
                            <th class="px-5 py-4 font-semibold">Items</th>
                            <th class="px-5 py-4 font-semibold">Total</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentOrders as $order)
                            @php
                                $statusClass = match($order->status) {
                                    'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'preparing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'ready' => 'bg-green-50 text-green-700 border-green-200',
                                    'served' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp

                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="font-bold text-gray-900">{{ $order->order_number }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $order->created_at ? $order->created_at->diffForHumans() : 'No date recorded' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    @forelse ($order->items->take(2) as $item)
                                        <p class="text-sm text-gray-700">
                                            {{ $item->quantity }}x {{ $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item' }}
                                        </p>
                                    @empty
                                        <span class="text-sm text-gray-400">No items</span>
                                    @endforelse

                                    @if ($order->items->count() > 2)
                                        <p class="text-xs text-gray-400 mt-1">
                                            +{{ $order->items->count() - 2 }} more
                                        </p>
                                    @endif
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="font-bold text-orange-500">
                                        ₱{{ number_format($order->total_amount, 2) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center">
                                    <h3 class="font-bold text-gray-900">No recent orders</h3>
                                    <p class="text-sm text-gray-500 mt-1">Orders will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Reservations -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Recent Reservations</h2>
                    <p class="text-sm text-gray-500">Latest customer reservation requests.</p>
                </div>

                <a href="{{ route('service.reservations') }}"
                   class="text-sm font-semibold text-orange-500 hover:text-orange-600">
                    View all
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-gray-500">
                            <th class="px-5 py-4 font-semibold">Customer</th>
                            <th class="px-5 py-4 font-semibold">Schedule</th>
                            <th class="px-5 py-4 font-semibold">Payment</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentReservations as $reservation)
                            @php
                                $paymentClass = match($reservation->payment_status) {
                                    'verified' => 'bg-green-50 text-green-700 border-green-200',
                                    'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                };

                                $statusClass = match($reservation->status) {
                                    'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'approved' => 'bg-green-50 text-green-700 border-green-200',
                                    'declined' => 'bg-red-50 text-red-700 border-red-200',
                                    'arrived' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'seated' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    default => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp

                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-900">{{ $reservation->customer_name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $reservation->customer_phone }}</p>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="font-semibold text-gray-900">
                                        {{ $reservation->reservation_date ? $reservation->reservation_date->format('M d, Y') : 'No date' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $reservation->reservation_time ? \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') : 'No time' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $paymentClass }}">
                                        {{ ucfirst($reservation->payment_status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center">
                                    <h3 class="font-bold text-gray-900">No recent reservations</h3>
                                    <p class="text-sm text-gray-500 mt-1">Reservations will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<script>
    let lastReadyCount = {{ $orderStats['ready'] ?? 0 }};
    let hasLoadedReadyChecker = false;

    function showReadyOrderToast(orderNumber) {
        const oldToast = document.getElementById('ready-order-toast');

        if (oldToast) {
            oldToast.remove();
        }

        const toast = document.createElement('div');
        toast.id = 'ready-order-toast';
        toast.className = 'fixed top-5 right-5 z-50 bg-green-600 text-white px-5 py-4 rounded-2xl shadow-lg max-w-sm';

        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <div>
                    <p class="font-bold text-sm">Order Ready to Serve</p>
                    <p class="text-sm mt-1">
                        ${orderNumber ? 'Order #' + orderNumber + ' is ready.' : 'A kitchen order is ready.'}
                    </p>
                </div>
                <button onclick="this.closest('#ready-order-toast').remove()" class="ml-3 text-white/80 hover:text-white font-bold">
                    ×
                </button>
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            if (toast) {
                toast.remove();
            }
        }, 7000);
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

            const data = await response.json();
            const currentReadyCount = Number(data.ready_count ?? 0);

            const readyCardNumber = document.getElementById('ready-to-serve-count');

            if (readyCardNumber) {
                readyCardNumber.textContent = currentReadyCount;
            }

            if (hasLoadedReadyChecker && currentReadyCount > lastReadyCount) {
                showReadyOrderToast(data.latest_order_number);
                playReadySound();

                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('Order Ready to Serve', {
                        body: data.latest_order_number
                            ? `Order #${data.latest_order_number} is ready.`
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
    setInterval(checkReadyOrders, 5000);
</script>

@endsection