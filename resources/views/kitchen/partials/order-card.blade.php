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
        'pending' => 'bg-orange-50 text-orange-700 border-orange-200',
        'preparing' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'ready' => 'bg-green-50 text-green-700 border-green-200',
        'served' => 'bg-gray-50 text-gray-600 border-gray-200',
        default => 'bg-gray-50 text-gray-600 border-gray-200',
    };

    $cardBorderClass = match ($columnType ?? $currentStatus) {
        'pending' => 'border-orange-100',
        'preparing' => 'border-yellow-100',
        'ready' => 'border-green-100',
        'served' => 'border-gray-200 opacity-80',
        default => 'border-gray-200',
    };

    $statusDotClass = match ($currentStatus) {
        'pending' => 'bg-orange-400',
        'preparing' => 'bg-yellow-400',
        'ready' => 'bg-green-400',
        'served' => 'bg-gray-400',
        default => 'bg-gray-400',
    };
@endphp

<div id="order-card-{{ $order->id }}"
     class="order-card bg-white border {{ $cardBorderClass }} rounded-3xl p-4 shadow-sm hover:shadow-md transition">

    {{-- ORDER HEADER --}}
    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $statusDotClass }}"></span>

                <h3 class="text-xl font-extrabold text-gray-900 leading-tight break-words">
                    #{{ $orderNumber }}
                </h3>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <span class="bg-orange-50 text-orange-700 border border-orange-200 px-3 py-1.5 rounded-xl text-xs font-bold">
                    {{ $tableNumber ? 'Table ' . $tableNumber : 'No Table' }}
                </span>

                @if ($customerName)
                    <span class="bg-gray-50 text-gray-600 border border-gray-200 px-3 py-1.5 rounded-xl text-xs font-bold">
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
    <div class="bg-orange-50/60 border border-orange-100 rounded-2xl p-3 mb-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wide">Order Time</p>
                <p class="text-base font-extrabold text-gray-800 mt-1">
                    {{ $order->created_at ? $order->created_at->format('h:i A') : 'No time' }}
                </p>
            </div>

            <div class="text-right">
                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wide">Items</p>
                <p class="text-base font-extrabold text-orange-500 mt-1">
                    {{ $order->items->count() }}
                </p>
            </div>
        </div>
    </div>

    {{-- ITEMS --}}
    <div class="mb-4">
        <p class="text-[11px] font-extrabold text-gray-400 uppercase tracking-wide mb-2">
            Order Items
        </p>

        <div class="space-y-2.5">
            @forelse ($order->items as $item)
                <div class="bg-white border border-gray-100 rounded-2xl p-3 flex gap-3 shadow-sm">
                    <div class="min-w-12 h-12 bg-orange-50 border border-orange-100 rounded-2xl flex items-center justify-center text-lg font-extrabold text-orange-500">
                        {{ $item->quantity }}x
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-base font-bold text-gray-900 leading-snug">
                            {{ $item->menuItem->name ?? 'Menu Item Deleted' }}
                        </p>

                        @if (!empty($item->notes))
                            <p class="text-xs text-yellow-600 font-semibold mt-1 leading-relaxed">
                                Note: {{ $item->notes }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 text-gray-400 text-sm text-center">
                    No items found
                </div>
            @endforelse
        </div>
    </div>

    {{-- SPECIAL INSTRUCTIONS --}}
    @if ($notes)
        <div class="bg-yellow-50 border border-yellow-100 rounded-2xl p-3 mb-4">
            <p class="text-[11px] font-extrabold text-yellow-600 uppercase tracking-wide mb-1">
                Special Instructions
            </p>

            <p class="text-sm font-semibold text-yellow-700 leading-relaxed">
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

            <button class="w-full {{ $buttonClass }} text-white py-4 rounded-2xl font-extrabold text-sm transition shadow-md active:scale-[0.98]">
                {{ $buttonText }}
            </button>
        </form>
    @else
        <div class="w-full bg-gray-100 text-gray-500 py-4 rounded-2xl font-extrabold text-center text-sm border border-gray-200">
            Completed
        </div>
    @endif

</div>