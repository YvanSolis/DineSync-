@extends('layouts.customer')

@section('content')

@php
    $today = now('Asia/Manila')->toDateString();
    $currentTime = now('Asia/Manila')->format('H:i');
    $selectedReservationDate = old('reservation_date', $today);
    $selectedReservationTime = old('reservation_time');

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

    html,
    body {
        background-image:
            linear-gradient(
                135deg,
                rgba(15, 23, 42, 0.72),
                rgba(67, 31, 12, 0.62)
            ),
            url('{{ asset('images/customer-menu/kds-background.png') }}');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-color: #0f172a;
    }

    .reservation-create-page {
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
        background-attachment: fixed;
    }

    @media (max-width: 1023px) {
        html,
        body,
        .reservation-create-page {
            background-attachment: scroll;
        }
    }

    .reservation-create-page::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 12% 10%, rgba(249, 115, 22, 0.22), transparent 22rem),
            radial-gradient(circle at 88% 18%, rgba(255, 255, 255, 0.12), transparent 24rem),
            linear-gradient(180deg, rgba(0,0,0,0.08), rgba(0,0,0,0.24));
        pointer-events: none;
    }

    .reservation-create-inner {
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
            radial-gradient(circle at 85% 12%, rgba(249, 115, 22, 0.24), transparent 18rem),
            linear-gradient(135deg, #111827 0%, #020617 100%);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
    }

    .form-input-clean {
        width: 100%;
        border-radius: 1rem;
        border-color: #e5e7eb;
        background: rgba(248, 250, 252, 0.92);
        transition: all 160ms ease;
        font-size: 0.95rem;
    }

    .form-input-clean:focus {
        border-color: #fdba74;
        box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.18);
    }

    .reservation-create-shell {
        display: grid;
        grid-template-columns: 360px minmax(0, 1fr);
        gap: 1.5rem;
        align-items: start;
    }

    .reservation-step {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .reservation-step-number {
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        background: #fff7ed;
        color: #ea580c;
        border: 1px solid #fed7aa;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        flex-shrink: 0;
    }

    .reservation-form-section {
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }

    .reservation-form-section:first-child {
        padding-top: 0;
        border-top: 0;
    }

    .payment-info-box {
        border-radius: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.1);
        padding: 1.25rem;
        backdrop-filter: blur(10px);
    }

    @media (max-width: 1023px) {
        .reservation-create-shell {
            grid-template-columns: 1fr;
        }

        .reservation-payment-panel {
            position: static !important;
            order: 2;
        }

        .reservation-form-card {
            order: 1;
        }
    }

    @media (max-width: 767px) {
        .reservation-create-inner {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            padding-top: 1rem !important;
            padding-bottom: 1.5rem !important;
        }

        .mobile-hide {
            display: none !important;
        }

        .reservation-create-header {
            padding: 1rem !important;
            border-radius: 1.25rem !important;
            margin-bottom: 1rem !important;
        }

        .reservation-create-header h1 {
            font-size: 1.45rem !important;
            line-height: 1.15 !important;
        }

        .reservation-create-header p {
            font-size: 0.82rem !important;
            line-height: 1.45 !important;
            margin-top: 0.4rem !important;
        }

        .reservation-create-header a {
            padding-top: 0.8rem !important;
            padding-bottom: 0.8rem !important;
            border-radius: 1rem !important;
            font-size: 0.82rem !important;
        }

        .reservation-form-card {
            padding: 1rem !important;
            border-radius: 1.25rem !important;
        }

        .reservation-form {
            gap: 1.25rem !important;
        }

        .reservation-form-section {
            padding-top: 1.25rem;
        }

        .reservation-step {
            gap: 0.75rem;
            margin-bottom: 0.9rem;
        }

        .reservation-step-number {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.85rem;
            font-size: 0.8rem;
        }

        .reservation-step h2 {
            font-size: 1rem !important;
            line-height: 1.25 !important;
        }

        .reservation-step p {
            font-size: 0.78rem !important;
            line-height: 1.35 !important;
            margin-top: 0.2rem !important;
        }

        .reservation-field-grid {
            gap: 0.8rem !important;
        }

        .reservation-field label {
            font-size: 0.78rem !important;
            margin-bottom: 0.4rem !important;
        }

        .reservation-field p {
            font-size: 0.7rem !important;
            margin-top: 0.35rem !important;
            line-height: 1.35 !important;
        }

        .form-input-clean {
            border-radius: 0.9rem;
            font-size: 0.85rem;
            padding-top: 0.72rem;
            padding-bottom: 0.72rem;
        }

        textarea.form-input-clean {
            min-height: 6rem;
        }

        .reservation-payment-panel .dark-card {
            padding: 1rem !important;
            border-radius: 1.25rem !important;
        }

        .reservation-payment-panel h2 {
            font-size: 1.75rem !important;
            margin-top: 0.35rem !important;
        }

        .reservation-payment-panel p {
            font-size: 0.8rem !important;
            line-height: 1.45 !important;
        }

        .payment-info-box {
            padding: 0.9rem !important;
            border-radius: 1rem !important;
            margin-top: 1rem !important;
            gap: 0.8rem !important;
        }

        .payment-info-box > div {
            display: grid;
            grid-template-columns: 105px 1fr;
            gap: 0.7rem;
            align-items: start;
        }

        .payment-info-box .payment-label {
            margin: 0 !important;
            font-size: 0.65rem !important;
            line-height: 1.3 !important;
        }

        .payment-info-box .payment-text {
            margin: 0 !important;
            font-size: 0.75rem !important;
            line-height: 1.35 !important;
        }

        .reservation-reminder {
            padding: 0.9rem !important;
            border-radius: 1rem !important;
            margin-top: 1rem !important;
        }

        .reservation-submit-card {
            padding: 1rem !important;
            border-radius: 1.25rem !important;
        }

        .reservation-submit-card h3 {
            font-size: 1.1rem !important;
            line-height: 1.25 !important;
        }

        .reservation-submit-card p {
            font-size: 0.78rem !important;
            line-height: 1.45 !important;
        }

        .reservation-submit-card button {
            width: 100% !important;
            padding-top: 0.85rem !important;
            padding-bottom: 0.85rem !important;
            border-radius: 1rem !important;
            font-size: 0.85rem !important;
        }

        .reservation-terms {
            font-size: 0.72rem !important;
            line-height: 1.45 !important;
            margin-top: 0.75rem !important;
        }
    }
