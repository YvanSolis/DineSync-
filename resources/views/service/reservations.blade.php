@extends('layouts.service')

@section('page-title', 'Reservations')
@section('page-subtitle', 'Manage customer reservations, payments, arrivals, and seating')

@section('content')

@php
    $reservationItems = $reservations instanceof \Illuminate\Pagination\AbstractPaginator
        ? $reservations->getCollection()
        : collect($reservations);

    $selectedDate = request('date')
        ? \Carbon\Carbon::parse(request('date'), 'Asia/Manila')->format('M d, Y')
        : now('Asia/Manila')->format('M d, Y');

    $dateValue = request('date', now('Asia/Manila')->toDateString());

    $totalReservations = $reservations instanceof \Illuminate\Pagination\AbstractPaginator
        ? $reservations->total()
        : $reservationItems->count();

    $pendingCount = $reservationItems->where('status', 'pending')->count();
    $approvedCount = $reservationItems->where('status', 'approved')->count();
@endphp

<style>
    .service-reservations-shell {
        min-height: calc(100vh - 80px);
    }

    .reservation-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(145px, 1fr));
        gap: 1rem;
    }

    .reservation-stat-card {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 1.15rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        padding: 1.1rem 1.25rem;
    }

    .reservation-panel {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 1.4rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    .date-filter-grid {
        display: grid;
        grid-template-columns: 180px 170px 170px 170px;
        gap: 0.65rem;
        align-items: center;
    }

    .service-table {
        width: 100%;
        min-width: 1220px;
        font-size: 0.875rem;
    }

    .service-table thead {
        background: #f8fafc;
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
    }

    .service-table th {
        padding: 1rem 1.25rem;
        color: #64748b;
        font-weight: 800;
        text-align: left;
        white-space: nowrap;
    }

    .service-table td {
        padding: 1.25rem;
        vertical-align: top;
    }

    .service-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .service-table tbody tr:hover {
        background: rgba(248, 250, 252, 0.9);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        border-radius: 999px;
        border: 1px solid transparent;
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.65rem;
        padding: 0.55rem 0.85rem;
        font-size: 0.75rem;
        font-weight: 800;
        transition: 0.2s ease;
        white-space: nowrap;
    }

    .action-stack {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        flex-wrap: wrap;
        min-width: 210px;
    }

    .reservation-card-grid {
        display: none;
    }

    .reservation-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 1.25rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .reservation-card-section {
        padding: 1rem;
        border-top: 1px solid #f3f4f6;
    }

    .reservation-card-label {
        font-size: 0.68rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #9ca3af;
        margin-bottom: 0.35rem;
    }

    @media (max-width: 1280px) {
        .reservation-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .date-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1180px) {
        .reservation-table-wrap {
            display: none;
        }

        .reservation-card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            padding: 1rem;
        }
    }

    @media (max-width: 768px) {
        .reservation-stats-grid {
            grid-template-columns: 1fr;
        }

        .date-filter-grid {
            grid-template-columns: 1fr;
        }

        .reservation-card-grid {
            grid-template-columns: 1fr;
            padding: 0.85rem;
        }

        .reservation-stat-card {
            padding: 0.95rem 1rem;
        }

        .reservation-panel {
            border-radius: 1.15rem;
        }

        .action-stack {
            justify-content: stretch;
            min-width: 0;
            width: 100%;
        }

        .action-stack form,
        .action-stack .action-button {
            width: 100%;
        }

        .action-button {
            width: 100%;
        }
    }
</style>

