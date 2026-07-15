@extends('layouts.service')

@section('page-title', 'Active Orders')
@section('page-subtitle', 'View active kitchen orders and record unlimited refills')

@section('content')


<style>
    .service-premium-shell {
        position: relative;
    }

    .service-premium-panel {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.94);
        border-radius: 1.5rem;
        box-shadow: 0 18px 46px rgba(15, 23, 42, 0.065);
        backdrop-filter: blur(14px);
    }

    .service-premium-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(254, 215, 170, 0.88);
        border-radius: 1.75rem;
        background:
            radial-gradient(circle at top right, rgba(251, 146, 60, 0.18), transparent 30%),
            linear-gradient(135deg, rgba(255, 247, 237, 0.98), rgba(255, 255, 255, 0.98) 55%, rgba(255, 251, 235, 0.98));
        box-shadow: 0 20px 50px rgba(249, 115, 22, 0.08);
    }

    .service-premium-stat {
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid rgba(226, 232, 240, 0.94);
        border-radius: 1.35rem;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.055);
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            border-color 0.2s ease;
    }

    .service-premium-stat:hover {
        transform: translateY(-2px);
        border-color: rgba(251, 146, 60, 0.32);
        box-shadow: 0 20px 42px rgba(15, 23, 42, 0.08);
    }

    .service-premium-card {
        border-radius: 1.35rem !important;
        border-color: rgba(226, 232, 240, 0.92) !important;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05) !important;
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            border-color 0.2s ease;
    }

    .service-premium-card:hover {
        transform: translateY(-1px);
        border-color: rgba(251, 146, 60, 0.28) !important;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.075) !important;
    }

    .service-premium-shell table thead {
        background: linear-gradient(to right, #fff7ed, #ffffff, #fffbeb) !important;
    }

    .service-premium-shell table tbody tr {
        transition: background-color 0.18s ease;
    }

    .service-premium-shell table tbody tr:hover {
        background: rgba(255, 247, 237, 0.66) !important;
    }

    .service-premium-shell input,
    .service-premium-shell select,
    .service-premium-shell textarea {
        border-radius: 0.9rem !important;
        border-color: #e2e8f0 !important;
        box-shadow: none !important;
    }

    .service-premium-shell input:focus,
    .service-premium-shell select:focus,
    .service-premium-shell textarea:focus {
        border-color: #fb923c !important;
        box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.12) !important;
    }

    .service-premium-shell button,
    .service-premium-shell a {
        -webkit-tap-highlight-color: transparent;
    }

    .service-premium-shell .premium-section-heading {
        font-weight: 900;
        letter-spacing: -0.02em;
        color: #0f172a;
    }

    .service-premium-shell .premium-muted {
        color: #64748b;
    }

    @media (max-width: 640px) {
        .service-premium-hero {
            border-radius: 1.35rem;
        }

        .service-premium-panel {
            border-radius: 1.25rem;
        }
    }
</style>

