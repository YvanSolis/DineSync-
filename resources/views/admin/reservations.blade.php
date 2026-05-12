@extends('layouts.admin')

@section('content')

<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reservations</h1>
            <p class="text-gray-500 mt-1">
                Review customer reservations and verify reservation fee payments.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm">
            <p class="text-sm text-gray-500">Total Reservations</p>
            <p class="text-2xl font-bold text-orange-500">{{ $reservations->total() }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Reservation Requests</h2>
            <p class="text-sm text-gray-500">
                Customer submissions will appear here after they reserve a table.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-5 py-4 font-semibold">Customer</th>
                        <th class="px-5 py-4 font-semibold">Date & Time</th>
                        <th class="px-5 py-4 font-semibold">Guests</th>
                        <th class="px-5 py-4 font-semibold">Reservation Fee</th>
                        <th class="px-5 py-4 font-semibold">Payment</th>
                        <th class="px-5 py-4 font-semibold">Reservation Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($reservations as $reservation)
                        <tr class="align-top">
                            <td class="px-5 py-4">
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
                                    <p class="text-xs text-gray-500 mt-3 max-w-xs">
                                        <span class="font-semibold text-gray-700">Notes:</span>
                                        {{ $reservation->notes }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-900">
                                    {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold">
                                    {{ $reservation->guest_count }} guest{{ $reservation->guest_count > 1 ? 's' : '' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-bold text-orange-500">
                                    ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    Non-refundable
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-900">
                                    {{ $reservation->payment_method ?? 'N/A' }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    Ref: {{ $reservation->payment_reference ?? 'N/A' }}
                                </p>

                                @php
                                    $paymentClasses = [
                                        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'verified' => 'bg-green-50 text-green-700 border-green-200',
                                        'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                        'unpaid' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    ];
                                @endphp

                                <span class="inline-flex mt-2 px-3 py-1 rounded-full border text-xs font-semibold {{ $paymentClasses[$reservation->payment_status] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst($reservation->payment_status) }}
                                </span>

                                @if ($reservation->payment_proof)
                                    <a
                                        href="{{ asset('storage/' . $reservation->payment_proof) }}"
                                        target="_blank"
                                        class="block mt-2 text-xs font-semibold text-orange-500 hover:text-orange-600"
                                    >
                                        View Payment Proof
                                    </a>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'approved' => 'bg-green-50 text-green-700 border-green-200',
                                        'declined' => 'bg-red-50 text-red-700 border-red-200',
                                        'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    ];
                                @endphp

                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClasses[$reservation->status] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst($reservation->status) }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex flex-col items-end gap-2">

                                    @if ($reservation->payment_status === 'pending')
                                        <form method="POST" action="{{ route('admin.reservations.verify-payment', $reservation) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="w-full px-4 py-2 rounded-xl bg-green-500 hover:bg-green-600 text-white font-semibold text-xs"
                                            >
                                                Verify Payment
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.reservations.reject-payment', $reservation) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="w-full px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold text-xs"
                                            >
                                                Reject Payment
                                            </button>
                                        </form>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('admin.reservations.update-status', $reservation) }}"
                                        class="flex items-center gap-2"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <select
                                            name="status"
                                            class="rounded-xl border-gray-200 text-xs focus:border-orange-300 focus:ring-orange-200"
                                        >
                                            <option value="pending" {{ $reservation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $reservation->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="declined" {{ $reservation->status === 'declined' ? 'selected' : '' }}>Declined</option>
                                            <option value="completed" {{ $reservation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $reservation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>

                                        <button
                                            type="submit"
                                            class="px-3 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs"
                                        >
                                            Save
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl mb-4">
                                        📅
                                    </div>

                                    <h3 class="font-bold text-gray-900">No reservations yet</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Customer reservation requests will appear here.
                                    </p>
                                </div>
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

@endsection