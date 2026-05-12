@extends('layouts.customer')

@section('content')

<section class="max-w-7xl mx-auto px-6 py-10">

    <!-- Header -->
    <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-8 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-sm font-semibold text-orange-500 mb-2">Reservations</p>

                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                    Manage your table reservations
                </h1>

                <p class="text-gray-500 mt-2 max-w-2xl">
                    Create a reservation and track your payment verification and approval status in one place.
                </p>
            </div>

            <a
                href="{{ route('customer.reservations.create') }}"
                class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
            >
                New Reservation
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-5 py-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @forelse ($reservations as $reservation)
        @php
            $reservationStatusClasses = [
                'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                'approved' => 'bg-green-50 text-green-700 border-green-200',
                'declined' => 'bg-red-50 text-red-700 border-red-200',
                'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                'cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
            ];

            $paymentStatusClasses = [
                'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                'verified' => 'bg-green-50 text-green-700 border-green-200',
                'rejected' => 'bg-red-50 text-red-700 border-red-200',
                'unpaid' => 'bg-gray-50 text-gray-600 border-gray-200',
            ];

            $statusMessage = [
                'pending' => 'Waiting for admin review.',
                'approved' => 'Confirmed. Please arrive on time.',
                'declined' => 'Declined. Please contact the restaurant for assistance.',
                'completed' => 'Completed reservation.',
                'cancelled' => 'Cancelled reservation.',
            ][$reservation->status] ?? 'Status unavailable.';
        @endphp

        <div class="bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden mb-5">

            <!-- Top Row -->
            <div class="p-6 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">

                <!-- Reservation Main Info -->
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl shrink-0">
                        📅
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-900">
                                Reservation #{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}
                            </h2>

                            <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $reservationStatusClasses[$reservation->status] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                {{ ucfirst($reservation->status) }}
                            </span>

                            <span class="inline-flex px-3 py-1 rounded-full border text-xs font-semibold {{ $paymentStatusClasses[$reservation->payment_status] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                Payment {{ ucfirst($reservation->payment_status) }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-500 mt-2">
                            {{ $statusMessage }}
                        </p>

                        <p class="text-xs text-gray-400 mt-1">
                            Submitted {{ $reservation->created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                </div>

                <!-- Schedule Summary -->
                <div class="grid grid-cols-3 gap-3 min-w-full xl:min-w-[520px]">
                    <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
                        <p class="text-xs text-gray-500">Date</p>
                        <p class="font-bold text-gray-900 mt-1">
                            {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
                        <p class="text-xs text-gray-500">Time</p>
                        <p class="font-bold text-gray-900 mt-1">
                            {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
                        <p class="text-xs text-gray-500">Guests</p>
                        <p class="font-bold text-gray-900 mt-1">
                            {{ $reservation->guest_count }}
                        </p>
                    </div>
                </div>

            </div>

            <!-- Details Row -->
            <div class="border-t border-gray-100 bg-gray-50 px-6 py-5">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            Reservation Fee
                        </p>
                        <p class="font-bold text-orange-500 mt-1">
                            ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Non-refundable securing fee
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            Payment
                        </p>
                        <p class="text-sm text-gray-700 mt-1">
                            {{ $reservation->payment_method ?? 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Ref: {{ $reservation->payment_reference ?? 'N/A' }}
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row lg:justify-end gap-3">
                        @if ($reservation->payment_proof)
                            <a
                                href="{{ asset('storage/' . $reservation->payment_proof) }}"
                                target="_blank"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-orange-200 text-orange-500 hover:bg-orange-50 font-semibold text-sm transition"
                            >
                                View Proof
                            </a>
                        @endif

                        @if ($reservation->status === 'approved')
                            <span class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-green-50 text-green-700 font-semibold text-sm">
                                Show this upon arrival
                            </span>
                        @elseif ($reservation->status === 'pending')
                            <span class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-yellow-50 text-yellow-700 font-semibold text-sm">
                                Waiting for approval
                            </span>
                        @elseif ($reservation->status === 'declined')
                            <a
                                href="{{ route('customer.reservations.create') }}"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-orange-500 text-white font-semibold text-sm hover:bg-orange-600 transition"
                            >
                                Create New
                            </a>
                        @endif
                    </div>

                </div>

                @if ($reservation->notes)
                    <div class="mt-5 pt-5 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                            Notes
                        </p>
                        <p class="text-sm text-gray-600 leading-6">
                            {{ $reservation->notes }}
                        </p>
                    </div>
                @endif
            </div>

        </div>

    @empty
        <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-3xl mx-auto mb-5">
                📅
            </div>

            <h2 class="text-2xl font-bold text-gray-900">No reservations yet</h2>

            <p class="text-gray-500 mt-2 max-w-md mx-auto">
                You can create a reservation and track its approval status here.
            </p>

            <a
                href="{{ route('customer.reservations.create') }}"
                class="inline-flex items-center justify-center mt-6 px-5 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
            >
                Create Reservation
            </a>
        </div>
    @endforelse

</section>

@endsection