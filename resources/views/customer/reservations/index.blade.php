@extends('layouts.customer')

@section('content')

<style>
    [x-cloak] {
        display: none !important;
    }

    html,
    body {
        background-image:
            linear-gradient(
                135deg,
                rgba(15, 23, 42, 0.78),
                rgba(67, 31, 12, 0.68)
            ),
            url("https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1800&q=80");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-color: #0f172a;
    }

    main {
        background: transparent !important;
    }

    footer {
        margin-top: 0 !important;
    }

    .reservation-page {
        min-height: calc(100vh - 80px);
        position: relative;
        overflow-x: hidden;
        background-image:
            linear-gradient(
                135deg,
                rgba(15, 23, 42, 0.78),
                rgba(67, 31, 12, 0.68)
            ),
            url("https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1800&q=80");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .reservation-page::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 12% 10%, rgba(249, 115, 22, 0.22), transparent 22rem),
            radial-gradient(circle at 88% 18%, rgba(255, 255, 255, 0.12), transparent 24rem),
            linear-gradient(180deg, rgba(0,0,0,0.08), rgba(0,0,0,0.24));
        pointer-events: none;
    }

    .reservation-inner {
        position: relative;
        z-index: 2;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.93);
        border: 1px solid rgba(255, 255, 255, 0.35);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
        backdrop-filter: blur(14px);
    }

    .dark-card {
        background:
            radial-gradient(circle at 85% 12%, rgba(249, 115, 22, 0.22), transparent 18rem),
            linear-gradient(135deg, #111827 0%, #020617 100%);
    }
</style>

<div class="reservation-page">
    <section class="reservation-inner max-w-7xl mx-auto px-4 sm:px-6 py-8 pb-12">

        <!-- HEADER -->
        <div class="glass-card rounded-[2rem] p-6 sm:p-8 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black shrink-0">
                        D+
                    </div>

                    <div>
                        <p class="text-sm font-black text-orange-600 uppercase tracking-widest mb-2">
                            Reservations
                        </p>

                        <h1 class="text-3xl md:text-4xl font-black text-gray-950 tracking-tight">
                            Manage your table reservations
                        </h1>

                        <p class="text-gray-600 mt-2 max-w-2xl leading-7">
                            Create a reservation and track your payment verification, approval status, and visit details in one place.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('customer.reservations.create') }}"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black transition shadow-lg shadow-orange-500/25"
                >
                    New Reservation
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-5 py-4 text-sm font-semibold shadow-sm">
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

            <div class="glass-card rounded-[2rem] overflow-hidden mb-5">

                <!-- MAIN ROW -->
                <div class="p-6 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">

                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black shrink-0">
                            RSV
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl font-black text-gray-950">
                                    Reservation #{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}
                                </h2>

                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $reservationStatusClasses[$reservation->status] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst($reservation->status) }}
                                </span>

                                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-black {{ $paymentStatusClasses[$reservation->payment_status] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    Payment {{ ucfirst($reservation->payment_status) }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mt-2">
                                {{ $statusMessage }}
                            </p>

                            <p class="text-xs text-gray-400 mt-1">
                                Submitted {{ $reservation->created_at->format('M d, Y h:i A') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 min-w-full xl:min-w-[520px]">
                        <div class="rounded-2xl bg-white border border-gray-100 p-4 shadow-sm">
                            <p class="text-xs font-semibold text-gray-500">Date</p>
                            <p class="font-black text-gray-950 mt-1">
                                {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white border border-gray-100 p-4 shadow-sm">
                            <p class="text-xs font-semibold text-gray-500">Time</p>
                            <p class="font-black text-gray-950 mt-1">
                                {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white border border-gray-100 p-4 shadow-sm">
                            <p class="text-xs font-semibold text-gray-500">Guests</p>
                            <p class="font-black text-gray-950 mt-1">
                                {{ $reservation->guest_count }}
                            </p>
                        </div>
                    </div>

                </div>

                <!-- DETAILS ROW -->
                <div class="border-t border-gray-100 bg-white/75 px-6 py-5">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                        <div>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-wider">
                                Reservation Fee
                            </p>
                            <p class="font-black text-orange-500 mt-1 text-lg">
                                ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Non-refundable securing fee
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-wider">
                                Payment
                            </p>
                            <p class="text-sm font-semibold text-gray-700 mt-1">
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
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-orange-200 bg-orange-50 text-orange-600 hover:bg-orange-100 font-black text-sm transition"
                                >
                                    View Proof
                                </a>
                            @endif

                            @if ($reservation->status === 'approved')
                                <span class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-green-50 text-green-700 font-black text-sm border border-green-100">
                                    Show this upon arrival
                                </span>
                            @elseif ($reservation->status === 'pending')
                                <span class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-yellow-50 text-yellow-700 font-black text-sm border border-yellow-100">
                                    Waiting for approval
                                </span>
                            @elseif ($reservation->status === 'declined')
                                <a
                                    href="{{ route('customer.reservations.create') }}"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-orange-500 text-white font-black text-sm hover:bg-orange-600 transition"
                                >
                                    Create New
                                </a>
                            @endif
                        </div>

                    </div>

                    @if ($reservation->notes)
                        <div class="mt-5 pt-5 border-t border-gray-200">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-1">
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
            <div class="glass-card rounded-[2rem] p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black mx-auto mb-5">
                    RSV
                </div>

                <h2 class="text-2xl font-black text-gray-950">No reservations yet</h2>

                <p class="text-gray-600 mt-2 max-w-md mx-auto leading-7">
                    You can create a reservation and track its approval status here.
                </p>

                <a
                    href="{{ route('customer.reservations.create') }}"
                    class="inline-flex items-center justify-center mt-6 px-5 py-3 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black transition shadow-lg shadow-orange-500/25"
                >
                    Create Reservation
                </a>
            </div>
        @endforelse

    </section>
</div>

@endsection 