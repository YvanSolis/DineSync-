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

    .reservation-create-page {
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
    }

    .form-input-clean {
        width: 100%;
        border-radius: 1rem;
        border-color: #e5e7eb;
        background: rgba(248, 250, 252, 0.92);
        transition: all 160ms ease;
    }

    .form-input-clean:focus {
        border-color: #fdba74;
        box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.18);
    }
</style>

<div class="reservation-create-page">
    <section class="reservation-create-inner max-w-7xl mx-auto px-4 sm:px-6 py-8 pb-12">

        <!-- PAGE HEADER -->
        <div class="glass-card rounded-[2rem] p-6 sm:p-8 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black shrink-0">
                        D+
                    </div>

                    <div>
                        <p class="text-sm font-black text-orange-600 uppercase tracking-widest mb-2">
                            Table Reservation
                        </p>

                        <h1 class="text-3xl md:text-4xl font-black text-gray-950 tracking-tight">
                            Reserve your table
                        </h1>

                        <p class="text-gray-600 mt-2 max-w-2xl leading-7">
                            Fill out your reservation details, then proceed to Xendit secure checkout to pay your reservation fee.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('customer.reservations.index') }}"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-gray-950 hover:bg-gray-800 text-white font-black transition"
                >
                    View My Reservations
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

        <div class="grid grid-cols-1 lg:grid-cols-[360px_1fr] gap-6 items-start">

            <!-- LEFT PAYMENT PANEL -->
            <aside class="lg:sticky lg:top-24">
                <div class="dark-card rounded-[2rem] p-6 text-white shadow-2xl overflow-hidden relative">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_90%_10%,rgba(249,115,22,0.24),transparent_18rem)]"></div>

                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-2xl font-black mb-5 shadow-lg shadow-orange-500/30">
                            ₱
                        </div>

                        <p class="text-sm font-black text-orange-300 uppercase tracking-widest">
                            Reservation Fee
                        </p>

                        <h2 class="mt-3 text-4xl font-black">
                            ₱{{ number_format($reservationFee, 2) }}
                        </h2>

                        <p class="text-sm text-gray-300 mt-4 leading-6">
                            This non-refundable fee is required to secure your table reservation.
                        </p>

                        <div class="mt-6 rounded-3xl border border-white/10 bg-white/10 p-5 space-y-5 backdrop-blur">
                            <div>
                                <p class="text-xs font-black text-orange-300 uppercase tracking-widest">
                                    Secure Checkout
                                </p>
                                <p class="text-sm text-gray-300 mt-1 leading-6">
                                    After submitting your reservation, you will be redirected to Xendit to complete your payment.
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-black text-orange-300 uppercase tracking-widest">
                                    Payment Methods
                                </p>
                                <p class="text-sm font-bold text-white mt-1">
                                    GCash, Maya, Cards, and other available Xendit methods
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-black text-orange-300 uppercase tracking-widest">
                                    No Manual Proof Upload
                                </p>
                                <p class="text-sm text-gray-300 mt-1 leading-6">
                                    Your payment status will be updated automatically once Xendit confirms the transaction.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 rounded-3xl bg-orange-500/15 border border-orange-300/20 p-5">
                            <p class="text-sm font-black text-orange-200">
                                Reminder
                            </p>

                            <p class="text-sm text-gray-300 mt-2 leading-6">
                                Please complete the payment after submitting the form so the restaurant can verify your reservation faster.
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- FORM CARD -->
            <div class="glass-card rounded-[2rem] p-6 sm:p-8">
                <form method="POST" action="{{ route('customer.reservations.store') }}" class="space-y-8">
                    @csrf

                    <!-- CUSTOMER DETAILS -->
                    <div>
                        <div class="flex items-start gap-4 mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black shrink-0">
                                01
                            </div>

                            <div>
                                <h2 class="text-xl font-black text-gray-950">Customer Details</h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Your contact information for reservation updates.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
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

                            <div>
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

                        <div class="mt-5">
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
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex items-start gap-4 mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black shrink-0">
                                02
                            </div>

                            <div>
                                <h2 class="text-xl font-black text-gray-950">Reservation Schedule</h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Select your preferred date, time, and guest count.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Date</label>
                                <input
                                    type="date"
                                    name="reservation_date"
                                    value="{{ old('reservation_date') }}"
                                    min="{{ date('Y-m-d') }}"
                                    class="form-input-clean"
                                    required
                                >
                                @error('reservation_date')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Time</label>
                                <input
                                    type="time"
                                    name="reservation_time"
                                    value="{{ old('reservation_time') }}"
                                    min="10:00"
                                    max="21:00"
                                    class="form-input-clean"
                                    required
                                >
                                @error('reservation_time')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Guests</label>
                                <input
                                    type="number"
                                    name="guest_count"
                                    value="{{ old('guest_count', 1) }}"
                                    min="1"
                                    max="30"
                                    class="form-input-clean"
                                    required
                                >
                                @error('guest_count')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- XENDIT PAYMENT INFO -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex items-start gap-4 mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black shrink-0">
                                03
                            </div>

                            <div>
                                <h2 class="text-xl font-black text-gray-950">Secure Payment</h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    You will pay the ₱{{ number_format($reservationFee, 2) }} reservation fee through Xendit checkout.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-orange-100 bg-orange-50/80 p-5">
                            <div class="flex flex-col md:flex-row md:items-center gap-5">
                                <div class="w-14 h-14 rounded-2xl bg-white border border-orange-100 text-orange-600 flex items-center justify-center text-2xl font-black shrink-0">
                                    ✓
                                </div>

                                <div>
                                    <h3 class="text-lg font-black text-gray-950">
                                        Payment will be processed by Xendit
                                    </h3>

                                    <p class="text-sm text-gray-600 mt-2 leading-6">
                                        After clicking the button below, your reservation will be saved first, then you will be redirected to the Xendit checkout page. You can choose available payment options such as GCash, Maya, or other enabled methods.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NOTES -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex items-start gap-4 mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black shrink-0">
                                04
                            </div>

                            <div>
                                <h2 class="text-xl font-black text-gray-950">Additional Notes</h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Add special requests or reminders for the restaurant staff.
                                </p>
                            </div>
                        </div>

                        <textarea
                            name="notes"
                            rows="4"
                            class="form-input-clean"
                            placeholder="Example: Birthday dinner, preferred seat, special request..."
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ACTIONS -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black transition shadow-lg shadow-orange-500/25"
                        >
                            Proceed to Xendit Payment
                        </button>

                        <a
                            href="{{ route('customer.home') }}"
                            class="inline-flex items-center justify-center px-6 py-3 rounded-2xl border border-gray-200 bg-white hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600 font-black text-center transition"
                        >
                            Back to Home
                        </a>
                    </div>

                </form>
            </div>

        </div>

    </section>
</div>

@endsection