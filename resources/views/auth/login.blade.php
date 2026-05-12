<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineSync+ Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow p-8">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-orange-500">DineSync+</h1>
            <p class="text-gray-500 mt-2">Login to continue</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />

                <x-text-input
                    id="email"
                    class="block mt-2 w-full rounded-xl"
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
                    class="block mt-2 w-full rounded-xl"
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
                        class="text-sm text-orange-500 hover:text-orange-600"
                        href="{{ route('password.request') }}"
                    >
                        Forgot password?
                    </a>
                @endif
            </div>

            <button
                type="submit"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 rounded-xl transition"
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

        <p class="text-xs text-gray-400 text-center mt-6">
            Admin and staff accounts are managed by the system admin.
        </p>

    </div>

</body>
</html>