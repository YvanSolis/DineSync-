@php
    $status = strtolower(trim($refill->status ?? 'requested'));

    $tableNumber = $refill->table_number
        ?? optional($refill->order)->table_number
        ?? null;

    $orderNumber = optional($refill->order)->order_number
        ?? ($refill->order_id ?? '—');

    $menuName = optional($refill->menuItem)->name
        ?? 'Unlimited Menu';

    $requestedAt = $refill->requested_at
        ?? $refill->created_at;

    $refillTime = $requestedAt
        ? \Carbon\Carbon::parse($requestedAt, 'UTC')
            ->timezone('Asia/Manila')
            ->format('h:i A')
        : 'No time';

    $statusBadgeClass = match ($status) {
        'requested' => 'bg-orange-100 text-orange-700 border-orange-200',
        'preparing' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'ready' => 'bg-green-100 text-green-700 border-green-200',
        'served' => 'bg-gray-100 text-gray-600 border-gray-200',
        'cancelled' => 'bg-red-100 text-red-700 border-red-200',
        default => 'bg-gray-100 text-gray-600 border-gray-200',
    };

    $statusDotClass = match ($status) {
        'requested' => 'bg-orange-500',
        'preparing' => 'bg-yellow-500',
        'ready' => 'bg-green-500',
        'served' => 'bg-gray-400',
        'cancelled' => 'bg-red-500',
        default => 'bg-gray-400',
    };

    $accentClass = match ($status) {
        'requested' => 'from-orange-400 to-orange-600',
        'preparing' => 'from-yellow-400 to-yellow-600',
        'ready' => 'from-green-400 to-green-600',
        'served' => 'from-gray-300 to-gray-500',
        'cancelled' => 'from-red-400 to-red-600',
        default => 'from-gray-300 to-gray-500',
    };

    $items = $refill->items ?? collect();
    $visibleItems = $items->take(2);
    $remainingItemCount = max($items->count() - 2, 0);
@endphp

<div
    id="refill-card-{{ $refill->id }}"
    class="refill-card relative overflow-hidden rounded-2xl bg-white shadow-md border border-white/80"
    data-refill-id="{{ $refill->id }}"
    data-refill-status="{{ $status }}"
>
    <div class="h-1 bg-gradient-to-r {{ $accentClass }}"></div>

    <div class="p-2.5">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full {{ $statusDotClass }} shrink-0"></span>

                    <h3 class="text-[13px] lg:text-[14px] font-extrabold text-gray-900 truncate leading-tight">
                        Refill #{{ $refill->id }}
                    </h3>
                </div>

                <div class="mt-1.5 flex flex-wrap items-center gap-1">
                    <span class="rounded-lg border border-orange-200 bg-orange-50 px-2 py-0.5 text-[9px] font-extrabold text-orange-700">
                        {{ $tableNumber ? 'Table ' . $tableNumber : 'No Table' }}
                    </span>

                    <span class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-0.5 text-[9px] font-bold text-gray-600">
                        {{ $refillTime }}
                    </span>

                    <span class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-0.5 text-[9px] font-bold text-gray-600">
                        {{ $items->count() }} item{{ $items->count() !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <span class="{{ $statusBadgeClass }} border px-2 py-0.5 rounded-lg text-[8px] font-extrabold uppercase whitespace-nowrap">
                {{ ucfirst($status) }}
            </span>
        </div>

        <div class="mt-2 rounded-xl border border-gray-100 bg-gray-50/90 px-2.5 py-1.5">
            <p class="text-[9px] font-extrabold uppercase tracking-wide text-orange-500">
                Unlimited Package
            </p>

            <p class="text-[11px] lg:text-[12px] font-bold text-gray-900 truncate mt-0.5">
                {{ $menuName }}
            </p>

            <p class="text-[9px] text-gray-500 mt-0.5 truncate">
                Order {{ $orderNumber }}
            </p>
        </div>

        <div class="mt-2 space-y-1">
            @forelse ($visibleItems as $item)
                @php
                    $ingredientName = optional($item->ingredient)->name
                        ?? 'Ingredient';

                    $unit = $item->unit
                        ?? optional($item->ingredient)->unit
                        ?? 'unit';
                @endphp

                <div class="flex items-center justify-between gap-2 rounded-xl border border-gray-100 bg-gray-50/90 px-2.5 py-1.5">
                    <div class="min-w-0 flex-1">
                        <p class="text-[12px] lg:text-[13px] font-bold text-gray-900 truncate leading-tight">
                            {{ $ingredientName }}
                        </p>

                        <p class="text-[9px] text-gray-500 mt-0.5">
                            Refill ingredient
                        </p>
                    </div>

                    <span class="shrink-0 rounded-lg bg-orange-100 px-2 py-0.5 text-[10px] font-extrabold text-orange-700">
                        {{ number_format((float) $item->quantity, 2) }} {{ $unit }}
                    </span>
                </div>
            @empty
                <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-center text-xs text-gray-400">
                    No refill items found
                </div>
            @endforelse

            @if ($remainingItemCount > 0)
                <div class="rounded-xl border border-dashed border-gray-200 bg-white px-2 py-1 text-center text-[9px] font-bold text-gray-500">
                    +{{ $remainingItemCount }} more item{{ $remainingItemCount !== 1 ? 's' : '' }}
                </div>
            @endif
        </div>

        @if (!empty($refill->notes))
            <div class="mt-1.5 rounded-xl border border-yellow-100 bg-yellow-50 px-2.5 py-1.5">
                <p class="text-[9px] font-semibold leading-relaxed text-yellow-700 truncate">
                    Note: {{ $refill->notes }}
                </p>
            </div>
        @endif

        <div class="mt-2">
            @if (!empty($buttonText) && !empty($nextStatus))
                <form
                    method="POST"
                    action="{{ route('kitchen.refills.update-status', $refill) }}"
                    class="kds-refill-status-form"
                    data-refill-id="{{ $refill->id }}"
                    data-next-status="{{ $nextStatus }}"
                >
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="status" value="{{ $nextStatus }}">

                    <button
                        type="submit"
                        class="w-full {{ $buttonClass }} text-white py-2 rounded-xl font-extrabold text-[10px] lg:text-[11px] uppercase tracking-wide transition active:scale-[0.98] shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        {{ $buttonText }}
                    </button>
                </form>
            @else
                <div class="w-full rounded-xl border border-gray-200 bg-gray-100 py-2 text-center text-[10px] lg:text-[11px] font-extrabold text-gray-500 uppercase">
                    {{ $status === 'cancelled' ? 'Cancelled' : 'Completed' }}
                </div>
            @endif
        </div>
    </div>
</div>
