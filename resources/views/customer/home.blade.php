@extends('layouts.customer')

@section('content')

@php
    $fallbackMenuImage = 'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1200&q=80';

    $bestSellerItems = collect($bestSellers ?? [])->values();
    $recommendedItems = collect($recommendedToday ?? [])->values();

    $topBestSeller = $bestSellerItems->first();

    $getItemValue = function ($item, $key, $default = null) {
        if (is_array($item)) {
            return $item[$key] ?? $default;
        }

        if (is_object($item)) {
            return $item->{$key} ?? $default;
        }

        return $default;
    };

    $getMenuImage = function ($item) use ($fallbackMenuImage, $getItemValue) {
        if (!$item) {
            return $fallbackMenuImage;
        }

        $image = $getItemValue($item, 'image_url') ?? $getItemValue($item, 'image');

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
                rgba(15, 23, 42, 0.80),
                rgba(67, 31, 12, 0.70)
            ),
            url('{{ asset('images/customer-menu/kds-background.png') }}');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-color: #0f172a;
    }

    @media (max-width: 1023px) {
        html,
        body,
        .customer-home-page {
            background-attachment: scroll;
        }
    }

    main {
        background: transparent !important;
    }

    footer {
        margin-top: 0 !important;
    }

    .customer-home-page {
        min-height: calc(100vh - 73px);
        position: relative;
        overflow-x: hidden;
        background-image:
            linear-gradient(
                135deg,
                rgba(15, 23, 42, 0.80),
                rgba(67, 31, 12, 0.70)
            ),
            url('{{ asset('images/customer-menu/kds-background.png') }}');
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
            linear-gradient(180deg, rgba(0,0,0,0.08), rgba(0,0,0,0.28));
        pointer-events: none;
    }

    .customer-home-inner {
        position: relative;
        z-index: 2;
    }

    .glass-light {
        background: rgba(255, 255, 255, 0.93);
        border: 1px solid rgba(255, 255, 255, 0.35);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
        backdrop-filter: blur(14px);
    }

    .glass-dark {
        background: rgba(10, 10, 10, 0.88);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.32);
        backdrop-filter: blur(14px);
    }

    .home-food-card {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(255, 255, 255, 0.42);
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
        overflow: hidden;
    }

    .home-section-title {
        font-size: clamp(1.35rem, 2vw, 2rem);
        line-height: 1.15;
    }

    .home-map-frame {
        width: 100%;
        min-height: 340px;
        border: 0;
        display: block;
    }

    @media (max-width: 767px) {
        .customer-home-inner {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .home-hero {
            padding: 1rem !important;
            border-radius: 1.25rem !important;
        }

        .home-hero h1 {
            font-size: 2rem !important;
            line-height: 1.05 !important;
        }

        .home-hero p {
            font-size: 0.88rem !important;
            line-height: 1.55 !important;
        }

        .home-hero-actions a {
            padding-top: 0.85rem !important;
            padding-bottom: 0.85rem !important;
            border-radius: 1rem !important;
            font-size: 0.85rem !important;
        }

        .home-stat-card {
            padding: 0.85rem !important;
            border-radius: 1rem !important;
        }

        .home-stat-card p:first-child {
            font-size: 1.05rem !important;
        }

        .home-section {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        .home-section-box {
            padding: 1rem !important;
            border-radius: 1.25rem !important;
        }

        .home-food-card {
            border-radius: 1.15rem !important;
        }

        .home-food-image {
            height: 145px !important;
        }

        .home-map-frame {
            min-height: 260px;
        }
    }
</style>

<div class="customer-home-page">
    <div class="customer-home-inner max-w-7xl mx-auto px-4 sm:px-6 py-5 sm:py-8">

        <!-- HERO -->
        <section class="home-hero glass-light rounded-[2rem] overflow-hidden p-5 sm:p-8 lg:p-10">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_440px] gap-6 lg:gap-8 items-center">

                <!-- HERO TEXT -->
                <div>
                    <div class="inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-xs sm:text-sm font-black text-orange-700 mb-5">
                        DineSync+ Customer Portal
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.02] tracking-tight text-gray-950">
                        Korean dining made easier.
                    </h1>

                    <p class="text-sm sm:text-base lg:text-lg leading-8 text-gray-600 mt-5 max-w-2xl">
                        Browse Chef Oppa’s menu, check customer favorites, and reserve your table in one clean customer portal.
                    </p>

                    <div class="home-hero-actions mt-6 flex flex-col sm:flex-row gap-3">
                        <a
                            href="{{ route('customer.menu') }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-7 py-4 font-black text-white shadow-lg shadow-orange-500/25 transition hover:bg-orange-600"
                        >
                            Open Menu
                        </a>

                        <a
                            href="{{ route('customer.reservations.create') }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-gray-950 px-7 py-4 font-black text-white transition hover:bg-gray-800"
                        >
                            Reserve a Table
                        </a>
                    </div>

                    <div class="mt-7 grid grid-cols-3 gap-3 max-w-xl">
                        <div class="home-stat-card rounded-3xl border border-gray-200 bg-white px-4 py-5 shadow-sm">
                            <p class="text-2xl font-black text-gray-950">8</p>
                            <p class="mt-1 text-xs font-bold text-gray-500">Categories</p>
                        </div>

                        <div class="home-stat-card rounded-3xl border border-gray-200 bg-white px-4 py-5 shadow-sm">
                            <p class="text-2xl font-black text-orange-500">Live</p>
                            <p class="mt-1 text-xs font-bold text-gray-500">Availability</p>
                        </div>

                        <div class="home-stat-card rounded-3xl border border-gray-200 bg-white px-4 py-5 shadow-sm">
                            <p class="text-2xl font-black text-gray-950">Easy</p>
                            <p class="mt-1 text-xs font-bold text-gray-500">Booking</p>
                        </div>
                    </div>
                </div>

                <!-- HERO FEATURE CARD -->
                <div class="rounded-[1.75rem] bg-gray-950 p-4 sm:p-5 text-white overflow-hidden relative">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_10%,rgba(249,115,22,0.34),transparent_18rem)]"></div>

                    <div class="relative z-10">
                        <div class="rounded-[1.35rem] bg-white/10 border border-white/10 p-5">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-300">
                                Chef Oppa
                            </p>

                            <h2 class="mt-3 text-2xl sm:text-3xl font-black leading-tight">
                                Menu browsing, reservation, and customer assistance.
                            </h2>

                            <p class="mt-3 text-sm leading-6 text-gray-300">
                                Customers can quickly browse the menu and submit reservation requests without confusion.
                            </p>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                                <div>
                                    <p class="text-xs text-gray-400">Customer action</p>
                                    <p class="text-sm font-bold text-white">Browse menu items</p>
                                </div>
                                <span class="rounded-full bg-orange-500/20 px-3 py-1 text-xs font-black text-orange-300">
                                    Menu
                                </span>
                            </div>

                            <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                                <div>
                                    <p class="text-xs text-gray-400">Customer action</p>
                                    <p class="text-sm font-bold text-white">Submit reservation</p>
                                </div>
                                <span class="rounded-full bg-green-500/20 px-3 py-1 text-xs font-black text-green-300">
                                    Reserve
                                </span>
                            </div>

                            <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                                <div>
                                    <p class="text-xs text-gray-400">Customer action</p>
                                    <p class="text-sm font-bold text-white">Ask AI assistant</p>
                                </div>
                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-black text-white">
                                    AI
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- BEST SELLERS -->
        <section class="home-section py-6">
            <div class="home-section-box glass-dark rounded-[2rem] p-5 sm:p-7 text-white">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
                    <div>
                        <p class="text-xs sm:text-sm font-black text-orange-400 uppercase tracking-widest">
                            Customer Favorites
                        </p>
                        <h2 class="home-section-title mt-2 font-black">
                            Best sellers
                        </h2>
                    </div>

                    <a
                        href="{{ route('customer.menu') }}"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl bg-white/10 hover:bg-white/15 border border-white/10 px-5 py-3 text-sm font-black transition"
                    >
                        View Menu
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @forelse ($bestSellerItems->take(3) as $item)
                        @php
                            $name = $getItemValue($item, 'name', 'Menu Item');
                            $category = $getItemValue($item, 'category', 'Chef Oppa');
                            $description = $getItemValue($item, 'description', 'One of the customer favorites from Chef Oppa.');
                            $price = $getItemValue($item, 'price', 0);
                            $status = $getItemValue($item, 'status', 'Available');
                            $sold = $getItemValue($item, 'total_sold', 0);
                            $image = $getMenuImage($item);
                        @endphp

                        <article class="home-food-card rounded-[1.5rem] text-gray-900">
                            <div class="home-food-image h-48 bg-gray-100 overflow-hidden">
                                <img
                                    src="{{ $image }}"
                                    alt="{{ $name }}"
                                    class="h-full w-full object-cover"
                                    onerror="this.src='{{ $fallbackMenuImage }}';"
                                >
                            </div>

                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-black text-gray-950 truncate">
                                            {{ $name }}
                                        </h3>
                                        <p class="text-xs font-bold text-gray-500 mt-1 truncate">
                                            {{ $category }}
                                        </p>
                                    </div>

                                    <p class="font-black text-orange-500 whitespace-nowrap">
                                        ₱{{ number_format($price, 2) }}
                                    </p>
                                </div>

                                <p class="text-sm text-gray-500 leading-6 mt-3 line-clamp-2">
                                    {{ $description }}
                                </p>

                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">
                                        {{ $status }}
                                    </span>

                                    <span class="text-xs font-bold text-gray-400">
                                        Sold: {{ $sold }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="md:col-span-3 rounded-[1.5rem] border border-white/10 bg-white/10 p-6 text-center">
                            <p class="font-black text-white">
                                No best sellers yet
                            </p>
                            <p class="text-sm text-gray-300 mt-2">
                                Best sellers will appear once orders are recorded.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- RECOMMENDED + RESERVATION -->
        <section class="home-section pb-6">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-5">

                <!-- RECOMMENDED -->
                <div class="home-section-box glass-light rounded-[2rem] p-5 sm:p-7">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
                        <div>
                            <p class="text-xs sm:text-sm font-black text-orange-600 uppercase tracking-widest">
                                Recommended Today
                            </p>
                            <h2 class="home-section-title mt-2 font-black text-gray-950">
                                Featured dishes
                            </h2>
                        </div>

                        <a
                            href="{{ route('customer.menu') }}"
                            class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl bg-orange-500 hover:bg-orange-600 px-5 py-3 text-sm font-black text-white transition"
                        >
                            See All
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($recommendedItems->take(4) as $item)
                            @php
                                $name = $getItemValue($item, 'name', 'Menu Item');
                                $category = $getItemValue($item, 'category', 'Chef Oppa');
                                $price = $getItemValue($item, 'price', 0);
                                $status = $getItemValue($item, 'status', 'Available');
                            @endphp

                            <div class="rounded-2xl border border-gray-200 bg-white p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-black text-gray-950 truncate">
                                            {{ $name }}
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1 truncate">
                                            {{ $category }}
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between sm:justify-end gap-3 sm:text-right">
                                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">
                                            {{ $status }}
                                        </span>

                                        <p class="font-black text-orange-500 whitespace-nowrap">
                                            ₱{{ number_format($price, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center">
                                <p class="font-black text-gray-950">
                                    No recommended items yet
                                </p>
                                <p class="text-sm text-gray-500 mt-2">
                                    Recommended dishes will appear here once menu items are available.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- RESERVATION CTA -->
                <div class="home-section-box glass-dark rounded-[2rem] p-5 sm:p-7 text-white">
                    <p class="text-xs sm:text-sm font-black text-orange-400 uppercase tracking-widest">
                        Table Reservation
                    </p>

                    <h2 class="home-section-title mt-2 font-black">
                        Reserve your table before visiting.
                    </h2>

                    <p class="text-sm leading-7 text-gray-300 mt-4">
                        Submit your reservation details and continue to secure payment through Xendit checkout.
                    </p>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl bg-white/10 border border-white/10 p-4">
                            <p class="text-xs text-gray-400">Operating Hours</p>
                            <p class="font-black text-white mt-1">10:00 AM - 9:00 PM</p>
                        </div>

                        <div class="rounded-2xl bg-white/10 border border-white/10 p-4">
                            <p class="text-xs text-gray-400">Reservation Slots</p>
                            <p class="font-black text-white mt-1">11:00 AM - 7:00 PM</p>
                        </div>
                    </div>

                    <a
                        href="{{ route('customer.reservations.create') }}"
                        class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-orange-500 hover:bg-orange-600 px-5 py-4 text-sm font-black text-white transition shadow-lg shadow-orange-500/25"
                    >
                        Create Reservation
                    </a>
                </div>

            </div>
        </section>

        <!-- LOCATION -->
        <section class="home-section pb-8">
            <div class="home-section-box glass-light rounded-[2rem] overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-[380px_1fr]">

                    <div class="p-5 sm:p-7">
                        <p class="text-xs sm:text-sm font-black text-orange-600 uppercase tracking-widest">
                            Visit Chef Oppa
                        </p>

                        <h2 class="home-section-title mt-2 font-black text-gray-950">
                            Find us easily.
                        </h2>

                        <p class="text-sm leading-7 text-gray-600 mt-4">
                            Check the restaurant location before your visit. You can also use the reservation page to secure your table.
                        </p>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-2xl border border-gray-200 bg-white p-4">
                                <p class="text-xs font-bold text-gray-500">Location</p>
                                <p class="font-black text-gray-950 mt-1">Quezon City</p>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-white p-4">
                                <p class="text-xs font-bold text-gray-500">Contact</p>
                                <p class="font-black text-gray-950 mt-1">0912 345 6789</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-200">
                        <iframe
                            class="home-map-frame"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q=Chef%20Oppa%20Quezon%20City&output=embed">
                        </iframe>
                    </div>

                </div>
            </div>
        </section>

    </div>
</div>

@endsection