</style>

<div class="reservation-create-page">
    <section class="reservation-create-inner max-w-7xl mx-auto px-4 sm:px-6 py-5 sm:py-8 pb-10 sm:pb-12">

        <!-- PAGE HEADER -->
        <div class="reservation-create-header glass-card rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-8 mb-5 sm:mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 sm:gap-6">
                <div class="flex items-start gap-4">
                    <div class="mobile-hide w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black shrink-0">
                        D+
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-black text-orange-600 uppercase tracking-widest mb-2">
                            Table Reservation
                        </p>

                        <h1 class="text-3xl md:text-4xl font-black text-gray-950 tracking-tight">
                            Reserve your table
                        </h1>

                        <p class="text-sm sm:text-base text-gray-600 mt-2 max-w-2xl leading-7">
                            Fill out your reservation details, then proceed to Xendit secure checkout to pay your reservation fee.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('customer.reservations.index') }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center px-6 py-3 rounded-2xl bg-gray-950 hover:bg-gray-800 text-white font-black transition"
                >
                    View My Reservations
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

        <div class="reservation-create-shell">

            <!-- LEFT PAYMENT PANEL -->
            <aside class="reservation-payment-panel lg:sticky lg:top-24">
                <div class="dark-card rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-6 text-white overflow-hidden relative">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_90%_10%,rgba(249,115,22,0.24),transparent_18rem)]"></div>

                    <div class="relative z-10">
                        <div class="mobile-hide w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-2xl font-black mb-5 shadow-lg shadow-orange-500/30">
                            ₱
                        </div>

                        <p class="text-xs sm:text-sm font-black text-orange-300 uppercase tracking-widest">
                            Reservation Fee
                        </p>

                        <h2 class="mt-3 text-3xl sm:text-4xl font-black">
                            ₱{{ number_format($reservationFee, 2) }}
                        </h2>

                        <p class="text-sm text-gray-300 mt-4 leading-6">
                            This non-refundable fee is required to secure your table reservation.
                        </p>

                        <div class="payment-info-box mt-6 space-y-5">
                            <div>
                                <p class="payment-label text-xs font-black text-orange-300 uppercase tracking-widest">
                                    Secure Checkout
                                </p>
                                <p class="payment-text text-sm text-gray-300 mt-1 leading-6">
                                    You will be redirected to Xendit after submitting your reservation.
                                </p>
                            </div>

                            <div>
                                <p class="payment-label text-xs font-black text-orange-300 uppercase tracking-widest">
                                    Payment Methods
                                </p>
                                <p class="payment-text text-sm font-bold text-white mt-1">
                                    GCash, Maya, Cards, and other Xendit methods
                                </p>
                            </div>

                            <div>
                                <p class="payment-label text-xs font-black text-orange-300 uppercase tracking-widest">
                                    Auto Verification
                                </p>
                                <p class="payment-text text-sm text-gray-300 mt-1 leading-6">
                                    Payment status updates automatically after Xendit confirmation.
                                </p>
                            </div>
                        </div>

                        <div class="reservation-reminder mt-6 rounded-3xl bg-orange-500/15 border border-orange-300/20 p-4 sm:p-5">
                            <p class="text-sm font-black text-orange-200">
                                Reminder
                            </p>

                            <p class="text-sm text-gray-300 mt-2 leading-6">
                                Please complete the payment after submitting so the restaurant can verify your reservation faster.
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- FORM CARD -->
            <div class="reservation-form-card glass-card rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-8">
                <form method="POST" action="{{ route('customer.reservations.store') }}" class="reservation-form flex flex-col gap-8">
                    @csrf

                    <!-- CUSTOMER DETAILS -->
                    <div class="reservation-form-section">
                        <div class="reservation-step">
                            <div class="reservation-step-number">
                                01
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-black text-gray-950">
                                    Customer Details
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Your contact information for reservation updates.
                                </p>
                            </div>
                        </div>

                        <div class="reservation-field-grid grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="reservation-field">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                                <input
                                    type="text"
                                    name="customer_name"
                                    value="{{ old('customer_name', auth()->user()->name) }}"
                                    class="form-input-clean"
                                    required
                                >
                                @error('customer_name')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="reservation-field">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                                <input
                                    type="email"
                                    name="customer_email"
                                    value="{{ old('customer_email', auth()->user()->email) }}"
                                    class="form-input-clean"
                                    required
                                >
                                @error('customer_email')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="reservation-field mt-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Contact Number</label>
                            <input
                                type="text"
                                name="customer_phone"
                                value="{{ old('customer_phone') }}"
                                class="form-input-clean"
                                placeholder="Example: 0912 345 6789"
                                required
                            >
                            @error('customer_phone')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- SCHEDULE -->
                    <div class="reservation-form-section">
                        <div class="reservation-step">
                            <div class="reservation-step-number">
                                02
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-black text-gray-950">
                                    Reservation Schedule
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Select your preferred date, time, and guest count.
                                </p>
                            </div>
                        </div>

                        <div
                            x-data="{
                                selectedDate: @json($selectedReservationDate),
                                selectedTime: @json($selectedReservationTime),
                                today: @json($today),
                                currentTime: @json($currentTime),
                                isPastSlot(slot) {
                                    return this.selectedDate === this.today && slot <= this.currentTime;
                                },
                                clearInvalidTime() {
                                    if (this.selectedTime && this.isPastSlot(this.selectedTime)) {
                                        this.selectedTime = '';
                                    }
                                }
                            }"
                            x-init="$watch('selectedDate', () => clearInvalidTime()); clearInvalidTime();"
                            class="reservation-field-grid grid grid-cols-1 md:grid-cols-3 gap-5"
                        >
                            <div class="reservation-field">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Date</label>
                                <input
                                    type="date"
                                    name="reservation_date"
                                    x-model="selectedDate"
                                    min="{{ $today }}"
                                    class="form-input-clean"
                                    required
                                >
                                <p class="mt-2 text-xs font-semibold text-gray-500">
                                    Past dates are disabled.
                                </p>
                                @error('reservation_date')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="reservation-field">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Time</label>
                                <select
                                    name="reservation_time"
                                    x-model="selectedTime"
                                    class="form-input-clean"
                                    required
                                >
                                    <option value="">Select time</option>

                                    @foreach ($reservationTimeSlots as $slot)
                                        <option
                                            value="{{ $slot['value'] }}"
                                            :disabled="isPastSlot('{{ $slot['value'] }}')"
                                        >
                                            {{ $slot['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs font-semibold text-gray-500">
                                    Available from 11:00 AM to 7:00 PM.
                                </p>
                                @error('reservation_time')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="reservation-field">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Guest Count</label>
                                <input
                                    type="number"
                                    name="guest_count"
                                    value="{{ old('guest_count') }}"
                                    min="1"
                                    max="20"
                                    class="form-input-clean"
                                    placeholder="Example: 4"
                                    required
                                >
                                <p class="mt-2 text-xs font-semibold text-gray-500">
                                    Enter number of guests.
                                </p>
                                @error('guest_count')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- NOTES -->
                    <div class="reservation-form-section">
                        <div class="reservation-step">
                            <div class="reservation-step-number">
                                03
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-black text-gray-950">
                                    Additional Notes
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Add any special request or instruction for the restaurant.
                                </p>
                            </div>
                        </div>

                        <div class="reservation-field">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Notes</label>
                            <textarea
                                name="notes"
                                rows="4"
                                class="form-input-clean resize-none"
                                placeholder="Optional: preferred seat, birthday request, allergies, etc."
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- SUMMARY / SUBMIT -->
                    <div class="reservation-form-section">
                        <div class="reservation-submit-card rounded-[1.5rem] sm:rounded-[2rem] bg-gray-950 p-5 sm:p-6 text-white">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                                <div>
                                    <p class="text-xs font-black text-orange-300 uppercase tracking-widest">
                                        Final Step
                                    </p>

                                    <h3 class="text-xl sm:text-2xl font-black mt-2">
                                        Submit reservation and continue to payment
                                    </h3>

                                    <p class="text-sm text-gray-300 mt-2 leading-6 max-w-2xl">
                                        You will be redirected to Xendit secure checkout. Your reservation will remain pending until payment is confirmed.
                                    </p>
                                </div>

                                <div class="shrink-0 w-full lg:w-auto">
                                    <button
                                        type="submit"
                                        class="w-full lg:w-auto inline-flex items-center justify-center px-7 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black transition shadow-lg shadow-orange-500/25"
                                    >
                                        Continue to Payment
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p class="reservation-terms text-xs sm:text-sm text-gray-500 mt-4 leading-6">
                            By submitting this form, you agree that the reservation fee is non-refundable and used to secure your selected table schedule.
                        </p>
                    </div>

                </form>
            </div>

        </div>
    </section>
</div>

@endsection