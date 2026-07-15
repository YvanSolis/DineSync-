@extends('layouts.admin')

@section('content')

<style>
.settings-overview-card{position:relative;overflow:hidden;transition:.22s ease}.settings-overview-card:hover{transform:translateY(-3px);box-shadow:0 18px 38px rgba(15,23,42,.08);border-color:rgba(249,115,22,.22)}.settings-tab{transition:.2s ease}.settings-tab-active{box-shadow:0 10px 24px rgba(249,115,22,.22);transform:translateY(-1px)}.settings-panel{animation:settingsPanelIn .22s ease-out}.settings-field{transition:.18s ease}.settings-field:focus{background:#fff!important;box-shadow:0 0 0 4px rgba(249,115,22,.12)!important}.settings-field.settings-dirty{border-color:#86efac!important;background:#f0fdf4!important}.settings-confirm-panel,.settings-leave-panel{animation:settingsModalIn .2s ease-out}.settings-toast{animation:settingsToastIn .24s ease-out}@keyframes settingsPanelIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}@keyframes settingsModalIn{from{opacity:0;transform:translateY(12px) scale(.97)}to{opacity:1;transform:none}}@keyframes settingsToastIn{from{opacity:0;transform:translateY(-10px) translateX(16px)}to{opacity:1;transform:none}}@media(max-width:640px){.settings-save-bar{padding-bottom:calc(12px + env(safe-area-inset-bottom))}#settingsConfirmModal,#settingsLeaveModal{align-items:flex-end!important;padding:0!important}#settingsConfirmModal>div,#settingsLeaveModal>div{width:100%!important;max-width:100%!important;border-radius:26px 26px 0 0!important}#settingsForm input,#settingsForm textarea{font-size:16px!important}}
</style>

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
        <div id="settingsSessionSuccess" data-message="{{ session('success') }}" class="hidden"></div>
    @endif

    @if ($errors->any())
        <div id="settingsValidationErrors" data-message="{{ $errors->first() }}" class="hidden"></div>
    @endif

    <form id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <!-- Quick Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="settings-overview-card bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
                <p class="text-xs text-gray-500 mb-2">Restaurant</p>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 truncate">
                    {{ old('restaurant_name', $settings->restaurant_name) ?: 'Not set' }}
                </h2>
                <p class="text-xs text-gray-400 mt-2">Customer-facing name</p>
            </div>

            <div class="settings-overview-card bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
                <p class="text-xs text-gray-500 mb-2">Operating Hours</p>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 truncate">
                    {{ old('opening_time', $settings->opening_time) ?: '--' }} - {{ old('closing_time', $settings->closing_time) ?: '--' }}
                </h2>
                <p class="text-xs text-gray-400 mt-2 truncate">
                    {{ old('opening_days', $settings->opening_days) ?: 'No days set' }}
                </p>
            </div>

            <div class="settings-overview-card bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
                <p class="text-xs text-gray-500 mb-2">Reservation Fee</p>
                <h2 class="text-lg sm:text-xl font-bold text-orange-500">
                    ₱{{ number_format((float) old('reservation_fee', $settings->reservation_fee ?? 0), 2) }}
                </h2>
                <p class="text-xs text-gray-400 mt-2">Non-refundable fee</p>
            </div>

            <div class="settings-overview-card bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
                <p class="text-xs text-gray-500 mb-2">Contact</p>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 truncate">
                    {{ old('contact_number', $settings->contact_number) ?: 'Not set' }}
                </h2>
                <p class="text-xs text-gray-400 mt-2">Displayed to customers</p>
            </div>
        </div>

        <!-- Settings Layout -->
        <div class="bg-white border border-gray-200 rounded-[28px] shadow-sm overflow-hidden">
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
                            class="settings-field w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-orange-300 focus:ring-orange-200"
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
        <div class="settings-save-bar fixed bottom-0 left-0 lg:left-[240px] right-0 bg-white/95 backdrop-blur border-t border-gray-200 px-4 sm:px-5 py-3 sm:py-4 z-40 shadow-[0_-10px_30px_rgba(15,23,42,0.06)]">
            <div class="max-w-[1500px] mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span id="settingsSaveStatusDot"
                            class="h-2.5 w-2.5 rounded-full bg-gray-300"></span>

                        <p id="settingsSaveStatusTitle"
                            class="text-sm font-semibold text-gray-900">
                            All changes saved
                        </p>
                    </div>

                    <p id="settingsSaveStatusText"
                        class="text-xs text-gray-500 mt-1">
                        Changes will update the customer-facing restaurant information.
                    </p>
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


<!-- Settings Toast Container -->
<div id="settingsToastContainer"
    class="fixed top-4 right-4 z-[95] w-[calc(100%-2rem)] max-w-sm space-y-3 pointer-events-none">
</div>

<!-- Save Confirmation Modal -->
<div id="settingsConfirmModal"
    class="fixed inset-0 z-[90] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">

    <div class="settings-confirm-panel w-full max-w-md overflow-hidden rounded-[28px] border border-orange-100 bg-white shadow-2xl">
        <div class="bg-gradient-to-br from-orange-50 via-white to-amber-50 px-6 py-6 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-orange-200 bg-orange-100 text-2xl font-black text-orange-600">
                ✓
            </div>

            <h3 class="mt-4 text-2xl font-extrabold text-gray-900">
                Save Restaurant Settings?
            </h3>

            <p class="mt-3 text-sm leading-6 text-gray-600">
                These changes will immediately update customer-facing restaurant information.
            </p>
        </div>

        <div class="border-t border-gray-100 px-6 py-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button id="settingsConfirmCancelBtn"
                    type="button"
                    class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 font-bold text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>

                <button id="settingsConfirmProceedBtn"
                    type="button"
                    class="w-full rounded-2xl bg-orange-500 px-4 py-3 font-bold text-white shadow-sm hover:bg-orange-600">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Unsaved Changes Modal -->
<div id="settingsLeaveModal"
    class="fixed inset-0 z-[91] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">

    <div class="settings-leave-panel w-full max-w-md overflow-hidden rounded-[28px] border border-yellow-100 bg-white shadow-2xl">
        <div class="bg-gradient-to-br from-yellow-50 via-white to-orange-50 px-6 py-6 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-yellow-200 bg-yellow-100 text-2xl font-black text-yellow-700">
                !
            </div>

            <h3 class="mt-4 text-2xl font-extrabold text-gray-900">
                Unsaved Changes
            </h3>

            <p class="mt-3 text-sm leading-6 text-gray-600">
                You have unsaved changes. Leaving now will discard them.
            </p>
        </div>

        <div class="border-t border-gray-100 px-6 py-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button id="settingsStayBtn"
                    type="button"
                    class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 font-bold text-gray-700 hover:bg-gray-50">
                    Stay Here
                </button>

                <button id="settingsLeaveBtn"
                    type="button"
                    class="w-full rounded-2xl bg-gray-900 px-4 py-3 font-bold text-white hover:bg-black">
                    Leave Anyway
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const settingsTabs = document.querySelectorAll('.settings-tab');
const settingsPanels = document.querySelectorAll('.settings-panel');
const settingsForm = document.getElementById('settingsForm');
const saveSettingsBtn = document.getElementById('saveSettingsBtn');
const confirmModal = document.getElementById('settingsConfirmModal');
const leaveModal = document.getElementById('settingsLeaveModal');

let dirty = false;
let submitting = false;
let initialSnapshot = '';
let pendingUrl = null;

function escapeSettingsText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function showSettingsToast(message, type = 'success') {
    const container = document.getElementById('settingsToastContainer');

    const styleMap = {
        success: {
            border: 'border-green-200',
            icon: 'bg-green-100 text-green-700',
            title: 'Settings Saved',
            symbol: '✓',
        },
        error: {
            border: 'border-red-200',
            icon: 'bg-red-100 text-red-700',
            title: 'Please Check the Form',
            symbol: '!',
        },
        warning: {
            border: 'border-yellow-200',
            icon: 'bg-yellow-100 text-yellow-700',
            title: 'Unsaved Changes',
            symbol: '!',
        },
    };

    const style = styleMap[type] || styleMap.success;
    const toast = document.createElement('div');

    toast.className = `
        settings-toast pointer-events-auto rounded-2xl border
        ${style.border} bg-white p-4 shadow-2xl
    `;

    toast.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl ${style.icon} font-black">
                ${style.symbol}
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-extrabold text-gray-900">
                    ${style.title}
                </p>

                <p class="mt-1 text-sm leading-5 text-gray-600">
                    ${escapeSettingsText(message)}
                </p>
            </div>

            <button type="button"
                class="text-lg text-gray-400 hover:text-gray-700">
                &times;
            </button>
        </div>
    `;

    const removeToast = () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(18px)';
        toast.style.transition = '.18s';

        setTimeout(() => toast.remove(), 180);
    };

    toast.querySelector('button').addEventListener('click', removeToast);
    container.appendChild(toast);

    setTimeout(removeToast, 3400);
}

function getSettingsSnapshot() {
    const formData = new FormData(settingsForm);
    const values = [];

    for (const [key, value] of formData.entries()) {
        if (key === '_token' || key === '_method') {
            continue;
        }

        values.push([key, String(value)]);
    }

    values.sort((a, b) => a[0].localeCompare(b[0]));

    return JSON.stringify(values);
}

function updateSettingsStatus() {
    const dot = document.getElementById('settingsSaveStatusDot');
    const title = document.getElementById('settingsSaveStatusTitle');
    const message = document.getElementById('settingsSaveStatusText');

    if (submitting) {
        dot.className = 'h-2.5 w-2.5 rounded-full bg-orange-500 animate-pulse';
        title.textContent = 'Saving changes...';
        message.textContent = 'Please wait while settings are being updated.';
        return;
    }

    if (dirty) {
        dot.className = 'h-2.5 w-2.5 rounded-full bg-yellow-500 animate-pulse';
        title.textContent = 'Unsaved changes';
        message.textContent = 'Review your changes, then click Save Settings.';
        return;
    }

    dot.className = 'h-2.5 w-2.5 rounded-full bg-green-500';
    title.textContent = 'All changes saved';
    message.textContent = 'Customer-facing restaurant information is up to date.';
}

function markSettingsDirty() {
    dirty = getSettingsSnapshot() !== initialSnapshot;

    document.querySelectorAll('.settings-field').forEach(field => {
        field.classList.toggle(
            'settings-dirty',
            dirty && String(field.value || '').trim() !== ''
        );
    });

    updateSettingsStatus();
}

function activateSettingsTab(tabName) {
    settingsTabs.forEach(button => {
        const isActive = button.dataset.tab === tabName;

        button.classList.toggle('bg-orange-500', isActive);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('settings-tab-active', isActive);

        button.classList.toggle('bg-gray-100', !isActive);
        button.classList.toggle('text-gray-600', !isActive);
        button.classList.toggle('hover:bg-orange-50', !isActive);
        button.classList.toggle('hover:text-orange-600', !isActive);
    });

    settingsPanels.forEach(panel => {
        panel.classList.toggle(
            'hidden',
            panel.id !== `tab-${tabName}`
        );
    });

    try {
        localStorage.setItem('adminSettingsActiveTab', tabName);
    } catch (error) {
        console.warn('Settings tab could not be saved.');
    }
}

function openSettingsConfirmModal() {
    confirmModal.classList.remove('hidden');
    confirmModal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeSettingsConfirmModal() {
    confirmModal.classList.add('hidden');
    confirmModal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function openLeaveModal(url) {
    pendingUrl = url;

    leaveModal.classList.remove('hidden');
    leaveModal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeLeaveModal() {
    pendingUrl = null;

    leaveModal.classList.add('hidden');
    leaveModal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function beforeUnloadHandler(event) {
    if (!dirty || submitting) {
        return;
    }

    event.preventDefault();
    event.returnValue = '';
}

settingsTabs.forEach(button => {
    button.addEventListener('click', () => {
        activateSettingsTab(button.dataset.tab);
    });
});

settingsForm.querySelectorAll('input, textarea, select').forEach(field => {
    field.classList.add('settings-field');
    field.addEventListener('input', markSettingsDirty);
    field.addEventListener('change', markSettingsDirty);
});

settingsForm.addEventListener('submit', event => {
    event.preventDefault();

    if (submitting) {
        return;
    }

    openSettingsConfirmModal();
});

document.getElementById('settingsConfirmCancelBtn')
    .addEventListener('click', closeSettingsConfirmModal);

document.getElementById('settingsConfirmProceedBtn')
    .addEventListener('click', () => {
        closeSettingsConfirmModal();

        submitting = true;
        dirty = false;

        saveSettingsBtn.disabled = true;
        saveSettingsBtn.innerHTML = `
            <span class="inline-flex items-center gap-2">
                <span class="h-4 w-4 rounded-full border-2 border-white/40 border-t-white animate-spin"></span>
                Saving...
            </span>
        `;

        updateSettingsStatus();
        window.removeEventListener('beforeunload', beforeUnloadHandler);

        settingsForm.submit();
    });

document.getElementById('settingsStayBtn')
    .addEventListener('click', closeLeaveModal);

document.getElementById('settingsLeaveBtn')
    .addEventListener('click', () => {
        const destination = pendingUrl;

        dirty = false;
        window.removeEventListener('beforeunload', beforeUnloadHandler);
        closeLeaveModal();

        if (destination) {
            window.location.href = destination;
        }
    });

confirmModal.addEventListener('click', event => {
    if (event.target === confirmModal) {
        closeSettingsConfirmModal();
    }
});

leaveModal.addEventListener('click', event => {
    if (event.target === leaveModal) {
        closeLeaveModal();
    }
});

document.addEventListener('click', event => {
    const link = event.target.closest('a[href]');

    if (!link || !dirty || submitting) {
        return;
    }

    const href = link.getAttribute('href');

    if (!href || href.startsWith('#') || link.target === '_blank') {
        return;
    }

    event.preventDefault();
    openLeaveModal(link.href);
});

document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') {
        return;
    }

    if (!confirmModal.classList.contains('hidden')) {
        closeSettingsConfirmModal();
        return;
    }

    if (!leaveModal.classList.contains('hidden')) {
        closeLeaveModal();
    }
});

window.addEventListener('beforeunload', beforeUnloadHandler);

try {
    const savedTab = localStorage.getItem('adminSettingsActiveTab');

    if (
        savedTab &&
        document.querySelector(`[data-tab="${savedTab}"]`)
    ) {
        activateSettingsTab(savedTab);
    }
} catch (error) {
    console.warn('Settings tab could not be restored.');
}

document.addEventListener('DOMContentLoaded', () => {
    initialSnapshot = getSettingsSnapshot();
    dirty = false;

    updateSettingsStatus();

    const successMessage = document.getElementById('settingsSessionSuccess');
    const validationMessage = document.getElementById('settingsValidationErrors');

    if (successMessage?.dataset.message) {
        showSettingsToast(
            successMessage.dataset.message,
            'success'
        );
    }

    if (validationMessage?.dataset.message) {
        showSettingsToast(
            validationMessage.dataset.message,
            'error'
        );
    }
});
</script>

@endsection
