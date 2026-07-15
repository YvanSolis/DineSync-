@extends('layouts.customer')

@section('content')

@php
    $fallbackMenuImage = 'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1400&q=82';
    $excludedPopularCategories = ['Drinks', 'Extras'];

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

    $bestSellerItems = collect($bestSellers ?? [])
        ->filter(fn ($item) => !in_array($getItemValue($item, 'category'), $excludedPopularCategories, true))
        ->values();

    $recommendedItems = collect($recommendedToday ?? [])
        ->filter(fn ($item) => !in_array($getItemValue($item, 'category'), $excludedPopularCategories, true))
        ->values();

    $topBestSeller = $bestSellerItems->first() ?? $recommendedItems->first();
    $heroImage = $getMenuImage($topBestSeller);
    $heroName = $getItemValue($topBestSeller, 'name', 'Signature Korean Dining');
    $heroCategory = $getItemValue($topBestSeller, 'category', 'Chef Oppa Special');
@endphp

<style>
    :root {
        --chef-ink: #111827;
        --chef-cream: #f8fafc;
        --chef-paper: #ffffff;
        --chef-copper: #f97316;
        --chef-copper-dark: #ea580c;
        --chef-line: rgba(15, 23, 42, .10);
    }

    html,
    body {
        background: #111318;
        scroll-behavior: smooth;
    }

    main {
        background: transparent !important;
    }

    .premium-home {
        min-height: calc(100vh - 72px);
        overflow: hidden;
        color: var(--chef-ink);
        background:
            radial-gradient(circle at 10% 8%, rgba(249, 115, 22, .18), transparent 25rem),
            radial-gradient(circle at 88% 18%, rgba(255, 255, 255, .07), transparent 24rem),
            linear-gradient(135deg, rgba(12, 15, 22, .90), rgba(54, 28, 17, .84)),
            url('{{ asset('images/customer-menu/kds-background.png') }}');
        background-size: cover;
        background-position: center;
        background-attachment: scroll;
    }

    .premium-shell {
        width: min(1280px, calc(100% - 2rem));
        margin: 0 auto;
    }

    .premium-hero {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        min-height: 660px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 1.75rem;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 28px 70px rgba(0, 0, 0, .28);
    }

    .premium-hero-copy {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: clamp(2rem, 5vw, 5rem);
        position: relative;
    }

    .premium-hero-copy::after {
        content: '';
        position: absolute;
        right: 2rem;
        top: 2rem;
        width: 92px;
        height: 92px;
        border: 1px solid rgba(200, 106, 42, .24);
        border-radius: 50%;
    }

    .eyebrow {
        display: inline-flex;
        width: fit-content;
        align-items: center;
        gap: .65rem;
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .2em;
        text-transform: uppercase;
        color: var(--chef-copper-dark);
    }

    .eyebrow::before {
        content: '';
        width: 34px;
        height: 1px;
        background: var(--chef-copper);
    }

    .premium-title {
        margin-top: 1.35rem;
        max-width: 720px;
        font-family: inherit;
        font-size: clamp(3rem, 5.6vw, 5.8rem);
        line-height: .98;
        letter-spacing: -.06em;
        color: #111827;
    }

    .premium-title span {
        color: var(--chef-copper);
        font-style: normal;
        font-weight: 900;
    }

    .premium-copy {
        max-width: 610px;
        margin-top: 1.7rem;
        font-size: 1rem;
        line-height: 1.9;
        color: #64748b;
    }

    .premium-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .8rem;
        margin-top: 2rem;
    }

    .premium-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 52px;
        padding: .9rem 1.4rem;
        border-radius: 999px;
        font-weight: 900;
        transition: transform .18s ease, background-color .18s ease, border-color .18s ease;
    }

    .premium-btn:hover {
        transform: translateY(-2px);
    }

    .premium-btn-primary {
        background: var(--chef-copper);
        color: #fff;
        box-shadow: 0 12px 26px rgba(200, 106, 42, .25);
    }

    .premium-btn-primary:hover {
        background: var(--chef-copper-dark);
    }

    .premium-btn-secondary {
        border: 1px solid var(--chef-line);
        background: transparent;
        color: #201713;
    }

    .premium-btn-secondary:hover {
        border-color: rgba(200, 106, 42, .45);
        background: #fff4e8;
    }

    .premium-stat-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        margin-top: 2.4rem;
        max-width: 620px;
    }

    .premium-stat {
        padding: 1rem;
        border-top: 1px solid var(--chef-line);
    }

    .premium-stat strong {
        display: block;
        font-family: inherit;
        font-size: 1.75rem;
        color: #201713;
    }

    .premium-stat span {
        display: block;
        margin-top: .25rem;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #85776d;
    }

    .premium-hero-media {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        background: #2a1912;
    }

    .premium-hero-media img {
        width: 100%;
        height: 100%;
        min-height: 660px;
        object-fit: cover;
        display: block;
    }

    .premium-hero-media::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,.05), rgba(0,0,0,.55));
    }

    .hero-dish-label {
        position: absolute;
        z-index: 2;
        left: 1.5rem;
        right: 1.5rem;
        bottom: 1.5rem;
        padding: 1rem 1.15rem;
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 1.2rem;
        background: rgba(17, 14, 12, .82);
        color: white;
    }

    .hero-dish-label small {
        display: block;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #f0a066;
    }

    .hero-dish-label strong {
        display: block;
        margin-top: .25rem;
        font-family: inherit;
        font-size: 1.55rem;
    }

    .premium-section {
        padding: 4.5rem 0 0;
        content-visibility: auto;
        contain-intrinsic-size: 800px;
    }

    .section-kicker {
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .2em;
        text-transform: uppercase;
        color: #e58b4c;
    }

    .section-title {
        margin-top: .7rem;
        font-family: inherit;
        font-size: clamp(2.4rem, 4vw, 4.2rem);
        line-height: 1;
        color: #ffffff;
    }

    .section-copy {
        max-width: 620px;
        margin-top: 1rem;
        line-height: 1.8;
        color: rgba(255,255,255,.72);
    }

    .dish-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 2rem;
    }

    .dish-card {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 1.4rem;
        background: #f9f1e7;
        box-shadow: 0 18px 40px rgba(0,0,0,.17);
    }

    .dish-card-image {
        aspect-ratio: 4 / 3;
        overflow: hidden;
        background: #e9dfd4;
    }

    .dish-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .35s ease;
    }

    .dish-card:hover .dish-card-image img {
        transform: scale(1.03);
    }

    .dish-card-body {
        padding: 1.15rem;
    }

    .dish-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .dish-card h3 {
        font-family: inherit;
        font-size: 1.35rem;
        color: #1f1713;
    }

    .dish-card p {
        margin-top: .45rem;
        color: #78695e;
        line-height: 1.6;
        font-size: .9rem;
    }

    .dish-price {
        color: var(--chef-copper-dark);
        font-weight: 900;
        white-space: nowrap;
    }

    .experience-grid {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 1rem;
        margin-top: 2rem;
    }

    .experience-card {
        min-height: 360px;
        overflow: hidden;
        border-radius: 1.5rem;
        border: 1px solid rgba(255,255,255,.1);
        background: #f8efe5;
    }

    .experience-copy {
        padding: clamp(1.5rem, 4vw, 3rem);
    }

    .experience-copy h3 {
        font-family: inherit;
        font-size: clamp(2rem, 3vw, 3.2rem);
        color: #201713;
    }

    .experience-copy p {
        margin-top: 1rem;
        color: #77685e;
        line-height: 1.8;
    }

    .experience-list {
        margin-top: 1.5rem;
        display: grid;
        gap: .75rem;
    }

    .experience-list div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .9rem 0;
        border-bottom: 1px solid var(--chef-line);
        font-weight: 800;
        color: #2a211c;
    }

    .experience-list span:last-child {
        color: var(--chef-copper-dark);
    }

    .recommended-panel {
        padding: 1rem;
        background:
            radial-gradient(circle at top right, rgba(249, 115, 22, .16), transparent 15rem),
            linear-gradient(145deg, #111827, #17191f);
    }

    .recommended-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid rgba(255,255,255,.08);
        color: white;
    }

    .recommended-item:last-child {
        border-bottom: 0;
    }

    .recommended-item strong {
        display: block;
        font-family: inherit;
        font-size: 1.15rem;
    }

    .recommended-item small {
        display: block;
        margin-top: .25rem;
        color: rgba(255,255,255,.48);
    }

    .recommended-item span {
        color: #f2a064;
        font-weight: 900;
    }

    .visit-card {
        display: grid;
        grid-template-columns: 390px 1fr;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 1.5rem;
        background: #f8efe5;
    }

    .visit-copy {
        padding: clamp(1.5rem, 4vw, 3rem);
    }

    .visit-copy h3 {
        margin-top: .7rem;
        font-family: inherit;
        font-size: 2.6rem;
        line-height: 1;
        color: #201713;
    }

    .visit-copy p {
        margin-top: 1rem;
        color: #76675d;
        line-height: 1.8;
    }

    .visit-meta {
        display: grid;
        gap: .75rem;
        margin-top: 1.5rem;
    }

    .visit-meta div {
        padding: .9rem 0;
        border-bottom: 1px solid var(--chef-line);
    }

    .visit-meta small {
        display: block;
        color: #97877a;
        text-transform: uppercase;
        letter-spacing: .12em;
        font-weight: 900;
        font-size: .66rem;
    }

    .visit-meta strong {
        display: block;
        margin-top: .25rem;
        color: #241b16;
    }

    .visit-map {
        min-height: 410px;
        background: #ddd2c6;
    }

    .visit-map iframe {
        width: 100%;
        height: 100%;
        min-height: 410px;
        border: 0;
        display: block;
    }

    .bottom-cta {
        margin-top: 4.5rem;
        padding: clamp(2rem, 5vw, 4rem);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 1.5rem;
        background:
            linear-gradient(135deg, rgba(200,106,42,.96), rgba(133,57,18,.98));
        color: white;
        text-align: center;
    }

    .bottom-cta h2 {
        font-family: inherit;
        font-size: clamp(2.5rem, 5vw, 4.8rem);
        line-height: 1;
    }

    .bottom-cta p {
        max-width: 650px;
        margin: 1rem auto 0;
        color: rgba(255,255,255,.78);
        line-height: 1.8;
    }

    .bottom-cta .premium-actions {
        justify-content: center;
    }

    .bottom-cta .premium-btn-primary {
        background: #ffffff;
        color: #111827;
        box-shadow: none;
    }

    .bottom-cta .premium-btn-secondary {
        border-color: rgba(255,255,255,.35);
        color: white;
    }

    .bottom-cta .premium-btn-secondary:hover {
        background: rgba(255,255,255,.12);
    }

    @media (max-width: 1023px) {
        .premium-hero,
        .experience-grid,
        .visit-card {
            grid-template-columns: 1fr;
        }

        .premium-hero {
            min-height: 0;
        }

        .premium-hero-media img {
            min-height: 430px;
        }

        .dish-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .visit-copy {
            order: 1;
        }

        .visit-map {
            order: 2;
        }
    }

    @media (max-width: 640px) {
        .premium-shell {
            width: min(100% - 1rem, 1280px);
        }

        .premium-home {
            padding-top: .5rem;
        }

        .premium-hero,
        .dish-card,
        .experience-card,
        .visit-card,
        .bottom-cta {
            border-radius: 1.15rem;
        }

        .premium-hero-copy {
            padding: 1.5rem;
        }

        .premium-title {
            font-size: 3.1rem;
        }

        .premium-stat-row,
        .dish-grid {
            grid-template-columns: 1fr;
        }

        .premium-stat-row {
            gap: 0;
        }

        .premium-hero-media img {
            min-height: 340px;
        }

        .premium-section {
            padding-top: 3rem;
        }

        .section-title {
            font-size: 2.6rem;
        }
    }
