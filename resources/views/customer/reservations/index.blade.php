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
                rgba(12, 15, 22, 0.84),
                rgba(62, 31, 18, 0.76)
            ),
            url('{{ asset('images/customer-menu/kds-background.png') }}');
        background-size: cover;
        background-position: center;
        background-attachment: scroll;
        background-color: #111318;
    }

    .reservations-page {
        min-height: calc(100vh - 72px);
        position: relative;
        overflow-x: hidden;
        background-image:
            linear-gradient(
                135deg,
                rgba(15, 23, 42, 0.72),
                rgba(67, 31, 12, 0.62)
            ),
            url('{{ asset('images/customer-menu/kds-background.png') }}');
        background-size: cover;
        background-position: center;
        background-attachment: scroll;
    }

    @media (max-width: 1023px) {
        html,
        body,
        .reservations-page {
            background-attachment: scroll;
        }
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
        background: rgba(17, 19, 24, 0.94);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 24px 56px rgba(0, 0, 0, 0.28);
        backdrop-filter: none;
    }

    .reservation-card {
        background: rgba(17, 19, 24, 0.94);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 20px 48px rgba(0, 0, 0, 0.24);
        backdrop-filter: none;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        border-radius: 999px;
        padding: 0.35rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .info-box {
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.055);
    }

    .reservation-main-grid {
        display: grid;
        grid-template-columns: 1fr 520px;
        gap: 1.5rem;
        align-items: start;
    }

    .reservation-top-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .reservation-bottom-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1.5rem;
        align-items: center;
    }

    .reservation-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .reservation-actions {
        width: 250px;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .reservation-action-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
    }

    .reservation-action-btn {
        width: 100%;
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1rem;
        border-radius: 0.9rem;
        font-size: 0.8rem;
        font-weight: 900;
        line-height: 1.2;
        text-align: center;
        transition: all 160ms ease;
        border: 1px solid transparent;
    }

    .reservation-action-btn:hover {
        transform: translateY(-1px);
    }

    .reservation-action-edit {
        background: rgba(148, 163, 184, 0.12);
        color: #e5e7eb;
        border-color: rgba(148, 163, 184, 0.28);
    }

    .reservation-action-edit:hover {
        background: rgba(148, 163, 184, 0.2);
        border-color: rgba(148, 163, 184, 0.42);
    }

    .reservation-action-cancel {
        background: rgba(251, 113, 133, 0.12);
        color: #fecdd3;
        border-color: rgba(251, 113, 133, 0.3);
    }

    .reservation-action-cancel:hover {
        background: rgba(251, 113, 133, 0.2);
        border-color: rgba(251, 113, 133, 0.45);
    }

    .reservation-action-payment {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #ffffff;
        box-shadow: 0 14px 28px rgba(249, 115, 22, 0.24);
    }

    .reservation-action-payment:hover {
        background: linear-gradient(135deg, #fb923c, #f97316);
    }

    .reservation-action-receipt {
        background: rgba(20, 184, 166, 0.15);
        color: #99f6e4;
        border-color: rgba(20, 184, 166, 0.35);
    }

    .reservation-action-receipt:hover {
        background: rgba(20, 184, 166, 0.24);
        border-color: rgba(20, 184, 166, 0.5);
    }

    .cancel-modal-backdrop {
        background: rgba(0, 0, 0, 0.68);
        backdrop-filter: none;
    }

    .cancel-modal-card {
        background: linear-gradient(135deg, #111318 0%, #07090d 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45);
    }

    @media (max-width: 1279px) {
        .reservation-main-grid {
            grid-template-columns: 1fr;
        }

        .reservation-bottom-grid {
            grid-template-columns: 1fr;
        }

        .reservation-actions {
            width: 100%;
            max-width: 420px;
        }
    }

    @media (max-width: 767px) {
        .reservations-inner {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            padding-top: 1rem !important;
        }

        .mobile-hide {
            display: none !important;
        }

        .reservation-page-header {
            padding: 1rem !important;
            border-radius: 1.25rem !important;
            margin-bottom: 1rem !important;
        }

        .reservation-page-header h1 {
            font-size: 1.35rem !important;
            line-height: 1.2 !important;
        }

        .reservation-page-header p {
            font-size: 0.8rem !important;
            line-height: 1.45 !important;
            margin-top: 0.4rem !important;
        }

        .reservation-card {
            border-radius: 1.25rem !important;
            margin-bottom: 1rem !important;
        }

        .reservation-card-top,
        .reservation-card-bottom {
            padding: 1rem !important;
        }

        .reservation-title-row {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.55rem !important;
        }

        .reservation-title {
            font-size: 1rem !important;
            line-height: 1.25 !important;
        }

        .reservation-message {
            font-size: 0.8rem !important;
            line-height: 1.45 !important;
            margin-top: 0.65rem !important;
        }

        .reservation-submitted {
            font-size: 0.72rem !important;
            margin-top: 0.45rem !important;
        }

        .reservation-top-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem;
            margin-top: 0.85rem;
        }

        .reservation-top-stats .info-box {
            padding: 0.75rem !important;
            border-radius: 1rem !important;
        }

        .reservation-detail-grid {
            grid-template-columns: 1fr;
            gap: 0.55rem;
        }

        .reservation-detail-grid .info-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            padding: 0.75rem 0.85rem !important;
            border-radius: 1rem !important;
        }

        .reservation-detail-grid .info-box > div:last-child {
            text-align: right;
        }

        .reservation-label {
            font-size: 0.7rem !important;
            margin-bottom: 0.25rem !important;
        }

        .reservation-value {
            font-size: 0.9rem !important;
            line-height: 1.25 !important;
        }

        .reservation-helper {
            display: none !important;
        }

        .reservation-actions {
            width: 100%;
            max-width: none;
            gap: 0.55rem;
        }

        .reservation-action-row {
            grid-template-columns: 1fr;
            gap: 0.55rem;
        }

        .reservation-action-btn {
            min-height: 44px;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
            border-radius: 1rem !important;
            font-size: 0.8rem !important;
        }

        .status-badge {
            padding: 0.3rem 0.65rem;
            font-size: 0.68rem;
        }
    }

    @media (max-width: 380px) {
        .reservation-top-stats {
            grid-template-columns: 1fr;
        }
    }

    /* Chef Oppa reservation theme */
    .reservation-create-header,
    .reservation-edit-header,
    .reservation-page-header {
        border: 1px solid rgba(255,255,255,.20);
    }

    .reservation-step-number,
    .mobile-hide {
        box-shadow: none !important;
    }

    .reservation-submit-card {
        background:
            radial-gradient(circle at top right, rgba(249,115,22,.16), transparent 14rem),
            linear-gradient(135deg, #111827, #111318) !important;
        border: 1px solid rgba(255,255,255,.08);
    }

    .reservation-action-btn,
    .reservation-create-header a,
    .reservation-edit-header a,
    .reservation-page-header a,
    .reservation-submit-card button,
    .reservation-submit-card a {
        border-radius: 0.9rem !important;
    }

    .reservation-card,
    .glass-dark,
    .glass-card,
    .dark-card {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            scroll-behavior: auto !important;
            transition-duration: 0.01ms !important;
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
        }
    }

