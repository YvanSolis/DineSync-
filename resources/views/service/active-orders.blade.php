@extends('layouts.service')

@section('page-title', 'Active Orders')
@section('page-subtitle', 'Monitor customer orders and serving status')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Active Orders</h1>
            <p class="text-gray-500 mt-1">
                Monitor kitchen progress and mark ready orders as served.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm">
            <p class="text-sm text-gray-500">Total Active Orders</p>
            <p class="text-2xl font-bold text-orange-500">{{ $orders->total() }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-orange-500">{{ $stats['pending'] ?? 0 }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm">
            <p class="text-sm text-gray-500">Preparing</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['preparing'] ?? 0 }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm">
            <p class="text-sm text-gray-500">Ready</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['ready'] ?? 0 }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm">
            <p class="text-sm text-gray-500">Served Today</p>
            <p class="text-2xl font-bold text-gray-700">{{ $stats['served_today'] ?? 0 }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Order List</h2>
            <p class="text-sm text-gray-500">
                Kitchen staff updates preparation status. Service staff handles serving.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-5 py-4 font-semibold">Order</th>
                        <th class="px-5 py-4 font-semibold">Items</th>
                        <th class="px-5 py-4 font-semibold">Payment</th>
                        <th class="px-5 py-4 font-semibold">Total</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        @php
                            $statusClass = match($order->status) {
                                'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                'preparing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'ready' => 'bg-green-50 text-green-700 border-green-200',
                                'served' => 'bg-gray-50 text-gray-600 border-gray-200',
                                'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                default => 'bg-gray-50 text-gray-600 border-gray-200',
                            };

                            $paymentStatus = $order->payment->status ?? 'pending';

                            $paymentClass = match($paymentStatus) {
                                'paid', 'verified' => 'bg-green-50 text-green-700 border-green-200',
                                'failed', 'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            };
                        @endphp

                        <tr class="align-top hover:bg-gray-50 transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-bold text-gray-900">
                                    {{ $order->order_number }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $order->created_at->diffForHumans() }}
                                </p>
                            </td>

                            <td class="px-5 py-4 w-[360px]">
                                @forelse ($order->items as $item)
                                    <p class="text-sm text-gray-800">
                                        <span class="font-semibold">{{ $item->quantity }}x</span>
                                        {{ $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        ₱{{ number_format($item->price, 2) }}
                                    </p>
                                @empty
                                    <span class="text-sm text-gray-400">No items</span>
                                @endforelse
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-semibold text-gray-900">
                                    {{ $order->payment->payment_method ?? 'N/A' }}
                                </p>

                                <span class="inline-flex mt-2 px-3 py-1 rounded-full border text-xs font-semibold {{ $paymentClass }}">
                                    {{ ucfirst($paymentStatus) }}
                                </span>
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

                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    @if ($order->status === 'ready')
                                        <form method="POST" action="{{ route('service.active-orders.mark-served', $order) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs">
                                                Mark Served
                                            </button>
                                        </form>
                                    @elseif ($order->status === 'served')
                                        <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-500 font-semibold text-xs">
                                            Served
                                        </span>
                                    @else
                                        <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-500 font-semibold text-xs whitespace-nowrap">
                                            Waiting for kitchen
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <h3 class="font-bold text-gray-900">No active orders found</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Customer and kiosk orders will appear here.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="p-5 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection