@extends('layouts.admin')

@section('content')

@php
    $selectedDateCarbon = \Carbon\Carbon::parse($selectedDate);
    $today = now('Asia/Manila')->toDateString();

    $paymentClasses = [
        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'verified' => 'bg-green-50 text-green-700 border-green-200',
        'rejected' => 'bg-red-50 text-red-700 border-red-200',
        'unpaid' => 'bg-gray-50 text-gray-600 border-gray-200',
        'paid' => 'bg-green-50 text-green-700 border-green-200',
        'settled' => 'bg-green-50 text-green-700 border-green-200',
        'completed' => 'bg-green-50 text-green-700 border-green-200',
        'failed' => 'bg-red-50 text-red-700 border-red-200',
        'expired' => 'bg-gray-50 text-gray-600 border-gray-200',
        'cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
        'canceled' => 'bg-gray-50 text-gray-600 border-gray-200',
    ];

    $statusClasses = [
        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'approved' => 'bg-green-50 text-green-700 border-green-200',
        'declined' => 'bg-red-50 text-red-700 border-red-200',
        'arrived' => 'bg-blue-50 text-blue-700 border-blue-200',
        'seated' => 'bg-purple-50 text-purple-700 border-purple-200',
        'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
        'cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
        'canceled' => 'bg-gray-50 text-gray-600 border-gray-200',
    ];

    $reservationTimeSlots = [];
    $slotStart = \Carbon\Carbon::createFromFormat('H:i', '11:00', 'Asia/Manila');
    $slotEnd = \Carbon\Carbon::createFromFormat('H:i', '19:00', 'Asia/Manila');

    while ($slotStart->lte($slotEnd)) {
        $reservationTimeSlots[] = [
            'value' => $slotStart->format('H:i'),
            'label' => $slotStart->format('g:i A'),
        ];

        $slotStart->addMinutes(30);
    }
@endphp

