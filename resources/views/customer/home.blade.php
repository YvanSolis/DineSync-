@extends('layouts.customer')

@section('content')

@php
    $topBestSeller = $bestSellers[0] ?? null;
@endphp

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<!-- HERO CAROUSEL -->
<section class="max-w-7xl mx-auto px-6 pt-8 pb-10">
    <div
        x-data="{
            activeSlide: 0,
            totalSlides: 3,
            next() {
                this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
            },
            prev() {
                this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
            }
        }"
        class="relative bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden min-h-[560px]"
    >

        <!-- SLIDE 1 -->
        <div
            x-cloak
            x-show="activeSlide === 0"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-x-8 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-x-8 scale-[0.98]"
            class="absolute inset-0 grid grid-cols-1 lg:grid-cols-2 min-h-[560px]"
        >
            <div class="p-8 md:p-12 lg:p-14 flex items-center">
                <div class="max-w-2xl relative z-40">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-50 text-orange-600 text-sm font-semibold mb-6">
                        <span>🍽️</span>
                        <span>Chef Oppa Customer Portal</span>
                    </div>

                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight text-gray-950 mb-6">
                        Korean meals made easy to browse and reserve.
                    </h1>

                    <p class="text-gray-500 text-base md:text-lg leading-8 mb-8 max-w-xl">
                        Check available dishes, view today’s best sellers, and plan your visit through DineSync+.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button
                            type="button"
                            onclick="window.location.href='{{ url('/menu') }}'"
                            class="relative z-50 inline-flex items-center justify-center px-7 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
                        >
                            Browse Menu
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-6 mt-12 max-w-lg">
                        <div>
                            <p class="text-2xl font-extrabold text-gray-950">8</p>
                            <p class="text-xs text-gray-500 mt-1">Menu Categories</p>
                        </div>

                        <div>
                            <p class="text-2xl font-extrabold text-gray-950">Open</p>
                            <p class="text-xs text-gray-500 mt-1">Today</p>
                        </div>

                        <div>
                            <p class="text-2xl font-extrabold text-gray-950">Fresh</p>
                            <p class="text-xs text-gray-500 mt-1">Meals Daily</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-orange-50/70 p-8 md:p-12 flex items-center justify-center">
                <div class="w-full max-w-md bg-white border border-orange-100 rounded-3xl shadow-sm p-6">
                    <div class="h-64 rounded-3xl bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center mb-6">
                        <span class="text-8xl">🍱</span>
                    </div>

                    <p class="text-sm font-semibold text-orange-500">Welcome to DineSync+</p>
                    <h3 class="text-2xl font-bold text-gray-950 mt-1">Your dining guide for Chef Oppa</h3>
                    <p class="text-sm text-gray-500 leading-6 mt-2">
                        Browse meals, check availability, and manage your reservations in one customer portal.
                    </p>
                </div>
            </div>
        </div>

        <!-- SLIDE 2 -->
        <div
            x-cloak
            x-show="activeSlide === 1"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-x-8 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-x-8 scale-[0.98]"
            class="absolute inset-0 grid grid-cols-1 lg:grid-cols-2 min-h-[560px] bg-[#0f172a]"
        >
            <div class="p-8 md:p-12 lg:p-14 flex items-center">
                <div class="max-w-2xl text-white relative z-40">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-orange-300 text-sm font-semibold mb-6">
                        <span>🔥</span>
                        <span>Best Seller</span>
                    </div>

                    @if ($topBestSeller)
                        <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-5">
                            {{ $topBestSeller['name'] }}
                        </h1>

                        <p class="text-orange-300 text-lg mb-3">
                            {{ $topBestSeller['category'] }}
                        </p>

                        <p class="text-gray-300 text-base md:text-lg leading-8 mb-8 max-w-xl">
                            {{ $topBestSeller['description'] ?? 'One of the most ordered dishes by customers. A great choice if you want to try a popular Chef Oppa favorite.' }}
                        </p>

                        <div class="flex flex-wrap items-center gap-4">
                            <div class="px-5 py-3 rounded-2xl bg-white/10 border border-white/10">
                                <p class="text-sm text-gray-300">Price</p>
                                <p class="text-2xl font-bold text-orange-300">
                                    ₱{{ number_format($topBestSeller['price'], 2) }}
                                </p>
                            </div>

                            <div class="px-5 py-3 rounded-2xl bg-white/10 border border-white/10">
                                <p class="text-sm text-gray-300">Status</p>
                                <p class="text-2xl font-bold text-green-300">
                                    {{ $topBestSeller['status'] }}
                                </p>
                            </div>

                            @if (isset($topBestSeller['total_sold']))
                                <div class="px-5 py-3 rounded-2xl bg-white/10 border border-white/10">
                                    <p class="text-sm text-gray-300">Sold</p>
                                    <p class="text-2xl font-bold text-white">
                                        {{ $topBestSeller['total_sold'] }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <button
                            type="button"
                            onclick="window.location.href='{{ url('/menu') }}'"
                            class="relative z-50 inline-flex items-center justify-center mt-8 px-7 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
                        >
                            View Full Menu
                        </button>
                    @else
                        <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-5">
                            Best seller will appear here soon.
                        </h1>

                        <p class="text-gray-300 text-base md:text-lg leading-8 mb-8 max-w-xl">
                            Once customer orders are recorded, the system will automatically show the best-selling item here.
                        </p>

                        <button
                            type="button"
                            onclick="window.location.href='{{ url('/menu') }}'"
                            class="relative z-50 inline-flex items-center justify-center px-7 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
                        >
                            Browse Menu
                        </button>
                    @endif
                </div>
            </div>

            <div class="p-8 md:p-12 flex items-center justify-center relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500/10 via-transparent to-orange-300/10"></div>

                @if (!empty($topBestSeller['image']))
                    <div class="relative z-10 w-full max-w-[520px] flex items-center justify-center">
                        <img
                            src="{{ asset('storage/' . $topBestSeller['image']) }}"
                            alt="{{ $topBestSeller['name'] }}"
                            class="w-full max-h-[420px] object-contain drop-shadow-[0_20px_40px_rgba(0,0,0,0.45)]"
                        >
                    </div>
                @else
                    <div class="relative z-10 w-full max-w-[520px] flex items-center justify-center">
                        <div class="w-[340px] h-[340px] rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
                            <span class="text-[160px]">🍲</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- SLIDE 3 -->
        <div
            x-cloak
            x-show="activeSlide === 2"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-x-8 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-x-8 scale-[0.98]"
            class="absolute inset-0 grid grid-cols-1 lg:grid-cols-2 min-h-[560px]"
        >
            <div class="p-8 md:p-12 lg:p-14 flex items-center bg-white">
                <div class="max-w-2xl relative z-40">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-50 text-orange-600 text-sm font-semibold mb-6">
                        <span>⭐</span>
                        <span>Recommended Today</span>
                    </div>

                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight text-gray-950 mb-6">
                        Not sure what to order? Start with today’s picks.
                    </h1>

                    <p class="text-gray-500 text-base md:text-lg leading-8 mb-8 max-w-xl">
                        These recommendations help customers quickly choose meals that are available and worth trying today.
                    </p>

                    <button
                        type="button"
                        onclick="window.location.href='{{ url('/menu') }}'"
                        class="relative z-50 inline-flex items-center justify-center px-7 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
                    >
                        See All Menu Items
                    </button>
                </div>
            </div>

            <div class="bg-[#111827] p-8 md:p-12 flex items-center justify-center">
                <div class="w-full max-w-md">
                    <div class="mb-5">
                        <p class="text-orange-400 text-sm font-semibold">Today’s Picks</p>
                        <h3 class="text-2xl font-bold text-white mt-1">Recommended dishes</h3>
                    </div>

                    <div class="space-y-4">
                        @forelse (array_slice($recommendedToday, 0, 3) as $item)
                            <div class="bg-white/5 border border-white/10 rounded-2xl p-5 flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-white">{{ $item['name'] }}</p>
                                    <p class="text-sm text-gray-400 mt-1">{{ $item['category'] }}</p>
                                </div>

                                <div class="text-right">
                                    <p class="font-bold text-orange-400">
                                        ₱{{ number_format($item['price'], 2) }}
                                    </p>
                                    <p class="text-xs text-green-400 mt-1">
                                        {{ $item['status'] }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-center">
                                <p class="text-sm text-gray-300">No recommended items yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTROLS -->
        <button
            type="button"
            @click="prev"
            class="absolute left-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white border border-gray-200 shadow-sm hover:bg-gray-50 flex items-center justify-center text-xl"
        >
            ‹
        </button>

        <button
            type="button"
            @click="next"
            class="absolute right-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white border border-gray-200 shadow-sm hover:bg-gray-50 flex items-center justify-center text-xl"
        >
            ›
        </button>

        <!-- DOTS -->
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2">
            <button
                type="button"
                @click="activeSlide = 0"
                class="h-2.5 rounded-full transition-all"
                :class="activeSlide === 0 ? 'w-8 bg-orange-500' : 'w-2.5 bg-gray-300'"
            ></button>

            <button
                type="button"
                @click="activeSlide = 1"
                class="h-2.5 rounded-full transition-all"
                :class="activeSlide === 1 ? 'w-8 bg-orange-500' : 'w-2.5 bg-gray-300'"
            ></button>

            <button
                type="button"
                @click="activeSlide = 2"
                class="h-2.5 rounded-full transition-all"
                :class="activeSlide === 2 ? 'w-8 bg-orange-500' : 'w-2.5 bg-gray-300'"
            ></button>
        </div>
    </div>
</section>

<!-- RESERVATION STATUS NOTICE -->
@if ($latestReservation)
    <section class="max-w-7xl mx-auto px-6 pb-4">
        @if ($latestReservation->status === 'approved')
            <div class="bg-green-50 border border-green-200 rounded-2xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-green-100 text-green-700 flex items-center justify-center text-xl">
                        ✅
                    </div>

                    <div>
                        <h3 class="font-bold text-green-800">
                            Your reservation has been approved.
                        </h3>

                        <p class="text-sm text-green-700 mt-1">
                            You have an approved reservation on
                            <span class="font-semibold">
                                {{ \Carbon\Carbon::parse($latestReservation->reservation_date)->format('F d, Y') }}
                            </span>
                            at
                            <span class="font-semibold">
                                {{ \Carbon\Carbon::parse($latestReservation->reservation_time)->format('h:i A') }}
                            </span>.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('customer.reservations.index') }}"
                    class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition"
                >
                    View Reservation
                </a>
            </div>
        @elseif ($latestReservation->status === 'pending')
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-yellow-100 text-yellow-700 flex items-center justify-center text-xl">
                        ⏳
                    </div>

                    <div>
                        <h3 class="font-bold text-yellow-800">
                            Your reservation is still pending.
                        </h3>

                        <p class="text-sm text-yellow-700 mt-1">
                            Please wait while the admin verifies your reservation fee payment.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('customer.reservations.index') }}"
                    class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold transition"
                >
                    Check Status
                </a>
            </div>
        @endif
    </section>
