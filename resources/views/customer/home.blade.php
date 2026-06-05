@extends('layouts.customer')

@section('content')

@php
    $topBestSeller = $bestSellers[0] ?? null;

    $fallbackMenuImage = 'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1200&q=80';

    $getMenuImage = function ($item) use ($fallbackMenuImage) {
        if (!$item) {
            return $fallbackMenuImage;
        }

        $image = $item['image_url'] ?? $item['image'] ?? null;

        if (!$image) {
            return $fallbackMenuImage;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        if (str_starts_with($image, '/storage/') || str_starts_with($image, '/images/')) {
            return $image;
        }

        if (str_starts_with($image, 'storage/')) {
            return asset($image);
        }

        return asset('storage/' . ltrim($image, '/'));
    };

    $topBestSellerImage = $getMenuImage($topBestSeller);
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

    .customer-home-page {
        min-height: calc(100vh - 80px);
        position: relative;
        overflow-x: hidden;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
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

    .customer-home-page::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 12% 10%, rgba(249, 115, 22, 0.22), transparent 22rem),
            radial-gradient(circle at 88% 18%, rgba(255, 255, 255, 0.12), transparent 24rem),
            linear-gradient(180deg, rgba(0,0,0,0.08), rgba(0,0,0,0.22));
        pointer-events: none;
    }

    .customer-home-inner {
        position: relative;
        z-index: 2;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.93);
        border: 1px solid rgba(255, 255, 255, 0.35);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
        backdrop-filter: blur(14px);
    }

    .dark-panel {
        background:
            radial-gradient(circle at 80% 12%, rgba(249, 115, 22, 0.24), transparent 18rem),
            linear-gradient(135deg, #111827 0%, #020617 100%);
    }

    .hero-shell {
        min-height: 600px;
    }

    .hero-slide {
        min-height: 600px;
    }

    @media (max-width: 1023px) {
        .hero-shell,
        .hero-slide {
            min-height: 900px;
        }
    }

    @media (max-width: 640px) {
        .hero-shell,
        .hero-slide {
            min-height: 980px;
        }
    }
</style>

<div class="customer-home-page">
    <div class="customer-home-inner">

        <!-- HERO CAROUSEL -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 pb-8">
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
                class="hero-shell relative overflow-hidden rounded-[2rem] border border-white/25 glass-card"
            >

                <!-- SLIDE 1 -->
                <div
                    x-cloak
                    x-show="activeSlide === 0"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 translate-x-8 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-x-8 scale-[0.98]"
                    class="hero-slide absolute inset-0 grid grid-cols-1 lg:grid-cols-[0.92fr_1.08fr]"
                >
                    <div class="relative z-10 p-8 sm:p-10 lg:p-14 flex items-center">
                        <div class="max-w-xl">
                            <div class="inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-extrabold text-orange-700 mb-6">
                                DineSync+ Customer Portal
                            </div>

                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.02] tracking-tight text-gray-950 mb-6">
                                Korean dining made easier.
                            </h1>

                            <p class="text-base sm:text-lg leading-8 text-gray-600 mb-8">
                                Browse Chef Oppa’s menu, check customer favorites, and reserve your table in one clean customer portal.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <a
                                    href="{{ url('/menu') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-7 py-4 font-extrabold text-white shadow-lg shadow-orange-500/25 transition hover:bg-orange-600"
                                >
                                    Open Menu Book
                                </a>

                                <a
                                    href="{{ route('customer.reservations.create') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-gray-950 px-7 py-4 font-extrabold text-white transition hover:bg-gray-800"
                                >
                                    Reserve a Table
                                </a>
                            </div>

                            <div class="mt-10 grid grid-cols-3 gap-3 max-w-lg">
                                <div class="rounded-3xl border border-gray-200 bg-white px-4 py-5 shadow-sm">
                                    <p class="text-2xl font-black text-gray-950">8</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">Categories</p>
                                </div>

                                <div class="rounded-3xl border border-gray-200 bg-white px-4 py-5 shadow-sm">
                                    <p class="text-2xl font-black text-orange-500">Live</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">Availability</p>
                                </div>

                                <div class="rounded-3xl border border-gray-200 bg-white px-4 py-5 shadow-sm">
                                    <p class="text-2xl font-black text-gray-950">Easy</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">Reservations</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative dark-panel p-8 sm:p-10 lg:p-14 flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 opacity-80 bg-[radial-gradient(circle_at_70%_25%,rgba(249,115,22,0.34),transparent_26rem)]"></div>

                        <div class="relative z-10 w-full max-w-[560px]">
                            <div class="rounded-[2rem] border border-white/10 bg-white/10 p-4 shadow-2xl backdrop-blur">
                                <div class="rounded-[1.5rem] bg-white p-6">
                                    <div class="rounded-[1.4rem] border border-orange-100 bg-gradient-to-br from-orange-50 via-white to-gray-50 p-7">
                                        <p class="text-xs font-black uppercase tracking-[0.26em] text-orange-500">
                                            Chef Oppa
                                        </p>

                                        <h2 class="mt-4 text-3xl font-black leading-tight text-gray-950">
                                            A clean digital restaurant experience.
                                        </h2>

                                        <p class="mt-3 text-sm leading-6 text-gray-500">
                                            Customers can view the menu, check restaurant details, and submit reservations without confusion.
                                        </p>
                                    </div>

                                    <div class="mt-5 space-y-3">
                                        <div class="flex items-center justify-between rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                                            <div>
                                                <p class="text-xs text-gray-500">Customer action</p>
                                                <p class="font-bold text-gray-950">Browse digital menu</p>
                                            </div>
                                            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700">Menu</span>
                                        </div>

                                        <div class="flex items-center justify-between rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                                            <div>
                                                <p class="text-xs text-gray-500">Customer action</p>
                                                <p class="font-bold text-gray-950">Submit table reservation</p>
                                            </div>
                                            <span class="rounded-full bg-gray-900 px-3 py-1 text-xs font-bold text-white">Reserve</span>
                                        </div>

                                        <div class="flex items-center justify-between rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                                            <div>
                                                <p class="text-xs text-gray-500">Customer action</p>
                                                <p class="font-bold text-gray-950">Track approval status</p>
                                            </div>
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">Status</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SLIDE 2 -->
                <div
                    x-cloak
                    x-show="activeSlide === 1"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 translate-x-8 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-x-8 scale-[0.98]"
                    class="hero-slide absolute inset-0 grid grid-cols-1 lg:grid-cols-[0.95fr_1.05fr] bg-[#0f172a]"
                >
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(249,115,22,0.28),transparent_32rem),radial-gradient(circle_at_0%_100%,rgba(251,146,60,0.16),transparent_26rem)]"></div>

                    <div class="relative z-10 p-8 sm:p-10 lg:p-14 flex items-center">
                        <div class="max-w-xl text-white">
                            <div class="mb-6 inline-flex items-center rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold text-orange-300">
                                Current Best Seller
                            </div>

                            @if ($topBestSeller)
                                <h1 class="mb-5 text-4xl sm:text-5xl font-black leading-[1.05] tracking-tight">
                                    {{ $topBestSeller['name'] }}
                                </h1>

                                <p class="mb-4 text-base sm:text-lg font-semibold text-orange-300">
                                    {{ $topBestSeller['category'] }}
                                </p>

                                <p class="mb-8 max-w-xl text-base sm:text-lg leading-8 text-gray-300">
                                    {{ $topBestSeller['description'] ?? 'One of the most ordered dishes by customers and a strong pick for first-time visitors.' }}
                                </p>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-xl">
                                    <div class="min-w-0 rounded-2xl border border-white/10 bg-white/10 p-4">
                                        <p class="mb-2 text-xs text-gray-300">Price</p>
                                        <p class="whitespace-nowrap text-xl font-black text-orange-300">
                                            ₱{{ number_format($topBestSeller['price'], 2) }}
                                        </p>
                                    </div>

                                    <div class="min-w-0 rounded-2xl border border-white/10 bg-white/10 p-4">
                                        <p class="mb-2 text-xs text-gray-300">Status</p>
                                        <p class="truncate text-xl font-black text-green-300">
                                            {{ $topBestSeller['status'] }}
                                        </p>
                                    </div>

                                    <div class="min-w-0 rounded-2xl border border-white/10 bg-white/10 p-4">
                                        <p class="mb-2 text-xs text-gray-300">Sold</p>
                                        <p class="truncate text-xl font-black text-white">
                                            {{ $topBestSeller['total_sold'] ?? 0 }}
                                        </p>
                                    </div>
                                </div>

                                <a
                                    href="{{ url('/menu') }}"
                                    class="mt-8 inline-flex items-center justify-center rounded-2xl bg-orange-500 px-7 py-4 font-extrabold text-white shadow-lg shadow-orange-500/25 transition hover:bg-orange-600"
                                >
                                    View Full Menu
                                </a>
                            @else
                                <h1 class="mb-5 text-4xl sm:text-5xl font-black leading-tight">
                                    Best seller will appear here soon.
                                </h1>

                                <p class="mb-8 max-w-xl text-base sm:text-lg leading-8 text-gray-300">
                                    Once customer orders are recorded, the system will automatically show the best-selling item here.
                                </p>

                                <a
                                    href="{{ url('/menu') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-7 py-4 font-extrabold text-white transition hover:bg-orange-600"
                                >
                                    Browse Menu
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="relative z-10 p-8 sm:p-10 lg:p-14 flex items-center justify-center">
                        <div class="w-full max-w-[570px]">
                            <div class="rounded-[2rem] border border-white/10 bg-white/10 p-4 shadow-2xl backdrop-blur">
                                <div class="relative h-[430px] overflow-hidden rounded-[1.5rem] bg-white">
                                    <img
                                        src="{{ $topBestSellerImage }}"
                                        alt="{{ $topBestSeller['name'] ?? 'Best seller item' }}"
                                        class="h-full w-full object-cover"
                                        onerror="this.src='{{ $fallbackMenuImage }}';"
                                    >

                                    @if (!$topBestSeller)
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                                            <div class="rounded-3xl bg-white/90 px-6 py-5 text-center shadow-xl backdrop-blur">
                                                <p class="text-lg font-black text-gray-900">Best seller will appear here soon.</p>
                                                <p class="mt-2 text-sm text-gray-500">Once orders are recorded, this section will update.</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($topBestSeller)
                                        <div class="absolute bottom-4 left-4 right-4 rounded-2xl bg-black/70 px-5 py-4 text-white backdrop-blur">
                                            <p class="text-xs font-bold uppercase tracking-widest text-orange-300">
                                                Customer favorite
                                            </p>

                                            <div class="mt-1 flex items-end justify-between gap-4">
                                                <p class="min-w-0 truncate text-lg font-bold leading-tight sm:text-xl">
                                                    {{ $topBestSeller['name'] }}
                                                </p>

                                                <p class="whitespace-nowrap font-black text-orange-300">
                                                    ₱{{ number_format($topBestSeller['price'], 2) }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 3 -->
                <div
                    x-cloak
                    x-show="activeSlide === 2"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 translate-x-8 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-x-8 scale-[0.98]"
                    class="hero-slide absolute inset-0 grid grid-cols-1 lg:grid-cols-[0.95fr_1.05fr]"
                >
                    <div class="p-8 sm:p-10 lg:p-14 flex items-center bg-white">
                        <div class="max-w-xl">
                            <div class="mb-6 inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-bold text-orange-700">
                                Recommended Today
                            </div>

                            <h1 class="mb-6 text-4xl sm:text-5xl font-black leading-[1.05] tracking-tight text-gray-950">
                                Featured dishes for today.
                            </h1>

                            <p class="mb-8 max-w-xl text-base sm:text-lg leading-8 text-gray-600">
                                Recommended picks help customers choose quickly before browsing the full menu book.
                            </p>

                            <a
                                href="{{ url('/menu') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-7 py-4 font-extrabold text-white shadow-lg shadow-orange-500/25 transition hover:bg-orange-600"
                            >
                                See All Menu Items
                            </a>
                        </div>
                    </div>

                    <div class="bg-gray-950 p-8 sm:p-10 lg:p-14 flex items-center justify-center">
                        <div class="w-full max-w-xl">
                            <div class="mb-6">
                                <p class="text-sm font-bold uppercase tracking-widest text-orange-300">Featured Picks</p>
                                <h3 class="mt-2 text-3xl font-black text-white">Recommended dishes</h3>
                            </div>

                            <div class="space-y-4">
                                @forelse (array_slice($recommendedToday, 0, 3) as $item)
                                    @php
                                        $recommendedImage = $getMenuImage($item);
                                    @endphp

                                    <div class="rounded-3xl border border-white/10 bg-white/10 p-4">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                            <div class="h-24 w-full overflow-hidden rounded-2xl bg-white/10 sm:h-24 sm:w-28 sm:flex-shrink-0">
                                                <img
                                                    src="{{ $recommendedImage }}"
                                                    alt="{{ $item['name'] }}"
                                                    class="h-full w-full object-cover"
                                                    loading="lazy"
                                                    onerror="this.src='{{ $fallbackMenuImage }}';"
                                                >
                                            </div>

                                            <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="min-w-0">
                                                    <p class="truncate text-lg font-bold text-white">{{ $item['name'] }}</p>
                                                    <p class="mt-1 truncate text-sm text-gray-400">{{ $item['category'] }}</p>
                                                </div>

                                                <div class="flex-shrink-0 sm:text-right">
                                                    <p class="whitespace-nowrap text-xl font-black text-orange-300">
                                                        ₱{{ number_format($item['price'], 2) }}
                                                    </p>
                                                    <p class="mt-1 text-xs font-semibold text-green-300">
                                                        {{ $item['status'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-3xl border border-white/10 bg-white/10 p-6 text-center">
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
                    aria-label="Previous slide"
                    class="absolute left-4 sm:left-6 top-1/2 z-30 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white/95 text-2xl text-gray-700 shadow-lg transition hover:scale-105 hover:bg-gray-50 active:scale-95"
                >
                    ‹
                </button>

                <button
                    type="button"
                    @click="next"
                    aria-label="Next slide"
                    class="absolute right-4 sm:right-6 top-1/2 z-30 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white/95 text-2xl text-gray-700 shadow-lg transition hover:scale-105 hover:bg-gray-50 active:scale-95"
                >
                    ›
                </button>

                <!-- DOTS -->
                <div class="absolute bottom-6 left-1/2 z-30 flex -translate-x-1/2 gap-2 rounded-full border border-gray-200 bg-white/90 px-3 py-2 shadow-sm">
                    <button
                        type="button"
                        @click="activeSlide = 0"
                        class="h-2.5 rounded-full transition-all duration-300"
                        :class="activeSlide === 0 ? 'w-8 bg-orange-500' : 'w-2.5 bg-gray-300'"
                        aria-label="Go to slide 1"
                    ></button>

                    <button
                        type="button"
                        @click="activeSlide = 1"
                        class="h-2.5 rounded-full transition-all duration-300"
                        :class="activeSlide === 1 ? 'w-8 bg-orange-500' : 'w-2.5 bg-gray-300'"
                        aria-label="Go to slide 2"
                    ></button>

                    <button
                        type="button"
                        @click="activeSlide = 2"
                        class="h-2.5 rounded-full transition-all duration-300"
                        :class="activeSlide === 2 ? 'w-8 bg-orange-500' : 'w-2.5 bg-gray-300'"
                        aria-label="Go to slide 3"
                    ></button>
                </div>
            </div>
        </section>

        <!-- RESERVATION STATUS NOTICE -->
        @if ($latestReservation)
            <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-4">
                @if ($latestReservation->status === 'approved')
                    <div class="glass-card rounded-[2rem] p-5 sm:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-green-50 text-green-700 flex items-center justify-center font-black text-xs border border-green-100">
                                OK
                            </div>

                            <div>
                                <h3 class="font-black text-green-800">
                                    Your reservation has been approved.
                                </h3>

                                <p class="text-sm text-green-700 mt-1 leading-6">
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
                            class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-sm font-bold transition"
                        >
                            View Reservation
                        </a>
                    </div>
                @elseif ($latestReservation->status === 'pending')
                    <div class="glass-card rounded-[2rem] p-5 sm:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-yellow-50 text-yellow-700 flex items-center justify-center font-black text-xs border border-yellow-100">
                                PND
                            </div>

                            <div>
                                <h3 class="font-black text-yellow-800">
                                    Your reservation is still pending.
                                </h3>

                                <p class="text-sm text-yellow-700 mt-1 leading-6">
                                    Please wait while the admin verifies your reservation.
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('customer.reservations.index') }}"
                            class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-bold transition"
                        >
                            Check Status
                        </a>
                    </div>
                @endif
            </section>
        @endif

        <!-- RESERVATION CTA -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            <div class="relative dark-panel text-white rounded-[2rem] shadow-xl overflow-hidden">
                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-8 items-center p-8 sm:p-10">
                    <div>
                        <p class="text-sm font-bold text-orange-300 uppercase tracking-widest mb-3">Table Reservation</p>
                        <h2 class="text-3xl sm:text-4xl font-black leading-tight">
                            Secure your table before visiting Chef Oppa.
                        </h2>
                        <p class="text-gray-300 mt-4 leading-7 max-w-3xl">
                            A ₱{{ number_format($settings->reservation_fee, 2) }} non-refundable reservation fee is required to secure your table.
                            After submitting your reservation, you can track the status in Reservations.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <a href="{{ route('customer.reservations.create') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-bold transition">
                            Make Reservation
                        </a>

                        <a href="{{ route('customer.reservations.index') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-bold transition border border-white/10">
                            View Reservation Status
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- RESTAURANT INFO -->
        <section id="restaurant-info" class="max-w-7xl mx-auto px-4 sm:px-6 py-8 pb-10">
            <div class="grid grid-cols-1 lg:grid-cols-[0.9fr_1.1fr] gap-6 items-stretch">

                <div class="glass-card rounded-[2rem] p-8">
                    <p class="text-sm font-bold text-orange-600 uppercase tracking-widest mb-3">Restaurant Details</p>
                    <h2 class="text-3xl sm:text-4xl font-black text-gray-950 mb-8">Before you visit</h2>

                    <div class="space-y-5">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-black text-xs border border-orange-100 flex-shrink-0">
                                LOC
                            </div>

                            <div class="min-w-0">
                                <p class="font-bold text-gray-950">Address</p>
                                <p class="text-sm text-gray-500 mt-1 leading-6">
                                    {{ $settings->address ?? 'Address not set yet.' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-black text-xs border border-orange-100 flex-shrink-0">
                                HRS
                            </div>

                            <div class="min-w-0">
                                <p class="font-bold text-gray-950">Opening Hours</p>
                                <p class="text-sm text-gray-500 mt-1 leading-6">
                                    {{ $settings->opening_days ?? 'Opening days not set' }}:
                                    {{ $settings->opening_time ?? 'Opening time not set' }}
                                    -
                                    {{ $settings->closing_time ?? 'Closing time not set' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-black text-xs border border-orange-100 flex-shrink-0">
                                TEL
                            </div>

                            <div class="min-w-0">
                                <p class="font-bold text-gray-950">Contact Number</p>
                                <p class="text-sm text-gray-500 mt-1 leading-6">
                                    {{ $settings->contact_number ?? 'Contact number not set yet.' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-black text-xs border border-orange-100 flex-shrink-0">
                                SOC
                            </div>

                            <div class="min-w-0">
                                <p class="font-bold text-gray-950">Social Media</p>

                                <div class="text-sm text-gray-500 mt-2 flex flex-wrap gap-3">
                                    @if ($settings->facebook_url)
                                        <a
                                            href="{{ $settings->facebook_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="hover:text-orange-600 font-semibold"
                                        >
                                            Facebook
                                        </a>
                                    @endif

                                    @if ($settings->instagram_url)
                                        <a
                                            href="{{ $settings->instagram_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="hover:text-orange-600 font-semibold"
                                        >
                                            Instagram
                                        </a>
                                    @endif

                                    @if ($settings->tiktok_url)
                                        <a
                                            href="{{ $settings->tiktok_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="hover:text-orange-600 font-semibold"
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

                <div class="glass-card rounded-[2rem] p-5 sm:p-6">
                    @if ($settings->map_embed_url)
                        <div class="h-[420px] rounded-[1.5rem] overflow-hidden border border-gray-200 bg-gray-100">
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
                        <div class="h-[420px] rounded-[1.5rem] border border-dashed border-gray-300 flex items-center justify-center text-center px-6 bg-gray-50">
                            <div>
                                <p class="font-black text-gray-800 text-lg">Map not set yet</p>
                                <p class="text-sm text-gray-500 mt-2">
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
                            class="mt-4 inline-flex items-center justify-center w-full px-5 py-3 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-bold transition"
                        >
                            Open in Google Maps
                        </a>
                    @endif
                </div>

            </div>
        </section>

    </div>
</div>

@endsection