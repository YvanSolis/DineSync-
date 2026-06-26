@extends('layouts.admin')

@section('content')

@php
    $selectedDateCarbon = \Carbon\Carbon::parse($selectedDate);

    $paymentClasses = [
        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'verified' => 'bg-green-50 text-green-700 border-green-200',
        'rejected' => 'bg-red-50 text-red-700 border-red-200',
        'unpaid' => 'bg-gray-50 text-gray-600 border-gray-200',
        'paid' => 'bg-green-50 text-green-700 border-green-200',
        'failed' => 'bg-red-50 text-red-700 border-red-200',
        'expired' => 'bg-gray-50 text-gray-600 border-gray-200',
    ];

    $statusClasses = [
        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'approved' => 'bg-green-50 text-green-700 border-green-200',
        'declined' => 'bg-red-50 text-red-700 border-red-200',
        'arrived' => 'bg-blue-50 text-blue-700 border-blue-200',
        'seated' => 'bg-purple-50 text-purple-700 border-purple-200',
        'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
        'cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
    ];
@endphp

<div class="w-full max-w-full space-y-5 sm:space-y-6 overflow-hidden">

    <!-- Header -->
    <div class="flex flex-col 2xl:flex-row 2xl:items-start 2xl:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Reservations</h1>
            <p class="text-sm sm:text-base text-gray-500 mt-1">
                View customer reservations and reservation fee payment status by selected date.
            </p>
        </div>

        <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full 2xl:w-auto 2xl:max-w-3xl">
            <div class="bg-white border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm min-w-0">
                <p class="text-xs text-gray-500">Selected Date</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 truncate">
                    {{ $selectedDateCarbon->format('M d, Y') }}
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm min-w-0">
                <p class="text-xs text-gray-500">Total</p>
                <p class="text-2xl font-bold text-orange-500">{{ $totalReservations }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm min-w-0">
                <p class="text-xs text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-yellow-500">{{ $pendingCount }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm min-w-0">
                <p class="text-xs text-gray-500">Approved</p>
                <p class="text-2xl font-bold text-green-500">{{ $approvedCount }}</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="w-full max-w-full bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Filter/Header -->
        <div class="p-4 sm:p-5 border-b border-gray-100">
            <div class="flex flex-col 2xl:flex-row 2xl:items-center 2xl:justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-gray-900">Reservation Records</h2>
                    <p class="text-sm text-gray-500">
                        Showing reservations for {{ $selectedDateCarbon->format('F d, Y') }} only.
                    </p>
                </div>

                <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2 w-full 2xl:w-auto">
                    <input
                        type="date"
                        name="date"
                        value="{{ $selectedDate }}"
                        class="border border-gray-300 rounded-xl px-4 py-2.5 text-sm w-full focus:ring-2 focus:ring-orange-100 focus:border-orange-400 outline-none"
                    >

                    <button type="submit"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold">
                        View Date
                    </button>

                    <a href="{{ url()->current() }}?date={{ now()->toDateString() }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold text-center">
                        Today
                    </a>

                    <a href="{{ url()->current() }}?date={{ now()->addDay()->toDateString() }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold text-center">
                        Tomorrow
                    </a>
                </form>
            </div>
        </div>

        <!-- Large Desktop Table Only -->
        <div class="hidden 2xl:block w-full overflow-x-auto">
            <table class="w-full min-w-[1180px] text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-5 py-4 font-semibold">Customer</th>
                        <th class="px-5 py-4 font-semibold">Time</th>
                        <th class="px-5 py-4 font-semibold">Guests</th>
                        <th class="px-5 py-4 font-semibold">Reservation Fee</th>
                        <th class="px-5 py-4 font-semibold">Payment</th>
                        <th class="px-5 py-4 font-semibold">Reservation Status</th>
                        <th class="px-5 py-4 font-semibold">Service Info</th>
                        <th class="px-5 py-4 font-semibold text-right">Access</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($reservations as $reservation)
                        @php
                            $paymentStatus = strtolower($reservation->payment_status ?? 'unpaid');
                            $reservationStatus = strtolower($reservation->status ?? 'pending');
                        @endphp

                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4 max-w-[230px]">
                                <p class="font-semibold text-gray-900 break-words">
                                    {{ $reservation->customer_name }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1 break-all">
                                    {{ $reservation->customer_email }}
                                </p>

                                <p class="text-xs text-gray-500 break-words">
                                    {{ $reservation->customer_phone }}
                                </p>

                                @if ($reservation->notes)
                                    <p class="text-xs text-gray-500 mt-3 break-words">
                                        <span class="font-semibold text-gray-700">Notes:</span>
                                        {{ $reservation->notes }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-semibold text-gray-900">
                                    {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}
                                </p>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold">
                                    {{ $reservation->guest_count }} guest{{ $reservation->guest_count > 1 ? 's' : '' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-bold text-orange-500">
                                    ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    Non-refundable
                                </p>
                            </td>

                            <td class="px-5 py-4 max-w-[240px]">
                                <p class="font-semibold text-gray-900">
                                    {{ $reservation->payment_method ?? 'N/A' }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1 break-all">
                                    Ref: {{ $reservation->payment_reference ?? 'N/A' }}
                                </p>

                                <span class="inline-flex mt-2 px-3 py-1 rounded-full border text-xs font-semibold {{ $paymentClasses[$paymentStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst($paymentStatus) }}
                                </span>

                                @if ($reservation->payment_proof)
                                    <a
                                        href="{{ asset('storage/' . $reservation->payment_proof) }}"
                                        target="_blank"
                                        class="block mt-2 text-xs font-semibold text-orange-500 hover:text-orange-600"
                                    >
                                        View Payment Proof
                                    </a>
                                @else
                                    <p class="text-xs text-gray-400 mt-2">
                                        No payment proof
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClasses[$reservationStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst($reservationStatus) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 min-w-[150px]">
                                <div class="space-y-2">
                                    <p class="text-xs text-gray-500">
                                        <span class="font-semibold text-gray-700">Table:</span>
                                        {{ $reservation->table_number ? 'Table ' . $reservation->table_number : 'Not assigned' }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        <span class="font-semibold text-gray-700">Arrived:</span>
                                        {{ $reservation->arrived_at ? \Carbon\Carbon::parse($reservation->arrived_at)->format('h:i A') : 'Not yet' }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        <span class="font-semibold text-gray-700">Seated:</span>
                                        {{ $reservation->seated_at ? \Carbon\Carbon::parse($reservation->seated_at)->format('h:i A') : 'Not yet' }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">
                                    View Only
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl mb-4">
                                        📅
                                    </div>

                                    <h3 class="font-bold text-gray-900">No reservations for this date</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Try selecting another reservation date.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Responsive Cards for Laptop / Tablet / Mobile -->
        <div class="2xl:hidden p-3 sm:p-4 lg:p-5">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                @forelse ($reservations as $reservation)
                    @php
                        $paymentStatus = strtolower($reservation->payment_status ?? 'unpaid');
                        $reservationStatus = strtolower($reservation->status ?? 'pending');
                    @endphp

                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 sm:p-5 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-900 leading-snug break-words">
                                    {{ $reservation->customer_name }}
                                </h3>

                                <p class="text-xs text-gray-500 mt-1 break-all">
                                    {{ $reservation->customer_email }}
                                </p>

                                <p class="text-xs text-gray-500 break-words">
                                    {{ $reservation->customer_phone }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 shrink-0">
                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClasses[$reservationStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst($reservationStatus) }}
                                </span>

                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $paymentClasses[$paymentStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst($paymentStatus) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-xl bg-gray-50 border border-gray-100 px-3 py-2 space-y-1 min-w-0">
                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">Schedule:</span>
                                    {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}
                                </p>

                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">Time:</span>
                                    {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}
                                </p>

                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">Guests:</span>
                                    {{ $reservation->guest_count }} guest{{ $reservation->guest_count > 1 ? 's' : '' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-orange-50/50 border border-orange-100 px-3 py-2 space-y-1 min-w-0">
                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">Fee:</span>
                                    <span class="font-bold text-orange-500">
                                        ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                                    </span>
                                </p>

                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">Method:</span>
                                    {{ $reservation->payment_method ?? 'N/A' }}
                                </p>

                                <p class="text-xs text-gray-600 break-all">
                                    <span class="font-semibold">Ref:</span>
                                    {{ $reservation->payment_reference ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-gray-100 px-3 py-2 space-y-1 min-w-0">
                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">Table:</span>
                                    {{ $reservation->table_number ? 'Table ' . $reservation->table_number : 'Not assigned' }}
                                </p>

                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">Arrived:</span>
                                    {{ $reservation->arrived_at ? \Carbon\Carbon::parse($reservation->arrived_at)->format('h:i A') : 'Not yet' }}
                                </p>

                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">Seated:</span>
                                    {{ $reservation->seated_at ? \Carbon\Carbon::parse($reservation->seated_at)->format('h:i A') : 'Not yet' }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-gray-100 px-3 py-2 min-w-0">
                                <p class="text-xs text-gray-600 font-semibold">Access</p>
                                <span class="inline-flex mt-2 px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">
                                    View Only
                                </span>

                                @if ($reservation->payment_proof)
                                    <a
                                        href="{{ asset('storage/' . $reservation->payment_proof) }}"
                                        target="_blank"
                                        class="block mt-3 text-xs font-semibold text-orange-500 hover:text-orange-600"
                                    >
                                        View Payment Proof
                                    </a>
                                @else
                                    <p class="text-xs text-gray-400 mt-3">
                                        No payment proof uploaded.
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if ($reservation->notes)
                            <div class="mt-3 rounded-xl bg-orange-50 border border-orange-100 px-3 py-2">
                                <p class="text-xs text-gray-600 break-words">
                                    <span class="font-semibold">Notes:</span>
                                    {{ $reservation->notes }}
                                </p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full px-5 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl mb-4">
                                📅
                            </div>

                            <h3 class="font-bold text-gray-900">No reservations for this date</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Try selecting another reservation date.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @if ($reservations->hasPages())
            <div class="p-4 sm:p-5 border-t border-gray-100">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>

</div>

@endsection