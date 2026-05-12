@extends('layouts.customer')

@section('content')

<section class="max-w-7xl mx-auto px-6 py-8">

    <!-- PAGE HEADER -->
    <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-8 mb-6">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-orange-500 mb-2">Customer Menu</p>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                    Browse available meals
                </h1>
                <p class="text-gray-500 mt-2 max-w-2xl">
                    Check menu items before ordering or reserving. Items marked unavailable cannot be selected today.
                </p>
            </div>

            <a href="{{ route('customer.home') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-gray-200 hover:border-orange-500 hover:text-orange-500 font-semibold transition">
                Back to Home
            </a>
        </div>
    </div>

    <!-- CATEGORY FILTER -->
    <div 
        x-data="{ selectedCategory: 'All' }"
        class="space-y-6"
    >
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 overflow-x-auto">
            <div class="flex gap-3 min-w-max">
                <button
                    @click="selectedCategory = 'All'"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition"
                    :class="selectedCategory === 'All' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-500'"
                >
                    All
                </button>

                @foreach ($categories as $category)
                    <button
                        @click="selectedCategory = @js($category)"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition"
                        :class="selectedCategory === @js($category) ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-500'"
                    >
                        {{ $category }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- MENU GROUPS -->
        @forelse ($groupedMenuItems as $category => $items)
            <div 
                x-show="selectedCategory === 'All' || selectedCategory === @js($category)"
                x-transition
                class="bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden"
            >
                <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            {{ $category }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ count($items) }} menu item{{ count($items) > 1 ? 's' : '' }}
                        </p>
                    </div>

                    <span class="text-xs px-3 py-1 rounded-full bg-orange-50 text-orange-500 font-semibold w-fit">
                        Menu Category
                    </span>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($items as $item)
                        <div class="relative border rounded-2xl p-5 transition
                            {{ $item['is_available'] ? 'border-gray-200 hover:border-orange-200 hover:shadow-sm' : 'border-gray-200 bg-gray-50 opacity-75' }}"
                        >
                            @if (!$item['is_available'])
                                <div class="absolute inset-0 bg-white/50 rounded-2xl pointer-events-none"></div>
                            @endif

                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl shrink-0">
                                    🍽️
                                </div>

                                @if ($item['is_available'])
                                    <span class="text-xs px-3 py-1 rounded-full bg-green-50 text-green-600 font-semibold">
                                        Available
                                    </span>
                                @else
                                    <span class="text-xs px-3 py-1 rounded-full bg-red-50 text-red-600 font-semibold">
                                        Unavailable
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-bold text-gray-900">
                                {{ $item['name'] }}
                            </h3>

                            <p class="text-sm text-gray-500 mt-2 leading-6">
                                {{ $item['description'] }}
                            </p>

                            <div class="mt-5 flex items-center justify-between">
                                <p class="text-xl font-bold text-orange-500">
                                    ₱{{ number_format($item['price'], 2) }}
                                </p>

                                @if ($item['is_available'])
                                    <button
                                        type="button"
                                        class="px-4 py-2 rounded-xl bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 transition"
                                    >
                                        View
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        disabled
                                        class="px-4 py-2 rounded-xl bg-gray-200 text-gray-400 text-sm font-semibold cursor-not-allowed"
                                    >
                                        Not Available
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-10 text-center">
                <div class="text-5xl mb-4">🍽️</div>
                <h2 class="text-2xl font-bold text-gray-900">No menu items found</h2>
                <p class="text-gray-500 mt-2">
                    Menu items added by the admin will appear here.
                </p>
            </div>
        @endforelse
    </div>

</section>

@endsection