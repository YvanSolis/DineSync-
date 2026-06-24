@extends('layouts.service')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Monitor restaurant service operations')

@section('content')
<div class="space-y-5 sm:space-y-6">

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-3 gap-3 sm:gap-4">
        <a href="{{ route('service.reservations') }}"
           class="bg-white/95 border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm hover:border-purple-300 hover:shadow-md transition">
            <p class="text-xs sm:text-sm text-gray-500">Pending Reservations</p>
            <p class="text-xl sm:text-2xl font-black text-purple-500 mt-1">
                {{ $reservationStats['pending'] ?? 0 }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Needs payment or approval</p>
        </a>

        <a href="{{ route('service.active-orders') }}"
           class="bg-white/95 border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm hover:border-green-300 hover:shadow-md transition">
            <p class="text-xs sm:text-sm text-gray-500">Ready to Serve</p>
            <p class="text-xl sm:text-2xl font-black text-green-500 mt-1">
                <span id="ready-to-serve-count">{{ $orderStats['ready'] ?? 0 }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">Waiting for service staff</p>
        </a>

        <a href="{{ route('service.table-monitoring') }}"
           class="bg-white/95 border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm hover:border-blue-300 hover:shadow-md transition sm:col-span-2 2xl:col-span-1">
            <p class="text-xs sm:text-sm text-gray-500">Seated</p>
            <p class="text-xl sm:text-2xl font-black text-blue-500 mt-1">
                {{ $reservationStats['seated'] ?? 0 }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Currently seated customers</p>
        </a>
    </div>

    <div class="grid grid-cols-1 2xl:grid-cols-2 gap-5 sm:gap-6">

        <!-- Recent Orders -->
        <div class="bg-white/95 rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-gray-900">
                        Recent Orders
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Latest kitchen orders currently tracked by service staff.
                    </p>
                </div>

                <a href="{{ route('service.active-orders') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-600 px-4 py-2 text-sm font-black transition w-full sm:w-auto">
                    View all
                </a>
            </div>

            <!-- Responsive Card View -->
            <div class="xl:hidden divide-y divide-gray-100">
                @forelse ($recentOrders as $order)
                    @php
                        $currentStatus = strtolower(trim($order->display_status ?? $order->status ?? 'pending'));

                        if (in_array($currentStatus, ['new', 'placed', 'confirmed'], true)) {
                            $currentStatus = 'pending';
                        }

                        $statusClass = match($currentStatus) {
                            'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'preparing' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'ready' => 'bg-green-50 text-green-700 border-green-200',
                            'served' => 'bg-gray-50 text-gray-600 border-gray-200',
                            'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                        };

                        $tableNumber = $order->source_table_number
                            ?? $order->table_number
                            ?? $order->table?->table_number
                            ?? $order->table?->name
                            ?? null;
                    @endphp

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-black text-gray-900 truncate">
                                    {{ $order->order_number ?? 'No order number' }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $order->created_at ? $order->created_at->diffForHumans() : 'No date recorded' }}
                                </p>
                            </div>

                            <span class="inline-flex shrink-0 px-3 py-1 rounded-full border text-xs font-black {{ $statusClass }}">
                                {{ ucfirst($currentStatus) }}
                            </span>
                        </div>

                        <div class="mt-3">
                            @if ($tableNumber)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-50 text-orange-700 border border-orange-200 text-xs font-black">
                                    Table {{ $tableNumber }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-50 text-gray-500 border border-gray-200 text-xs font-bold">
                                    No table assigned
                                </span>
                            @endif
                        </div>

                        <div class="mt-4 rounded-2xl bg-gray-50 border border-gray-100 p-3">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400 mb-2">
                                Items
                            </p>

                            <div class="space-y-1">
                                @forelse ($order->items->take(3) as $item)
                                    <p class="text-sm text-gray-700">
                                        <span class="font-bold">{{ $item->quantity }}x</span>
                                        {{ $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item' }}
                                    </p>
                                @empty
                                    <span class="text-sm text-gray-400">No items</span>
                                @endforelse

                                @if ($order->items->count() > 3)
                                    <p class="text-xs text-gray-400 mt-1">
                                        +{{ $order->items->count() - 3 }} more
                                    </p>
                                @endif
                            </div>

                            <p class="text-sm font-black text-orange-500 mt-3">
                                Total: ₱{{ number_format($order->total_amount ?? 0, 2) }}
                            </p>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <p class="text-xs text-gray-400">
                                KDS controlled
                            </p>

                            <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $statusClass }}">
                                {{ ucfirst($currentStatus) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        <h3 class="font-black text-gray-900">No recent orders</h3>
                        <p class="text-sm text-gray-500 mt-1">Kitchen orders will appear here.</p>
                    </div>
                @endforelse
            </div>

            <!-- Wide Desktop Table View -->
            <div class="hidden xl:block overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-gray-500">
                            <th class="px-5 py-4 font-bold">Order</th>
                            <th class="px-5 py-4 font-bold">Items</th>
                            <th class="px-5 py-4 font-bold">Total</th>
                            <th class="px-5 py-4 font-bold">Kitchen Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentOrders as $order)
                            @php
                                $currentStatus = strtolower(trim($order->display_status ?? $order->status ?? 'pending'));

                                if (in_array($currentStatus, ['new', 'placed', 'confirmed'], true)) {
                                    $currentStatus = 'pending';
                                }

                                $statusClass = match($currentStatus) {
                                    'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'preparing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'ready' => 'bg-green-50 text-green-700 border-green-200',
                                    'served' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-gray-50 text-gray-600 border-gray-200',
                                };

                                $tableNumber = $order->source_table_number
                                    ?? $order->table_number
                                    ?? $order->table?->table_number
                                    ?? $order->table?->name
                                    ?? null;
                            @endphp

                            <tr class="hover:bg-gray-50 transition align-top">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="font-black text-gray-900">
                                        {{ $order->order_number ?? 'No order number' }}
                                    </p>

                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $order->created_at ? $order->created_at->diffForHumans() : 'No date recorded' }}
                                    </p>

                                    <div class="mt-2">
                                        @if ($tableNumber)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-50 text-orange-700 border border-orange-200 text-xs font-black">
                                                Table {{ $tableNumber }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-50 text-gray-500 border border-gray-200 text-xs font-bold">
                                                No table
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    @forelse ($order->items->take(3) as $item)
                                        <p class="text-sm text-gray-700">
                                            <span class="font-bold">{{ $item->quantity }}x</span>
                                            {{ $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item' }}
                                        </p>
                                    @empty
                                        <span class="text-sm text-gray-400">No items</span>
                                    @endforelse

                                    @if ($order->items->count() > 3)
                                        <p class="text-xs text-gray-400 mt-1">
                                            +{{ $order->items->count() - 3 }} more
                                        </p>
                                    @endif
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="font-black text-orange-500">
                                        ₱{{ number_format($order->total_amount ?? 0, 2) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $statusClass }}">
                                        {{ ucfirst($currentStatus) }}
                                    </span>

                                    <p class="text-xs text-gray-400 mt-2">
                                        KDS controlled
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <h3 class="font-black text-gray-900">No recent orders</h3>
                                    <p class="text-sm text-gray-500 mt-1">Kitchen orders will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Reservations -->
        <div class="bg-white/95 rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-gray-900">
                        Recent Reservations
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Latest customer reservation requests.
                    </p>
                </div>

                <a href="{{ route('service.reservations') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-600 px-4 py-2 text-sm font-black transition w-full sm:w-auto">
                    View all
                </a>
            </div>

            <!-- Responsive Card View -->
            <div class="xl:hidden divide-y divide-gray-100">
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

                        $paymentClass = match($paymentStatus) {
                            'paid', 'verified', 'settled', 'completed' => 'bg-green-50 text-green-700 border-green-200',
                            'rejected', 'failed' => 'bg-red-50 text-red-700 border-red-200',
                            'expired' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        };

                        $statusClass = match($reservationStatus) {
                            'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'approved' => 'bg-green-50 text-green-700 border-green-200',
                            'declined' => 'bg-red-50 text-red-700 border-red-200',
                            'arrived' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'seated' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'cancelled', 'canceled' => 'bg-gray-50 text-gray-600 border-gray-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                        };
                    @endphp

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-black text-gray-900 truncate">
                                    {{ $reservation->customer_name }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $reservation->customer_phone }}
                                </p>
                            </div>

                            <span class="inline-flex shrink-0 px-3 py-1 rounded-full border text-xs font-black {{ $statusClass }}">
                                {{ ucfirst($reservationStatus) }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-3">
                                <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                    Schedule
                                </p>
                                <p class="text-sm font-black text-gray-900 mt-1">
                                    {{ $reservation->reservation_date ? $reservation->reservation_date->format('M d, Y') : 'No date' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $reservation->reservation_time ? \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') : 'No time' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-3">
                                <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                    Payment
                                </p>
                                <span class="inline-flex mt-2 px-3 py-1 rounded-full border text-xs font-black {{ $paymentClass }}">
                                    {{ $paymentLabel }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        <h3 class="font-black text-gray-900">No recent reservations</h3>
                        <p class="text-sm text-gray-500 mt-1">Reservations will appear here.</p>
                    </div>
                @endforelse
            </div>

            <!-- Wide Desktop Table View -->
            <div class="hidden xl:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-gray-500">
                            <th class="px-5 py-4 font-bold">Customer</th>
                            <th class="px-5 py-4 font-bold">Schedule</th>
                            <th class="px-5 py-4 font-bold">Payment</th>
                            <th class="px-5 py-4 font-bold">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
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

                                $paymentClass = match($paymentStatus) {
                                    'paid', 'verified', 'settled', 'completed' => 'bg-green-50 text-green-700 border-green-200',
                                    'rejected', 'failed' => 'bg-red-50 text-red-700 border-red-200',
                                    'expired' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                };

                                $statusClass = match($reservationStatus) {
                                    'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'approved' => 'bg-green-50 text-green-700 border-green-200',
                                    'declined' => 'bg-red-50 text-red-700 border-red-200',
                                    'arrived' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'seated' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'cancelled', 'canceled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    default => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp

                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4">
                                    <p class="font-black text-gray-900">{{ $reservation->customer_name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $reservation->customer_phone }}</p>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="font-black text-gray-900">
                                        {{ $reservation->reservation_date ? $reservation->reservation_date->format('M d, Y') : 'No date' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $reservation->reservation_time ? \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') : 'No time' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $paymentClass }}">
                                        {{ $paymentLabel }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $statusClass }}">
                                        {{ ucfirst($reservationStatus) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <h3 class="font-black text-gray-900">No recent reservations</h3>
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
        toast.className = 'fixed top-5 right-4 sm:right-5 z-50 bg-green-600 text-white px-4 sm:px-5 py-4 rounded-2xl shadow-lg max-w-[calc(100vw-2rem)] sm:max-w-sm';

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
    setInterval(checkReadyOrders, 10000);
</script>

@endsection