<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineSync+ Forgot Password</title>

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
                    Reset your password
                </p>
            </div>

            <div class="mb-5 rounded-2xl bg-orange-50 border border-orange-100 px-4 py-4">
                <p class="text-sm text-gray-600 leading-6">
                    Forgot your password? No problem. Enter your email address and we will send you a password reset link.
                </p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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
                        placeholder="Enter your email"
                    />

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- SUBMIT -->
                <button
                    type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 sm:py-3.5 rounded-xl transition shadow-md text-sm sm:text-base"
                >
                    Email Password Reset Link
                </button>
            </form>

            <div class="mt-5 sm:mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Remember your password?
                    <a href="{{ route('login') }}" class="text-orange-500 font-semibold hover:text-orange-600">
                        Back to login
                    </a>
                </p>
            </div>

        </div>

    </main>

</body>
</html>