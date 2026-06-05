@extends('layouts.customer')

@section('content')

<style>
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

    .reservations-page {
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

    .reservations-page::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 12% 10%, rgba(249, 115, 22, 0.22), transparent 22rem),
            radial-gradient(circle at 88% 18%, rgba(255, 255, 255, 0.12), transparent 24rem),
            linear-gradient(180deg, rgba(0,0,0,0.08), rgba(0,0,0,0.24));
        pointer-events: none;
    }

    .reservations-inner {
        position: relative;
        z-index: 2;
    }

    .glass-dark {
        background: rgba(10, 10, 10, 0.88);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.32);
        backdrop-filter: blur(14px);
    }

    .reservation-card {
        background: rgba(10, 10, 10, 0.88);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.28);
        backdrop-filter: blur(14px);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0.35rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 900;
        line-height: 1;
    }

    .info-box {
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.04);
    }
</style>

<div class="reservations-page">
    <section class="reservations-inner max-w-7xl mx-auto px-4 sm:px-6 py-8 pb-12">

        <!-- PAGE HEADER -->
        <div class="glass-dark rounded-[2rem] p-6 sm:p-8 mb-6 text-white">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-orange-500/10 text-orange-500 border border-orange-500/30 flex items-center justify-center font-black shrink-0">
                        D+
                    </div>

                    <div>
                        <p class="text-sm font-black text-orange-500 uppercase tracking-widest mb-2">
                            Reservations
                        </p>

                        <h1 class="text-3xl md:text-4xl font-black tracking-tight">
                            Manage your table reservations
                        </h1>

                        <p class="text-gray-300 mt-3 max-w-3xl leading-7">
                            Create a reservation and track your reservation status in one place.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('customer.reservations.create') }}"
                    class="inline-flex items-center justify-center px-6 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black transition shadow-lg shadow-orange-500/30"
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

        @if (session('error'))
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-5 py-4 text-sm font-semibold shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @forelse ($reservations as $reservation)
            @php
                $reservationStatus = strtolower($reservation->status ?? 'pending');

                $reservationStatusLabel = match ($reservationStatus) {
                    'approved' => 'Approved',
                    'declined' => 'Declined',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    default => 'Pending',
                };

                $reservationMessage = match ($reservationStatus) {
                    'approved' => 'Your reservation has been approved by the restaurant.',
                    'declined' => 'Your reservation was declined. Please contact the restaurant for assistance.',
                    'completed' => 'This reservation has been completed.',
                    'cancelled' => 'This reservation was cancelled.',
                    default => 'Your reservation request has been submitted. Please wait for the admin to review your reservation.',
                };

                $reservationDate = \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y');
                $reservationTime = \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A');
                $submittedAt = $reservation->created_at
                    ? \Carbon\Carbon::parse($reservation->created_at)->format('M d, Y h:i A')
                    : 'N/A';
            @endphp

            <div class="reservation-card rounded-[2rem] overflow-hidden text-white mb-5">
                <div class="p-6">
                    <div class="grid grid-cols-1 xl:grid-cols-[1fr_520px] gap-6 items-start">

                        <!-- LEFT DETAILS -->
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-orange-500/10 text-orange-500 border border-orange-500/30 flex items-center justify-center font-black shrink-0">
                                RSV
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-xl font-black">
                                        Reservation #{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}
                                    </h2>

                                    @if ($reservationStatus === 'approved')
                                        <span class="status-badge bg-green-500/10 text-green-400 border border-green-500/30">
                                            {{ $reservationStatusLabel }}
                                        </span>
                                    @elseif ($reservationStatus === 'declined' || $reservationStatus === 'cancelled')
                                        <span class="status-badge bg-red-500/10 text-red-400 border border-red-500/30">
                                            {{ $reservationStatusLabel }}
                                        </span>
                                    @elseif ($reservationStatus === 'completed')
                                        <span class="status-badge bg-blue-500/10 text-blue-400 border border-blue-500/30">
                                            {{ $reservationStatusLabel }}
                                        </span>
                                    @else
                                        <span class="status-badge bg-yellow-500/10 text-yellow-400 border border-yellow-500/30">
                                            {{ $reservationStatusLabel }}
                                        </span>
                                    @endif
                                </div>

                                <p class="text-sm text-gray-300 mt-3 leading-6">
                                    {{ $reservationMessage }}
                                </p>

                                <p class="text-xs text-gray-400 mt-2">
                                    Submitted {{ $submittedAt }}
                                </p>
                            </div>
                        </div>

                        <!-- RIGHT DATE TIME GUESTS -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="info-box rounded-2xl p-4">
                                <p class="text-xs font-bold text-gray-400 mb-2">Date</p>
                                <p class="font-black text-white">{{ $reservationDate }}</p>
                            </div>

                            <div class="info-box rounded-2xl p-4">
                                <p class="text-xs font-bold text-gray-400 mb-2">Time</p>
                                <p class="font-black text-white">{{ $reservationTime }}</p>
                            </div>

                            <div class="info-box rounded-2xl p-4">
                                <p class="text-xs font-bold text-gray-400 mb-2">Guests</p>
                                <p class="font-black text-white">{{ $reservation->guest_count }}</p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="border-t border-white/10 p-6">
                    <div class="grid grid-cols-1 gap-5 items-center">

                        <!-- FEE ONLY -->
                        <div>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest">
                                Reservation Fee
                            </p>

                            <p class="text-xl font-black text-orange-500 mt-2">
                                ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                            </p>

                            <p class="text-xs text-gray-400 mt-2">
                                Non-refundable securing fee
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="glass-dark rounded-[2rem] p-10 text-center text-white">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-orange-500/10 text-orange-500 border border-orange-500/30 flex items-center justify-center font-black mb-5">
                    RSV
                </div>

                <h2 class="text-2xl font-black">
                    No reservations yet
                </h2>

                <p class="text-gray-300 mt-3 max-w-xl mx-auto leading-7">
                    You do not have any table reservations yet. Create a new reservation to secure your table.
                </p>

                <a
                    href="{{ route('customer.reservations.create') }}"
                    class="mt-6 inline-flex items-center justify-center px-6 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black transition shadow-lg shadow-orange-500/30"
                >
                    New Reservation
                </a>
            </div>
        @endforelse

    </section>
</div>

@endsection