</style>

<div class="premium-home py-4 sm:py-7 lg:py-10">
    <div class="premium-shell">
        <section class="premium-hero">
            <div class="premium-hero-copy">
                <span class="eyebrow">Chef Oppa · Quezon City</span>

                <h1 class="premium-title">
                    Korean dining,<br>
                    <span>beautifully served.</span>
                </h1>

                <p class="premium-copy">
                    Discover signature Korean dishes, unlimited dining favorites, and a seamless reservation experience in one refined customer portal.
                </p>

                <div class="premium-actions">
                    <a href="{{ route('customer.menu') }}" class="premium-btn premium-btn-primary">
                        Explore the Menu
                    </a>

                    <a href="{{ route('customer.reservations.create') }}" class="premium-btn premium-btn-secondary">
                        Reserve a Table
                    </a>
                </div>

                <div class="premium-stat-row">
                    <div class="premium-stat">
                        <strong>8</strong>
                        <span>Menu Categories</span>
                    </div>
                    <div class="premium-stat">
                        <strong>Live</strong>
                        <span>Availability</span>
                    </div>
                    <div class="premium-stat">
                        <strong>Easy</strong>
                        <span>Reservations</span>
                    </div>
                </div>
            </div>

            <div class="premium-hero-media">
                <img
                    src="{{ $heroImage }}"
                    alt="{{ $heroName }}"
                    decoding="async"
                    fetchpriority="high"
                    onerror="this.src='{{ $fallbackMenuImage }}';"
                >

                <div class="hero-dish-label">
                    <small>{{ $heroCategory }}</small>
                    <strong>{{ $heroName }}</strong>
                </div>
            </div>
        </section>

        <section class="premium-section">
            <p class="section-kicker">Customer Favorites</p>
            <h2 class="section-title">Signature dishes worth coming back for.</h2>
            <p class="section-copy">
                A curated look at the dishes customers order most, presented with a more refined restaurant-first experience.
            </p>

            <div class="dish-grid">
                @forelse ($bestSellerItems->take(3) as $item)
                    @php
                        $name = $getItemValue($item, 'name', 'Menu Item');
                        $category = $getItemValue($item, 'category', 'Chef Oppa');
                        $description = $getItemValue($item, 'description', 'One of the signature dishes from Chef Oppa.');
                        $price = $getItemValue($item, 'price', 0);
                        $image = $getMenuImage($item);
                    @endphp

                    <article class="dish-card">
                        <div class="dish-card-image">
                            <img
                                src="{{ $image }}"
                                alt="{{ $name }}"
                                loading="lazy"
                                decoding="async"
                                onerror="this.src='{{ $fallbackMenuImage }}';"
                            >
                        </div>

                        <div class="dish-card-body">
                            <div class="dish-card-top">
                                <div>
                                    <h3>{{ $name }}</h3>
                                    <p>{{ $category }}</p>
                                </div>

                                <span class="dish-price">₱{{ number_format($price, 2) }}</span>
                            </div>

                            <p>{{ \Illuminate\Support\Str::limit($description, 110) }}</p>
                        </div>
                    </article>
                @empty
                    <div class="dish-card md:col-span-3 p-8 text-center">
                        <h3>No best sellers yet</h3>
                        <p>Popular dishes will appear once order data is available.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="premium-section">
            <p class="section-kicker">The Chef Oppa Experience</p>
            <h2 class="section-title">Built for dining, not just browsing.</h2>

            <div class="experience-grid">
                <div class="experience-card experience-copy">
                    <h3>A simpler way to enjoy Korean dining.</h3>
                    <p>
                        Browse dishes clearly, check availability, and reserve your table without unnecessary steps. The experience stays focused on the food and the visit ahead.
                    </p>

                    <div class="experience-list">
                        <div><span>Browse the full menu</span><span>Menu</span></div>
                        <div><span>Secure a reservation</span><span>Reserve</span></div>
                        <div><span>Ask for dining assistance</span><span>AI</span></div>
                    </div>
                </div>

                <div class="experience-card recommended-panel">
                    @forelse ($recommendedItems->take(5) as $item)
                        @php
                            $name = $getItemValue($item, 'name', 'Menu Item');
                            $category = $getItemValue($item, 'category', 'Chef Oppa');
                            $price = $getItemValue($item, 'price', 0);
                        @endphp

                        <div class="recommended-item">
                            <div>
                                <strong>{{ $name }}</strong>
                                <small>{{ $category }}</small>
                            </div>
                            <span>₱{{ number_format($price, 2) }}</span>
                        </div>
                    @empty
                        <div class="recommended-item">
                            <div>
                                <strong>Recommended dishes</strong>
                                <small>Featured items will appear here.</small>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="premium-section">
            <div class="visit-card">
                <div class="visit-copy">
                    <p class="section-kicker" style="color:#a45220;">Visit Chef Oppa</p>
                    <h3>Plan your next Korean dining experience.</h3>
                    <p>
                        Reserve ahead, check our operating hours, and find the restaurant before your visit.
                    </p>

                    <div class="visit-meta">
                        <div>
                            <small>Location</small>
                            <strong>Quezon City</strong>
                        </div>
                        <div>
                            <small>Operating Hours</small>
                            <strong>10:00 AM – 9:00 PM</strong>
                        </div>
                        <div>
                            <small>Contact</small>
                            <strong>0912 345 6789</strong>
                        </div>
                    </div>
                </div>

                <div class="visit-map">
                    <iframe
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q=Chef%20Oppa%20Quezon%20City&output=embed">
                    </iframe>
                </div>
            </div>
        </section>

        <section class="bottom-cta">
            <p class="section-kicker" style="color:#ffd0ae;">Your table is waiting</p>
            <h2>Make your next meal feel special.</h2>
            <p>
                Explore the menu first, then reserve your table in just a few steps through the Chef Oppa customer portal.
            </p>

            <div class="premium-actions">
                <a href="{{ route('customer.reservations.create') }}" class="premium-btn premium-btn-primary">
                    Reserve Now
                </a>
                <a href="{{ route('customer.menu') }}" class="premium-btn premium-btn-secondary">
                    View Full Menu
                </a>
            </div>
        </section>
    </div>
</div>

@endsection