@endif

<!-- RESERVATION CTA -->
<section class="max-w-7xl mx-auto px-6 py-10">
    <div class="bg-[#111827] text-white rounded-3xl shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-center p-8 md:p-10">

            <div class="lg:col-span-2">
                <p class="text-sm font-semibold text-orange-400 mb-2">Table Reservation</p>
                <h2 class="text-3xl font-bold leading-tight">Secure your table before visiting Chef Oppa.</h2>
                <p class="text-gray-300 mt-3 leading-7 max-w-2xl">
                    A ₱{{ number_format($settings->reservation_fee, 2) }} non-refundable reservation fee is required to secure your table.
                    After submitting your payment proof, you can track the status in Reservations.
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <a href="{{ route('customer.reservations.create') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition">
                    Make Reservation
                </a>

                <a href="{{ route('customer.reservations.index') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold transition">
                    View Reservation Status
                </a>
            </div>

        </div>
    </div>
</section>

<!-- RESTAURANT INFO -->
<section id="restaurant-info" class="max-w-7xl mx-auto px-6 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8">
            <p class="text-sm font-semibold text-orange-500 mb-2">Restaurant Details</p>
            <h2 class="text-3xl font-bold text-gray-950 mb-6">Before you visit</h2>

            <div class="space-y-5">
                <div class="flex gap-4">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                        📍
                    </div>

                    <div>
                        <p class="font-semibold text-gray-950">Address</p>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $settings->address ?? 'Address not set yet.' }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                        ⏰
                    </div>

                    <div>
                        <p class="font-semibold text-gray-950">Opening Hours</p>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $settings->opening_days ?? 'Opening days not set' }}:
                            {{ $settings->opening_time ?? 'Opening time not set' }}
                            -
                            {{ $settings->closing_time ?? 'Closing time not set' }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                        📞
                    </div>

                    <div>
                        <p class="font-semibold text-gray-950">Contact Number</p>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $settings->contact_number ?? 'Contact number not set yet.' }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                        🌐
                    </div>

                    <div>
                        <p class="font-semibold text-gray-950">Social Media</p>

                        <div class="text-sm text-gray-500 mt-1 flex flex-wrap gap-3">
                            @if ($settings->facebook_url)
                                <a
                                    href="{{ $settings->facebook_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="hover:text-orange-500 font-medium"
                                >
                                    Facebook
                                </a>
                            @endif

                            @if ($settings->instagram_url)
                                <a
                                    href="{{ $settings->instagram_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="hover:text-orange-500 font-medium"
                                >
                                    Instagram
                                </a>
                            @endif

                            @if ($settings->tiktok_url)
                                <a
                                    href="{{ $settings->tiktok_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="hover:text-orange-500 font-medium"
                                >
                                    TikTok
                                </a>
                            @endif

                            @if (!$settings->facebook_url && !$settings->instagram_url && !$settings->tiktok_url)
                                <span>No social media links set yet.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            @if ($settings->map_embed_url)
                <div class="h-[350px] rounded-2xl overflow-hidden border border-gray-200">
                    <iframe
                        src="{{ $settings->map_embed_url }}"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            @else
                <div class="h-[350px] rounded-2xl border border-dashed border-gray-300 flex items-center justify-center text-center px-6">
                    <div>
                        <div class="text-5xl mb-4">🗺️</div>
                        <p class="font-semibold text-gray-700">Map not set yet</p>
                        <p class="text-sm text-gray-500 mt-1">
                            Add the Google Maps embed URL in admin settings.
                        </p>
                    </div>
                </div>
            @endif

            @if ($settings->google_maps_url)
                <a
                    href="{{ $settings->google_maps_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-4 inline-flex items-center justify-center w-full px-5 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
                >
                    Open in Google Maps
                </a>
            @endif
        </div>

    </div>
</section>

@endsection