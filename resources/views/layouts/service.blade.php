<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DineSync Service Staff</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .service-bg {
            background:
                linear-gradient(135deg, rgba(255, 247, 237, 0.94), rgba(255, 255, 255, 0.96)),
                url('https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1800&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .service-sidebar-bg {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(255, 247, 237, 0.94)),
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
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
    </style>
</head>

<body class="font-sans text-gray-900 h-screen overflow-hidden">

<div class="h-screen overflow-hidden service-bg">

    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 z-50 h-screen w-[260px] service-sidebar-bg border-r border-orange-100/70 flex flex-col overflow-y-auto shadow-[8px_0_30px_rgba(15,23,42,0.04)]">

        <!-- Brand -->
        <div class="px-5 py-5 border-b border-orange-100/70 shrink-0">
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

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-5 space-y-2 text-[14px] font-semibold">

            <a href="{{ route('service.dashboard') }}"
               class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition
               {{ request()->routeIs('service.dashboard') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-6 text-center">⌂</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('service.active-orders') }}"
               class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition
               {{ request()->routeIs('service.active-orders') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-6 text-center">▤</span>
                <span>Active Orders</span>
            </a>

            <a href="{{ route('service.table-monitoring') }}"
               class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition
               {{ request()->routeIs('service.table-monitoring') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-6 text-center">▦</span>
                <span>Table Monitoring</span>
            </a>

            <a href="{{ route('service.reservations') }}"
               class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition
               {{ request()->routeIs('service.reservations') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-6 text-center">◷</span>
                <span>Reservations</span>
            </a>
        </nav>

        <!-- User / Logout -->
        <div class="px-4 pb-5 space-y-3">
            <div class="rounded-2xl border border-orange-100 bg-white/80 px-4 py-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center text-sm font-bold shadow-md shadow-orange-200">
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
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                    class="w-full rounded-2xl bg-red-50 px-4 py-3.5 text-sm font-bold text-red-600 hover:bg-red-100 transition">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="ml-[260px] h-screen overflow-y-auto">

        <!-- Topbar -->
        <header class="sticky top-0 z-40 soft-glass border-b border-orange-100/70 px-7 py-4">
            <div class="flex items-center justify-between gap-4 flex-wrap">

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-orange-500">
                        Service Operations
                    </p>

                    <h2 class="text-2xl font-extrabold text-gray-900 leading-tight">
                        @yield('page-title', 'Service Staff')
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        @yield('page-subtitle', 'Manage daily restaurant service operations')
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden md:block bg-white/80 border border-orange-100 rounded-2xl px-5 py-3 shadow-sm">
                        <p class="text-xs text-gray-400">Today</p>
                        <p class="text-sm font-bold text-gray-700">{{ now()->format('M d, Y') }}</p>
                    </div>

                    <div class="bg-green-50 border border-green-100 text-green-600 px-4 py-3 rounded-2xl text-sm font-bold shadow-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        Online
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="p-7">
            <div class="max-w-[1600px] mx-auto">
                @yield('content')
            </div>
        </main>

    </div>
</div>

@stack('scripts')

</body>
</html>