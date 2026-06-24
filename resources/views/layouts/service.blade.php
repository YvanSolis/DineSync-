<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DineSync+ Service Staff</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('favicon2.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html,
        body {
            min-height: 100%;
            overflow-x: hidden;
        }

        .service-bg {
            background:
                linear-gradient(135deg, rgba(255, 247, 237, 0.94), rgba(255, 255, 255, 0.96)),
                url('https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1800&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        @media (max-width: 1023px) {
            .service-bg {
                background-attachment: scroll;
            }
        }

        .service-sidebar-bg {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 247, 237, 0.96)),
                url('https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: left center;
        }

        .service-brand-card {
            background:
                linear-gradient(135deg, rgba(249, 115, 22, 0.96), rgba(251, 146, 60, 0.88)),
                url('https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
        }

        .soft-glass {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .service-overlay {
            background: rgba(15, 23, 42, 0.38);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .no-scrollbar::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        .no-scrollbar {
            scrollbar-width: none;
        }

        .service-sidebar {
            width: 280px;
        }

        .service-main-wrapper {
            min-height: 100vh;
            width: 100%;
            min-width: 0;
            overflow-x: hidden;
        }

        .service-content-container {
            width: 100%;
            max-width: 1600px;
            margin-left: auto;
            margin-right: auto;
            min-width: 0;
        }

        @media (min-width: 640px) {
            .service-sidebar {
                width: 300px;
            }
        }

        @media (min-width: 1024px) {
            .service-sidebar {
                width: 260px;
                transform: translateX(0) !important;
            }

            .service-main-wrapper {
                margin-left: 260px;
                width: calc(100% - 260px);
            }
        }

        @media (max-width: 1023px) {
            .service-main-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }

        .service-main-wrapper table {
            max-width: 100%;
        }

        .service-main-wrapper .overflow-x-auto {
            max-width: 100%;
            overflow-x: auto;
        }
    </style>
</head>

<body class="font-sans text-gray-900 min-h-screen overflow-x-hidden">

<div class="min-h-screen service-bg">

    <!-- Mobile Overlay -->
    <div
        id="serviceSidebarOverlay"
        class="fixed inset-0 z-40 service-overlay hidden lg:hidden">
    </div>

    <!-- Sidebar -->
    <aside
        id="serviceSidebar"
        class="service-sidebar fixed left-0 top-0 z-50 h-dvh
               service-sidebar-bg border-r border-orange-100/70 flex flex-col overflow-hidden
               shadow-xl
               transform -translate-x-full transition-transform duration-300 ease-in-out">

        <!-- Top Area -->
        <div class="shrink-0">
            <!-- Mobile Close -->
            <div class="lg:hidden flex items-center justify-between px-5 pt-5">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-orange-500">
                    Service Menu
                </p>

                <button
                    type="button"
                    id="closeServiceSidebar"
                    class="w-10 h-10 rounded-2xl bg-white border border-orange-100 text-gray-700 shadow-sm flex items-center justify-center font-bold">
                    ×
                </button>
            </div>

            <!-- Brand -->
            <div class="px-5 py-5 border-b border-orange-100/70">
                <div class="service-brand-card rounded-2xl px-4 py-4 text-white shadow-lg shadow-orange-200/60">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white border border-white/60 flex items-center justify-center overflow-hidden shadow-sm shrink-0">
                            <img
                                src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                                alt="Chef Oppa Logo"
                                class="w-full h-full object-cover"
                            >
                        </div>

                        <div class="min-w-0">
                            <h1 class="text-[20px] font-extrabold leading-tight truncate">
                                Chef Oppa
                            </h1>
                            <p class="text-[12px] text-white/85">
                                Service Staff Panel
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Scroll Area -->
        <nav class="flex-1 min-h-0 overflow-y-auto no-scrollbar px-4 py-4 space-y-2 text-[14px] font-semibold">

            <a href="{{ route('service.dashboard') }}"
               class="service-nav-link flex items-center gap-3 px-4 py-3.5 rounded-2xl transition
               {{ request()->routeIs('service.dashboard') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-6 text-center">⌂</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('service.active-orders') }}"
               class="service-nav-link flex items-center gap-3 px-4 py-3.5 rounded-2xl transition
               {{ request()->routeIs('service.active-orders') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-6 text-center">▤</span>
                <span>Active Orders</span>
            </a>
            
            <a href="{{ route('service.payments') }}"
                class="service-nav-link flex items-center gap-3 px-4 py-3.5 rounded-2xl transition
                {{ request()->routeIs('service.payments') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                    <span class="w-6 text-center">₱</span>
                    <span>Payments</span>
            </a>

            <a href="{{ route('service.table-monitoring') }}"
               class="service-nav-link flex items-center gap-3 px-4 py-3.5 rounded-2xl transition
               {{ request()->routeIs('service.table-monitoring') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-6 text-center">▦</span>
                <span>Table Monitoring</span>
            </a>

            <a href="{{ route('service.reservations') }}"
               class="service-nav-link flex items-center gap-3 px-4 py-3.5 rounded-2xl transition
               {{ request()->routeIs('service.reservations') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-6 text-center">◷</span>
                <span>Reservations</span>
            </a>
        </nav>

        <!-- User / Logout Fixed Bottom -->
        <div class="shrink-0 px-4 pt-3 pb-4 border-t border-orange-100/70 bg-white/85 backdrop-blur">
            <div class="rounded-2xl border border-orange-100 bg-white/90 px-3 py-3 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center text-sm font-bold shadow-md shadow-orange-200 shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">
                            {{ auth()->user()->name ?? 'Service Staff' }}
                        </p>

                        <div class="flex items-center gap-2 mt-1">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <p class="text-xs text-gray-400">On Duty</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="w-full rounded-xl bg-red-50 px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-100 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <div class="service-main-wrapper">

        <!-- Topbar -->
        <header class="sticky top-0 z-30 soft-glass border-b border-orange-100/70 px-4 sm:px-5 lg:px-7 py-3 sm:py-4">
            <div class="flex items-center justify-between gap-3">

                <div class="flex items-center gap-3 min-w-0">
                    <!-- Mobile Menu Button -->
                    <button
                        type="button"
                        id="openServiceSidebar"
                        class="lg:hidden w-11 h-11 rounded-2xl bg-white border border-orange-100 text-gray-700 shadow-sm flex items-center justify-center font-bold shrink-0">
                        ☰
                    </button>

                    <div class="min-w-0">
                        <p class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.16em] sm:tracking-[0.18em] text-orange-500 truncate">
                            Service Operations
                        </p>

                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-tight truncate">
                            @yield('page-title', 'Service Staff')
                        </h2>

                        <p class="hidden sm:block text-sm text-gray-500 mt-1 truncate">
                            @yield('page-subtitle', 'Manage daily restaurant service operations')
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <div class="hidden md:block bg-white/80 border border-orange-100 rounded-2xl px-5 py-3 shadow-sm">
                        <p class="text-xs text-gray-400">Today</p>
                        <p class="text-sm font-bold text-gray-700">{{ now()->format('M d, Y') }}</p>
                    </div>

                    <div class="bg-green-50 border border-green-100 text-green-600 px-3 sm:px-4 py-2.5 sm:py-3 rounded-2xl text-xs sm:text-sm font-bold shadow-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="hidden sm:inline">Online</span>
                        <span class="sm:hidden">On</span>
                    </div>
                </div>
            </div>

            <p class="sm:hidden text-xs text-gray-500 mt-2 pl-[56px] leading-snug">
                @yield('page-subtitle', 'Manage daily restaurant service operations')
            </p>
        </header>

        <!-- Content -->
        <main class="px-4 sm:px-5 lg:px-7 py-5 sm:py-6 lg:py-7 overflow-x-hidden">
            <div class="service-content-container">
                @yield('content')
            </div>
        </main>

    </div>
</div>

@stack('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('serviceSidebar');
        const overlay = document.getElementById('serviceSidebarOverlay');
        const openButton = document.getElementById('openServiceSidebar');
        const closeButton = document.getElementById('closeServiceSidebar');
        const navLinks = document.querySelectorAll('.service-nav-link');

        function openSidebar() {
            if (!sidebar || !overlay) return;

            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebar() {
            if (!sidebar || !overlay) return;

            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-full');
            }

            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        if (openButton) {
            openButton.addEventListener('click', openSidebar);
        }

        if (closeButton) {
            closeButton.addEventListener('click', closeSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        navLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 1024) {
                    closeSidebar();
                }
            });
        });

        function handleResize() {
            if (!sidebar || !overlay) return;

            if (window.innerWidth >= 1024) {
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                sidebar.classList.remove('-translate-x-full');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();
    });
</script>

</body>
</html>