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

            <!-- SUCCESS MESSAGE -->
            @if (session('status'))
                <div class="mb-5 flex justify-center">
                    <div class="inline-flex items-center justify-center rounded-xl bg-green-50 px-4 py-2 text-center border border-green-100">
                        <p class="font-semibold text-sm text-green-600">
                            {{ session('status') }}
                        </p>
                    </div>
                </div>
            @endif

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

                    <div class="relative mt-2">
                        <x-text-input
                            id="password"
                            class="block w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500 pr-12"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        />

                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 hover:text-orange-500 focus:outline-none"
                            aria-label="Show password"
                        >
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>

                            <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.88 5.09A9.77 9.77 0 0112 4.88C18 4.88 21.75 12 21.75 12a18.28 18.28 0 01-3.16 4.16" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.53 6.53C3.8 8.36 2.25 12 2.25 12s3.75 7.12 9.75 7.12a9.7 9.7 0 004.18-.94" />
                            </svg>
                        </button>
                    </div>

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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');

            if (togglePassword && passwordInput && eyeIcon && eyeOffIcon) {
                togglePassword.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';

                    passwordInput.type = isPassword ? 'text' : 'password';

                    eyeIcon.classList.toggle('hidden', isPassword);
                    eyeOffIcon.classList.toggle('hidden', !isPassword);

                    togglePassword.setAttribute(
                        'aria-label',
                        isPassword ? 'Hide password' : 'Show password'
                    );
                });
            }
        });
    </script>

</body>
</html>