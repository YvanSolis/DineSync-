<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineSync+ Register</title>

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

    <main class="w-full max-w-[460px]">

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
                    Create your customer account
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-4 sm:space-y-5">
                @csrf

                <!-- NAME -->
                <div>
                    <x-input-label for="name" :value="__('Name')" />

                    <x-text-input
                        id="name"
                        class="block mt-2 w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm sm:text-base"
                        type="text"
                        name="name"
                        :value="old('name')"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Enter your name"
                    />

                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

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
                            class="block w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500 pr-12 text-sm sm:text-base"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Create a password"
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.88 5.09A9.77 9.77 0 0112 4.88C18 4.88 21.75 12 21.75 12a18.28 18.28 0 01-3.16 4.16" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.53 6.53C3.8 8.36 2.25 12 2.25 12s3.75 7.12 9.75 7.12a9.7 9.7 0 004.18-.94" />
                            </svg>
                        </button>
                    </div>

                    <!-- PASSWORD STRENGTH INDICATOR -->
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-xs font-medium text-gray-500">
                                Password strength
                            </p>

                            <p id="passwordStrengthText" class="text-xs font-semibold text-gray-400">
                                Empty
                            </p>
                        </div>

                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div
                                id="passwordStrengthBar"
                                class="h-full w-0 rounded-full bg-gray-300 transition-all duration-300"
                            ></div>
                        </div>

                        <p id="passwordHint" class="text-xs text-gray-400 mt-1.5 leading-relaxed">
                            Use at least 8 characters with uppercase, lowercase, number, and symbol.
                        </p>
                    </div>

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- CONFIRM PASSWORD -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                    <div class="relative mt-2">
                        <x-text-input
                            id="password_confirmation"
                            class="block w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500 pr-12 text-sm sm:text-base"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm your password"
                        />

                        <button
                            type="button"
                            id="toggleConfirmPassword"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 hover:text-orange-500 focus:outline-none"
                            aria-label="Show confirm password"
                        >
                            <svg id="confirmEyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>

                            <svg id="confirmEyeOffIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.88 5.09A9.77 9.77 0 0112 4.88C18 4.88 21.75 12 21.75 12a18.28 18.28 0 01-3.16 4.16" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.53 6.53C3.8 8.36 2.25 12 2.25 12s3.75 7.12 9.75 7.12a9.7 9.7 0 004.18-.94" />
                            </svg>
                        </button>
                    </div>

                    <p id="passwordMatchText" class="text-xs mt-2 hidden"></p>

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- SUBMIT -->
                <button
                    type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 sm:py-3.5 rounded-xl transition shadow-md text-sm sm:text-base"
                >
                    Register
                </button>
            </form>

            <div class="mt-5 sm:mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-orange-500 font-semibold hover:text-orange-600">
                        Log in
                    </a>
                </p>

                <p class="text-xs text-gray-400 mt-4">
                    Registration is for customers only.
                </p>
            </div>

        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');
            const passwordHint = document.getElementById('passwordHint');
            const matchText = document.getElementById('passwordMatchText');

            function setupPasswordToggle(buttonId, inputId, eyeId, eyeOffId) {
                const toggleButton = document.getElementById(buttonId);
                const passwordInput = document.getElementById(inputId);
                const eyeIcon = document.getElementById(eyeId);
                const eyeOffIcon = document.getElementById(eyeOffId);

                if (!toggleButton || !passwordInput || !eyeIcon || !eyeOffIcon) {
                    return;
                }

                toggleButton.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';

                    passwordInput.type = isPassword ? 'text' : 'password';

                    eyeIcon.classList.toggle('hidden', isPassword);
                    eyeOffIcon.classList.toggle('hidden', !isPassword);

                    toggleButton.setAttribute(
                        'aria-label',
                        isPassword ? 'Hide password' : 'Show password'
                    );
                });
            }

            function resetStrengthBar() {
                strengthBar.style.width = '0%';
                strengthBar.className = 'h-full w-0 rounded-full bg-gray-300 transition-all duration-300';
                strengthText.textContent = 'Empty';
                strengthText.className = 'text-xs font-semibold text-gray-400';
                passwordHint.textContent = 'Use at least 8 characters with uppercase, lowercase, number, and symbol.';
                passwordHint.className = 'text-xs text-gray-400 mt-1.5 leading-relaxed';
            }

            function checkPasswordStrength(password) {
                let score = 0;

                const hasMinLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSymbol = /[^A-Za-z0-9]/.test(password);

                if (hasMinLength) score++;
                if (hasUppercase) score++;
                if (hasLowercase) score++;
                if (hasNumber) score++;
                if (hasSymbol) score++;

                strengthBar.className = 'h-full rounded-full transition-all duration-300';

                if (password.length === 0) {
                    resetStrengthBar();
                    return;
                }

                if (score <= 2) {
                    strengthBar.style.width = '33%';
                    strengthBar.classList.add('bg-red-500');
                    strengthText.textContent = 'Weak';
                    strengthText.className = 'text-xs font-semibold text-red-600';
                    passwordHint.textContent = 'Weak password. Add more characters, numbers, or symbols.';
                    passwordHint.className = 'text-xs text-red-500 mt-1.5 leading-relaxed';
                } else if (score <= 4) {
                    strengthBar.style.width = '66%';
                    strengthBar.classList.add('bg-yellow-500');
                    strengthText.textContent = 'Medium';
                    strengthText.className = 'text-xs font-semibold text-yellow-600';
                    passwordHint.textContent = 'Medium password. Add the missing uppercase, lowercase, number, or symbol.';
                    passwordHint.className = 'text-xs text-yellow-600 mt-1.5 leading-relaxed';
                } else {
                    strengthBar.style.width = '100%';
                    strengthBar.classList.add('bg-green-500');
                    strengthText.textContent = 'Strong';
                    strengthText.className = 'text-xs font-semibold text-green-600';
                    passwordHint.textContent = 'Strong password.';
                    passwordHint.className = 'text-xs text-green-600 mt-1.5 leading-relaxed';
                }
            }

            function checkPasswordMatch() {
                if (!confirmPasswordInput || !matchText) {
                    return;
                }

                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (confirmPassword.length === 0) {
                    matchText.classList.add('hidden');
                    matchText.textContent = '';
                    return;
                }

                matchText.classList.remove('hidden');

                if (password === confirmPassword) {
                    matchText.textContent = 'Passwords match.';
                    matchText.className = 'text-xs mt-2 text-green-600 font-medium';
                } else {
                    matchText.textContent = 'Passwords do not match.';
                    matchText.className = 'text-xs mt-2 text-red-600 font-medium';
                }
            }

            if (passwordInput) {
                passwordInput.addEventListener('input', function () {
                    checkPasswordStrength(passwordInput.value);
                    checkPasswordMatch();
                });
            }

            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', checkPasswordMatch);
            }

            setupPasswordToggle('togglePassword', 'password', 'eyeIcon', 'eyeOffIcon');
            setupPasswordToggle('toggleConfirmPassword', 'password_confirmation', 'confirmEyeIcon', 'confirmEyeOffIcon');
        });
    </script>

</body>
</html>