@extends('layouts.customer')

@section('content')

<style>
    [x-cloak] {
        display: none !important;
    }

    body {
        overflow-x: hidden;
    }

    footer,
    #customer-chatbot-root,
    #customer-chatbot-window,
    #customer-chatbot-button,
    .fixed.bottom-6.right-6,
    .fixed.bottom-8.right-8,
    .fixed.bottom-10.right-10 {
        display: none !important;
    }

    @media (min-width: 1024px) {
        body {
            overflow: hidden;
        }
    }

    @media (max-width: 1023px) {
        body {
            overflow-y: auto;
        }
    }

    .menu-bg {
        position: relative;
        width: 100%;
        background:
            radial-gradient(circle at 18% 12%, rgba(255, 255, 255, 0.18), transparent 18rem),
            radial-gradient(circle at 82% 20%, rgba(249, 115, 22, 0.20), transparent 20rem),
            linear-gradient(135deg, rgba(45, 22, 10, 0.92), rgba(99, 51, 22, 0.92)),
            repeating-linear-gradient(
                90deg,
                #4b2410 0px,
                #4b2410 78px,
                #5f3016 78px,
                #5f3016 156px
            );
    }

    .menu-bg::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(90deg, rgba(255,255,255,0.05), transparent 28%, rgba(0,0,0,0.18)),
            repeating-linear-gradient(
                0deg,
                rgba(255,255,255,0.025) 0px,
                rgba(255,255,255,0.025) 1px,
                transparent 1px,
                transparent 8px
            );
        opacity: 0.9;
    }

    .menu-bg::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at center, rgba(255,255,255,0.16), transparent 34rem),
            linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.14) 100%);
    }

    /* DESKTOP: keep the original PageFlip book */
    .desktop-menu-book {
        display: none;
    }

    @media (min-width: 1024px) {
        .desktop-menu-book {
            display: flex;
        }
    }

    .menu-book-page {
        height: calc(100dvh - 72px);
        min-height: 640px;
        overflow: hidden;
        align-items: center;
        justify-content: center;
    }

    .menu-book-stage {
        position: relative;
        z-index: 5;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        perspective: 2600px;
        transform-style: preserve-3d;
        padding: 24px 0;
    }

    .menu-book-wrap {
        position: relative;
        width: min(760px, 94vw);
        height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        transform-style: preserve-3d;
        animation: bookFloat 5.2s ease-in-out infinite;
    }

    @keyframes bookFloat {
        0%, 100% {
            transform: translateY(0px) rotateZ(-0.25deg);
        }

        50% {
            transform: translateY(-8px) rotateZ(0.25deg);
        }
    }

    .menu-book-shell {
        position: relative;
        z-index: 5;
        width: min(740px, 94vw);
        height: 470px;
        display: flex;
        align-items: center;
        justify-content: center;
        transform-style: preserve-3d;
        transition: transform 260ms ease;
    }

    .menu-book-shell.single-page-mode {
        transform: translateX(-165px);
    }

    .menu-book-shell.spread-mode {
        transform: translateX(0);
    }

    #chefOppaFlipBook {
        margin: 0 auto;
        transform-style: preserve-3d;
        filter: drop-shadow(0 22px 26px rgba(0, 0, 0, 0.34));
    }

    .page {
        background: transparent;
        overflow: hidden;
    }

    .page-content {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
        background: #fffaf3;
        border-radius: 4px;
    }

    .page-content::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 2;
        pointer-events: none;
        background:
            linear-gradient(
                90deg,
                rgba(0,0,0,0.02),
                transparent 8%,
                transparent 92%,
                rgba(0,0,0,0.02)
            );
    }

    .page-content img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        user-select: none;
        -webkit-user-drag: none;
        position: relative;
        z-index: 1;
    }

    .page-cover,
    .back-cover {
        color: white;
        overflow: hidden;
        position: relative;
        border-radius: 16px;
        background:
            radial-gradient(circle at 75% 14%, rgba(249, 115, 22, 0.12), transparent 12rem),
            linear-gradient(135deg, #1e293b 0%, #111827 55%, #020617 100%);
        box-shadow:
            inset 0 0 0 1px rgba(255,255,255,0.06),
            inset 12px 0 20px rgba(255,255,255,0.03),
            inset -18px 0 26px rgba(0,0,0,0.45);
    }

    .cover-texture {
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            repeating-linear-gradient(
                35deg,
                rgba(255,255,255,0.024) 0px,
                rgba(255,255,255,0.024) 1px,
                transparent 1px,
                transparent 5px
            ),
            repeating-linear-gradient(
                125deg,
                rgba(0,0,0,0.18) 0px,
                rgba(0,0,0,0.18) 1px,
                transparent 1px,
                transparent 7px
            );
        opacity: 0.88;
    }

    .cover-spine {
        position: absolute;
        left: 0;
        top: 0;
        width: 15%;
        height: 100%;
        z-index: 2;
        background: linear-gradient(90deg, #020617, #111827 58%, rgba(255,255,255,0.04));
        box-shadow:
            inset -18px 0 24px rgba(0,0,0,0.55),
            10px 0 18px rgba(0,0,0,0.24);
    }

    .cover-spine::after {
        content: "";
        position: absolute;
        right: 10px;
        top: 8%;
        width: 2px;
        height: 84%;
        border-radius: 999px;
        background: rgba(249, 115, 22, 0.58);
    }

    .hard-cover-inner {
        position: relative;
        z-index: 5;
        height: 100%;
        padding: 30px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .cover-accent {
        color: #fb923c;
        text-shadow: 0 2px 8px rgba(249, 115, 22, 0.22);
    }

    .cover-line {
        width: 84px;
        height: 2px;
        background: linear-gradient(90deg, transparent, #fb923c, transparent);
    }

    .cover-line-small {
        width: 50px;
        height: 2px;
        background: #fb923c;
        margin-top: 10px;
    }

    .cover-content-block {
        margin-top: 54px;
    }

    /* MOBILE: readable menu pages, no PageFlip */
    .mobile-menu-page {
        display: block;
        min-height: calc(100dvh - 72px);
        overflow-x: hidden;
        padding: 16px 14px 28px;
    }

    @media (min-width: 1024px) {
        .mobile-menu-page {
            display: none;
        }
    }

    .mobile-menu-inner {
        position: relative;
        z-index: 5;
        max-width: 640px;
        margin: 0 auto;
    }

    .mobile-menu-header {
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(15, 23, 42, 0.76);
        backdrop-filter: blur(14px);
        box-shadow: 0 18px 40px rgba(0,0,0,0.22);
    }

    .mobile-menu-card {
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.10);
        backdrop-filter: blur(12px);
        box-shadow: 0 18px 40px rgba(0,0,0,0.22);
    }

    .mobile-menu-card img {
        display: block;
        width: 100%;
        height: auto;
        background: white;
    }
</style>

{{-- MOBILE / TABLET PORTRAIT: readable vertical menu --}}
<div class="menu-bg mobile-menu-page">
    <div class="mobile-menu-inner space-y-4">
        <div class="mobile-menu-header rounded-[1.5rem] p-5 text-white">
            <p class="text-xs font-black text-orange-300 uppercase tracking-[0.25em]">
                Chef Oppa Menu
            </p>

            <h1 class="mt-2 text-2xl font-black leading-tight">
                Browse the menu clearly
            </h1>

            <p class="mt-2 text-sm leading-6 text-white/70">
                On mobile, the menu is shown as full-width pages so every item is readable.
            </p>
        </div>

        @for ($i = 1; $i <= 7; $i++)
            <a
                href="{{ asset('images/customer-menu/page-' . $i . '.jpg') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="mobile-menu-card block overflow-hidden rounded-[1.25rem]"
            >
                <div class="flex items-center justify-between gap-3 bg-gray-950/90 px-4 py-3 text-white">
                    <p class="text-sm font-black">Menu Page {{ $i }}</p>
                    <p class="text-xs font-bold text-orange-300">Tap to zoom</p>
                </div>

                <img
                    src="{{ asset('images/customer-menu/page-' . $i . '.jpg') }}"
                    alt="DineSync+ menu page {{ $i }}"
                    loading="{{ $i <= 2 ? 'eager' : 'lazy' }}"
                >
            </a>
        @endfor
    </div>
</div>

{{-- DESKTOP / LAPTOP: original PageFlip menu --}}
<div
    class="menu-bg menu-book-page desktop-menu-book"
    x-data="{
        currentPage: 0,
        totalPages: 9,
        pageFlip: null,

        updatePage(page) {
            this.currentPage = page;
        },

        isSinglePageView() {
            return this.currentPage === 0 || this.currentPage >= this.totalPages - 1;
        }
    }"
    x-init="
        const initFlipBook = () => {
            const book = document.getElementById('chefOppaFlipBook');

            if (!book || !window.St || !window.St.PageFlip) {
                setTimeout(initFlipBook, 120);
                return;
            }

            pageFlip = new window.St.PageFlip(book, {
                width: 330,
                height: 470,
                size: 'stretch',
                minWidth: 285,
                maxWidth: 740,
                minHeight: 400,
                maxHeight: 470,
                maxShadowOpacity: 0.24,
                showCover: true,
                mobileScrollSupport: false,
                usePortrait: true,
                flippingTime: 950,
                drawShadow: true,
                startPage: 0,
                autoSize: true,
                clickEventForward: true,
                swipeDistance: 18,
                showPageCorners: true
            });

            pageFlip.loadFromHTML(document.querySelectorAll('#chefOppaFlipBook .page'));

            totalPages = pageFlip.getPageCount();

            pageFlip.on('flip', (event) => {
                updatePage(event.data);
            });
        };

        initFlipBook();
    "
>
    <main class="menu-book-stage">
        <div class="menu-book-wrap">
            <div
                class="menu-book-shell"
                :class="isSinglePageView() ? 'single-page-mode' : 'spread-mode'"
            >
                <div id="chefOppaFlipBook">
                    <div class="page page-cover" data-density="hard">
                        <div class="cover-texture"></div>
                        <div class="cover-spine"></div>

                        <div class="hard-cover-inner">
                            <div>
                                <div class="cover-accent">
                                    <div class="text-[10px] tracking-[0.40em] font-black">DINESYNC+</div>
                                    <div class="cover-line mt-3"></div>
                                </div>

                                <div class="cover-content-block">
                                    <p class="cover-accent text-[11px] font-black uppercase tracking-[0.28em]">
                                        Digital Restaurant Menu
                                    </p>

                                    <h2 class="mt-4 text-4xl font-black leading-none tracking-tight text-white">
                                        MENU BOOK
                                    </h2>

                                    <div class="cover-line-small"></div>

                                    <p class="mt-5 max-w-[250px] text-sm leading-6 text-white/65">
                                        Browse our menu with a smooth realistic page-flip experience.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <p class="text-sm text-white/60 mb-3">
                                    Tap or drag the cover to open
                                </p>

                                <div class="inline-flex rounded-full border border-orange-300/60 bg-orange-400 px-5 py-3 text-sm font-black text-black shadow-lg shadow-black/25">
                                    Open Menu
                                </div>
                            </div>
                        </div>
                    </div>

                    @for ($i = 1; $i <= 7; $i++)
                        <div class="page">
                            <div class="page-content">
                                <img
                                    src="{{ asset('images/customer-menu/page-' . $i . '.jpg') }}"
                                    alt="DineSync+ menu page {{ $i }}"
                                >
                            </div>
                        </div>
                    @endfor

                    <div class="page back-cover" data-density="hard">
                        <div class="cover-texture"></div>
                        <div class="cover-spine"></div>

                        <div class="hard-cover-inner">
                            <div>
                                <div class="cover-accent">
                                    <div class="text-[10px] tracking-[0.40em] font-black">DINESYNC+</div>
                                    <div class="cover-line mt-3"></div>
                                </div>

                                <h2 class="mt-20 text-4xl font-black leading-none tracking-tight text-white">
                                    Thank You
                                </h2>

                                <p class="mt-5 max-w-[260px] text-sm leading-6 text-white/65">
                                    Please call our service staff if you need assistance with ordering or table requests.
                                </p>
                            </div>

                            <div class="text-sm text-white/50">
                                DineSync+ Customer Portal
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>

@endsection
