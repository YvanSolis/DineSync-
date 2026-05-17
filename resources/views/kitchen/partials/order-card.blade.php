@php
    $orderNumber = $order->order_number ?? $order->id;
    $tableNumber = $order->source_table_number
    ?? $order->table_number
    ?? $order->table_id
    ?? null;
    $customerName = $order->customer_name ?? $order->customer?->name ?? null;
    $notes = $order->notes ?? $order->special_instructions ?? null;

    $statusBadgeClass = match ($order->status) {
        'pending' => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
        'preparing' => 'bg-orange-500/20 text-orange-300 border-orange-500/40',
        'ready' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
        'served' => 'bg-slate-700 text-slate-300 border-slate-600',
        default => 'bg-slate-700 text-slate-300 border-slate-600',
    };

    $cardBorderClass = match ($columnType ?? $order->status) {
        'pending' => 'border-blue-500/40',
        'preparing' => 'border-orange-500/40',
        'ready' => 'border-emerald-500/40',
        'served' => 'border-slate-700 opacity-75',
        default => 'border-slate-700',
    };
@endphp

<div id="order-card-{{ $order->id }}" class="order-card bg-slate-950 border {{ $cardBorderClass }} rounded-3xl p-4 shadow-lg">

    <div class="flex items-start justify-between gap-3 mb-4">
        <div>
            <h3 class="text-3xl font-black text-white leading-none">
                #{{ $orderNumber }}
            </h3>

            <div class="mt-3 flex flex-wrap gap-2">
                <span class="bg-slate-800 text-slate-300 px-3 py-2 rounded-xl text-sm font-black">
                    {{ $tableNumber ? 'TABLE ' . $tableNumber : 'NO TABLE' }}
                </span>

                @if ($customerName)
                    <span class="bg-slate-800 text-slate-300 px-3 py-2 rounded-xl text-sm font-black">
                        {{ strtoupper($customerName) }}
                    </span>
                @endif
            </div>
        </div>

        <span class="{{ $statusBadgeClass }} border px-3 py-2 rounded-xl text-xs font-black uppercase">
            {{ strtoupper($order->status) }}
        </span>
    </div>

    <div class="bg-slate-900 rounded-2xl p-3 mb-4 border border-slate-800">
        <p class="text-xs text-slate-500 font-black uppercase">Order Time</p>
        <p class="text-base font-black text-slate-200">
            {{ $order->created_at ? $order->created_at->format('h:i A') : 'No time' }}
        </p>
    </div>

    <div class="mb-4">
        <p class="text-xs font-black text-slate-500 uppercase mb-2">
            Items
        </p>

        <div class="space-y-2">
            @forelse ($order->items as $item)
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 flex gap-3">
                    <div class="min-w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center text-xl font-black text-white">
                        {{ $item->quantity }}x
                    </div>

                    <div class="flex-1">
                        <p class="text-lg font-black text-white leading-tight">
                            {{ $item->menuItem->name ?? 'Menu Item Deleted' }}
                        </p>

                        @if (!empty($item->notes))
                            <p class="text-sm text-yellow-300 font-bold mt-1">
                                Note: {{ $item->notes }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 text-slate-500 font-bold">
                    No items found
                </div>
            @endforelse
        </div>
    </div>

    @if ($notes)
        <div class="bg-yellow-500/15 border border-yellow-500/40 rounded-2xl p-3 mb-4">
            <p class="text-xs font-black text-yellow-300 uppercase mb-1">
                Special Instructions
            </p>
            <p class="text-sm font-bold text-yellow-100">
                {{ $notes }}
            </p>
        </div>
    @endif

    @if (!empty($buttonText) && !empty($nextStatus))
        <form method="POST" action="{{ route('kitchen.orders.status', $order) }}" class="kds-status-form" data-next-status="{{ $nextStatus }}">
            @csrf
            @method('PATCH')

            <input type="hidden" name="status" value="{{ $nextStatus }}">

            <button class="w-full {{ $buttonClass }} text-white py-5 rounded-2xl font-black text-xl active:scale-95 transition">
                {{ $buttonText }}
            </button>
        </form>
    @else
        <div class="w-full bg-slate-800 text-slate-400 py-5 rounded-2xl font-black text-center">
            COMPLETED
        </div>
    @endif

</div>