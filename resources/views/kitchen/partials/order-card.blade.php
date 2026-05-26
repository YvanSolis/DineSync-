@php
    $orderNumber = $order->order_number ?? $order->id;

    $tableNumber = $order->source_table_number
        ?? $order->table_number
        ?? $order->table_id
        ?? null;

    $customerName = $order->customer_name ?? $order->customer?->name ?? null;
    $notes = $order->notes ?? $order->special_instructions ?? null;

    $currentStatus = strtolower($order->status ?? 'pending');

    $statusBadgeClass = match ($currentStatus) {
        'pending' => 'bg-blue-500/10 text-blue-300 border-blue-500/30',
        'preparing' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
        'ready' => 'bg-green-500/10 text-green-300 border-green-500/30',
        'served' => 'bg-gray-700 text-gray-300 border-gray-600',
        default => 'bg-gray-700 text-gray-300 border-gray-600',
    };

    $cardBorderClass = match ($columnType ?? $currentStatus) {
        'pending' => 'border-blue-500/30',
        'preparing' => 'border-amber-500/30',
        'ready' => 'border-green-500/30',
        'served' => 'border-gray-700 opacity-75',
        default => 'border-gray-700',
    };

    $statusDotClass = match ($currentStatus) {
        'pending' => 'bg-blue-400',
        'preparing' => 'bg-amber-400',
        'ready' => 'bg-green-400',
        'served' => 'bg-gray-500',
        default => 'bg-gray-500',
    };
@endphp

<div id="order-card-{{ $order->id }}"
     class="order-card bg-gray-900/95 border {{ $cardBorderClass }} rounded-3xl p-4 shadow-xl shadow-black/20">

    {{-- ORDER HEADER --}}
    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $statusDotClass }}"></span>

                <h3 class="text-xl font-extrabold text-white leading-tight break-words">
                    #{{ $orderNumber }}
                </h3>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <span class="bg-orange-500/10 text-orange-200 border border-orange-500/20 px-3 py-1.5 rounded-xl text-xs font-bold">
                    {{ $tableNumber ? 'Table ' . $tableNumber : 'No Table' }}
                </span>

                @if ($customerName)
                    <span class="bg-gray-800 text-gray-300 border border-gray-700 px-3 py-1.5 rounded-xl text-xs font-bold">
                        {{ $customerName }}
                    </span>
                @endif
            </div>
        </div>

        <span class="{{ $statusBadgeClass }} border px-3 py-1.5 rounded-xl text-[11px] font-extrabold uppercase whitespace-nowrap">
            {{ ucfirst($currentStatus) }}
        </span>
    </div>

    {{-- ORDER TIME --}}
    <div class="bg-gray-950/90 border border-gray-800 rounded-2xl p-3 mb-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wide">Order Time</p>
                <p class="text-base font-extrabold text-gray-100 mt-1">
                    {{ $order->created_at ? $order->created_at->format('h:i A') : 'No time' }}
                </p>
            </div>

            <div class="text-right">
                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wide">Items</p>
                <p class="text-base font-extrabold text-orange-300 mt-1">
                    {{ $order->items->count() }}
                </p>
            </div>
        </div>
    </div>

    {{-- ITEMS --}}
    <div class="mb-4">
        <p class="text-[11px] font-extrabold text-gray-500 uppercase tracking-wide mb-2">
            Order Items
        </p>

        <div class="space-y-2.5">
            @forelse ($order->items as $item)
                <div class="bg-gray-950/90 border border-gray-800 rounded-2xl p-3 flex gap-3">
                    <div class="min-w-12 h-12 bg-orange-500/15 border border-orange-500/20 rounded-2xl flex items-center justify-center text-lg font-extrabold text-orange-200">
                        {{ $item->quantity }}x
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-base font-bold text-white leading-snug">
                            {{ $item->menuItem->name ?? 'Menu Item Deleted' }}
                        </p>

                        @if (!empty($item->notes))
                            <p class="text-xs text-amber-300 font-semibold mt-1 leading-relaxed">
                                Note: {{ $item->notes }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-gray-950/90 border border-gray-800 rounded-2xl p-4 text-gray-500 text-sm text-center">
                    No items found
                </div>
            @endforelse
        </div>
    </div>

    {{-- SPECIAL INSTRUCTIONS --}}
    @if ($notes)
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-3 mb-4">
            <p class="text-[11px] font-extrabold text-amber-300 uppercase tracking-wide mb-1">
                Special Instructions
            </p>

            <p class="text-sm font-semibold text-amber-100 leading-relaxed">
                {{ $notes }}
            </p>
        </div>
    @endif

    {{-- ACTION BUTTON --}}
    @if (!empty($buttonText) && !empty($nextStatus))
        <form method="POST"
              action="{{ route('kitchen.orders.status', $order) }}"
              class="kds-status-form"
              data-next-status="{{ $nextStatus }}">
            @csrf
            @method('PATCH')

            <input type="hidden" name="status" value="{{ $nextStatus }}">

            <button class="w-full {{ $buttonClass }} text-white py-4 rounded-2xl font-extrabold text-sm transition shadow-lg shadow-black/20 active:scale-[0.98]">
                {{ $buttonText }}
            </button>
        </form>
    @else
        <div class="w-full bg-gray-800 text-gray-400 py-4 rounded-2xl font-extrabold text-center text-sm">
            Completed
        </div>
    @endif

</div>