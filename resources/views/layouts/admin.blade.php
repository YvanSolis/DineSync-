<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DineSync+ Admin</title>

    <link rel="icon" href="{{ asset('favicon2.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon2.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .admin-bg {
            background:
                linear-gradient(135deg, rgba(255, 247, 237, 0.45), rgba(255, 255, 255, 0.58)),
                url('{{ asset('images/customer-menu/login-bg.png') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .sidebar-bg {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(255, 247, 237, 0.68)),
                url('{{ asset('images/customer-menu/login-bg.png') }}');
            background-size: cover;
            background-position: center;
        }

        .brand-card {
            background:
                linear-gradient(135deg, rgba(249, 115, 22, 0.72), rgba(251, 146, 60, 0.60)),
                url('{{ asset('images/login-bg.png') }}');
            background-size: cover;
            background-position: center;
        }

        .no-scrollbar::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        .no-scrollbar {
            scrollbar-width: none;
        }

        @media (max-width: 1023px) {
            .admin-bg {
                background-attachment: scroll;
            }
        }
    </style>
</head>

@php
    $pendingReservationsCount = \App\Models\Reservation::where('status', 'pending')->count();

    $navLinks = [
        [
            'label' => 'Dashboard',
            'url' => url('/admin/dashboard'),
            'icon' => '⌂',
            'active' => request()->is('admin/dashboard'),
        ],
        [
            'label' => 'Menu Management',
            'url' => url('/admin/menu-items'),
            'icon' => '☰',
            'active' => request()->is('admin/menu-items*'),
        ],
        [
            'label' => 'Inventory',
            'url' => url('/admin/ingredients'),
            'icon' => '◈',
            'active' => request()->is('admin/ingredients*'),
        ],
        [
            'label' => 'Payments',
            'url' => url('/admin/payments'),
            'icon' => '▣',
            'active' => request()->is('admin/payments*'),
        ],
        [
            'label' => 'Reservations',
            'url' => url('/admin/reservations'),
            'icon' => '◷',
            'active' => request()->is('admin/reservations*'),
            'badge' => $pendingReservationsCount,
        ],
        [
            'label' => 'Reports & Forecast',
            'url' => url('/admin/reports'),
            'icon' => '▥',
            'active' => request()->is('admin/reports*'),
        ],
        [
            'label' => 'Audit Trail',
            'url' => url('/admin/audit-trail'),
            'icon' => '📝',
            'active' => request()->is('admin/audit-trail*'),
        ],
        [
            'label' => 'User Management',
            'url' => url('/admin/users'),
            'icon' => '♙',
            'active' => request()->is('admin/users*'),
        ],
        [
            'label' => 'Settings',
            'url' => url('/admin/settings'),
            'icon' => '⚙',
            'active' => request()->is('admin/settings*'),
        ],
    ];
@endphp

<body class="text-gray-800 min-h-screen overflow-x-hidden">