<style>
    [x-cloak] {
        display: none !important;
    }

    .admin-reservation-page {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }

    .admin-reservation-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 1.25rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        border-radius: 999px;
        border: 1px solid transparent;
        padding: 0.35rem 0.75rem;
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.7rem;
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
        min-width: 250px;
    }

    .reservation-card-grid {
        display: none;
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

    .create-modal-backdrop {
        background: rgba(15, 23, 42, 0.68);
        backdrop-filter: blur(7px);
    }

    .create-modal-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.95);
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.25);
    }

    .form-input-clean {
        width: 100%;
        border-radius: 0.9rem;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        font-size: 0.9rem;
        padding: 0.7rem 0.85rem;
        outline: none;
    }

    .form-input-clean:focus {
        border-color: #fb923c;
        box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.16);
        background: #ffffff;
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
        .reservation-card-grid {
            grid-template-columns: 1fr;
            padding: 0.85rem;
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


    /* Premium reservation interactions */
    .reservation-premium-card,
    .admin-reservation-card,
    .admin-reservation-page .bg-white.rounded-2xl {
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .admin-reservation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.09);
        border-color: #fed7aa;
    }

    .reservation-modal-animate {
        animation: reservationModalIn .2s ease-out;
    }

    .reservation-toast {
        animation: reservationToastIn .28s ease-out;
    }

    .reservation-toast.is-leaving {
        animation: reservationToastOut .22s ease-in forwards;
    }

    @keyframes reservationModalIn {
        from { opacity: 0; transform: translateY(12px) scale(.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes reservationToastIn {
        from { opacity: 0; transform: translateY(-10px) translateX(12px); }
        to { opacity: 1; transform: translateY(0) translateX(0); }
    }

    @keyframes reservationToastOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(22px); }
    }

    .summary-premium-card {
        position: relative;
        overflow: hidden;
    }

    .summary-premium-card::after {
        content: '';
        position: absolute;
        width: 92px;
        height: 92px;
        right: -38px;
        top: -42px;
        border-radius: 999px;
        background: rgba(251, 146, 60, .08);
        pointer-events: none;
    }

    .reservation-submit-loading {
        opacity: .72;
        cursor: wait !important;
        pointer-events: none;
    }

</style>

<div class="admin-reservation-page space-y-5 sm:space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col 2xl:flex-row 2xl:items-start 2xl:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                Reservations
            </h1>
            <p class="text-sm sm:text-base text-gray-500 mt-1">
                Manage customer reservations, payments, arrivals, seating, and admin-created bookings.
            </p>

            <button
                type="button"
                onclick="openAdminReservationModal()"
                class="mt-4 inline-flex items-center justify-center px-5 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-black shadow-sm transition"
            >
                + Create Reservation
            </button>
        </div>

        <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full 2xl:w-auto 2xl:max-w-3xl">
            <div class="summary-premium-card bg-white border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm min-w-0 hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-xs text-gray-500">Selected Date</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 truncate">
                    {{ $selectedDateCarbon->format('M d, Y') }}
                </p>
            </div>

            <div class="summary-premium-card bg-white border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm min-w-0 hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-xs text-gray-500">Total</p>
                <p data-animate-count="{{ $totalReservations }}" class="text-2xl font-bold text-orange-500">{{ $totalReservations }}</p>
            </div>

            <div class="summary-premium-card bg-white border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm min-w-0 hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-xs text-gray-500">Pending</p>
                <p data-animate-count="{{ $pendingCount }}" class="text-2xl font-bold text-yellow-500">{{ $pendingCount }}</p>
            </div>

            <div class="summary-premium-card bg-white border border-gray-200 rounded-2xl px-4 sm:px-5 py-4 shadow-sm min-w-0 hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-xs text-gray-500">Approved</p>
                <p data-animate-count="{{ $approvedCount }}" class="text-2xl font-bold text-green-500">{{ $approvedCount }}</p>
            </div>
        </div>
    </div>

    <!-- Server feedback is displayed as premium toasts -->
    <div id="reservationServerMessages" class="hidden"
        data-success="{{ session('success') }}"
        data-error="{{ session('error') }}"
        data-validation='@json($errors->all())'>
    </div>

    <!-- MAIN PANEL -->
    <div class="w-full max-w-full bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- FILTER -->
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

                    <a href="{{ url()->current() }}?date={{ now('Asia/Manila')->toDateString() }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold text-center">
                        Today
                    </a>

                    <a href="{{ url()->current() }}?date={{ now('Asia/Manila')->addDay()->toDateString() }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold text-center">
                        Tomorrow
                    </a>
                </form>
            </div>
        </div>

        <!-- DESKTOP TABLE -->
        <div class="reservation-table-wrap overflow-x-auto">
            <table class="w-full min-w-[1320px] text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-5 py-4 font-semibold">Reservation</th>
                        <th class="px-5 py-4 font-semibold">Customer</th>
                        <th class="px-5 py-4 font-semibold">Date & Time</th>
                        <th class="px-5 py-4 font-semibold">Guests</th>
                        <th class="px-5 py-4 font-semibold">Reservation Fee</th>
                        <th class="px-5 py-4 font-semibold">Payment</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold">Service Info</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($reservations as $reservation)
                        @php
                            $paymentStatus = strtolower($reservation->payment_status ?? 'pending');
                            $reservationStatus = strtolower($reservation->status ?? 'pending');
                            $billingType = strtolower($reservation->reservation_fee_billing_type ?? 'online_payment');
                            $isAdminBooking = strtolower($reservation->created_by_role ?? 'customer') === 'admin';
                            $isAddToBill = $billingType === 'add_to_bill';
                            $isPaymentVerified = in_array($paymentStatus, ['paid', 'verified', 'settled', 'completed'], true);
                            $canAccept = $reservationStatus === 'pending' && ($isPaymentVerified || $isAddToBill);
                        @endphp

                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-black text-gray-950">
                                    #RES-{{ str_pad($reservation->id, 4, '0', STR_PAD_LEFT) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $reservation->created_at ? $reservation->created_at->diffForHumans() : 'No timestamp' }}
                                </p>

                                @if ($isAdminBooking)
                                    <span class="status-pill mt-2 bg-orange-50 text-orange-700 border-orange-200">
                                        Admin Booking
                                    </span>
                                @endif
                            </td>

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
                                    {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}
                                </p>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="status-pill bg-gray-100 text-gray-700 border-gray-100">
                                    {{ $reservation->guest_count }} guest{{ $reservation->guest_count > 1 ? 's' : '' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-bold text-orange-500">
                                    ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                                </p>

                                @if ($isAddToBill)
                                    <p class="text-xs text-purple-600 font-bold mt-1">
                                        Add to bill when seated
                                    </p>

                                    @if ($reservation->reservation_fee_added_to_bill)
                                        <span class="status-pill mt-2 bg-purple-50 text-purple-700 border-purple-200">
                                            Added to Bill
                                        </span>
                                    @endif
                                @else
                                    <p class="text-xs text-gray-500 mt-1">
                                        Non-refundable
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 max-w-[210px]">
                                <p class="font-semibold text-gray-900">
                                    {{ $reservation->payment_method ?? 'N/A' }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1 break-all">
                                    Ref: {{ $reservation->payment_reference ?? 'N/A' }}
                                </p>

                                <span class="status-pill mt-2 {{ $paymentClasses[$paymentStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst($paymentStatus) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="status-pill {{ $statusClasses[$reservationStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
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

                            <td class="px-5 py-4 text-right">
                                <div class="action-stack">
                                    @if ($paymentStatus === 'pending' && !$isAddToBill)
                                        <form method="POST" action="{{ route('admin.reservations.verify-payment', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-button bg-green-500 hover:bg-green-600 text-white">
                                                Verify
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.reservations.reject-payment', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-button bg-red-500 hover:bg-red-600 text-white">
                                                Reject
                                            </button>
                                        </form>
                                    @endif

                                    @if ($canAccept)
                                        <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="action-button bg-orange-500 hover:bg-orange-600 text-white">
                                                Accept
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="declined">
                                            <button type="submit" class="action-button bg-gray-100 hover:bg-gray-200 text-gray-600">
                                                Decline
                                            </button>
                                        </form>
                                    @endif

                                    @if (!$canAccept && !$isPaymentVerified && !$isAddToBill && $paymentStatus !== 'rejected' && $reservationStatus === 'pending')
                                        <span class="action-button bg-yellow-50 text-yellow-700">
                                            Verify Payment First
                                        </span>
                                    @endif

                                    @if ($reservationStatus === 'approved')
                                        <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
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
                                            action="{{ route('admin.reservations.update-status', $reservation) }}"
                                            class="flex items-center justify-end gap-2 flex-wrap"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="seated">

                                            <select
                                                name="table_number"
                                                class="w-24 rounded-lg border-gray-200 text-xs focus:border-orange-300 focus:ring-orange-200"
                                                required
                                            >
                                                <option value="">Table</option>
                                                @foreach ($tables ?? [] as $table)
                                                    <option value="{{ $table->table_number }}">
                                                        Table {{ $table->table_number }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <button type="submit" class="action-button bg-purple-500 hover:bg-purple-600 text-white">
                                                Seat
                                            </button>
                                        </form>
                                    @endif

                                    @if ($reservationStatus === 'seated')
                                        <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="action-button bg-gray-900 hover:bg-black text-white">
                                                Complete
                                            </button>
                                        </form>
                                    @endif

                                    @if (!in_array($reservationStatus, ['declined', 'cancelled', 'canceled', 'completed'], true))
                                        <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
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
                                            No Action
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
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

        <!-- RESPONSIVE CARDS -->
        <div class="reservation-card-grid">
            @forelse ($reservations as $reservation)
                @php
                    $paymentStatus = strtolower($reservation->payment_status ?? 'pending');
                    $reservationStatus = strtolower($reservation->status ?? 'pending');
                    $billingType = strtolower($reservation->reservation_fee_billing_type ?? 'online_payment');
                    $isAdminBooking = strtolower($reservation->created_by_role ?? 'customer') === 'admin';
                    $isAddToBill = $billingType === 'add_to_bill';
                    $isPaymentVerified = in_array($paymentStatus, ['paid', 'verified', 'settled', 'completed'], true);
                    $canAccept = $reservationStatus === 'pending' && ($isPaymentVerified || $isAddToBill);
                @endphp

                <div class="admin-reservation-card overflow-hidden">
                    <div class="p-4 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-black text-gray-950">
                                #RES-{{ str_pad($reservation->id, 4, '0', STR_PAD_LEFT) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $reservation->created_at ? $reservation->created_at->diffForHumans() : 'No timestamp' }}
                            </p>

                            @if ($isAdminBooking)
                                <span class="status-pill mt-2 bg-orange-50 text-orange-700 border-orange-200">
                                    Admin Booking
                                </span>
                            @endif
                        </div>

                        <span class="status-pill {{ $statusClasses[$reservationStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                            {{ ucfirst($reservationStatus) }}
                        </span>
                    </div>

                    <div class="reservation-card-section">
                        <p class="reservation-card-label">Customer</p>
                        <p class="font-black text-gray-950">{{ $reservation->customer_name }}</p>
                        <p class="text-xs text-gray-500 mt-1 break-words">{{ $reservation->customer_email }}</p>
                        <p class="text-xs text-gray-500">{{ $reservation->customer_phone }}</p>
                    </div>

                    <div class="reservation-card-section grid grid-cols-2 gap-3">
                        <div>
                            <p class="reservation-card-label">Date & Time</p>
                            <p class="font-black text-gray-950">
                                {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}
                            </p>
                        </div>

                        <div>
                            <p class="reservation-card-label">Guests</p>
                            <span class="status-pill bg-gray-100 text-gray-700 border-gray-100">
                                {{ $reservation->guest_count }} guest{{ $reservation->guest_count > 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>

                    <div class="reservation-card-section grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <p class="reservation-card-label">Reservation Fee</p>
                            <p class="font-black text-orange-500">
                                ₱{{ number_format($reservation->reservation_fee_amount, 2) }}
                            </p>

                            @if ($isAddToBill)
                                <p class="text-xs text-purple-600 font-bold mt-1">
                                    Add to bill when seated
                                </p>

                                @if ($reservation->reservation_fee_added_to_bill)
                                    <span class="status-pill mt-2 bg-purple-50 text-purple-700 border-purple-200">
                                        Added to Bill
                                    </span>
                                @endif
                            @else
                                <p class="text-xs text-gray-500 mt-1">
                                    Non-refundable
                                </p>
                            @endif
                        </div>

                        <div>
                            <p class="reservation-card-label">Payment</p>
                            <p class="font-black text-gray-950">
                                {{ $reservation->payment_method ?? 'N/A' }}
                            </p>
                            <span class="status-pill mt-2 {{ $paymentClasses[$paymentStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                {{ ucfirst($paymentStatus) }}
                            </span>
                        </div>
                    </div>

                    <div class="reservation-card-section">
                        <p class="reservation-card-label">Service Info</p>
                        <p class="text-xs text-gray-600 leading-6">
                            <span class="font-black text-gray-900">Table:</span>
                            {{ $reservation->table_number ? 'Table ' . $reservation->table_number : 'Not assigned' }}
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

                    @if ($reservation->notes)
                        <div class="reservation-card-section">
                            <p class="reservation-card-label">Notes</p>
                            <p class="text-xs text-gray-600 leading-5 break-words">
                                {{ $reservation->notes }}
                            </p>
                        </div>
                    @endif

                    <div class="reservation-card-section">
                        <p class="reservation-card-label">Actions</p>

                        <div class="action-stack">
                            @if ($paymentStatus === 'pending' && !$isAddToBill)
                                <form method="POST" action="{{ route('admin.reservations.verify-payment', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-button bg-green-500 hover:bg-green-600 text-white">
                                        Verify
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.reservations.reject-payment', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-button bg-red-500 hover:bg-red-600 text-white">
                                        Reject
                                    </button>
                                </form>
                            @endif

                            @if ($canAccept)
                                <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="action-button bg-orange-500 hover:bg-orange-600 text-white">
                                        Accept
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="declined">
                                    <button type="submit" class="action-button bg-gray-100 hover:bg-gray-200 text-gray-600">
                                        Decline
                                    </button>
                                </form>
                            @endif

                            @if ($reservationStatus === 'approved')
                                <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="arrived">
                                    <button type="submit" class="action-button bg-blue-500 hover:bg-blue-600 text-white">
                                        Mark Arrived
                                    </button>
                                </form>
                            @endif

                            @if ($reservationStatus === 'arrived')
                                <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}" class="flex items-center gap-2 flex-wrap w-full">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="seated">

                                    <select name="table_number" class="w-full rounded-lg border-gray-200 text-xs focus:border-orange-300 focus:ring-orange-200" required>
                                        <option value="">Select Table</option>
                                        @foreach ($tables ?? [] as $table)
                                            <option value="{{ $table->table_number }}">
                                                Table {{ $table->table_number }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="action-button bg-purple-500 hover:bg-purple-600 text-white">
                                        Seat
                                    </button>
                                </form>
                            @endif

                            @if ($reservationStatus === 'seated')
                                <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="action-button bg-gray-900 hover:bg-black text-white">
                                        Complete
                                    </button>
                                </form>
                            @endif

                            @if (!in_array($reservationStatus, ['declined', 'cancelled', 'canceled', 'completed'], true))
                                <form method="POST" action="{{ route('admin.reservations.update-status', $reservation) }}">
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
                                    No Action
                                </span>
                            @endif
                        </div>
                    </div>
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

        @if ($reservations->hasPages())
            <div class="p-4 sm:p-5 border-t border-gray-100">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>

    <!-- CREATE RESERVATION MODAL -->
    <div
        id="adminReservationModal"
        class="fixed inset-0 z-[999] create-modal-backdrop hidden items-center justify-center px-4 py-6"
    >
        <div class="create-modal-card reservation-modal-animate w-full max-w-3xl max-h-[94dvh] overflow-y-auto rounded-[1.5rem]">
            <div class="px-5 sm:px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black text-orange-500 uppercase tracking-widest">
                        Admin Booking
                    </p>
                    <h2 class="text-xl sm:text-2xl font-black text-gray-950 mt-1">
                        Create Reservation
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Reservation fee will be added to the customer bill once seated.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="closeAdminReservationModal()"
                    class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 font-black transition"
                >
                    ×
                </button>
            </div>

            <form id="adminReservationCreateForm" method="POST" action="{{ route('admin.reservations.store') }}" class="p-5 sm:p-6 space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Customer Name</label>
                        <input
                            type="text"
                            name="customer_name"
                            value="{{ old('customer_name') }}"
                            maxlength="255"
                            class="form-input-clean"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                        <input
                            type="email"
                            name="customer_email"
                            value="{{ old('customer_email') }}"
                            maxlength="255"
                            class="form-input-clean"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Contact Number</label>
                        <input
                            type="text"
                            name="customer_phone"
                            value="{{ old('customer_phone') }}"
                            maxlength="30"
                            class="form-input-clean"
                            placeholder="Example: 0912 345 6789"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Guest Count</label>
                        <input
                            type="number"
                            name="guest_count"
                            value="{{ old('guest_count') }}"
                            min="1"
                            max="30"
                            class="form-input-clean"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date</label>
                        <input
                            type="date"
                            name="reservation_date"
                            id="adminReservationDate"
                            value="{{ old('reservation_date', $selectedDate ?? $today) }}"
                            min="{{ $today }}"
                            class="form-input-clean"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Time</label>
                        <select
                            name="reservation_time"
                            id="adminReservationTime"
                            data-today="{{ $today }}"
                            data-current-time="{{ now('Asia/Manila')->format('H:i') }}"
                            class="form-input-clean"
                            required
                        >
                            <option value="">Select time</option>
                            @foreach ($reservationTimeSlots as $slot)
                                <option
                                    value="{{ $slot['value'] }}"
                                    @selected(old('reservation_time') === $slot['value'])
                                >
                                    {{ $slot['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Notes</label>
                    <textarea
                        name="notes"
                        rows="3"
                        maxlength="1000"
                        class="form-input-clean resize-none"
                        placeholder="Optional notes, request, or instruction"
                    >{{ old('notes') }}</textarea>
                </div>

                <div class="rounded-2xl bg-purple-50 border border-purple-100 px-4 py-3">
                    <p class="text-sm font-black text-purple-700">
                        Payment Handling: Add to Bill
                    </p>
                    <p class="text-xs text-purple-600 mt-1 leading-5">
                        This admin-created reservation will not generate Xendit payment. The reservation fee will be added to the customer's bill once they are seated.
                    </p>
                </div>

                <label class="flex items-start gap-3 rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                    <input
                        type="checkbox"
                        name="auto_approve"
                        value="1"
                        class="mt-1 rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                        checked
                    >
                    <span>
                        <span class="block text-sm font-black text-gray-800">
                            Automatically approve this reservation
                        </span>
                        <span class="block text-xs text-gray-500 mt-1">
                            Recommended for admin/manual bookings.
                        </span>
                    </span>
                </label>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 pt-2">
                    <button
                        type="button"
                        onclick="closeAdminReservationModal()"
                        class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-black transition"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        id="adminReservationCreateBtn"
                        class="w-full sm:w-auto px-5 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-black transition shadow-sm disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        Create Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Premium Toasts -->
<div id="reservationToastContainer"
    class="fixed right-4 top-20 z-[1100] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3 sm:right-6 sm:top-6">
</div>

<!-- Reservation Action Confirmation -->
<div id="reservationConfirmModal"
    class="fixed inset-0 z-[1050] hidden items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm">
    <div class="reservation-modal-animate w-full max-w-md overflow-hidden rounded-[28px] border border-orange-100 bg-white shadow-2xl">
        <div id="reservationConfirmHeader" class="bg-gradient-to-br from-orange-50 via-white to-amber-50 px-6 py-6 text-center">
            <div id="reservationConfirmIcon"
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-orange-200 bg-orange-100 text-2xl font-black text-orange-600 shadow-sm">
                ?
            </div>
            <h3 id="reservationConfirmTitle" class="mt-4 text-2xl font-black text-gray-950">
                Confirm Action
            </h3>
            <p id="reservationConfirmMessage" class="mt-3 text-sm leading-6 text-gray-600">
                Continue with this reservation action?
            </p>
        </div>

        <div class="border-t border-gray-100 bg-white px-6 py-5">
            <div id="reservationConfirmNote" class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3 text-sm leading-6 text-orange-800">
                The reservation record will be updated immediately.
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button id="reservationConfirmCancel" type="button"
                    class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 font-black text-gray-700 transition hover:bg-gray-50">
                    Go Back
                </button>
                <button id="reservationConfirmProceed" type="button"
                    class="w-full rounded-2xl bg-orange-500 px-4 py-3 font-black text-white shadow-sm transition hover:bg-orange-600">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<script>

    let pendingReservationForm = null;
    let pendingReservationSubmitter = null;

    const reservationActionMeta = {
        'Verify': {
            title: 'Verify Payment?',
            message: 'Confirm that this reservation payment has been received and is valid.',
            note: 'After verification, the reservation may be accepted.',
            icon: '✓',
            tone: 'green',
            proceed: 'Yes, Verify Payment'
        },
        'Reject': {
            title: 'Reject Payment?',
            message: 'This will mark the submitted payment as rejected.',
            note: 'Use this only when the payment reference or transaction is invalid.',
            icon: '!',
            tone: 'red',
            proceed: 'Yes, Reject Payment'
        },
        'Accept': {
            title: 'Accept Reservation?',
            message: 'The reservation will be approved for the selected date and time.',
            note: 'Make sure the booking details and payment status are correct.',
            icon: '✓',
            tone: 'orange',
            proceed: 'Yes, Accept'
        },
        'Decline': {
            title: 'Decline Reservation?',
            message: 'This reservation will be marked as declined.',
            note: 'The customer will no longer have an active booking for this reservation.',
            icon: '×',
            tone: 'red',
            proceed: 'Yes, Decline'
        },
        'Mark Arrived': {
            title: 'Mark Customer as Arrived?',
            message: 'Confirm that the customer is already at the restaurant.',
            note: 'The reservation will move to the arrived stage.',
            icon: '→',
            tone: 'blue',
            proceed: 'Yes, Mark Arrived'
        },
        'Seat': {
            title: 'Seat This Reservation?',
            message: 'The selected table will be assigned to this reservation.',
            note: 'Confirm that the chosen table is correct before continuing.',
            icon: '⌂',
            tone: 'purple',
            proceed: 'Yes, Seat Customer'
        },
        'Complete': {
            title: 'Complete Reservation?',
            message: 'This reservation will be marked as completed.',
            note: 'Use this after the reservation service is finished.',
            icon: '✓',
            tone: 'gray',
            proceed: 'Yes, Complete'
        },
        'Cancel': {
            title: 'Cancel Reservation?',
            message: 'This will cancel the active reservation.',
            note: 'This action changes the reservation status immediately.',
            icon: '!',
            tone: 'red',
            proceed: 'Yes, Cancel Reservation'
        }
    };

    function showReservationToast(type, title, message = '') {
        const container = document.getElementById('reservationToastContainer');
        if (!container) return;

        const styles = {
            success: { border: 'border-green-200', bg: 'bg-green-50', iconBg: 'bg-green-100', iconText: 'text-green-700', icon: '✓' },
            error: { border: 'border-red-200', bg: 'bg-red-50', iconBg: 'bg-red-100', iconText: 'text-red-700', icon: '!' },
            warning: { border: 'border-yellow-200', bg: 'bg-yellow-50', iconBg: 'bg-yellow-100', iconText: 'text-yellow-700', icon: '!' },
            info: { border: 'border-blue-200', bg: 'bg-blue-50', iconBg: 'bg-blue-100', iconText: 'text-blue-700', icon: 'i' }
        };

        const style = styles[type] || styles.info;
        const toast = document.createElement('div');
        toast.className = `reservation-toast overflow-hidden rounded-2xl border ${style.border} ${style.bg} shadow-xl`;
        toast.innerHTML = `
            <div class="flex items-start gap-3 p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${style.iconBg} ${style.iconText} font-black">${style.icon}</div>
                <div class="min-w-0 flex-1">
                    <p class="font-black text-gray-950">${escapeReservationText(title)}</p>
                    ${message ? `<p class="mt-1 text-sm leading-5 text-gray-600">${escapeReservationText(message)}</p>` : ''}
                </div>
                <button type="button" class="text-lg text-gray-400 hover:text-gray-700" aria-label="Close notification">×</button>
            </div>`;

        const close = () => {
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 220);
        };

        toast.querySelector('button').addEventListener('click', close);
        container.appendChild(toast);
        setTimeout(close, type === 'error' ? 6500 : 4200);
    }

    function escapeReservationText(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function openReservationConfirm(form, submitter) {
        const modal = document.getElementById('reservationConfirmModal');
        const actionText = (submitter?.textContent || 'Continue').trim();
        const meta = reservationActionMeta[actionText] || {
            title: 'Confirm Reservation Action?',
            message: 'Continue with this update?',
            note: 'The reservation record will be updated immediately.',
            icon: '?', tone: 'orange', proceed: 'Continue'
        };

        pendingReservationForm = form;
        pendingReservationSubmitter = submitter;

        document.getElementById('reservationConfirmTitle').textContent = meta.title;
        document.getElementById('reservationConfirmMessage').textContent = meta.message;
        document.getElementById('reservationConfirmNote').textContent = meta.note;
        document.getElementById('reservationConfirmIcon').textContent = meta.icon;
        document.getElementById('reservationConfirmProceed').textContent = meta.proceed;

        const toneClasses = {
            green: ['bg-green-100', 'text-green-700', 'border-green-200', 'bg-green-600', 'hover:bg-green-700'],
            red: ['bg-red-100', 'text-red-700', 'border-red-200', 'bg-red-600', 'hover:bg-red-700'],
            blue: ['bg-blue-100', 'text-blue-700', 'border-blue-200', 'bg-blue-600', 'hover:bg-blue-700'],
            purple: ['bg-purple-100', 'text-purple-700', 'border-purple-200', 'bg-purple-600', 'hover:bg-purple-700'],
            gray: ['bg-gray-100', 'text-gray-700', 'border-gray-200', 'bg-gray-900', 'hover:bg-black'],
            orange: ['bg-orange-100', 'text-orange-700', 'border-orange-200', 'bg-orange-500', 'hover:bg-orange-600']
        };
        const icon = document.getElementById('reservationConfirmIcon');
        const proceed = document.getElementById('reservationConfirmProceed');
        icon.className = 'mx-auto flex h-16 w-16 items-center justify-center rounded-full border text-2xl font-black shadow-sm';
        proceed.className = 'w-full rounded-2xl px-4 py-3 font-black text-white shadow-sm transition';
        const cls = toneClasses[meta.tone] || toneClasses.orange;
        icon.classList.add(cls[0], cls[1], cls[2]);
        proceed.classList.add(cls[3], cls[4]);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeReservationConfirm() {
        const modal = document.getElementById('reservationConfirmModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingReservationForm = null;
        pendingReservationSubmitter = null;
        document.body.style.overflow = '';
    }

    function submitPendingReservationAction() {
        if (!pendingReservationForm) return;
        const form = pendingReservationForm;
        const submitter = pendingReservationSubmitter;
        closeReservationConfirm();

        if (submitter) {
            submitter.dataset.originalText = submitter.textContent;
            submitter.textContent = 'Processing...';
            submitter.classList.add('reservation-submit-loading');
            submitter.disabled = true;
        }
        form.dataset.confirmed = 'true';
        form.submit();
    }
    function openAdminReservationModal() {
        const modal = document.getElementById('adminReservationModal');

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        refreshAdminReservationTimeSlots();
    }

    function closeAdminReservationModal() {
        const modal = document.getElementById('adminReservationModal');

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function refreshAdminReservationTimeSlots() {
        const dateInput = document.getElementById('adminReservationDate');
        const timeSelect = document.getElementById('adminReservationTime');

        if (!dateInput || !timeSelect) {
            return;
        }

        const selectedDate = dateInput.value;
        const today = timeSelect.dataset.today;
        const currentTime = timeSelect.dataset.currentTime;
        const selectedTime = timeSelect.value;

        Array.from(timeSelect.options).forEach(function (option) {
            if (!option.value) {
                option.disabled = false;
                return;
            }

            option.disabled = selectedDate === today && option.value <= currentTime;
        });

        const currentOption = Array.from(timeSelect.options).find(function (option) {
            return option.value === selectedTime;
        });

        if (currentOption && currentOption.disabled) {
            timeSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const dateInput = document.getElementById('adminReservationDate');

        if (dateInput) {
            dateInput.addEventListener('change', refreshAdminReservationTimeSlots);
        }

        refreshAdminReservationTimeSlots();

        const serverMessages = document.getElementById('reservationServerMessages');
        if (serverMessages) {
            const success = serverMessages.dataset.success || '';
            const error = serverMessages.dataset.error || '';
            let validation = [];
            try { validation = JSON.parse(serverMessages.dataset.validation || '[]'); } catch (e) {}

            if (success) showReservationToast('success', 'Reservation Updated', success);
            if (error) showReservationToast('error', 'Reservation Error', error);
            if (validation.length) {
                showReservationToast('error', 'Please Check the Form', validation.join(' '));
            }
        }

        document.querySelectorAll('.admin-reservation-page form').forEach(function (form) {
            if (form.id === 'adminReservationCreateForm' || (form.method || '').toLowerCase() === 'get') return;

            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === 'true') return;
                const submitter = event.submitter || form.querySelector('button[type="submit"]');
                if (!submitter) return;
                event.preventDefault();
                openReservationConfirm(form, submitter);
            });
        });

        const createForm = document.getElementById('adminReservationCreateForm');
        if (createForm) {
            createForm.addEventListener('submit', function () {
                const button = document.getElementById('adminReservationCreateBtn');
                if (!button) return;
                button.dataset.originalText = button.textContent;
                button.textContent = 'Creating Reservation...';
                button.disabled = true;
                button.classList.add('reservation-submit-loading');
            });
        }

        document.querySelectorAll('[data-animate-count]').forEach(function (element) {
            const target = Number(element.dataset.animateCount || element.textContent || 0);
            let current = 0;
            const duration = 500;
            const started = performance.now();
            function frame(now) {
                const progress = Math.min((now - started) / duration, 1);
                current = Math.round(target * (1 - Math.pow(1 - progress, 3)));
                element.textContent = current.toLocaleString();
                if (progress < 1) requestAnimationFrame(frame);
            }
            requestAnimationFrame(frame);
        });

        document.getElementById('reservationConfirmCancel')?.addEventListener('click', closeReservationConfirm);
        document.getElementById('reservationConfirmProceed')?.addEventListener('click', submitPendingReservationAction);

        @if ($errors->any())
            openAdminReservationModal();
        @endif
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAdminReservationModal();
            closeReservationConfirm();
        }
    });

    document.addEventListener('click', function (event) {
        const confirmModal = document.getElementById('reservationConfirmModal');
        if (confirmModal && event.target === confirmModal) {
            closeReservationConfirm();
            return;
        }

        const modal = document.getElementById('adminReservationModal');

        if (!modal || modal.classList.contains('hidden')) {
            return;
        }

        if (event.target === modal) {
            closeAdminReservationModal();
        }
    });
</script>

@endsection