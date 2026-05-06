<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineSync+ Admin Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f7f7f7] text-gray-800">

<div class="min-h-screen flex">

    <!-- Left Branding Panel -->
    <div class="hidden lg:flex w-1/2 bg-white border-r border-gray-200 items-center justify-center px-16">
        <div class="max-w-md">
            <p class="text-sm font-semibold text-orange-500 mb-3">
                Chef Oppa Admin
            </p>

            <h1 class="text-4xl font-bold leading-tight mb-4">
                Welcome back to <span class="text-orange-500">DineSync+</span>
            </h1>

            <p class="text-gray-500 text-base leading-7 mb-8">
                Manage restaurant operations in one place. Track menu items, inventory,
                payments, reports, and staff accounts through the admin dashboard.
            </p>

            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-500 flex items-center justify-center">
                        📋
                    </div>
                    <div>
                        <p class="font-semibold">Menu & Inventory</p>
                        <p class="text-sm text-gray-500">
                            Manage menu items and monitor ingredient stock levels.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 text-green-500 flex items-center justify-center">
                        💳
                    </div>
                    <div>
                        <p class="font-semibold">Payments & Reports</p>
                        <p class="text-sm text-gray-500">
                            View transactions, sales reports, and forecasting insights.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-500 flex items-center justify-center">
                        👥
                    </div>
                    <div>
                        <p class="font-semibold">Staff Management</p>
                        <p class="text-sm text-gray-500">
                            Control admin, cashier, service, and kitchen staff access.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Panel -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-10">
        <div class="w-full max-w-md">

            <!-- Mobile Brand -->
            <div class="lg:hidden mb-8 text-center">
                <h1 class="text-2xl font-bold text-orange-500">Chef Oppa Admin</h1>
                <p class="text-sm text-gray-500 mt-1">POS Management System</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8">
                <div class="mb-8">
                    <p class="text-sm font-semibold text-orange-500 mb-2">DineSync+ Admin</p>
                    <h2 class="text-2xl font-bold">Sign in to your account</h2>
                    <p class="text-gray-500 mt-1">Access your restaurant admin dashboard.</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="mb-2" />

                        <x-text-input
                            id="email"
                            class="block w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm focus:border-orange-300 focus:ring-orange-200"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="admin@dinesync.com"
                        />

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" class="mb-2" />

                        <x-text-input
                            id="password"
                            class="block w-full h-12 rounded-xl border-gray-200 bg-gray-50 px-4 text-sm focus:border-orange-300 focus:ring-orange-200"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me + Forgot Password -->
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <label for="remember_me" class="inline-flex items-center">
                            <input
                                id="remember_me"
                                type="checkbox"
                                class="rounded border-gray-300 text-orange-500 shadow-sm focus:ring-orange-200"
                                name="remember"
                            >
                            <span class="ms-2 text-sm text-gray-600">
                                {{ __('Remember me') }}
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a
                                class="text-sm text-orange-500 hover:text-orange-600 font-medium"
                                href="{{ route('password.request') }}"
                            >
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    <button
                        type="submit"
                        class="w-full h-12 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
                    >
                        {{ __('Log In') }}
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-400">
                        Chef Oppa POS Management System
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>