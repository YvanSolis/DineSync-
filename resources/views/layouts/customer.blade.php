<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chef Oppa Customer Portal</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            overflow-x: hidden;
        }

        /* ================================
           CUSTOMER NAVBAR
        ================================= */

        .customer-navbar {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(229, 231, 235, 0.95);
        }

        .customer-nav-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0.7rem 1.5rem;
        }

        .customer-brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 16px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.12);
        }

        .customer-brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .customer-menu-button {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            border: 1px solid #e5e7eb;
            background: white;
            align-items: center;
            justify-content: center;
            color: #374151;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }

        .customer-mobile-panel {
            max-height: calc(100vh - 70px);
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.98);
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
        }

        .customer-mobile-panel-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0.85rem 1rem 1rem;
        }

        .customer-mobile-links {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.45rem;
        }

        .customer-mobile-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 1rem;
            padding: 0.8rem 1rem;
            font-size: 0.92rem;
            font-weight: 800;
            transition: all 160ms ease;
        }

        .customer-mobile-link-active {
            background: #fff7ed;
            color: #f97316;
        }

        .customer-mobile-link-normal {
            color: #4b5563;
        }

        .customer-mobile-link-normal:hover {
            background: #fff7ed;
            color: #f97316;
        }

        .customer-mobile-userbox {
            margin-top: 0.85rem;
            padding-top: 0.85rem;
            border-top: 1px solid #f3f4f6;
        }

        .customer-mobile-user-card {
            border-radius: 1rem;
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            padding: 0.8rem 1rem;
        }

        .customer-mobile-logout {
            width: 100%;
            margin-top: 0.65rem;
            border-radius: 1rem;
            background: #f97316;
            color: white;
            padding: 0.85rem 1rem;
            font-size: 0.9rem;
            font-weight: 900;
            transition: background 160ms ease;
        }

        .customer-mobile-logout:hover {
            background: #ea580c;
        }

        /* ================================
           FORCE RESPONSIVE CUSTOMER NAV
        ================================= */

        @media (min-width: 1024px) {
            .customer-menu-button {
                display: none !important;
            }

            .customer-mobile-panel {
                display: none !important;
            }
        }

        @media (max-width: 1023px) {
            .customer-menu-button {
                display: flex !important;
            }

            .customer-desktop-links,
            .customer-desktop-auth {
                display: none !important;
            }
        }

        /* ================================
           CUSTOMER CHATBOT
        ================================= */

        #customer-chatbot-root {
            position: fixed !important;
            right: 24px !important;
            bottom: 24px !important;
            left: auto !important;
            top: auto !important;
            z-index: 99999 !important;
            transform: none !important;
        }

        #customer-chatbot-window {
            position: fixed !important;
            right: 24px !important;
            bottom: 96px !important;
            left: auto !important;
            top: auto !important;
            z-index: 99999 !important;
            transform: none !important;
        }

        #customer-chatbot-button {
            position: fixed !important;
            right: 24px !important;
            bottom: 24px !important;
            left: auto !important;
            top: auto !important;
            z-index: 99999 !important;
            transform: none !important;
        }

        /* ================================
           COMPACT CUSTOMER FOOTER
        ================================= */

        .customer-footer {
            margin-top: 0 !important;
            background:
                linear-gradient(135deg, rgba(17, 24, 39, 0.98), rgba(3, 7, 18, 0.98));
            color: white;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .customer-footer-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 18px 24px;
        }

        .customer-footer-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .customer-footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .customer-footer-logo {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: #fff7ed;
            border: 1px solid rgba(251, 146, 60, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 12px 24px rgba(249, 115, 22, 0.18);
        }

        .customer-footer-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .customer-footer-title {
            font-size: 1rem;
            font-weight: 900;
            line-height: 1.2;
            color: white;
        }

        .customer-footer-subtitle {
            font-size: 0.76rem;
            color: #9ca3af;
            margin-top: 2px;
        }

        .customer-footer-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            font-size: 0.78rem;
            color: #d1d5db;
            font-weight: 700;
            flex-wrap: wrap;
        }

        .customer-footer-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.18);
            white-space: nowrap;
        }

        .customer-footer-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #4ade80;
            flex-shrink: 0;
        }

        .customer-footer-links {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
            font-size: 0.8rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .customer-footer-links a {
            color: #d1d5db;
            transition: color 160ms ease;
        }

        .customer-footer-links a:hover {
            color: #fb923c;
        }

        .customer-footer-bottom {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 0.72rem;
            color: #6b7280;
        }

        @media (max-width: 1024px) {
            .customer-footer-main {
                align-items: flex-start;
                flex-direction: column;
                gap: 14px;
            }

            .customer-footer-info {
                justify-content: flex-start;
            }

            .customer-footer-links {
                justify-content: flex-start;
            }
        }

        @media (max-width: 640px) {
            .customer-nav-inner {
                padding: 0.55rem 0.9rem;
            }

            .customer-brand-logo {
                width: 40px;
                height: 40px;
                border-radius: 14px;
            }

            .customer-brand-title {
                font-size: 0.98rem !important;
            }

            .customer-brand-subtitle {
                font-size: 0.68rem !important;
            }

            .customer-menu-button {
                width: 40px;
                height: 40px;
                border-radius: 13px;
            }

            .customer-mobile-panel-inner {
                padding: 0.7rem 0.9rem 0.9rem;
            }

            .customer-mobile-link {
                padding: 0.72rem 0.85rem;
                border-radius: 0.9rem;
                font-size: 0.86rem;
            }

            .customer-mobile-userbox {
                margin-top: 0.7rem;
                padding-top: 0.7rem;
            }

            .customer-mobile-user-card {
                padding: 0.7rem 0.85rem;
                border-radius: 0.9rem;
            }

            .customer-mobile-logout {
                margin-top: 0.55rem;
                padding: 0.75rem 0.85rem;
                border-radius: 0.9rem;
                font-size: 0.85rem;
            }

            #customer-chatbot-root {
                right: 14px !important;
                bottom: 14px !important;
            }

            #customer-chatbot-window {
                right: 14px !important;
                bottom: 84px !important;
                width: calc(100vw - 28px) !important;
                max-width: calc(100vw - 28px) !important;
                max-height: calc(100vh - 120px) !important;
            }

            #customer-chatbot-button {
                right: 14px !important;
                bottom: 14px !important;
                width: 50px !important;
                height: 50px !important;
                font-size: 0.9rem !important;
            }

            .customer-footer-inner {
                padding: 12px 14px;
            }

            .customer-footer-main {
                align-items: center;
                text-align: center;
                gap: 10px;
            }

            .customer-footer-brand {
                justify-content: center;
            }

            .customer-footer-logo {
                width: 34px;
                height: 34px;
                border-radius: 12px;
            }

            .customer-footer-title {
                font-size: 0.86rem;
            }

            .customer-footer-subtitle {
                font-size: 0.68rem;
            }

            .customer-footer-info {
                gap: 8px;
                font-size: 0.68rem;
                line-height: 1.3;
            }

            .customer-footer-pill {
                padding: 6px 10px;
                font-size: 0.68rem;
            }

            .customer-footer-location,
            .customer-footer-contact {
                display: none;
            }

            .customer-footer-links {
                display: none;
            }

            .customer-footer-bottom {
                margin-top: 9px;
                padding-top: 8px;
                justify-content: center;
                text-align: center;
                font-size: 0.65rem;
            }

            .customer-footer-bottom p:last-child {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-[#f7f7f7] text-gray-800">

    <!-- CUSTOMER NAVBAR -->
    <nav
        x-data="{ mobileMenuOpen: false }"
        class="customer-navbar sticky top-0 z-50"
    >
        <div class="customer-nav-inner">
            <div class="flex items-center justify-between gap-4">

                <!-- Logo -->
                <a href="{{ route('customer.home') }}" class="flex items-center gap-3 min-w-0">
                    <div class="customer-brand-logo">
                        <img
                            src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                            alt="Chef Oppa Logo"
                        >
                    </div>

                    <div class="min-w-0">
                        <h1 class="customer-brand-title text-base sm:text-lg font-black leading-none truncate">
                            Chef Oppa
                        </h1>
                        <p class="customer-brand-subtitle text-[11px] sm:text-xs text-gray-400 truncate">
                            Powered by DineSync+
                        </p>
                    </div>
                </a>

                <!-- Desktop Links -->
                <div class="customer-desktop-links hidden lg:flex items-center gap-8 text-sm font-semibold">
                    <a
                        href="{{ route('customer.home') }}"
                        class="{{ request()->routeIs('customer.home') ? 'text-orange-500' : 'text-gray-600 hover:text-orange-500' }} transition"
                    >
                        Home
                    </a>

                    <a
                        href="{{ route('customer.menu') }}"
                        class="{{ request()->routeIs('customer.menu') ? 'text-orange-500' : 'text-gray-600 hover:text-orange-500' }} transition"
                    >
                        Menu
                    </a>

                    <a
                        href="{{ route('customer.reservations.index') }}"
                        class="{{ request()->routeIs('customer.reservations.*') ? 'text-orange-500' : 'text-gray-600 hover:text-orange-500' }} transition"
                    >
                        Reservations
                    </a>
                </div>

                <!-- Desktop Auth Buttons -->
                <div class="customer-desktop-auth hidden lg:flex items-center gap-3">
                    @guest
                        <a
                            href="{{ route('login') }}"
                            class="text-sm px-4 py-2 rounded-xl border border-orange-500 text-orange-500 hover:bg-orange-50 transition font-semibold"
                        >
                            Login
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="text-sm px-4 py-2 rounded-xl bg-orange-500 text-white hover:bg-orange-600 transition font-semibold"
                        >
                            Register
                        </a>
                    @endguest

                    @auth
                        <div class="flex items-center gap-3">
                            <div class="text-right max-w-[180px]">
                                <p class="text-sm font-bold text-gray-700 truncate">
                                    {{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-gray-400 capitalize truncate">
                                    {{ auth()->user()->role }}
                                </p>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button
                                    type="submit"
                                    class="text-sm px-4 py-2 rounded-xl bg-orange-500 text-white hover:bg-orange-600 transition font-semibold"
                                >
                                    Logout
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>

                <!-- Mobile Button -->
                <button
                    type="button"
                    class="customer-menu-button lg:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    :aria-expanded="mobileMenuOpen.toString()"
                    aria-label="Toggle menu"
                >
                    <span x-show="!mobileMenuOpen" class="text-lg leading-none">☰</span>
                    <span x-cloak x-show="mobileMenuOpen" class="text-xl leading-none">×</span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div
            x-cloak
            x-show="mobileMenuOpen"
            x-transition
            @click.outside="mobileMenuOpen = false"
            class="customer-mobile-panel lg:hidden"
        >
            <div class="customer-mobile-panel-inner">

                <div class="customer-mobile-links">
                    <a
                        href="{{ route('customer.home') }}"
                        class="customer-mobile-link {{ request()->routeIs('customer.home') ? 'customer-mobile-link-active' : 'customer-mobile-link-normal' }}"
                    >
                        <span>Home</span>
                        @if (request()->routeIs('customer.home'))
                            <span class="text-xs">●</span>
                        @endif
                    </a>

                    <a
                        href="{{ route('customer.menu') }}"
                        class="customer-mobile-link {{ request()->routeIs('customer.menu') ? 'customer-mobile-link-active' : 'customer-mobile-link-normal' }}"
                    >
                        <span>Menu</span>
                        @if (request()->routeIs('customer.menu'))
                            <span class="text-xs">●</span>
                        @endif
                    </a>

                    <a
                        href="{{ route('customer.reservations.index') }}"
                        class="customer-mobile-link {{ request()->routeIs('customer.reservations.*') ? 'customer-mobile-link-active' : 'customer-mobile-link-normal' }}"
                    >
                        <span>Reservations</span>
                        @if (request()->routeIs('customer.reservations.*'))
                            <span class="text-xs">●</span>
                        @endif
                    </a>
                </div>

                <div class="customer-mobile-userbox">
                    @guest
                        <div class="grid grid-cols-2 gap-2">
                            <a
                                href="{{ route('login') }}"
                                class="text-sm px-4 py-3 rounded-xl border border-orange-500 text-orange-500 text-center font-black"
                            >
                                Login
                            </a>

                            <a
                                href="{{ route('register') }}"
                                class="text-sm px-4 py-3 rounded-xl bg-orange-500 text-white text-center font-black"
                            >
                                Register
                            </a>
                        </div>
                    @endguest

                    @auth
                        <div>
                            <div class="customer-mobile-user-card">
                                <p class="text-sm font-black text-gray-700 truncate">
                                    {{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-gray-400 capitalize truncate mt-0.5">
                                    {{ auth()->user()->role }}
                                </p>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button
                                    type="submit"
                                    class="customer-mobile-logout"
                                >
                                    Logout
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>

            </div>
        </div>
    </nav>

    <!-- PAGE CONTENT -->
    <main class="min-h-[calc(100vh-73px)]">
        @yield('content')
    </main>

    <!-- COMPACT FOOTER -->
    <footer id="contact" class="customer-footer">
        <div class="customer-footer-inner">

            <div class="customer-footer-main">

                <!-- Brand -->
                <div class="customer-footer-brand">
                    <div class="customer-footer-logo">
                        <img
                            src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                            alt="Chef Oppa Logo"
                        >
                    </div>

                    <div class="min-w-0">
                        <p class="customer-footer-title">
                            Chef Oppa
                        </p>
                        <p class="customer-footer-subtitle">
                            Powered by DineSync+
                        </p>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="customer-footer-info">
                    <span class="customer-footer-pill">
                        <span class="customer-footer-dot"></span>
                        Open Today • 10:00 AM - 9:00 PM
                    </span>

                    <span class="customer-footer-location">
                        Quezon City
                    </span>

                    <span class="customer-footer-contact">
                        0912 345 6789
                    </span>
                </div>

                <!-- Links -->
                <div class="customer-footer-links">
                    <a href="{{ route('customer.home') }}">
                        Home
                    </a>

                    <a href="{{ route('customer.menu') }}">
                        Menu
                    </a>

                    <a href="{{ route('customer.reservations.index') }}">
                        Reservations
                    </a>
                </div>

            </div>

            <div class="customer-footer-bottom">
                <p>
                    © {{ date('Y') }} Chef Oppa. All rights reserved.
                </p>

                <p>
                    Customer portal powered by DineSync+.
                </p>
            </div>

        </div>
    </footer>

    <!-- OPENAI CUSTOMER CHATBOT -->
    <div
        id="customer-chatbot-root"
        x-data="{
            open: false,
            message: '',
            loading: false,
            messages: [
                {
                    sender: 'bot',
                    text: 'Hi! I am Chef Oppa Assistant powered by DineSync+. Ask me about best sellers, food under your budget, reservation fee, GCash payment, opening hours, location, and available meals.'
                }
            ],

            async sendMessage() {
                if (!this.message.trim() || this.loading) return;

                const userMessage = this.message.trim();

                this.messages.push({
                    sender: 'user',
                    text: userMessage
                });

                this.message = '';
                this.loading = true;

                this.$nextTick(() => {
                    const box = this.$refs.chatMessages;
                    if (box) {
                        box.scrollTop = box.scrollHeight;
                    }
                });

                try {
                    const response = await fetch('{{ route('customer.chatbot.ask') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            message: userMessage
                        })
                    });

                    const data = await response.json();

                    this.messages.push({
                        sender: 'bot',
                        text: data.reply ?? 'Sorry, I could not understand that.'
                    });
                } catch (error) {
                    this.messages.push({
                        sender: 'bot',
                        text: 'Sorry, something went wrong. Please try again.'
                    });
                }

                this.loading = false;

                this.$nextTick(() => {
                    const box = this.$refs.chatMessages;
                    if (box) {
                        box.scrollTop = box.scrollHeight;
                    }
                });
            }
        }"
    >
        <!-- Chat Box -->
        <div
            id="customer-chatbot-window"
            x-cloak
            x-show="open"
            x-transition
            class="w-[380px] max-w-[calc(100vw-3rem)] bg-white border border-gray-200 rounded-3xl shadow-2xl overflow-hidden"
        >
            <!-- Header -->
            <div class="bg-[#111827] text-white px-5 py-4 flex items-center justify-between">
                <div>
                    <h3 class="font-bold">Chef Oppa Assistant</h3>
                    <p class="text-xs text-gray-300">Powered by DineSync+</p>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white font-bold"
                >
                    ×
                </button>
            </div>

            <!-- Messages -->
            <div
                x-ref="chatMessages"
                class="h-[330px] max-h-[42vh] overflow-y-auto bg-gray-50 px-4 py-4 space-y-3"
            >
                <template x-for="(chat, index) in messages" :key="index">
                    <div
                        class="flex"
                        :class="chat.sender === 'user' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[82%] rounded-2xl px-4 py-3 text-sm leading-6"
                            :class="chat.sender === 'user'
                                ? 'bg-orange-500 text-white rounded-br-md'
                                : 'bg-white text-gray-700 border border-gray-200 rounded-bl-md'"
                        >
                            <p x-text="chat.text"></p>
                        </div>
                    </div>
                </template>

                <div x-show="loading" class="flex justify-start">
                    <div class="max-w-[82%] rounded-2xl rounded-bl-md px-4 py-3 text-sm bg-white text-gray-500 border border-gray-200">
                        Typing...
                    </div>
                </div>
            </div>

            <!-- Input -->
            <form
                class="p-4 border-t border-gray-100 bg-white"
                @submit.prevent="sendMessage"
            >
                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        x-model="message"
                        placeholder="Ask about menu or reservations..."
                        class="flex-1 rounded-2xl border-gray-200 text-sm focus:border-orange-300 focus:ring-orange-200"
                    >

                    <button
                        type="submit"
                        :disabled="loading"
                        class="px-4 py-2.5 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold disabled:opacity-60"
                    >
                        Send
                    </button>
                </div>
            </form>
        </div>

        <!-- Floating Button -->
        <button
            id="customer-chatbot-button"
            type="button"
            @click="open = !open"
            class="w-14 h-14 rounded-full bg-orange-500 hover:bg-orange-600 text-white shadow-2xl shadow-orange-500/30 flex items-center justify-center font-black text-lg"
        >
            AI
        </button>
    </div>

</body>
</html>