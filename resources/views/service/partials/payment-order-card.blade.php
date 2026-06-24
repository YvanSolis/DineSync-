@php
    $rawMethod = trim($order->payment_method ?? '');
    $normalizedMethod = strtolower(str_replace(['_', '-'], ' ', $rawMethod));

    $paymentMethod = match (true) {
        str_contains($normalizedMethod, 'digital') => 'Digital Payment',
        str_contains($normalizedMethod, 'qr') => 'Digital Payment',
        str_contains($normalizedMethod, 'xendit') => 'Digital Payment',
        str_contains($normalizedMethod, 'later') => 'Pay Later',
        str_contains($normalizedMethod, 'counter') => 'Pay at Counter',
        str_contains($normalizedMethod, 'cash') => 'Pay at Counter',
        default => $rawMethod ?: 'Pay at Counter',
    };

    $paymentStatus = strtolower(trim($order->payment_status ?? 'pending'));
    $orderStatus = strtolower(trim($order->status ?? 'pending'));

    $isPaid = in_array($paymentStatus, ['paid', 'verified'], true);
    $isAwaitingPayment = $orderStatus === 'awaiting_payment';

    $methodClass = match ($paymentMethod) {
        'Digital Payment' => 'bg-blue-50 text-blue-700 border-blue-200',
        'Pay Later' => 'bg-purple-50 text-purple-700 border-purple-200',
        'Pay at Counter' => 'bg-orange-50 text-orange-700 border-orange-200',
        default => 'bg-gray-50 text-gray-700 border-gray-200',
    };

    $paymentClass = $isPaid
        ? 'bg-green-50 text-green-700 border-green-200'
        : 'bg-red-50 text-red-700 border-red-200';

    $tableNumber = $order->source_table_number
        ?? $order->table_number
        ?? $order->table?->table_number
        ?? $order->table?->name
        ?? null;
@endphp

<div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-100">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-black text-gray-950 truncate">
                    {{ $order->order_number ?? 'No order number' }}
                </p>

                <p class="text-xs text-gray-500 mt-1">
                    Created: {{ $order->created_at ? $order->created_at->diffForHumans() : 'No date' }}
                </p>
            </div>

            @if ($tableNumber)
                <span class="inline-flex shrink-0 px-3 py-1 rounded-full bg-orange-50 text-orange-700 border border-orange-200 text-xs font-black">
                    Table {{ $tableNumber }}
                </span>
            @else
                <span class="inline-flex shrink-0 px-3 py-1 rounded-full bg-gray-50 text-gray-500 border border-gray-200 text-xs font-bold">
                    No table
                </span>
            @endif
        </div>
    </div>

    <div class="p-4 space-y-4">
        <div>
            <p class="text-xs font-black uppercase tracking-wide text-gray-400 mb-2">
                Items
            </p>

            <div class="space-y-1">
                @forelse ($order->items as $item)
                    <p class="text-sm text-gray-800">
                        <span class="font-bold">{{ $item->quantity }}x</span>
                        {{ $item->menuItem->name ?? $item->menuItem->item_name ?? 'Menu item' }}
                    </p>
                @empty
                    <p class="text-sm text-gray-400">
                        No items
                    </p>
                @endforelse
            </div>

            <p class="text-sm font-black text-orange-500 mt-3">
                Total: ₱{{ number_format($order->total_amount ?? 0, 2) }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-3">
            <p class="text-xs font-black uppercase tracking-wide text-gray-400 mb-2">
                Payment
            </p>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $methodClass }}">
                    {{ $paymentMethod }}
                </span>

                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $paymentClass }}">
                    {{ $isPaid ? 'Paid' : 'Pending Payment' }}
                </span>
            </div>

            @if ($paymentMethod === 'Digital Payment' && !$isPaid)
                <p class="text-xs text-blue-600 font-bold mt-2">
                    Waiting for Xendit QR payment.
                </p>
            @elseif ($isAwaitingPayment)
                <p class="text-xs text-orange-600 font-bold mt-2">
                    Waiting for payment. Not yet sent to KDS.
                </p>
            @else
                <p class="text-xs text-green-600 font-bold mt-2">
                    Sent to kitchen. Status: {{ ucfirst($orderStatus) }}
                </p>
            @endif
        </div>

        @if (!$isPaid && in_array($paymentMethod, ['Pay at Counter', 'Pay Later'], true))
            <form method="POST" action="{{ route('service.orders.mark-paid', $order) }}">
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    onclick="return confirm('Mark this order as paid? Pay at Counter orders will be sent to KDS after this.')"
                    class="w-full rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-black px-4 py-3 transition">
                    Mark Paid
                </button>
            </form>
        @elseif ($paymentMethod === 'Digital Payment' && !$isPaid)
            <div class="w-full rounded-xl bg-blue-50 text-blue-700 text-sm font-black px-4 py-3 text-center">
                Waiting Xendit
            </div>
        @else
            <div class="w-full rounded-xl bg-gray-100 text-gray-500 text-sm font-black px-4 py-3 text-center">
                No Action
            </div>
        @endif
    </div>
</div>