</style>

<div
    class="reservations-page"
    x-data="{
        cancelModalOpen: false,
        cancelFormId: null,

        openCancelModal(formId) {
            this.cancelFormId = formId;
            this.cancelModalOpen = true;
        },

        closeCancelModal() {
            this.cancelModalOpen = false;
            this.cancelFormId = null;
        },

        confirmCancel() {
            if (this.cancelFormId) {
                const form = document.getElementById(this.cancelFormId);

                if (form) {
                    form.submit();
                }
            }
        }
    }"
>
    <section class="reservations-inner max-w-7xl mx-auto px-4 sm:px-6 py-5 sm:py-8 pb-10 sm:pb-12">

        <!-- PAGE HEADER -->
        <div class="reservation-page-header glass-dark rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-8 mb-5 sm:mb-6 text-white">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 sm:gap-6">
                <div class="flex items-start gap-4">
                    <div class="mobile-hide w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-orange-500/10 text-orange-500 border border-orange-500/30 flex items-center justify-center font-black shrink-0">
                        D+
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-black text-orange-500 uppercase tracking-widest mb-2">
                            Reservations
                        </p>

                        <h1 class="text-3xl md:text-4xl font-black tracking-tight">
                            Manage your table reservations
                        </h1>

                        <p class="text-sm sm:text-base text-gray-300 mt-3 max-w-3xl leading-7">
                            Create a reservation and track your reservation status in one place.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('customer.reservations.create') }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center px-6 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black transition shadow-lg shadow-orange-500/30"
                >
                    New Reservation
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 sm:mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-5 py-4 text-sm font-semibold shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 sm:mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-5 py-4 text-sm font-semibold shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @forelse ($reservations as $reservation)
            @php
                $reservationStatus = strtolower($reservation->status ?? 'pending');
                $paymentStatus = strtolower($reservation->payment_status ?? 'pending');

                $reservationStatusLabel = match ($reservationStatus) {
                    'approved' => 'Approved',
                    'arrived' => 'Arrived',
                    'seated' => 'Seated',
                    'completed' => 'Completed',
                    'declined' => 'Declined',
                    'cancelled' => 'Cancelled',
                    default => 'Pending',
                };

                $reservationMessage = match ($reservationStatus) {
                    'approved' => 'Your reservation has been approved by the restaurant.',
                    'arrived' => 'You have been marked as arrived by the restaurant.',
                    'seated' => 'You are currently seated at the restaurant.',
                    'completed' => 'This reservation has been completed.',
                    'declined' => 'Your reservation was declined. Please contact the restaurant for assistance.',
                    'cancelled' => 'This reservation was cancelled.',
                    default => 'Your reservation request has been submitted. Please wait for the restaurant to review your reservation.',
                };

                $statusClass = match ($reservationStatus) {
                    'approved' => 'bg-green-500/10 text-green-400 border border-green-500/30',
                    'arrived' => 'bg-blue-500/10 text-blue-400 border border-blue-500/30',
                    'seated' => 'bg-purple-500/10 text-purple-400 border border-purple-500/30',
                    'completed' => 'bg-blue-500/10 text-blue-400 border border-blue-500/30',
                    'declined', 'cancelled' => 'bg-red-500/10 text-red-400 border border-red-500/30',
                    default => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/30',
                };

                $paymentLabel = match ($paymentStatus) {
                    'paid', 'verified', 'settled', 'completed' => 'Paid',
                    'expired' => 'Expired',
                    'failed', 'rejected', 'cancelled', 'canceled' => 'Failed',
                    default => 'Pending Payment',
                };

                $paymentClass = match ($paymentStatus) {
                    'paid', 'verified', 'settled', 'completed' => 'bg-green-500/10 text-green-400 border border-green-500/30',
                    'expired' => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/30',
                    'failed', 'rejected', 'cancelled', 'canceled' => 'bg-red-500/10 text-red-400 border border-red-500/30',
                    default => 'bg-orange-500/10 text-orange-400 border border-orange-500/30',
                };

                $reservationDate = $reservation->reservation_date
                    ? \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y')
                    : 'N/A';

                $reservationTime = $reservation->reservation_time
                    ? \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A')
                    : 'N/A';

                $submittedAt = $reservation->created_at
                    ? \Carbon\Carbon::parse($reservation->created_at)->format('M d, Y h:i A')
                    : 'N/A';

                $invoiceUrl = $reservation->xendit_invoice_url
                    ?? $reservation->invoice_url
                    ?? null;

                $feeAmount = $reservation->reservation_fee_amount
                    ?? $reservation->reservation_fee
                    ?? 0;

                $isPaymentPaid = in_array($paymentStatus, ['paid', 'verified', 'settled', 'completed'], true);
                $canContinuePayment = !$isPaymentPaid && $invoiceUrl;

                $canManageReservation = !in_array(
                    $reservationStatus,
                    ['approved', 'arrived', 'seated', 'completed', 'declined', 'cancelled'],
                    true
                );

                $cancelFormId = 'cancel-reservation-form-' . $reservation->id;
            @endphp

            <div class="reservation-card rounded-[1.5rem] sm:rounded-[2rem] overflow-hidden text-white mb-5">
                <div class="reservation-card-top p-5 sm:p-6">
                    <div class="reservation-main-grid">

                        <!-- LEFT DETAILS -->
                        <div class="flex items-start gap-4">
                            <div class="mobile-hide w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-orange-500/10 text-orange-500 border border-orange-500/30 flex items-center justify-center font-black shrink-0">
                                RSV
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="reservation-title-row flex flex-wrap items-center gap-3">
                                    <h2 class="reservation-title text-lg sm:text-xl font-black break-words">
                                        Reservation #{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}
                                    </h2>

                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $reservationStatusLabel }}
                                    </span>
                                </div>

                                <p class="reservation-message text-sm text-gray-300 mt-3 leading-6">
                                    {{ $reservationMessage }}
                                </p>

                                <p class="reservation-submitted text-xs text-gray-400 mt-2">
                                    Submitted {{ $submittedAt }}
                                </p>
                            </div>
                        </div>

                        <!-- DATE TIME GUESTS -->
                        <div class="reservation-top-stats">
                            <div class="info-box rounded-2xl p-4">
                                <p class="reservation-label text-xs font-bold text-gray-400 mb-2">Date</p>
                                <p class="reservation-value font-black text-white">{{ $reservationDate }}</p>
                            </div>

                            <div class="info-box rounded-2xl p-4">
                                <p class="reservation-label text-xs font-bold text-gray-400 mb-2">Time</p>
                                <p class="reservation-value font-black text-white">{{ $reservationTime }}</p>
                            </div>

                            <div class="info-box rounded-2xl p-4">
                                <p class="reservation-label text-xs font-bold text-gray-400 mb-2">Guests</p>
                                <p class="reservation-value font-black text-white">
                                    {{ $reservation->guest_count ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="reservation-card-bottom border-t border-white/10 p-5 sm:p-6">
                    <div class="reservation-bottom-grid">

                        <!-- DETAILS -->
                        <div class="reservation-detail-grid">
                            <div class="info-box rounded-2xl p-4">
                                <div>
                                    <p class="reservation-label text-xs font-bold text-gray-400 mb-2">
                                        Reservation Fee
                                    </p>
                                    <p class="reservation-value font-black text-orange-300">
                                        ₱{{ number_format($feeAmount, 2) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="reservation-helper text-xs text-gray-400 mt-2">
                                        Non-refundable securing fee
                                    </p>
                                </div>
                            </div>

                            <div class="info-box rounded-2xl p-4">
                                <div>
                                    <p class="reservation-label text-xs font-bold text-gray-400 mb-2">
                                        Payment
                                    </p>

                                    <span class="status-badge {{ $paymentClass }}">
                                        {{ $paymentLabel }}
                                    </span>
                                </div>

                                <div>
                                    <p class="reservation-helper text-xs text-gray-400 mt-2 break-words">
                                        {{ $reservation->payment_method ?? 'Xendit Checkout' }}
                                    </p>
                                </div>
                            </div>

                            <div class="info-box rounded-2xl p-4">
                                <div>
                                    <p class="reservation-label text-xs font-bold text-gray-400 mb-2">
                                        Table
                                    </p>

                                    @if ($reservation->table_number)
                                        <p class="reservation-value font-black text-purple-300">
                                            Table {{ $reservation->table_number }}
                                        </p>
                                    @else
                                        <p class="reservation-value font-black text-white">
                                            Not assigned
                                        </p>
                                    @endif
                                </div>

                                <div>
                                    <p class="reservation-helper text-xs text-gray-400 mt-2">
                                        Assigned by staff
                                    </p>
                                </div>
                            </div>

                            <div class="info-box rounded-2xl p-4">
                                <div>
                                    <p class="reservation-label text-xs font-bold text-gray-400 mb-2">
                                        Contact
                                    </p>

                                    <p class="reservation-value text-sm font-bold text-white break-words">
                                        {{ $reservation->customer_name ?? auth()->user()->name }}
                                    </p>
                                </div>

                                <div>
                                    <p class="reservation-helper text-xs text-gray-400 mt-1 break-words">
                                        {{ $reservation->customer_phone ?? 'No phone recorded' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ACTIONS -->
                        <div class="reservation-actions">
                            @if ($canManageReservation)
                                <div class="reservation-action-row">
                                    <a
                                        href="{{ route('customer.reservations.edit', $reservation) }}"
                                        class="reservation-action-btn reservation-action-edit"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        id="{{ $cancelFormId }}"
                                        method="POST"
                                        action="{{ route('customer.reservations.destroy', $reservation) }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="button"
                                            @click="openCancelModal('{{ $cancelFormId }}')"
                                            class="reservation-action-btn reservation-action-cancel"
                                        >
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @if ($canContinuePayment)
                                <a
                                    href="{{ $invoiceUrl }}"
                                    target="_blank"
                                    class="reservation-action-btn reservation-action-payment"
                                >
                                    Continue Payment
                                </a>
                            @endif

                            @if ($isPaymentPaid && $invoiceUrl)
                                <a
                                    href="{{ $invoiceUrl }}"
                                    target="_blank"
                                    class="reservation-action-btn reservation-action-receipt"
                                >
                                    View Receipt
                                </a>
                            @endif
                        </div>

                    </div>

                    @if ($reservation->notes)
                        <div class="mt-5 info-box rounded-2xl p-4">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest">
                                Notes
                            </p>
                            <p class="text-sm text-gray-300 mt-2 leading-6 break-words">
                                {{ $reservation->notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="glass-dark rounded-[1.5rem] sm:rounded-[2rem] p-8 sm:p-10 text-center text-white">
                <div class="mx-auto w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-orange-500/10 text-orange-500 border border-orange-500/30 flex items-center justify-center font-black mb-5">
                    RSV
                </div>

                <h2 class="text-2xl font-black">
                    No reservations yet
                </h2>

                <p class="text-gray-300 mt-3 max-w-xl mx-auto leading-7 text-sm sm:text-base">
                    You do not have any table reservations yet. Create a new reservation to secure your table.
                </p>

                <a
                    href="{{ route('customer.reservations.create') }}"
                    class="mt-6 inline-flex w-full sm:w-auto items-center justify-center px-6 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black transition shadow-lg shadow-orange-500/30"
                >
                    New Reservation
                </a>
            </div>
        @endforelse

        @if (method_exists($reservations, 'hasPages') && $reservations->hasPages())
            <div class="mt-6 glass-dark rounded-2xl p-4 text-white">
                {{ $reservations->links() }}
            </div>
        @endif

    </section>

    <!-- CANCEL RESERVATION MODAL -->
    <div
        x-cloak
        x-show="cancelModalOpen"
        x-transition.opacity
        class="fixed inset-0 z-[999] cancel-modal-backdrop flex items-center justify-center px-4"
    >
        <div
            x-show="cancelModalOpen"
            x-transition.scale
            @click.away="closeCancelModal()"
            class="cancel-modal-card w-full max-w-md rounded-[1.75rem] p-6 sm:p-7 text-white"
        >
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-red-500/15 border border-red-400/20 text-red-400 flex items-center justify-center font-black shrink-0">
                    !
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-black text-red-400 uppercase tracking-widest mb-2">
                        Cancel Reservation
                    </p>

                    <h3 class="text-xl sm:text-2xl font-black leading-tight">
                        Are you sure?
                    </h3>

                    <p class="text-sm text-gray-300 mt-3 leading-6">
                        Do you really want to cancel this reservation? This will mark your reservation as cancelled.
                    </p>

                    <p class="text-xs text-gray-400 mt-3 leading-5">
                        If the reservation fee has already been paid, it will remain recorded as paid because the reservation fee is non-refundable.
                    </p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button
                    type="button"
                    @click="closeCancelModal()"
                    class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/15 text-white text-sm font-black transition"
                >
                    Keep Reservation
                </button>

                <button
                    type="button"
                    @click="confirmCancel()"
                    class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-red-500 hover:bg-red-600 text-white text-sm font-black transition shadow-lg shadow-red-500/20"
                >
                    Yes, Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection