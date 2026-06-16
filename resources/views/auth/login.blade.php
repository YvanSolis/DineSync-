<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineSync+ Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen flex items-center justify-center px-4 py-8 bg-cover bg-center bg-no-repeat"
    style="background-image: url('{{ asset('images/customer-menu/login-bg.png') }}');"
>

    <div class="w-full max-w-md bg-white/95 rounded-2xl shadow-xl p-8 border border-orange-100">

        <div class="text-center mb-8">
            <img
                src="{{ asset('images/customer-menu/Dinesync-logo.png') }}"
                alt="DineSync+ Logo"
                class="mx-auto h-24 w-auto mb-3"
            >

            <h1
                class="text-4xl font-extrabold tracking-tight"
                style="font-family: 'Poppins', sans-serif;"
            >
                <span class="text-gray-800">Dine</span><span class="text-orange-500">Sync</span><span class="text-orange-500">+</span>
            </h1>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />

                <x-text-input
                    id="email"
                    class="block mt-2 w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your email"
                />

                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input
                    id="password"
                    class="block mt-2 w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center">
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="rounded border-gray-300 text-orange-500 shadow-sm focus:ring-orange-500"
                        name="remember"
                    >

                    <span class="ms-2 text-sm text-gray-600">
                        Remember me
                    </span>
                </label>

                @if (Route::has('password.request'))
                    <a
                        class="text-sm text-orange-500 hover:text-orange-600 font-semibold"
                        href="{{ route('password.request') }}"
                    >
                        Forgot password?
                    </a>
                @endif
            </div>

            <button
                type="submit"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 rounded-xl transition shadow-md"
            >
                Log In
            </button>
        </form>

        @if (Route::has('register'))
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    New customer?
                    <a href="{{ route('register') }}" class="text-orange-500 font-semibold hover:text-orange-600">
                        Create an account
                    </a>
                </p>
            </div>
        @endif

    </div>

</body>
</html>