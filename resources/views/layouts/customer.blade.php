<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineSync+ Customer</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f7f7f7] text-gray-800">

    <!-- CUSTOMER NAVBAR -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

            <!-- Logo -->
            <a href="{{ route('customer.home') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center font-bold">
                    D+
                </div>

                <div>
                    <h1 class="text-lg font-bold leading-none">
                        DineSync+
                    </h1>
                    <p class="text-xs text-gray-400">
                        Chef Oppa Customer Portal
                    </p>
                </div>
            </a>

            <!-- Desktop Links -->
            <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                <a
                    href="{{ route('customer.home') }}"
                    class="{{ request()->routeIs('customer.home') ? 'text-orange-500' : 'text-gray-600 hover:text-orange-500' }}"
                >
                    Home
                </a>

                <a
                    href="{{ route('customer.menu') }}"
                    class="{{ request()->routeIs('customer.menu') ? 'text-orange-500' : 'text-gray-600 hover:text-orange-500' }}"
                >
                    Menu
                </a>

                <a
                    href="{{ route('customer.reservations.index') }}"
                    class="{{ request()->routeIs('customer.reservations.*') ? 'text-orange-500' : 'text-gray-600 hover:text-orange-500' }}"
                >
                    Reservations
                </a>
            </div>

            <!-- Auth Buttons -->
            <div class="hidden md:flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="text-sm px-4 py-2 rounded-xl border border-orange-500 text-orange-500 hover:bg-orange-50 transition">
                        Login
                    </a>

                    <a href="{{ route('register') }}" class="text-sm px-4 py-2 rounded-xl bg-orange-500 text-white hover:bg-orange-600 transition">
                        Register
                    </a>
                @endguest

                @auth
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-700">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="text-xs text-gray-400 capitalize">
                                {{ auth()->user()->role }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="text-sm px-4 py-2 rounded-xl bg-orange-500 text-white hover:bg-orange-600 transition"
                            >
                                Logout
                            </button>
                        </form>
                    </div>
                @endauth
            </div>

            <!-- Mobile Button -->
            <button
                class="md:hidden w-10 h-10 rounded-xl border border-gray-200 flex items-center justify-center text-gray-700"
                onclick="document.getElementById('mobileMenu').classList.toggle('hidden')"
            >
                ☰
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-gray-200 px-6 py-4 space-y-3">
            <a
                href="{{ route('customer.home') }}"
                class="block {{ request()->routeIs('customer.home') ? 'text-orange-500 font-medium' : 'text-gray-600 hover:text-orange-500' }}"
            >
                Home
            </a>

            <a
                href="{{ route('customer.menu') }}"
                class="block {{ request()->routeIs('customer.menu') ? 'text-orange-500 font-medium' : 'text-gray-600 hover:text-orange-500' }}"
            >
                Menu
            </a>

            <a
                href="{{ route('customer.reservations.index') }}"
                class="block {{ request()->routeIs('customer.reservations.*') ? 'text-orange-500 font-medium' : 'text-gray-600 hover:text-orange-500' }}"
            >
                Reservations
            </a>

            <div class="pt-3 border-t border-gray-100">
                @guest
                    <div class="flex gap-3">
                        <a href="{{ route('login') }}" class="text-sm px-4 py-2 rounded-xl border border-orange-500 text-orange-500">
                            Login
                        </a>

                        <a href="{{ route('register') }}" class="text-sm px-4 py-2 rounded-xl bg-orange-500 text-white">
                            Register
                        </a>
                    </div>
                @endguest

                @auth
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="text-xs text-gray-400 capitalize">
                                {{ auth()->user()->role }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="text-sm px-4 py-2 rounded-xl bg-orange-500 text-white"
                            >
                                Logout
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- PAGE CONTENT -->
    <main>
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer id="contact" class="mt-20 bg-[#111827] text-white">
        <div class="max-w-7xl mx-auto px-6 py-12">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

                <!-- Brand -->
                <div class="lg:col-span-5">
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500 text-white flex items-center justify-center font-bold shadow">
                            D+
                        </div>

                        <div>
                            <h3 class="text-2xl font-bold">DineSync+</h3>
                            <p class="text-sm text-gray-400">Chef Oppa Customer Portal</p>
                        </div>
                    </div>

                    <p class="text-gray-400 leading-7 max-w-md">
                        A customer-friendly dining portal for checking available meals,
                        viewing best sellers, and making reservations with Chef Oppa.
                    </p>

                    <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-500/10 text-green-400 text-sm font-semibold">
                        <span class="w-2 h-2 rounded-full bg-green-400"></span>
                        Open Today • 10:00 AM - 9:00 PM
                    </div>
                </div>

                <!-- Visit Info -->
                <div class="lg:col-span-4">
                    <h4 class="text-sm font-semibold uppercase tracking-widest text-gray-300 mb-5">
                        Visit Information
                    </h4>

                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-gray-500 mb-1">Location</p>
                            <p class="text-gray-200">123 Sample Street, Quezon City</p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">Contact Number</p>
                            <p class="text-gray-200">0912 345 6789</p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">Operating Hours</p>
                            <p class="text-gray-200">Monday - Sunday, 10:00 AM - 9:00 PM</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Actions -->
                <div class="lg:col-span-3">
                    <h4 class="text-sm font-semibold uppercase tracking-widest text-gray-300 mb-5">
                        Customer Access
                    </h4>

                    <div class="space-y-3">
                        <a href="{{ route('customer.menu') }}" class="block text-gray-400 hover:text-orange-400 transition">
                            Browse Menu
                        </a>

                        <a href="{{ route('customer.reservations.index') }}" class="block text-gray-400 hover:text-orange-400 transition">
                            Reservations
                        </a>

                        <a href="#contact" class="block text-gray-400 hover:text-orange-400 transition">
                            Contact Restaurant
                        </a>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <span class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-gray-300">
                            f
                        </span>

                        <span class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-gray-300">
                            ig
                        </span>

                        <span class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-gray-300">
                            tt
                        </span>
                    </div>
                </div>

            </div>

            <div class="border-t border-white/10 mt-10 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-500">
                    © {{ date('Y') }} DineSync+. All rights reserved.
                </p>

                <p class="text-sm text-gray-500">
                    Built for Chef Oppa restaurant operations.
                </p>
            </div>

        </div>
    </footer>

    <!-- OPENAI CUSTOMER CHATBOT -->
    <div
        x-data="{
            open: false,
            message: '',
            loading: false,
            messages: [
                {
                    sender: 'bot',
                    text: 'Hi! I am DineSync Assistant powered by OpenAI. Ask me about best sellers, food under your budget, reservation fee, GCash payment, opening hours, location, and available meals.'
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
        class="fixed bottom-6 right-6 z-[9999]"
    >
        <!-- Chat Box -->
        <div
            x-show="open"
            x-transition
            class="w-[380px] max-w-[calc(100vw-3rem)] bg-white border border-gray-200 rounded-3xl shadow-2xl overflow-hidden mb-4"
        >
            <!-- Header -->
            <div class="bg-[#111827] text-white px-5 py-4 flex items-center justify-between">
                <div>
                    <h3 class="font-bold">DineSync Assistant</h3>
                    <p class="text-xs text-gray-300">Powered by OpenAI</p>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center"
                >
                    ×
                </button>
            </div>

            <!-- Messages -->
            <div
                x-ref="chatMessages"
                class="h-[360px] overflow-y-auto p-4 space-y-3 bg-gray-50"
            >
                <template x-for="(chat, index) in messages" :key="index">
                    <div
                        class="flex"
                        :class="chat.sender === 'user' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[82%] rounded-2xl px-4 py-3 text-sm whitespace-pre-line leading-6"
                            :class="chat.sender === 'user'
                                ? 'bg-orange-500 text-white rounded-br-md'
                                : 'bg-white border border-gray-200 text-gray-700 rounded-bl-md'"
                            x-text="chat.text"
                        ></div>
                    </div>
                </template>

                <div x-show="loading" class="flex justify-start">
                    <div class="bg-white border border-gray-200 text-gray-500 rounded-2xl rounded-bl-md px-4 py-3 text-sm">
                        Thinking...
                    </div>
                </div>
            </div>

            <!-- Quick Questions -->
            <div class="px-4 py-3 border-t border-gray-100 bg-white">
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <button
                        type="button"
                        @click="message = 'What are your best sellers?'; sendMessage();"
                        class="shrink-0 px-3 py-2 rounded-full bg-orange-50 text-orange-600 text-xs font-semibold hover:bg-orange-100"
                    >
                        Best sellers
                    </button>

                    <button
                        type="button"
                        @click="message = 'What can I order under 500?'; sendMessage();"
                        class="shrink-0 px-3 py-2 rounded-full bg-orange-50 text-orange-600 text-xs font-semibold hover:bg-orange-100"
                    >
                        Under ₱500
                    </button>

                    <button
                        type="button"
                        @click="message = 'How much is the reservation fee?'; sendMessage();"
                        class="shrink-0 px-3 py-2 rounded-full bg-orange-50 text-orange-600 text-xs font-semibold hover:bg-orange-100"
                    >
                        Reservation fee
                    </button>

                    <button
                        type="button"
                        @click="message = 'What is your GCash?'; sendMessage();"
                        class="shrink-0 px-3 py-2 rounded-full bg-orange-50 text-orange-600 text-xs font-semibold hover:bg-orange-100"
                    >
                        GCash
                    </button>
                </div>
            </div>

            <!-- Input -->
            <form
                @submit.prevent="sendMessage"
                class="p-4 border-t border-gray-100 bg-white flex items-center gap-2"
            >
                <input
                    type="text"
                    x-model="message"
                    placeholder="Ask something..."
                    class="flex-1 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-orange-300 focus:ring-orange-200"
                >

                <button
                    type="submit"
                    :disabled="loading"
                    class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white text-sm font-semibold"
                >
                    Send
                </button>
            </form>
        </div>

        <!-- Floating Button -->
        <button
            type="button"
            @click="open = !open"
            class="w-16 h-16 rounded-full bg-orange-500 hover:bg-orange-600 text-white shadow-xl flex items-center justify-center text-2xl"
        >
            💬
        </button>
    </div>

</body>
</html>