<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineSync+ Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html,
        body {
            min-height: 100%;
        }

        body {
            background-image:
                linear-gradient(
                    135deg,
                    rgba(15, 23, 42, 0.62),
                    rgba(67, 31, 12, 0.50)
                ),
                url('{{ asset('images/customer-menu/login-bg.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        @media (max-width: 768px) {
            body {
                background-attachment: scroll;
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center px-4 sm:px-6 py-6 sm:py-10">

    <main class="w-full max-w-[440px]">

        <div class="bg-white/95 backdrop-blur rounded-2xl sm:rounded-3xl shadow-2xl p-5 sm:p-8 border border-orange-100">

            <!-- LOGO / BRAND -->
            <div class="text-center mb-6 sm:mb-8">
                <img
                    src="{{ asset('images/customer-menu/Dinesync-logo.png') }}"
                    alt="DineSync+ Logo"
                    class="mx-auto h-16 sm:h-20 md:h-24 w-auto mb-3"
                >

                <h1
                    class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight"
                    style="font-family: 'Poppins', sans-serif;"
                >
                    <span class="text-gray-800">Dine</span><span class="text-orange-500">Sync</span><span class="text-orange-500">+</span>
                </h1>

                <p class="text-xs sm:text-sm text-gray-500 mt-2">
                    Sign in to continue
                </p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- LOGIN FORM -->
            <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
                @csrf

                <!-- EMAIL -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />

                    <x-text-input
                        id="email"
                        class="block mt-2 w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm sm:text-base"
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

                <!-- PASSWORD -->
                <div>
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input
                        id="password"
                        class="block mt-2 w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm sm:text-base"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- REMEMBER / FORGOT -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
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

                <!-- SUBMIT -->
                <button
                    type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 sm:py-3.5 rounded-xl transition shadow-md text-sm sm:text-base"
                >
                    Log In
                </button>
            </form>

            <!-- REGISTER -->
            @if (Route::has('register'))
                <div class="mt-5 sm:mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        New customer?
                        <a href="{{ route('register') }}" class="text-orange-500 font-semibold hover:text-orange-600">
                            Create an account
                        </a>
                    </p>
                </div>
            @endif

        </div>

    </main>

</body>
</html>