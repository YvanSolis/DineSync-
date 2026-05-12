<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DineSync Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $pendingReservationsCount = \App\Models\Reservation::where('status', 'pending')->count();
@endphp

<body class="bg-[#f7f7f7] text-gray-800 min-h-screen">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-[220px] bg-white border-r border-gray-200 flex flex-col">
        <!-- Brand -->
        <div class="px-6 py-6 border-b border-gray-100">
            <h1 class="text-[18px] font-bold text-orange-500 leading-none">Chef Oppa Admin</h1>
            <p class="text-[11px] text-gray-400 mt-1">POS Management System</p>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-5 space-y-1 text-[13px]">

            <a href="{{ url('/admin/dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition
               {{ request()->is('admin/dashboard') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span>⌂</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ url('/admin/menu-items') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition
               {{ request()->is('admin/menu-items') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span>☰</span>
                <span>Menu Management</span>
            </a>

            <a href="{{ url('/admin/ingredients') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition
               {{ request()->is('admin/ingredients') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span>◈</span>
                <span>Inventory</span>
            </a>

            <a href="{{ url('/admin/payments') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition
               {{ request()->is('admin/payments') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span>▣</span>
                <span>Payments</span>
            </a>

            <a href="{{ url('/admin/reservations') }}"
               class="flex items-center justify-between px-3 py-2 rounded-lg transition
               {{ request()->is('admin/reservations') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">

                <div class="flex items-center gap-3">
                    <span>◷</span>
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
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition
               {{ request()->is('admin/reports') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span>▥</span>
                <span>Reports & Forecast</span>
            </a>

            <a href="{{ url('/admin/users') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition
               {{ request()->is('admin/users') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span>♙</span>
                <span>User Management</span>
            </a>

            <a href="{{ url('/admin/settings') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition
               {{ request()->is('admin/settings') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                <span>⚙</span>
                <span>Settings</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 min-w-0">

        <!-- Top Header -->
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between gap-4 flex-wrap">

                <!-- Search + Today -->
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="Search..."
                            class="w-[260px] h-10 pl-10 pr-4 rounded-lg border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300"
                        >

                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                            🔍
                        </span>
                    </div>

                    <button type="button"
                        class="h-10 px-4 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-600 flex items-center gap-2 hover:bg-gray-100">
                        <span>📅</span>
                        <span>Today</span>
                    </button>
                </div>

                <!-- Right Side -->
                <div class="flex items-center gap-4">

                    <!-- Notification -->
                    <a href="{{ url('/admin/reservations') }}" class="relative text-gray-500 hover:text-orange-500 transition">
                        <span class="text-lg">🔔</span>

                        @if ($pendingReservationsCount > 0)
                            <span class="absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $pendingReservationsCount > 99 ? '99+' : $pendingReservationsCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Admin Dropdown -->
                    <div class="relative" id="adminDropdownWrapper">
                        <button type="button" onclick="toggleAdminDropdown()"
                            class="flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-gray-50">
                            <div class="w-8 h-8 rounded-full bg-orange-400 text-white flex items-center justify-center text-xs font-bold">
                                A
                            </div>

                            <span class="text-sm font-medium text-gray-700">
                                Admin
                            </span>

                            <span class="text-gray-400 text-xs">
                                ▾
                            </span>
                        </button>

                        <div id="adminDropdown"
                            class="hidden absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b">
                                <p class="text-sm font-semibold text-gray-700">Admin Account</p>
                                <p class="text-xs text-gray-400">Owner / Admin</p>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button type="submit"
                                    class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>

    </div>
</div>

<script>
function toggleAdminDropdown() {
    const dropdown = document.getElementById('adminDropdown');
    dropdown.classList.toggle('hidden');
}

document.addEventListener('click', function(event) {
    const wrapper = document.getElementById('adminDropdownWrapper');

    if (wrapper && !wrapper.contains(event.target)) {
        document.getElementById('adminDropdown').classList.add('hidden');
    }
});
</script>

@stack('scripts')

</body>
</html>