<div class="service-premium-shell space-y-5 sm:space-y-6">

    <section class="service-premium-hero px-5 py-6 sm:px-7">
        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-orange-600">
                    Live Service Operations
                </span>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    Active Orders
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Monitor kitchen progress, table assignments, and active unlimited orders.
                </p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/90 px-4 py-3 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Live Queue</p>
                <p class="mt-1 text-lg font-black text-orange-600">
                    {{ ($stats['pending'] ?? 0) + ($stats['preparing'] ?? 0) + ($stats['ready'] ?? 0) }}
                    active
                </p>
            </div>
        </div>
    </section>


    {{-- STATS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="service-premium-stat px-4 sm:px-5 py-4">
            <p class="text-xs sm:text-sm text-gray-500">Pending</p>
            <p class="text-xl sm:text-2xl font-black text-orange-500">
                {{ $stats['pending'] ?? 0 }}
            </p>
        </div>

        <div class="service-premium-stat px-4 sm:px-5 py-4">
            <p class="text-xs sm:text-sm text-gray-500">Preparing</p>
            <p class="text-xl sm:text-2xl font-black text-blue-500">
                {{ $stats['preparing'] ?? 0 }}
            </p>
        </div>

        <div class="service-premium-stat px-4 sm:px-5 py-4">
            <p class="text-xs sm:text-sm text-gray-500">Ready</p>
            <p class="text-xl sm:text-2xl font-black text-green-500">
                {{ $stats['ready'] ?? 0 }}
            </p>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 sm:px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 sm:px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 sm:px-5 py-4 rounded-xl text-sm font-semibold">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- MOBILE CARD VIEW --}}
    <div class="lg:hidden space-y-4">
        @forelse ($orders as $order)
            @php
                $currentStatus = strtolower(
                    trim($order->display_status ?? $order->status ?? 'pending')
                );

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

                $canRecordRefill = in_array(
                    $currentStatus,
                    ['preparing', 'ready'],
                    true
                );
            @endphp

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-extrabold text-gray-900 truncate">
                                {{ $order->order_number ?? 'No order number' }}
                            </p>

                            <p class="text-xs text-gray-500 mt-1">
                                Created:
                                {{ $order->created_at
                                    ? $order->created_at->diffForHumans()
                                    : 'No date recorded' }}
                            </p>
                        </div>

                        <span class="inline-flex shrink-0 px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                            {{ ucfirst($currentStatus) }}
                        </span>
                    </div>

                    <div class="mt-3">
                        @if ($tableNumber)
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-50 text-orange-700 border border-orange-200 text-xs font-bold">
                                From: Table {{ $tableNumber }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-50 text-gray-500 border border-gray-200 text-xs font-semibold">
                                No table assigned
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">
                            Items
                        </p>

                        <div class="space-y-4">
                            @forelse ($order->items as $item)
                                @php
                                    $menuItem = $item->menuItem;

                                    $refillableIngredients = $menuItem
                                        ? $menuItem->ingredients->filter(function ($ingredient) {
                                            return (bool) (
                                                $ingredient->pivot->is_refillable ?? false
                                            );
                                        })
                                        : collect();

                                    $isUnlimited = (bool) (
                                        $menuItem?->is_unlimited ?? false
                                    );
                                @endphp

                                <div class="border border-gray-100 rounded-xl p-3">
                                    <p class="text-sm text-gray-800">
                                        <span class="font-semibold">
                                            {{ $item->quantity }}x
                                        </span>

                                        {{ $menuItem->name
                                            ?? $item->item_name
                                            ?? $item->name
                                            ?? 'Menu item' }}

                                        @if ($isUnlimited)
                                            <span class="ml-1 inline-flex px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200 text-[10px] font-bold">
                                                Unlimited
                                            </span>
                                        @endif
                                    </p>

                                    @if ($item->notes)
                                        <p class="text-xs text-gray-500 mt-1">
                                            Notes: {{ $item->notes }}
                                        </p>
                                    @endif

                                    @if ($isUnlimited && $refillableIngredients->isNotEmpty())
                                        <div class="mt-3 pt-3 border-t border-gray-100">
                                            <p class="text-xs font-bold text-gray-600 mb-2">
                                                Refill Options
                                            </p>

                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($refillableIngredients as $ingredient)
                                                    @php
                                                        $refillCount = $item->refillRecords
                                                            ->where(
                                                                'ingredient_id',
                                                                $ingredient->id
                                                            )
                                                            ->count();

                                                        $refillQuantity = (float) (
                                                            $ingredient->pivot->refill_quantity
                                                            ?? 0
                                                        );
                                                    @endphp

                                                    <form
                                                        action="{{ route(
                                                            'service.orders.refills.store',
                                                            [$order, $item]
                                                        ) }}"
                                                        method="POST"
                                                        onsubmit="return confirm(
                                                            'Record {{ addslashes($ingredient->name) }} refill of {{ number_format($refillQuantity, 2) }} {{ addslashes($ingredient->unit) }}?'
                                                        );"
                                                    >
                                                        @csrf

                                                        <input
                                                            type="hidden"
                                                            name="ingredient_id"
                                                            value="{{ $ingredient->id }}"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold border transition
                                                                {{ $canRecordRefill
                                                                    ? 'bg-orange-500 hover:bg-orange-600 text-white border-orange-500'
                                                                    : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' }}"
                                                            {{ $canRecordRefill ? '' : 'disabled' }}
                                                        >
                                                            <span>
                                                                + {{ $ingredient->name }} Refill
                                                            </span>

                                                            <span class="bg-white/20 px-1.5 py-0.5 rounded">
                                                                {{ $refillCount }}
                                                            </span>
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>

                                            @unless ($canRecordRefill)
                                                <p class="text-[11px] text-gray-400 mt-2">
                                                    Refills are enabled once the order is preparing.
                                                </p>
                                            @endunless
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <span class="text-sm text-gray-400">
                                    No items
                                </span>
                            @endforelse
                        </div>

                        <p class="text-sm font-extrabold text-orange-500 mt-3">
                            Total: ₱{{ number_format($order->total_amount ?? 0, 2) }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs text-gray-400">
                        <span>KDS controlled</span>

                        <span>
                            Updated:
                            {{ $order->updated_at
                                ? $order->updated_at->diffForHumans()
                                : 'No update recorded' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-2xl px-5 py-12 text-center shadow-sm">
                <h3 class="font-bold text-gray-900">
                    No active orders found
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Orders sent to the kitchen will appear here.
                </p>
            </div>
        @endforelse

        @if ($orders->hasPages())
            <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    {{-- DESKTOP TABLE VIEW --}}
    <div class="hidden lg:block service-premium-panel overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="premium-section-heading text-lg">
                Order List
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-5 py-4 font-semibold">Order</th>
                        <th class="px-5 py-4 font-semibold">Items and Refills</th>
                        <th class="px-5 py-4 font-semibold">Kitchen Status</th>
                        <th class="px-5 py-4 font-semibold">Last Updated</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        @php
                            $currentStatus = strtolower(
                                trim(
                                    $order->display_status
                                    ?? $order->status
                                    ?? 'pending'
                                )
                            );

                            if (in_array(
                                $currentStatus,
                                ['new', 'placed', 'confirmed'],
                                true
                            )) {
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

                            $canRecordRefill = in_array(
                                $currentStatus,
                                ['preparing', 'ready'],
                                true
                            );
                        @endphp

                        <tr class="align-top hover:bg-gray-50 transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-bold text-gray-900">
                                    {{ $order->order_number ?? 'No order number' }}
                                </p>

                                <div class="mt-2">
                                    @if ($tableNumber)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-50 text-orange-700 border border-orange-200 text-xs font-bold">
                                            From: Table {{ $tableNumber }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-50 text-gray-500 border border-gray-200 text-xs font-semibold">
                                            No table assigned
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-gray-500 mt-2">
                                    Created:
                                    {{ $order->created_at
                                        ? $order->created_at->diffForHumans()
                                        : 'No date recorded' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 w-[560px]">
                                <div class="space-y-4">
                                    @forelse ($order->items as $item)
                                        @php
                                            $menuItem = $item->menuItem;

                                            $refillableIngredients = $menuItem
                                                ? $menuItem->ingredients->filter(
                                                    function ($ingredient) {
                                                        return (bool) (
                                                            $ingredient->pivot->is_refillable
                                                            ?? false
                                                        );
                                                    }
                                                )
                                                : collect();

                                            $isUnlimited = (bool) (
                                                $menuItem?->is_unlimited
                                                ?? false
                                            );
                                        @endphp

                                        <div class="border border-gray-100 rounded-xl p-3">
                                            <p class="text-sm text-gray-800">
                                                <span class="font-semibold">
                                                    {{ $item->quantity }}x
                                                </span>

                                                {{ $menuItem->name
                                                    ?? $item->item_name
                                                    ?? $item->name
                                                    ?? 'Menu item' }}

                                                @if ($isUnlimited)
                                                    <span class="ml-1 inline-flex px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200 text-[10px] font-bold">
                                                        Unlimited
                                                    </span>
                                                @endif
                                            </p>

                                            @if ($item->notes)
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Notes: {{ $item->notes }}
                                                </p>
                                            @endif

                                            @if (
                                                $isUnlimited
                                                && $refillableIngredients->isNotEmpty()
                                            )
                                                <div class="mt-3 pt-3 border-t border-gray-100">
                                                    <p class="text-xs font-bold text-gray-600 mb-2">
                                                        Record Refill
                                                    </p>

                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach ($refillableIngredients as $ingredient)
                                                            @php
                                                                $refillCount = $item
                                                                    ->refillRecords
                                                                    ->where(
                                                                        'ingredient_id',
                                                                        $ingredient->id
                                                                    )
                                                                    ->count();

                                                                $refillQuantity = (float) (
                                                                    $ingredient->pivot->refill_quantity
                                                                    ?? 0
                                                                );
                                                            @endphp

                                                            <form
                                                                action="{{ route(
                                                                    'service.orders.refills.store',
                                                                    [$order, $item]
                                                                ) }}"
                                                                method="POST"
                                                                onsubmit="return confirm(
                                                                    'Record {{ addslashes($ingredient->name) }} refill of {{ number_format($refillQuantity, 2) }} {{ addslashes($ingredient->unit) }}?'
                                                                );"
                                                            >
                                                                @csrf

                                                                <input
                                                                    type="hidden"
                                                                    name="ingredient_id"
                                                                    value="{{ $ingredient->id }}"
                                                                >

                                                                <button
                                                                    type="submit"
                                                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold border transition
                                                                        {{ $canRecordRefill
                                                                            ? 'bg-orange-500 hover:bg-orange-600 text-white border-orange-500'
                                                                            : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' }}"
                                                                    {{ $canRecordRefill ? '' : 'disabled' }}
                                                                >
                                                                    <span>
                                                                        + {{ $ingredient->name }}
                                                                    </span>

                                                                    <span class="bg-white/20 px-1.5 py-0.5 rounded">
                                                                        {{ $refillCount }}
                                                                    </span>
                                                                </button>
                                                            </form>
                                                        @endforeach
                                                    </div>

                                                    @unless ($canRecordRefill)
                                                        <p class="text-[11px] text-gray-400 mt-2">
                                                            Available once order is preparing.
                                                        </p>
                                                    @endunless
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-sm text-gray-400">
                                            No items
                                        </span>
                                    @endforelse
                                </div>

                                <p class="text-xs font-bold text-orange-500 mt-3">
                                    Total: ₱{{ number_format($order->total_amount ?? 0, 2) }}
                                </p>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst($currentStatus) }}
                                </span>

                                <p class="text-xs text-gray-400 mt-2">
                                    KDS controlled
                                </p>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="text-sm font-semibold text-gray-700">
                                    {{ $order->updated_at
                                        ? $order->updated_at->diffForHumans()
                                        : 'No update recorded' }}
                                </p>

                                <p class="text-xs text-gray-400 mt-1">
                                    View and record refills
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <h3 class="font-bold text-gray-900">
                                    No active orders found
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    Orders sent to the kitchen will appear here.
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

<script>
let activeOrdersReloadTimer = null;

function startActiveOrdersReloadTimer() {
    clearInterval(activeOrdersReloadTimer);

    activeOrdersReloadTimer = setInterval(() => {
        if (document.hidden) {
            return;
        }

        const activeElement = document.activeElement;

        if (
            activeElement &&
            (
                activeElement.tagName === 'BUTTON' ||
                activeElement.tagName === 'INPUT' ||
                activeElement.tagName === 'SELECT'
            )
        ) {
            return;
        }

        window.location.reload();
    }, 30000);
}

startActiveOrdersReloadTimer();
</script>

@endsection