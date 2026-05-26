@extends('layouts.service')

@section('page-title', 'Reservations')
@section('page-subtitle', 'Manage customer reservations, payments, arrivals, and seating')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reservations</h1>
            <p class="text-gray-500 mt-1">
                Verify payment first before accepting a reservation.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm">
            <p class="text-sm text-gray-500">Total Reservations</p>
            <p class="text-2xl font-bold text-orange-500">{{ $reservations->total() }}</p>
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
            <h2 class="text-lg font-bold text-gray-900">Reservation List</h2>
            <p class="text-sm text-gray-500">
                Service staff controls payment verification, reservation approval, arrivals, and seating.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1180px] text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-5 py-4 font-semibold">Reservation</th>
                        <th class="px-5 py-4 font-semibold">Customer</th>
                        <th class="px-5 py-4 font-semibold">Date & Time</th>
                        <th class="px-5 py-4 font-semibold">Guests</th>
                        <th class="px-5 py-4 font-semibold">Table</th>
                        <th class="px-5 py-4 font-semibold">Payment</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($reservations as $reservation)
                        @php
                            $paymentClass = match($reservation->payment_status) {
                                'paid', 'verified' => 'bg-green-50 text-green-700 border-green-200',
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

                        <tr class="align-top hover:bg-gray-50 transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-bold text-gray-900">
                                    #RES-{{ str_pad($reservation->id, 4, '0', STR_PAD_LEFT) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $reservation->created_at->diffForHumans() }}
                                </p>
                            </td>

                            <td class="px-5 py-4 w-[220px]">
                                <p class="font-semibold text-gray-900">
                                    {{ $reservation->customer_name }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $reservation->customer_email }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $reservation->customer_phone }}
                                </p>

                                @if ($reservation->notes)
                                    <p class="text-xs text-gray-500 mt-2">
                                        <span class="font-semibold text-gray-700">Notes:</span>
                                        {{ $reservation->notes }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-semibold text-gray-900">
                                    {{ $reservation->reservation_date ? $reservation->reservation_date->format('M d, Y') : 'No date' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}
                                </p>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                                    {{ $reservation->guest_count }} guest{{ $reservation->guest_count > 1 ? 's' : '' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                @if ($reservation->table_number)
                                    <span class="inline-flex px-3 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200 text-xs font-semibold">
                                        Table {{ $reservation->table_number }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">Not assigned</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 w-[170px]">
                                <p class="font-bold text-orange-500">
                                    ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                                </p>

                                <p class="font-semibold text-gray-900 mt-1">
                                    {{ $reservation->payment_method ?? 'N/A' }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    Ref: {{ $reservation->payment_reference ?? 'N/A' }}
                                </p>

                                <span class="inline-flex mt-2 px-3 py-1 rounded-full border text-xs font-semibold {{ $paymentClass }}">
                                    {{ ucfirst($reservation->payment_status) }}
                                </span>

                                @if ($reservation->payment_proof)
                                    <a href="{{ asset('storage/' . $reservation->payment_proof) }}"
                                       target="_blank"
                                       class="block mt-2 text-xs font-semibold text-orange-500 hover:text-orange-600">
                                        View Proof
                                    </a>
                                @else
                                    <p class="text-xs text-gray-400 mt-2">No proof</p>
                                @endif
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst($reservation->status) }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2 flex-wrap min-w-[190px]">

                                    @if ($reservation->payment_status === 'pending')
                                        <form method="POST" action="{{ route('service.reservations.verify-payment', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold text-xs">
                                                Verify
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('service.reservations.reject-payment', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold text-xs">
                                                Reject
                                            </button>
                                        </form>
                                    @endif

                                    @if ($reservation->payment_status === 'rejected')
                                        <span class="px-3 py-2 rounded-lg bg-red-50 text-red-600 font-semibold text-xs">
                                            Payment rejected
                                        </span>
                                    @endif

                                    @if ($reservation->payment_status === 'verified' && $reservation->status === 'pending')
                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs">
                                                Accept
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="declined">
                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold text-xs">
                                                Decline
                                            </button>
                                        </form>
                                    @endif

                                    @if ($reservation->payment_status !== 'verified' && $reservation->payment_status !== 'rejected' && $reservation->status === 'pending')
                                        <span class="px-3 py-2 rounded-lg bg-yellow-50 text-yellow-700 font-semibold text-xs whitespace-nowrap">
                                            Verify payment first
                                        </span>
                                    @endif

                                    @if ($reservation->status === 'approved')
                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="arrived">
                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-semibold text-xs">
                                                Mark Arrived
                                            </button>
                                        </form>
                                    @endif

                                    @if ($reservation->status === 'arrived')
                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="seated">

                                            <input type="text" name="table_number" placeholder="Table #"
                                                class="w-20 rounded-lg border-gray-200 text-xs focus:border-orange-300 focus:ring-orange-200">

                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-purple-500 hover:bg-purple-600 text-white font-semibold text-xs">
                                                Seat
                                            </button>
                                        </form>
                                    @endif

                                    @if ($reservation->status === 'seated')
                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-gray-800 hover:bg-gray-900 text-white font-semibold text-xs">
                                                Complete
                                            </button>
                                        </form>
                                    @endif

                                    @if (!in_array($reservation->status, ['declined', 'cancelled', 'completed']))
                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold text-xs">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif

                                    @if (in_array($reservation->status, ['declined', 'cancelled', 'completed']))
                                        <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-500 font-semibold text-xs whitespace-nowrap">
                                            No action needed
                                        </span>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <h3 class="font-bold text-gray-900">No reservations found</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Customer reservation requests will appear here.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reservations->hasPages())
            <div class="p-5 border-t border-gray-100">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>

<script>
setInterval(() => {
    if (document.hidden) {
        return;
    }

    const activeElement = document.activeElement;
    const isInteracting =
        activeElement &&
        (
            activeElement.tagName === 'INPUT' ||
            activeElement.tagName === 'TEXTAREA' ||
            activeElement.tagName === 'SELECT' ||
            activeElement.tagName === 'BUTTON'
        );

    if (isInteracting) {
        return;
    }

    window.location.reload();
}, 30000);
</script>

@endsection