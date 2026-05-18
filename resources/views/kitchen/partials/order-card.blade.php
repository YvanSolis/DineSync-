@php
    $orderNumber = $order->order_number ?? $order->id;

    $tableNumber = $order->source_table_number
        ?? $order->table_number
        ?? $order->table_id
        ?? null;

    $customerName = $order->customer_name ?? $order->customer?->name ?? null;
    $notes = $order->notes ?? $order->special_instructions ?? null;

    $statusBadgeClass = match ($order->status) {
        'pending' => 'bg-blue-500/10 text-blue-300 border-blue-500/20',
        'preparing' => 'bg-amber-500/10 text-amber-300 border-amber-500/20',
        'ready' => 'bg-green-500/10 text-green-300 border-green-500/20',
        'served' => 'bg-gray-700 text-gray-300 border-gray-600',
        default => 'bg-gray-700 text-gray-300 border-gray-600',
    };

    $cardBorderClass = match ($columnType ?? $order->status) {
        'pending' => 'border-blue-500/20',
        'preparing' => 'border-amber-500/20',
        'ready' => 'border-green-500/20',
        'served' => 'border-gray-800 opacity-75',
        default => 'border-gray-800',
    };
@endphp

<div id="order-card-{{ $order->id }}"
     class="order-card bg-gray-900 border {{ $cardBorderClass }} rounded-2xl p-4 shadow-sm">

    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="min-w-0">
            <h3 class="text-lg font-bold text-white leading-tight break-words">
                #{{ $orderNumber }}
            </h3>

            <div class="mt-2 flex flex-wrap gap-2">
                <span class="bg-gray-800 text-gray-300 border border-gray-700 px-2.5 py-1 rounded-lg text-xs font-semibold">
                    {{ $tableNumber ? 'Table ' . $tableNumber : 'No table' }}
                </span>

                @if ($customerName)
                    <span class="bg-gray-800 text-gray-300 border border-gray-700 px-2.5 py-1 rounded-lg text-xs font-semibold">
                        {{ $customerName }}
                    </span>
                @endif
            </div>
        </div>

        <span class="{{ $statusBadgeClass }} border px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase whitespace-nowrap">
            {{ ucfirst($order->status) }}
        </span>
    </div>

    <div class="bg-gray-950 border border-gray-800 rounded-xl p-3 mb-4">
        <p class="text-[11px] text-gray-500 font-semibold uppercase tracking-wide">Order Time</p>
        <p class="text-sm font-semibold text-gray-200 mt-1">
            {{ $order->created_at ? $order->created_at->format('h:i A') : 'No time' }}
        </p>
    </div>

    <div class="mb-4">
        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2">
            Items
        </p>

        <div class="space-y-2">
            @forelse ($order->items as $item)
                <div class="bg-gray-950 border border-gray-800 rounded-xl p-3 flex gap-3">
                    <div class="min-w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center text-sm font-bold text-white">
                        {{ $item->quantity }}x
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white leading-snug">
                            {{ $item->menuItem->name ?? 'Menu Item Deleted' }}
                        </p>

                        @if (!empty($item->notes))
                            <p class="text-xs text-amber-300 font-medium mt-1">
                                Note: {{ $item->notes }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-gray-950 border border-gray-800 rounded-xl p-3 text-gray-500 text-sm">
                    No items found
                </div>
            @endforelse
        </div>
    </div>

    @if ($notes)
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-3 mb-4">
            <p class="text-[11px] font-semibold text-amber-300 uppercase tracking-wide mb-1">
                Special Instructions
            </p>
            <p class="text-sm font-medium text-amber-100">
                {{ $notes }}
            </p>
        </div>
    @endif

    @if (!empty($buttonText) && !empty($nextStatus))
        <form method="POST"
              action="{{ route('kitchen.orders.status', $order) }}"
              class="kds-status-form"
              data-next-status="{{ $nextStatus }}">
            @csrf
            @method('PATCH')

            <input type="hidden" name="status" value="{{ $nextStatus }}">

            <button class="w-full {{ $buttonClass }} text-white py-3 rounded-xl font-semibold text-sm transition">
                {{ $buttonText }}
            </button>
        </form>
    @else
        <div class="w-full bg-gray-800 text-gray-400 py-3 rounded-xl font-semibold text-center text-sm">
            Completed
        </div>
    @endif

</div>