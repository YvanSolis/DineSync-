@extends('layouts.admin')

@section('content')

<div class="space-y-5 sm:space-y-6 pb-28">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Restaurant Settings</h1>
            <p class="text-sm sm:text-base text-gray-500 mt-1">
                Manage restaurant details, schedule, payment settings, socials, and map links.
            </p>
        </div>

        <div class="bg-white border rounded-2xl px-4 py-3 shadow-sm w-full sm:w-auto">
            <p class="text-xs text-gray-500">Settings Scope</p>
            <p class="text-sm font-bold text-orange-500">Customer-Facing Info</p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm">
            <p class="font-bold mb-1">Please check the form.</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <!-- Quick Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
                <p class="text-xs text-gray-500 mb-2">Restaurant</p>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 truncate">
                    {{ old('restaurant_name', $settings->restaurant_name) ?: 'Not set' }}
                </h2>
                <p class="text-xs text-gray-400 mt-2">Customer-facing name</p>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
                <p class="text-xs text-gray-500 mb-2">Operating Hours</p>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 truncate">
                    {{ old('opening_time', $settings->opening_time) ?: '--' }} - {{ old('closing_time', $settings->closing_time) ?: '--' }}
                </h2>
                <p class="text-xs text-gray-400 mt-2 truncate">
                    {{ old('opening_days', $settings->opening_days) ?: 'No days set' }}
                </p>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
                <p class="text-xs text-gray-500 mb-2">Reservation Fee</p>
                <h2 class="text-lg sm:text-xl font-bold text-orange-500">
                    ₱{{ number_format((float) old('reservation_fee', $settings->reservation_fee ?? 0), 2) }}
                </h2>
                <p class="text-xs text-gray-400 mt-2">Non-refundable fee</p>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
                <p class="text-xs text-gray-500 mb-2">Contact</p>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 truncate">
                    {{ old('contact_number', $settings->contact_number) ?: 'Not set' }}
                </h2>
                <p class="text-xs text-gray-400 mt-2">Displayed to customers</p>
            </div>
        </div>

        <!-- Settings Layout -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <!-- Tabs -->
            <div class="border-b border-gray-100 bg-white">
                <div class="p-3 sm:p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-2">
                        <button type="button" data-tab="basic"
                            class="settings-tab px-3 sm:px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold bg-orange-500 text-white">
                            Basic Info
                        </button>

                        <button type="button" data-tab="hours"
                            class="settings-tab px-3 sm:px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600">
                            Hours
                        </button>

                        <button type="button" data-tab="payment"
                            class="settings-tab px-3 sm:px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600">
                            Payment
                        </button>

                        <button type="button" data-tab="social"
                            class="settings-tab px-3 sm:px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600">
                            Socials
                        </button>

                        <button type="button" data-tab="maps"
                            class="settings-tab px-3 sm:px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600">
                            Maps
                        </button>
                    </div>
                </div>
            </div>

            <!-- Basic Restaurant Info -->
            <section id="tab-basic" class="settings-panel">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Basic Information</h2>
                    <p class="text-sm text-gray-500 mt-1">Restaurant name, address, and customer contact details.</p>
                </div>

                <div class="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-2 gap-5">
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

                    <div class="lg:col-span-2">
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
            </section>

            <!-- Operating Hours -->
            <section id="tab-hours" class="settings-panel hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Operating Hours</h2>
                    <p class="text-sm text-gray-500 mt-1">Set the restaurant schedule shown to customers.</p>
                </div>

                <div class="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-3 gap-5">
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
            </section>

            <!-- Reservation Fee + Payment -->
            <section id="tab-payment" class="settings-panel hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Reservation Payment</h2>
                    <p class="text-sm text-gray-500 mt-1">Set reservation fee and GCash display details.</p>
                </div>

                <div class="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-3 gap-5">
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
                        <p class="text-xs text-gray-400 mt-2">Shown as non-refundable reservation fee.</p>
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
            </section>

            <!-- Social Media -->
            <section id="tab-social" class="settings-panel hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Social Media</h2>
                    <p class="text-sm text-gray-500 mt-1">Optional links for customer reference.</p>
                </div>

                <div class="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Facebook URL</label>
                        <input
                            type="text"
                            name="facebook_url"
                            value="{{ old('facebook_url', $settings->facebook_url) }}"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
                            placeholder="https://facebook.com/."
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
                            placeholder="https://instagram.com/."
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
                            placeholder="https://tiktok.com/."
                        >
                        @error('tiktok_url')
                            <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <!-- Google Maps -->
            <section id="tab-maps" class="settings-panel hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Google Maps</h2>
                    <p class="text-sm text-gray-500 mt-1">Paste Google Maps embed link and open map link.</p>
                </div>

                <div class="p-4 sm:p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Google Maps Embed URL</label>
                        <textarea
                            name="map_embed_url"
                            rows="5"
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
            </section>
        </div>

        <!-- Sticky Save Bar -->
        <div class="fixed bottom-0 left-0 lg:left-[240px] right-0 bg-white/95 backdrop-blur border-t border-gray-200 px-4 sm:px-5 py-3 sm:py-4 z-40">
            <div class="max-w-[1500px] mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">Save restaurant settings</p>
                    <p class="text-xs text-gray-500">Changes will update the customer-facing restaurant information.</p>
                </div>

                <button
                    id="saveSettingsBtn"
                    type="submit"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    Save Settings
                </button>
            </div>
        </div>
    </form>

</div>

<script>
const settingsTabs = document.querySelectorAll('.settings-tab');
const settingsPanels = document.querySelectorAll('.settings-panel');
const settingsForm = document.getElementById('settingsForm');
const saveSettingsBtn = document.getElementById('saveSettingsBtn');

function activateSettingsTab(tabName) {
    settingsTabs.forEach(button => {
        const isActive = button.dataset.tab === tabName;

        button.classList.toggle('bg-orange-500', isActive);
        button.classList.toggle('text-white', isActive);

        button.classList.toggle('bg-gray-100', !isActive);
        button.classList.toggle('text-gray-600', !isActive);
        button.classList.toggle('hover:bg-orange-50', !isActive);
        button.classList.toggle('hover:text-orange-600', !isActive);
    });

    settingsPanels.forEach(panel => {
        panel.classList.toggle('hidden', panel.id !== `tab-${tabName}`);
    });

    try {
        localStorage.setItem('adminSettingsActiveTab', tabName);
    } catch (error) {
        console.warn('Settings tab could not be saved.');
    }
}

settingsTabs.forEach(button => {
    button.addEventListener('click', () => {
        activateSettingsTab(button.dataset.tab);
    });
});

settingsForm.addEventListener('submit', () => {
    saveSettingsBtn.disabled = true;
    saveSettingsBtn.textContent = 'Saving...';
});

try {
    const savedTab = localStorage.getItem('adminSettingsActiveTab');

    if (savedTab && document.querySelector(`[data-tab="${savedTab}"]`)) {
        activateSettingsTab(savedTab);
    }
} catch (error) {
    console.warn('Settings tab could not be restored.');
}
</script>

@endsection