<div class="min-h-screen admin-bg">

    <!-- Mobile Top Bar -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-white/90 backdrop-blur border-b border-orange-100 shadow-sm">
        <div class="h-16 px-4 flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white border border-orange-100 flex items-center justify-center overflow-hidden shadow-sm shrink-0">
                    <img
                        src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                        alt="Chef Oppa Logo"
                        class="w-full h-full object-cover"
                    >
                </div>

                <div class="min-w-0">
                    <h1 class="text-sm font-extrabold text-gray-900 truncate">Chef Oppa</h1>
                    <p class="text-[11px] text-gray-500 truncate">Admin Control Panel</p>
                </div>
            </div>

            <button
                type="button"
                onclick="openAdminSidebar()"
                class="w-11 h-11 rounded-xl bg-orange-500 text-white flex items-center justify-center text-xl shadow-md shadow-orange-200"
                aria-label="Open admin menu"
            >
                ☰
            </button>
        </div>
    </header>

    <!-- Mobile Overlay -->
    <div
        id="adminSidebarOverlay"
        onclick="closeAdminSidebar()"
        class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden"
    ></div>

    <!-- Mobile Drawer Sidebar -->
    <aside
        id="mobileAdminSidebar"
        class="fixed left-0 top-0 z-50 h-dvh w-[285px] max-w-[86vw] sidebar-bg border-r border-orange-100/70 flex flex-col overflow-hidden shadow-2xl transform -translate-x-full transition-transform duration-300 lg:hidden"
    >
        <div class="px-5 py-5 border-b border-orange-100/70 shrink-0">
            <div class="flex items-center justify-between gap-3 mb-4">
                <p class="text-xs font-bold text-orange-500 uppercase tracking-wide">Admin Menu</p>

                <button
                    type="button"
                    onclick="closeAdminSidebar()"
                    class="w-9 h-9 rounded-xl bg-white/80 hover:bg-orange-50 border border-orange-100 text-gray-600"
                    aria-label="Close admin menu"
                >
                    ✕
                </button>
            </div>

            <div class="brand-card rounded-2xl px-4 py-4 text-gray-900 shadow-lg shadow-orange-200/60">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-white/60 flex items-center justify-center overflow-hidden shadow-sm shrink-0">
                        <img
                            src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                            alt="Chef Oppa Logo"
                            class="w-full h-full object-cover"
                        >
                    </div>

                    <div class="min-w-0">
                        <h1 class="text-[18px] font-extrabold leading-tight truncate">
                            Chef Oppa
                        </h1>
                        <p class="text-[11px] text-gray-700 font-semibold">
                            Admin Control Panel
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <nav class="flex-1 min-h-0 overflow-y-auto no-scrollbar px-4 py-4 space-y-1.5 text-[13px] font-medium">
            @foreach ($navLinks as $link)
                <a href="{{ $link['url'] }}"
                   onclick="closeAdminSidebar()"
                   class="flex items-center {{ isset($link['badge']) ? 'justify-between' : '' }} gap-3 px-3.5 py-2.5 rounded-xl transition
                   {{ $link['active'] ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">

                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-5 text-center shrink-0">{{ $link['icon'] }}</span>
                        <span class="truncate">{{ $link['label'] }}</span>
                    </div>

                    @if (($link['badge'] ?? 0) > 0)
                        <span class="min-w-[22px] h-[22px] px-2 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0
                            {{ $link['active'] ? 'bg-white text-orange-500' : 'bg-red-500 text-white' }}">
                            {{ $link['badge'] > 99 ? '99+' : $link['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="shrink-0 px-4 pt-3 pb-4 border-t border-orange-100/70 bg-white/85 backdrop-blur">
            <div class="rounded-2xl border border-orange-100 bg-white/90 px-3 py-3 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center text-sm font-bold shadow-md shadow-orange-200 shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">
                            {{ Auth::user()->name ?? 'Admin' }}
                        </p>
                        <p class="text-xs text-gray-400">Owner / Admin</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="w-full rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-100 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Desktop Fixed Sidebar -->
    <aside class="hidden lg:flex fixed left-0 top-0 z-30 h-screen w-[240px] sidebar-bg border-r border-orange-100/70 flex-col overflow-hidden shadow-[8px_0_30px_rgba(15,23,42,0.04)]">

        <div class="px-5 py-5 border-b border-orange-100/70 shrink-0">
            <div class="brand-card rounded-2xl px-4 py-4 text-gray-900 shadow-lg shadow-orange-200/60">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-white/60 flex items-center justify-center overflow-hidden shadow-sm shrink-0">
                        <img
                            src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                            alt="Chef Oppa Logo"
                            class="w-full h-full object-cover"
                        >
                    </div>

                    <div class="min-w-0">
                        <h1 class="text-[18px] font-extrabold leading-tight truncate">
                            Chef Oppa
                        </h1>
                        <p class="text-[11px] text-gray-700 font-semibold">
                            Admin Control Panel
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <nav class="flex-1 min-h-0 overflow-y-auto no-scrollbar px-4 py-5 space-y-1.5 text-[13px] font-medium">
            @foreach ($navLinks as $link)
                <a href="{{ $link['url'] }}"
                   class="flex items-center {{ isset($link['badge']) ? 'justify-between' : '' }} gap-3 px-3.5 py-2.5 rounded-xl transition
                   {{ $link['active'] ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">

                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-5 text-center shrink-0">{{ $link['icon'] }}</span>
                        <span class="truncate">{{ $link['label'] }}</span>
                    </div>

                    @if (($link['badge'] ?? 0) > 0)
                        <span class="min-w-[22px] h-[22px] px-2 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0
                            {{ $link['active'] ? 'bg-white text-orange-500' : 'bg-red-500 text-white' }}">
                            {{ $link['badge'] > 99 ? '99+' : $link['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="shrink-0 px-4 pt-3 pb-5 border-t border-orange-100/70 bg-white/75 backdrop-blur">
            <div class="rounded-2xl border border-orange-100 bg-white/90 px-3 py-3 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center text-sm font-bold shadow-md shadow-orange-200 shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">
                            {{ Auth::user()->name ?? 'Admin' }}
                        </p>
                        <p class="text-xs text-gray-400">Owner / Admin</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="w-full rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-100 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="min-h-screen lg:ml-[240px] pt-16 lg:pt-0">
        <main class="admin-content w-full px-4 py-5 sm:px-5 sm:py-6 lg:p-7 overflow-x-hidden">
            <div class="w-full max-w-[1500px] mx-auto">
                @yield('content')
            </div>
        </main>
    </div>
</div>

@stack('scripts')

<script>
function openAdminSidebar() {
    const sidebar = document.getElementById('mobileAdminSidebar');
    const overlay = document.getElementById('adminSidebarOverlay');

    sidebar.classList.remove('-translate-x-full');
    sidebar.classList.add('translate-x-0');

    overlay.classList.remove('hidden');

    document.body.classList.add('overflow-hidden');
}

function closeAdminSidebar() {
    const sidebar = document.getElementById('mobileAdminSidebar');
    const overlay = document.getElementById('adminSidebarOverlay');

    sidebar.classList.add('-translate-x-full');
    sidebar.classList.remove('translate-x-0');

    overlay.classList.add('hidden');

    document.body.classList.remove('overflow-hidden');
}

window.addEventListener('resize', function () {
    if (window.innerWidth >= 1024) {
        closeAdminSidebar();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeAdminSidebar();
    }
});
</script>

</body>
</html>