<div class="service-reservations-shell space-y-6">

    {{-- STATS --}}
    <div class="reservation-stats-grid">
        <div class="reservation-stat-card">
            <p class="text-xs font-semibold text-gray-500">
                Selected Date
            </p>
            <p class="text-lg font-black text-gray-950 mt-1">
                {{ $selectedDate }}
            </p>
        </div>

        <div class="reservation-stat-card">
            <p class="text-xs font-semibold text-gray-500">
                Total
            </p>
            <p class="text-2xl font-black text-orange-500 mt-1">
                {{ $totalReservations }}
            </p>
        </div>

        <div class="reservation-stat-card">
            <p class="text-xs font-semibold text-gray-500">
                Pending
            </p>
            <p class="text-2xl font-black text-yellow-500 mt-1">
                {{ $pendingCount }}
            </p>
        </div>

        <div class="reservation-stat-card">
            <p class="text-xs font-semibold text-gray-500">
                Approved
            </p>
            <p class="text-2xl font-black text-green-500 mt-1">
                {{ $approvedCount }}
            </p>
        </div>
    </div>

    {{-- FLASH --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl text-sm font-bold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm font-bold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- RESERVATION PANEL --}}
    <div class="reservation-panel">
        <div class="p-5 lg:p-6 border-b border-gray-100">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-gray-950">
                        Reservation Records
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Service staff controls payment verification, reservation approval, arrivals, and seating.
                    </p>
                </div>

                <form method="GET" action="{{ url()->current() }}" class="date-filter-grid">
                    <input
                        type="date"
                        name="date"
                        value="{{ $dateValue }}"
                        class="w-full rounded-xl border-gray-200 text-sm font-semibold text-gray-700 focus:border-orange-400 focus:ring-orange-200"
                    >

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-black px-5 py-3 transition shadow-sm"
                    >
                        View Date
                    </button>

                    <a
                        href="{{ url()->current() }}?date={{ now('Asia/Manila')->toDateString() }}"
                        class="w-full rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-black px-5 py-3 text-center transition"
                    >
                        Today
                    </a>

                    <a
                        href="{{ url()->current() }}?date={{ now('Asia/Manila')->addDay()->toDateString() }}"
                        class="w-full rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-black px-5 py-3 text-center transition"
                    >
                        Tomorrow
                    </a>
                </form>
            </div>
        </div>

        {{-- RESPONSIVE CARD VIEW --}}
        <div class="reservation-card-grid">
            @forelse ($reservations as $reservation)
                @php
                    $paymentStatus = strtolower($reservation->payment_status ?? 'pending');
                    $reservationStatus = strtolower($reservation->status ?? 'pending');

                    $isPaymentVerified = in_array($paymentStatus, ['paid', 'verified', 'settled', 'completed'], true);

                    $paymentLabel = match ($paymentStatus) {
                        'paid', 'verified', 'settled', 'completed' => 'Verified',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                        'failed', 'cancelled', 'canceled' => 'Failed',
                        default => 'Pending',
                    };

                    $paymentClass = match ($paymentStatus) {
                        'paid', 'verified', 'settled', 'completed' => 'bg-green-50 text-green-700 border-green-200',
                        'rejected', 'failed', 'cancelled', 'canceled' => 'bg-red-50 text-red-700 border-red-200',
                        'expired' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                    };

                    $statusLabel = match ($reservationStatus) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'declined' => 'Declined',
                        'arrived' => 'Arrived',
                        'seated' => 'Seated',
                        'completed' => 'Completed',
                        'cancelled', 'canceled' => 'Cancelled',
                        default => ucfirst($reservationStatus),
                    };

                    $statusClass = match ($reservationStatus) {
                        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        'approved' => 'bg-green-50 text-green-700 border-green-200',
                        'declined' => 'bg-red-50 text-red-700 border-red-200',
                        'arrived' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'seated' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'cancelled', 'canceled' => 'bg-gray-50 text-gray-600 border-gray-200',
                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                    };

                    $reservationDate = $reservation->reservation_date
                        ? \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y')
                        : 'No date';

                    $reservationTime = $reservation->reservation_time
                        ? \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A')
                        : 'No time';
                @endphp

                <div class="reservation-card">
                    <div class="p-4 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-black text-gray-950">
                                #RES-{{ str_pad($reservation->id, 4, '0', STR_PAD_LEFT) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $reservation->created_at ? $reservation->created_at->diffForHumans() : 'No timestamp' }}
                            </p>
                        </div>

                        <span class="status-pill {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="reservation-card-section">
                        <p class="reservation-card-label">
                            Customer
                        </p>

                        <p class="font-black text-gray-950">
                            {{ $reservation->customer_name }}
                        </p>

                        <p class="text-xs text-gray-500 mt-1 break-words">
                            {{ $reservation->customer_email }}
                        </p>

                        <p class="text-xs text-gray-500">
                            {{ $reservation->customer_phone }}
                        </p>

                        @if ($reservation->notes)
                            <p class="text-xs text-gray-600 mt-3 leading-5">
                                <span class="font-black text-gray-800">Notes:</span>
                                {{ $reservation->notes }}
                            </p>
                        @endif
                    </div>

                    <div class="reservation-card-section grid grid-cols-2 gap-3">
                        <div>
                            <p class="reservation-card-label">
                                Date & Time
                            </p>

                            <p class="font-black text-gray-950">
                                {{ $reservationDate }}
                            </p>

                            <p class="text-xs text-gray-500 mt-1">
                                {{ $reservationTime }}
                            </p>
                        </div>

                        <div>
                            <p class="reservation-card-label">
                                Guests
                            </p>

                            <span class="status-pill bg-gray-100 text-gray-700 border-gray-100">
                                {{ $reservation->guest_count }} guest{{ $reservation->guest_count > 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>

                    <div class="reservation-card-section grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <p class="reservation-card-label">
                                Reservation Fee
                            </p>

                            <p class="font-black text-orange-500">
                                ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                            </p>

                            <p class="text-xs text-gray-500 mt-1">
                                Non-refundable
                            </p>
                        </div>

                        <div>
                            <p class="reservation-card-label">
                                Payment
                            </p>

                            <p class="font-black text-gray-950">
                                {{ $reservation->payment_method ?? 'Xendit' }}
                            </p>

                            <p class="text-xs text-gray-500 mt-1 break-words">
                                Ref: {{ $reservation->payment_reference ?? 'N/A' }}
                            </p>

                            <span class="status-pill mt-2 {{ $paymentClass }}">
                                {{ $paymentLabel }}
                            </span>

                            @if ($reservation->payment_proof)
                                <a
                                    href="{{ asset('storage/' . $reservation->payment_proof) }}"
                                    target="_blank"
                                    class="block mt-2 text-xs font-black text-orange-500 hover:text-orange-600"
                                >
                                    View Proof
                                </a>
                            @else
                                <p class="text-xs text-gray-400 mt-2">
                                    No proof
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="reservation-card-section">
                        <p class="reservation-card-label">
                            Service Info
                        </p>

                        <p class="text-xs text-gray-600 leading-6">
                            <span class="font-black text-gray-900">Table:</span>
                            @if ($reservation->table_number)
                                Table {{ $reservation->table_number }}
                            @else
                                <span class="text-gray-400">Not assigned</span>
                            @endif
                        </p>

                        <p class="text-xs text-gray-600 leading-6">
                            <span class="font-black text-gray-900">Arrived:</span>
                            {{ $reservation->arrived_at ? \Carbon\Carbon::parse($reservation->arrived_at)->format('h:i A') : 'Not yet' }}
                        </p>

                        <p class="text-xs text-gray-600 leading-6">
                            <span class="font-black text-gray-900">Seated:</span>
                            {{ $reservation->seated_at ? \Carbon\Carbon::parse($reservation->seated_at)->format('h:i A') : 'Not yet' }}
                        </p>
                    </div>

                    <div class="reservation-card-section">
                        <p class="reservation-card-label">
                            Actions
                        </p>

                        <div class="action-stack">
                            @if ($paymentStatus === 'pending')
                                <form method="POST" action="{{ route('service.reservations.verify-payment', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-button bg-green-500 hover:bg-green-600 text-white">
                                        Verify
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('service.reservations.reject-payment', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-button bg-red-500 hover:bg-red-600 text-white">
                                        Reject
                                    </button>
                                </form>
                            @endif

                            @if ($paymentStatus === 'rejected')
                                <span class="action-button bg-red-50 text-red-600">
                                    Payment Rejected
                                </span>
                            @endif

                            @if ($isPaymentVerified && $reservationStatus === 'pending')
                                <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="action-button bg-orange-500 hover:bg-orange-600 text-white">
                                        Accept
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="declined">
                                    <button type="submit" class="action-button bg-gray-100 hover:bg-gray-200 text-gray-600">
                                        Decline
                                    </button>
                                </form>
                            @endif

                            @if (!$isPaymentVerified && $paymentStatus !== 'rejected' && $reservationStatus === 'pending')
                                <span class="action-button bg-yellow-50 text-yellow-700">
                                    Verify Payment First
                                </span>
                            @endif

                            @if ($reservationStatus === 'approved')
                                <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="arrived">
                                    <button type="submit" class="action-button bg-blue-500 hover:bg-blue-600 text-white">
                                        Mark Arrived
                                    </button>
                                </form>
                            @endif

                            @if ($reservationStatus === 'arrived')
                                <form
                                    method="POST"
                                    action="{{ route('service.reservations.update-status', $reservation) }}"
                                    class="flex items-center justify-end gap-2 flex-wrap w-full"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="seated">

                                    <input
                                        type="text"
                                        name="table_number"
                                        placeholder="Table #"
                                        class="w-full sm:w-24 rounded-lg border-gray-200 text-xs focus:border-orange-300 focus:ring-orange-200"
                                    >

                                    <button type="submit" class="action-button bg-purple-500 hover:bg-purple-600 text-white">
                                        Seat
                                    </button>
                                </form>
                            @endif

                            @if ($reservationStatus === 'seated')
                                <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="action-button bg-gray-900 hover:bg-black text-white">
                                        Complete
                                    </button>
                                </form>
                            @endif

                            @if (!in_array($reservationStatus, ['declined', 'cancelled', 'canceled', 'completed'], true))
                                <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="action-button bg-gray-100 hover:bg-gray-200 text-gray-600">
                                        Cancel
                                    </button>
                                </form>
                            @endif

                            @if (in_array($reservationStatus, ['declined', 'cancelled', 'canceled', 'completed'], true))
                                <span class="action-button bg-gray-100 text-gray-500">
                                    No Action Needed
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 px-5 py-14 text-center">
                    <h3 class="text-lg font-black text-gray-950">
                        No reservations found
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Customer reservation requests will appear here.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- DESKTOP TABLE VIEW --}}
        <div class="reservation-table-wrap overflow-x-auto">
            <table class="service-table">
                <thead>
                    <tr>
                        <th>Reservation</th>
                        <th>Customer</th>
                        <th>Date & Time</th>
                        <th>Guests</th>
                        <th>Reservation Fee</th>
                        <th>Payment</th>
                        <th>Reservation Status</th>
                        <th>Service Info</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($reservations as $reservation)
                        @php
                            $paymentStatus = strtolower($reservation->payment_status ?? 'pending');
                            $reservationStatus = strtolower($reservation->status ?? 'pending');

                            $isPaymentVerified = in_array($paymentStatus, ['paid', 'verified', 'settled', 'completed'], true);

                            $paymentLabel = match ($paymentStatus) {
                                'paid', 'verified', 'settled', 'completed' => 'Verified',
                                'rejected' => 'Rejected',
                                'expired' => 'Expired',
                                'failed', 'cancelled', 'canceled' => 'Failed',
                                default => 'Pending',
                            };

                            $paymentClass = match ($paymentStatus) {
                                'paid', 'verified', 'settled', 'completed' => 'bg-green-50 text-green-700 border-green-200',
                                'rejected', 'failed', 'cancelled', 'canceled' => 'bg-red-50 text-red-700 border-red-200',
                                'expired' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            };

                            $statusLabel = match ($reservationStatus) {
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'declined' => 'Declined',
                                'arrived' => 'Arrived',
                                'seated' => 'Seated',
                                'completed' => 'Completed',
                                'cancelled', 'canceled' => 'Cancelled',
                                default => ucfirst($reservationStatus),
                            };

                            $statusClass = match ($reservationStatus) {
                                'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                'approved' => 'bg-green-50 text-green-700 border-green-200',
                                'declined' => 'bg-red-50 text-red-700 border-red-200',
                                'arrived' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'seated' => 'bg-purple-50 text-purple-700 border-purple-200',
                                'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'cancelled', 'canceled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                default => 'bg-gray-50 text-gray-600 border-gray-200',
                            };

                            $reservationDate = $reservation->reservation_date
                                ? \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y')
                                : 'No date';

                            $reservationTime = $reservation->reservation_time
                                ? \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A')
                                : 'No time';
                        @endphp

                        <tr>
                            <td>
                                <p class="font-black text-gray-950">
                                    #RES-{{ str_pad($reservation->id, 4, '0', STR_PAD_LEFT) }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $reservation->created_at ? $reservation->created_at->diffForHumans() : 'No timestamp' }}
                                </p>
                            </td>

                            <td>
                                <p class="font-black text-gray-950">
                                    {{ $reservation->customer_name }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1 break-words">
                                    {{ $reservation->customer_email }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ $reservation->customer_phone }}
                                </p>

                                @if ($reservation->notes)
                                    <p class="text-xs text-gray-600 mt-3 leading-5">
                                        <span class="font-black text-gray-800">Notes:</span>
                                        {{ $reservation->notes }}
                                    </p>
                                @endif
                            </td>

                            <td>
                                <p class="font-black text-gray-950">
                                    {{ $reservationDate }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $reservationTime }}
                                </p>
                            </td>

                            <td>
                                <span class="status-pill bg-gray-100 text-gray-700 border-gray-100">
                                    {{ $reservation->guest_count }} guest{{ $reservation->guest_count > 1 ? 's' : '' }}
                                </span>
                            </td>

                            <td>
                                <p class="font-black text-orange-500">
                                    ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    Non-refundable
                                </p>
                            </td>

                            <td>
                                <p class="font-black text-gray-950">
                                    {{ $reservation->payment_method ?? 'Xendit' }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1 break-words">
                                    Ref: {{ $reservation->payment_reference ?? 'N/A' }}
                                </p>

                                <span class="status-pill mt-2 {{ $paymentClass }}">
                                    {{ $paymentLabel }}
                                </span>

                                @if ($reservation->payment_proof)
                                    <a
                                        href="{{ asset('storage/' . $reservation->payment_proof) }}"
                                        target="_blank"
                                        class="block mt-2 text-xs font-black text-orange-500 hover:text-orange-600"
                                    >
                                        View Proof
                                    </a>
                                @else
                                    <p class="text-xs text-gray-400 mt-2">
                                        No proof
                                    </p>
                                @endif
                            </td>

                            <td>
                                <span class="status-pill {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td>
                                <p class="text-xs text-gray-600 leading-6">
                                    <span class="font-black text-gray-900">Table:</span>
                                    @if ($reservation->table_number)
                                        Table {{ $reservation->table_number }}
                                    @else
                                        <span class="text-gray-400">Not assigned</span>
                                    @endif
                                </p>

                                <p class="text-xs text-gray-600 leading-6">
                                    <span class="font-black text-gray-900">Arrived:</span>
                                    {{ $reservation->arrived_at ? \Carbon\Carbon::parse($reservation->arrived_at)->format('h:i A') : 'Not yet' }}
                                </p>

                                <p class="text-xs text-gray-600 leading-6">
                                    <span class="font-black text-gray-900">Seated:</span>
                                    {{ $reservation->seated_at ? \Carbon\Carbon::parse($reservation->seated_at)->format('h:i A') : 'Not yet' }}
                                </p>
                            </td>

                            <td class="text-right">
                                <div class="action-stack">
                                    @if ($paymentStatus === 'pending')
                                        <form method="POST" action="{{ route('service.reservations.verify-payment', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-button bg-green-500 hover:bg-green-600 text-white">
                                                Verify
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('service.reservations.reject-payment', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-button bg-red-500 hover:bg-red-600 text-white">
                                                Reject
                                            </button>
                                        </form>
                                    @endif

                                    @if ($paymentStatus === 'rejected')
                                        <span class="action-button bg-red-50 text-red-600">
                                            Payment Rejected
                                        </span>
                                    @endif

                                    @if ($isPaymentVerified && $reservationStatus === 'pending')
                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="action-button bg-orange-500 hover:bg-orange-600 text-white">
                                                Accept
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="declined">
                                            <button type="submit" class="action-button bg-gray-100 hover:bg-gray-200 text-gray-600">
                                                Decline
                                            </button>
                                        </form>
                                    @endif

                                    @if (!$isPaymentVerified && $paymentStatus !== 'rejected' && $reservationStatus === 'pending')
                                        <span class="action-button bg-yellow-50 text-yellow-700">
                                            Verify Payment First
                                        </span>
                                    @endif

                                    @if ($reservationStatus === 'approved')
                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="arrived">
                                            <button type="submit" class="action-button bg-blue-500 hover:bg-blue-600 text-white">
                                                Mark Arrived
                                            </button>
                                        </form>
                                    @endif

                                    @if ($reservationStatus === 'arrived')
                                        <form
                                            method="POST"
                                            action="{{ route('service.reservations.update-status', $reservation) }}"
                                            class="flex items-center justify-end gap-2 flex-wrap"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="seated">

                                            <input
                                                type="text"
                                                name="table_number"
                                                placeholder="Table #"
                                                class="w-20 rounded-lg border-gray-200 text-xs focus:border-orange-300 focus:ring-orange-200"
                                            >

                                            <button type="submit" class="action-button bg-purple-500 hover:bg-purple-600 text-white">
                                                Seat
                                            </button>
                                        </form>
                                    @endif

                                    @if ($reservationStatus === 'seated')
                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="action-button bg-gray-900 hover:bg-black text-white">
                                                Complete
                                            </button>
                                        </form>
                                    @endif

                                    @if (!in_array($reservationStatus, ['declined', 'cancelled', 'canceled', 'completed'], true))
                                        <form method="POST" action="{{ route('service.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="action-button bg-gray-100 hover:bg-gray-200 text-gray-600">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif

                                    @if (in_array($reservationStatus, ['declined', 'cancelled', 'canceled', 'completed'], true))
                                        <span class="action-button bg-gray-100 text-gray-500">
                                            No Action Needed
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-14 text-center">
                                <h3 class="text-lg font-black text-gray-950">
                                    No reservations found
                                </h3>

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
const reservationScrollKey =
    'service_reservations_scroll_' + window.location.pathname;

function saveReservationScrollPosition() {
    sessionStorage.setItem(
        reservationScrollKey,
        String(window.scrollY || window.pageYOffset || 0)
    );
}

function restoreReservationScrollPosition() {
    const savedScroll =
        sessionStorage.getItem(reservationScrollKey);

    if (savedScroll !== null) {
        window.scrollTo({
            top: Number(savedScroll),
            left: 0,
            behavior: 'instant'
        });

        sessionStorage.removeItem(reservationScrollKey);
    }
}

window.addEventListener('load', () => {
    setTimeout(() => {
        restoreReservationScrollPosition();
    }, 80);
});

document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
        saveReservationScrollPosition();
    });
});

document.querySelectorAll('a[href]').forEach((link) => {
    link.addEventListener('click', () => {
        const href = link.getAttribute('href') || '';

        if (
            href.includes('/service/reservations') ||
            href.includes('date=')
        ) {
            saveReservationScrollPosition();
        }
    });
});

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

    saveReservationScrollPosition();
    window.location.reload();
}, 30000);
</script>

@endsection