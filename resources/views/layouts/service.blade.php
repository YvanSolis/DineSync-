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
</head>

<body class="font-sans bg-gray-50 text-gray-900">
    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 min-h-screen fixed left-0 top-0 bottom-0 flex flex-col">
            <!-- Brand -->
            <div class="h-24 flex items-center px-6 border-b border-gray-100">
                <div>
                    <h1 class="text-2xl font-extrabold text-orange-500 leading-tight">DineSync</h1>
                    <p class="text-sm text-gray-400">Service Staff Panel</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('service.dashboard') }}"
                   class="flex items-center px-4 py-3 rounded-xl text-sm font-medium transition
                   {{ request()->routeIs('service.dashboard') ? 'bg-orange-500 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    Dashboard
                </a>

                <a href="{{ route('service.active-orders') }}"
                   class="flex items-center px-4 py-3 rounded-xl text-sm font-medium transition
                   {{ request()->routeIs('service.active-orders') ? 'bg-orange-500 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    Active Orders
                </a>

                <a href="{{ route('service.table-monitoring') }}"
                   class="flex items-center px-4 py-3 rounded-xl text-sm font-medium transition
                   {{ request()->routeIs('service.table-monitoring') ? 'bg-orange-500 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    Table Monitoring
                </a>

                <a href="{{ route('service.reservations') }}"
                   class="flex items-center px-4 py-3 rounded-xl text-sm font-medium transition
                   {{ request()->routeIs('service.reservations') ? 'bg-orange-500 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    Reservations
                </a>
            </nav>

            <!-- User / Logout -->
            <div class="p-4 border-t border-gray-100">
                <div class="bg-gray-50 rounded-2xl px-4 py-4 mb-3">
                    <p class="font-semibold text-gray-900 truncate">
                        {{ auth()->user()->name ?? 'Service Staff' }}
                    </p>
                    <p class="text-sm text-gray-400">On Duty</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full px-4 py-3 rounded-xl text-sm font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <main class="ml-64 flex-1 min-h-screen">
            <!-- Topbar -->
            <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8">
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 w-80">
                        <input
                            type="text"
                            placeholder="Search..."
                            class="bg-transparent border-0 focus:ring-0 text-sm w-full p-0 text-gray-600 placeholder:text-gray-400">
                    </div>

                    <div class="hidden lg:block bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                        <p class="text-xs text-gray-400">Today</p>
                        <p class="text-sm font-semibold text-gray-700">{{ now()->format('M d, Y') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-2 bg-green-50 text-green-600 px-4 py-2 rounded-xl text-sm font-semibold">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        Online
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">
                            {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="hidden md:block">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ auth()->user()->name ?? 'Service Staff' }}
                            </p>
                            <p class="text-xs text-gray-400">Service Staff</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <section class="p-8">
                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>