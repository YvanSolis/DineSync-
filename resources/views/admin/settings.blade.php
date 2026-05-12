@extends('layouts.admin')

@section('content')

<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Restaurant Settings</h1>
            <p class="text-gray-500 mt-1">
                Manage restaurant information shown on the customer side.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <!-- Basic Restaurant Info -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Basic Information</h2>
                <p class="text-sm text-gray-500">Restaurant name, address, and contact details.</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Restaurant Name</label>
                    <input
                        type="text"
                        name="restaurant_name"
                        value="{{ old('restaurant_name', $settings->restaurant_name) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        required
                    >
                    @error('restaurant_name')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
                    <input
                        type="text"
                        name="contact_number"
                        value="{{ old('contact_number', $settings->contact_number) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="Example: 0912 345 6789"
                    >
                    @error('contact_number')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                    <input
                        type="text"
                        name="address"
                        value="{{ old('address', $settings->address) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="Restaurant address"
                    >
                    @error('address')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Operating Hours -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Operating Hours</h2>
                <p class="text-sm text-gray-500">Set the restaurant schedule shown to customers.</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Opening Days</label>
                    <input
                        type="text"
                        name="opening_days"
                        value="{{ old('opening_days', $settings->opening_days) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="Monday - Sunday"
                        required
                    >
                    @error('opening_days')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Opening Time</label>
                    <input
                        type="text"
                        name="opening_time"
                        value="{{ old('opening_time', $settings->opening_time) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="10:00 AM"
                        required
                    >
                    @error('opening_time')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Closing Time</label>
                    <input
                        type="text"
                        name="closing_time"
                        value="{{ old('closing_time', $settings->closing_time) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="9:00 PM"
                        required
                    >
                    @error('closing_time')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Reservation Fee + Payment -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Reservation Payment</h2>
                <p class="text-sm text-gray-500">Set reservation fee and GCash details.</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Reservation Fee</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="reservation_fee"
                        value="{{ old('reservation_fee', $settings->reservation_fee) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        required
                    >
                    @error('reservation_fee')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">GCash Name</label>
                    <input
                        type="text"
                        name="gcash_name"
                        value="{{ old('gcash_name', $settings->gcash_name) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="Chef Oppa"
                    >
                    @error('gcash_name')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">GCash Number</label>
                    <input
                        type="text"
                        name="gcash_number"
                        value="{{ old('gcash_number', $settings->gcash_number) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="0912 345 6789"
                    >
                    @error('gcash_number')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Social Media -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Social Media</h2>
                <p class="text-sm text-gray-500">Optional links for customer reference.</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Facebook URL</label>
                    <input
                        type="text"
                        name="facebook_url"
                        value="{{ old('facebook_url', $settings->facebook_url) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="https://facebook.com/..."
                    >
                    @error('facebook_url')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instagram URL</label>
                    <input
                        type="text"
                        name="instagram_url"
                        value="{{ old('instagram_url', $settings->instagram_url) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="https://instagram.com/..."
                    >
                    @error('instagram_url')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">TikTok URL</label>
                    <input
                        type="text"
                        name="tiktok_url"
                        value="{{ old('tiktok_url', $settings->tiktok_url) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="https://tiktok.com/..."
                    >
                    @error('tiktok_url')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Google Maps -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Google Maps</h2>
                <p class="text-sm text-gray-500">Paste Google Maps embed link and open map link.</p>
            </div>

            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Google Maps Embed URL</label>
                    <textarea
                        name="map_embed_url"
                        rows="4"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="Paste the iframe src link only"
                    >{{ old('map_embed_url', $settings->map_embed_url) }}</textarea>
                    <p class="text-xs text-gray-500 mt-2">
                        Paste only the value inside iframe src, not the whole iframe code.
                    </p>
                    @error('map_embed_url')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Google Maps Open Link</label>
                    <input
                        type="text"
                        name="google_maps_url"
                        value="{{ old('google_maps_url', $settings->google_maps_url) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                        placeholder="https://www.google.com/maps/search/?api=1&query=Chef+Oppa"
                    >
                    @error('google_maps_url')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button
                type="submit"
                class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
            >
                Save Settings
            </button>
        </div>

    </form>

</div>

@endsection