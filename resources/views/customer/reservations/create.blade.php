@extends('layouts.customer')

@section('content')

<section class="max-w-6xl mx-auto px-6 py-10">

    <div class="mb-6">
        <p class="text-sm font-semibold text-orange-500 mb-2">Table Reservation</p>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900">Reserve your table</h1>
        <p class="text-gray-500 mt-2">
            Submit your reservation details and upload your reservation fee payment proof.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-5 py-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Reservation Fee Info -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-6 sticky top-24">
                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl mb-5">
                    ₱
                </div>

                <h2 class="text-xl font-bold text-gray-900">Reservation Fee</h2>

                <p class="text-4xl font-bold text-orange-500 mt-3">
                    ₱{{ number_format($reservationFee, 2) }}
                </p>

                <p class="text-sm text-gray-500 mt-4 leading-6">
                    This is a non-refundable reservation fee required to secure your table.
                    It will not be deducted from your final bill.
                </p>

                <div class="mt-6 border-t border-gray-100 pt-5 space-y-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">GCash Name</p>
                        <p class="text-sm text-gray-500 mt-1">Chef Oppa</p>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-900">GCash Number</p>
                        <p class="text-sm text-gray-500 mt-1">0912 345 6789</p>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-900">Status</p>
                        <p class="text-sm text-gray-500 mt-1">
                            Payment proof will be checked by the admin.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-8">

                <form method="POST" action="{{ route('customer.reservations.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Customer Details</h2>
                        <p class="text-sm text-gray-500 mt-1">Your contact information for reservation updates.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                            <input
                                type="text"
                                name="customer_name"
                                value="{{ old('customer_name', auth()->user()->name) }}"
                                class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                                required
                            >
                            @error('customer_name')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input
                                type="email"
                                name="customer_email"
                                value="{{ old('customer_email', auth()->user()->email) }}"
                                class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                                required
                            >
                            @error('customer_email')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
                        <input
                            type="text"
                            name="customer_phone"
                            value="{{ old('customer_phone') }}"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                            placeholder="Example: 0912 345 6789"
                            required
                        >
                        @error('customer_phone')
                            <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-gray-100 pt-6">
                        <h2 class="text-xl font-bold text-gray-900">Reservation Schedule</h2>
                        <p class="text-sm text-gray-500 mt-1">Select your preferred date, time, and guest count.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Date</label>
                            <input
                                type="date"
                                name="reservation_date"
                                value="{{ old('reservation_date') }}"
                                min="{{ date('Y-m-d') }}"
                                class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                                required
                            >
                            @error('reservation_date')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Time</label>
                            <input
                                type="time"
                                name="reservation_time"
                                value="{{ old('reservation_time') }}"
                                min="10:00"
                                max="21:00"
                                class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                                required
                            >
                            @error('reservation_time')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Guests</label>
                            <input
                                type="number"
                                name="guest_count"
                                value="{{ old('guest_count', 1) }}"
                                min="1"
                                max="30"
                                class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                                required
                            >
                            @error('guest_count')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-6">
                        <h2 class="text-xl font-bold text-gray-900">Payment Details</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Pay the ₱{{ number_format($reservationFee, 2) }} non-refundable reservation fee and upload your proof of payment.
                        </p>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Method</label>
                                <select
                                    name="payment_method"
                                    class="w-full rounded-xl border-gray-200 bg-white focus:border-orange-300 focus:ring-orange-200"
                                    required
                                >
                                    <option value="">Select method</option>
                                    <option value="GCash" {{ old('payment_method') === 'GCash' ? 'selected' : '' }}>GCash</option>
                                    <option value="Maya" {{ old('payment_method') === 'Maya' ? 'selected' : '' }}>Maya</option>
                                    <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                </select>
                                @error('payment_method')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Reference Number</label>
                                <input
                                    type="text"
                                    name="payment_reference"
                                    value="{{ old('payment_reference') }}"
                                    class="w-full rounded-xl border-gray-200 bg-white focus:border-orange-300 focus:ring-orange-200"
                                    placeholder="Enter payment reference number"
                                    required
                                >
                                @error('payment_reference')
                                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Proof of Payment</label>
                            <input
                                type="file"
                                name="payment_proof"
                                accept="image/png,image/jpeg,image/jpg"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-orange-300 focus:ring-orange-200"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-2">
                                Accepted files: JPG, JPEG, PNG. Maximum size: 2MB.
                            </p>
                            @error('payment_proof')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                        <textarea
                            name="notes"
                            rows="4"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                            placeholder="Example: Birthday dinner, preferred seat, special request..."
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3">
                        <button
                            type="submit"
                            class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
                        >
                            Submit Reservation
                        </button>

                        <a
                            href="{{ route('customer.home') }}"
                            class="px-6 py-3 rounded-xl border border-gray-200 hover:border-orange-500 hover:text-orange-500 font-semibold text-center transition"
                        >
                            Back to Home
                        </a>
                    </div>

                </form>
            </div>
        </div>

    </div>

</section>

@endsection