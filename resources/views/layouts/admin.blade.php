<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DineSync Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .admin-bg {
            background:
                linear-gradient(135deg, rgba(255, 247, 237, 0.94), rgba(255, 255, 255, 0.96)),
                url('https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1800&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .sidebar-bg {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(255, 247, 237, 0.94)),
                url('https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: left center;
        }

        .brand-card {
            background:
                linear-gradient(135deg, rgba(249, 115, 22, 0.96), rgba(251, 146, 60, 0.88)),
                url('https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

@php
    $pendingReservationsCount = \App\Models\Reservation::where('status', 'pending')->count();
@endphp

<body class="text-gray-800 h-screen overflow-hidden">

<div class="h-screen overflow-hidden admin-bg">

    <!-- Fixed Sidebar -->
    <aside class="fixed left-0 top-0 z-50 h-screen w-[240px] sidebar-bg border-r border-orange-100/70 flex flex-col overflow-y-auto shadow-[8px_0_30px_rgba(15,23,42,0.04)]">

        <!-- Brand -->
        <div class="px-5 py-5 border-b border-orange-100/70 shrink-0">
            <div class="brand-card rounded-2xl px-4 py-4 text-white shadow-lg shadow-orange-200/60">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center text-xl">
                        🍜
                    </div>

                    <div>
                        <h1 class="text-[18px] font-extrabold leading-tight">Chef Oppa</h1>
                        <p class="text-[11px] text-white/85">Admin Control Panel</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-5 space-y-1.5 text-[13px] font-medium">

            <a href="{{ url('/admin/dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition
               {{ request()->is('admin/dashboard') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-5 text-center">⌂</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ url('/admin/menu-items') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition
               {{ request()->is('admin/menu-items') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-5 text-center">☰</span>
                <span>Menu Management</span>
            </a>

            <a href="{{ url('/admin/ingredients') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition
               {{ request()->is('admin/ingredients') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-5 text-center">◈</span>
                <span>Inventory</span>
            </a>

            <a href="{{ url('/admin/payments') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition
               {{ request()->is('admin/payments') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-5 text-center">▣</span>
                <span>Payments</span>
            </a>

            <a href="{{ url('/admin/reservations') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition
               {{ request()->is('admin/reservations') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">

                <div class="flex items-center gap-3">
                    <span class="w-5 text-center">◷</span>
                    <span>Reservations</span>
                </div>

                @if ($pendingReservationsCount > 0)
                    <span class="min-w-[22px] h-[22px] px-2 rounded-full flex items-center justify-center text-[11px] font-bold
                        {{ request()->is('admin/reservations') ? 'bg-white text-orange-500' : 'bg-red-500 text-white' }}">
                        {{ $pendingReservationsCount > 99 ? '99+' : $pendingReservationsCount }}
                    </span>
                @endif
            </a>

            <a href="{{ url('/admin/reports') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition
               {{ request()->is('admin/reports') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-5 text-center">▥</span>
                <span>Reports & Forecast</span>
            </a>

            <a href="{{ url('/admin/users') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition
               {{ request()->is('admin/users') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-5 text-center">♙</span>
                <span>User Management</span>
            </a>

            <a href="{{ url('/admin/settings') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition
               {{ request()->is('admin/settings') ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span class="w-5 text-center">⚙</span>
                <span>Settings</span>
            </a>
        </nav>

        <!-- Admin Account + Logout -->
        <div class="px-4 pb-5 space-y-3">
            <div class="rounded-2xl border border-orange-100 bg-white/80 px-4 py-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center text-sm font-bold shadow-md shadow-orange-200">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">
                            {{ Auth::user()->name ?? 'Admin' }}
                        </p>
                        <p class="text-xs text-gray-400">Owner / Admin</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                    class="w-full rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-100 transition">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Right Side Scroll Area -->
    <div class="ml-[240px] h-screen overflow-y-auto">

        <!-- Page Content -->
        <main class="p-7">
            <div class="max-w-[1500px] mx-auto">
                @yield('content')
            </div>
        </main>

    </div>
</div>

@stack('scripts')

</body>
</html>