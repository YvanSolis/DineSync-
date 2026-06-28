@extends('layouts.customer')

@section('content')

@php
    $today = now('Asia/Manila')->toDateString();
    $currentTime = now('Asia/Manila')->format('H:i');

    $selectedReservationDate = old(
        'reservation_date',
        $reservation->reservation_date
            ? \Carbon\Carbon::parse($reservation->reservation_date)->toDateString()
            : $today
    );

    $selectedReservationTime = old(
        'reservation_time',
        $reservation->reservation_time
            ? \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i')
            : ''
    );

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

    .reservation-edit-page {
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
        .reservation-edit-page {
            background-attachment: scroll;
        }
    }

    .reservation-edit-page::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 12% 10%, rgba(249, 115, 22, 0.22), transparent 22rem),
            radial-gradient(circle at 88% 18%, rgba(255, 255, 255, 0.12), transparent 24rem),
            linear-gradient(180deg, rgba(0,0,0,0.08), rgba(0,0,0,0.24));
        pointer-events: none;
    }

    .reservation-edit-inner {
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

    .reservation-edit-shell {
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
        .reservation-edit-shell {
            grid-template-columns: 1fr;
        }

        .reservation-info-panel {
            position: static !important;
            order: 2;
        }

        .reservation-form-card {
            order: 1;
        }
    }

    @media (max-width: 767px) {
        .reservation-edit-inner {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            padding-top: 1rem !important;
            padding-bottom: 1.5rem !important;
        }

        .mobile-hide {
            display: none !important;
        }

        .reservation-edit-header {
            padding: 1rem !important;
            border-radius: 1.25rem !important;
            margin-bottom: 1rem !important;
        }

        .reservation-edit-header h1 {
            font-size: 1.45rem !important;
            line-height: 1.15 !important;
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

        .form-input-clean {
            border-radius: 0.9rem;
            font-size: 0.85rem;
            padding-top: 0.72rem;
            padding-bottom: 0.72rem;
        }

        textarea.form-input-clean {
            min-height: 6rem;
        }

        .reservation-submit-card button,
        .reservation-submit-card a {
            width: 100% !important;
        }
    }
</style>

<div class="reservation-edit-page">
    <section class="reservation-edit-inner max-w-7xl mx-auto px-4 sm:px-6 py-5 sm:py-8 pb-10 sm:pb-12">

        <div class="reservation-edit-header glass-card rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-8 mb-5 sm:mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 sm:gap-6">
                <div class="flex items-start gap-4">
                    <div class="mobile-hide w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black shrink-0">
                        D+
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-black text-orange-600 uppercase tracking-widest mb-2">
                            Edit Reservation
                        </p>

                        <h1 class="text-3xl md:text-4xl font-black text-gray-950 tracking-tight">
                            Update your reservation
                        </h1>

                        <p class="text-sm sm:text-base text-gray-600 mt-2 max-w-2xl leading-7">
                            You can update your reservation details while it is still pending and unpaid.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('customer.reservations.index') }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center px-6 py-3 rounded-2xl bg-gray-950 hover:bg-gray-800 text-white font-black transition"
                >
                    Back to Reservations
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

        <div class="reservation-edit-shell">

            <aside class="reservation-info-panel lg:sticky lg:top-24">
                <div class="dark-card rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-6 text-white overflow-hidden relative">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_90%_10%,rgba(249,115,22,0.24),transparent_18rem)]"></div>

                    <div class="relative z-10">
                        <div class="mobile-hide w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-xl font-black mb-5 shadow-lg shadow-orange-500/30">
                            RSV
                        </div>

                        <p class="text-xs sm:text-sm font-black text-orange-300 uppercase tracking-widest">
                            Reservation #{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}
                        </p>

                        <h2 class="mt-3 text-3xl sm:text-4xl font-black">
                            ₱{{ number_format($reservationFee, 2) }}
                        </h2>

                        <p class="text-sm text-gray-300 mt-4 leading-6">
                            Reservation fee remains non-refundable and is used to secure your table schedule.
                        </p>

                        <div class="payment-info-box mt-6 space-y-5">
                            <div>
                                <p class="text-xs font-black text-orange-300 uppercase tracking-widest">
                                    Current Status
                                </p>
                                <p class="text-sm font-bold text-white mt-1">
                                    {{ ucfirst($reservation->status ?? 'pending') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-black text-orange-300 uppercase tracking-widest">
                                    Payment Status
                                </p>
                                <p class="text-sm font-bold text-white mt-1">
                                    {{ ucfirst($reservation->payment_status ?? 'pending') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-black text-orange-300 uppercase tracking-widest">
                                    Reminder
                                </p>
                                <p class="text-sm text-gray-300 mt-1 leading-6">
                                    Once the reservation is paid, approved, arrived, seated, completed, or cancelled, editing will be disabled.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="reservation-form-card glass-card rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-8">
                <form method="POST" action="{{ route('customer.reservations.update', $reservation) }}" class="reservation-form flex flex-col gap-8">
                    @csrf
                    @method('PATCH')

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
                                    Update your contact information.
                                </p>
                            </div>
                        </div>

                        <div class="reservation-field-grid grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="reservation-field">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                                <input
                                    type="text"
                                    name="customer_name"
                                    value="{{ old('customer_name', $reservation->customer_name) }}"
                                    maxlength="255"
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
                                    value="{{ old('customer_email', $reservation->customer_email) }}"
                                    maxlength="255"
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
                                value="{{ old('customer_phone', $reservation->customer_phone) }}"
                                maxlength="30"
                                class="form-input-clean"
                                placeholder="Example: 0912 345 6789"
                                required
                            >
                            @error('customer_phone')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

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
                                    Update your preferred date, time, and guest count.
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
                                @error('reservation_time')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="reservation-field">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Guest Count</label>
                                <input
                                    type="number"
                                    name="guest_count"
                                    value="{{ old('guest_count', $reservation->guest_count) }}"
                                    min="1"
                                    max="30"
                                    class="form-input-clean"
                                    placeholder="Example: 4"
                                    required
                                >
                                @error('guest_count')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

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
                                    Update any special request or instruction.
                                </p>
                            </div>
                        </div>

                        <div class="reservation-field">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Notes</label>
                            <textarea
                                name="notes"
                                rows="4"
                                maxlength="1000"
                                class="form-input-clean resize-none"
                                placeholder="Optional: preferred seat, birthday request, allergies, etc."
                            >{{ old('notes', $reservation->notes) }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="reservation-form-section">
                        <div class="reservation-submit-card rounded-[1.5rem] sm:rounded-[2rem] bg-gray-950 p-5 sm:p-6 text-white">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                                <div>
                                    <p class="text-xs font-black text-orange-300 uppercase tracking-widest">
                                        Final Step
                                    </p>

                                    <h3 class="text-xl sm:text-2xl font-black mt-2">
                                        Save reservation changes
                                    </h3>

                                    <p class="text-sm text-gray-300 mt-2 leading-6 max-w-2xl">
                                        Your reservation details will be updated after saving.
                                    </p>
                                </div>

                                <div class="shrink-0 w-full lg:w-auto flex flex-col sm:flex-row gap-3">
                                    <a
                                        href="{{ route('customer.reservations.index') }}"
                                        class="w-full lg:w-auto inline-flex items-center justify-center px-7 py-4 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-black transition"
                                    >
                                        Cancel
                                    </a>

                                    <button
                                        type="submit"
                                        class="w-full lg:w-auto inline-flex items-center justify-center px-7 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black transition shadow-lg shadow-orange-500/25"
                                    >
                                        Update Reservation
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs sm:text-sm text-gray-500 mt-4 leading-6">
                            Editing is only available for pending and unpaid reservations.
                        </p>
                    </div>

                </form>
            </div>

        </div>
    </section>
</div>

@endsection