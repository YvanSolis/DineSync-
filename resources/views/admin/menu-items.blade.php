@extends('layouts.admin')

@section('content')

<style>
    /*
        Responsive modal rules:
        - Mobile: modals become true full-screen sheets.
        - Menu modal: footer is NOT sticky on mobile so it will not cover fields.
        - Ingredients modal: header/summary scrolls together with the content so the form is not hidden.
    */
    @media (max-width: 640px) {
        #menuModal,
        #ingredientsModal {
            align-items: stretch !important;
            justify-content: stretch !important;
            padding: 0 !important;
        }

        #menuModal > div,
        #ingredientsModal > div {
            width: 100% !important;
            max-width: 100% !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            border-radius: 0 !important;
        }

        #menuModal > div {
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        #menuForm {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }

        #menuForm > .p-4 {
            padding-bottom: 18px !important;
        }

        .menu-modal-footer {
            position: static !important;
            padding-bottom: calc(16px + env(safe-area-inset-bottom)) !important;
            box-shadow: none !important;
        }

        #ingredientsModal > div {
            display: block !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }

        #ingredientsModal > div > .relative {
            position: relative !important;
            padding: 18px !important;
        }

        #ingredientsModal > div > .flex-1 {
            overflow: visible !important;
            padding: 16px !important;
            padding-bottom: calc(28px + env(safe-area-inset-bottom)) !important;
            min-height: auto !important;
        }

        #ingredientsModalTitle {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            font-size: 24px !important;
            line-height: 1.18 !important;
        }

        #ingredientsModal .grid.grid-cols-1.md\:grid-cols-3 {
            gap: 10px !important;
            margin-top: 16px !important;
        }

        #ingredientsModal .grid.grid-cols-1.md\:grid-cols-3 > div {
            padding: 14px !important;
            border-radius: 18px !important;
        }

        #ingredientsSummaryCount,
        #ingredientsSummaryPossibleOrders {
            font-size: 26px !important;
            line-height: 1.1 !important;
        }

        #ingredientsStockLabel {
            font-size: 16px !important;
            line-height: 1.3 !important;
        }

        .menu-mobile-card-header {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .menu-mobile-card-actions {
            grid-template-columns: 1fr !important;
        }

        .menu-mobile-card-actions button {
            width: 100% !important;
            min-height: 44px !important;
            font-size: 13px !important;
        }

        .menu-mobile-card-main {
            flex-direction: column !important;
        }

        .menu-mobile-card-image {
            width: 100% !important;
        }

        .menu-mobile-card-image img,
        .menu-mobile-card-image > div {
            width: 100% !important;
            height: 170px !important;
            border-radius: 18px !important;
        }

        #flavorTagsContainer {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        #menuModal input,
        #menuModal select,
        #menuModal textarea,
        #ingredientsModal input,
        #ingredientsModal select {
            font-size: 16px !important;
        }
    }

    @media (min-width: 641px) and (max-width: 1024px) {
        #menuModal > div,
        #ingredientsModal > div {
            max-width: calc(100vw - 32px) !important;
            max-height: calc(100dvh - 32px) !important;
        }

        #flavorTagsContainer {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media (min-width: 641px) {
        .menu-modal-footer {
            position: sticky;
            bottom: 0;
        }
    }


    .premium-modal-panel { animation: premiumModalIn .20s ease-out; }
    .premium-toast { animation: premiumToastIn .25s ease-out; }
    @keyframes premiumModalIn { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
    @keyframes premiumToastIn { from { opacity: 0; transform: translateY(-10px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

</style>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">Menu Management</h1>
            <p class="text-sm sm:text-base text-gray-500">
                Manage restaurant menu items, categories, images, menu details, and linked ingredients.
            </p>
        </div>

        <button onclick="openMenuModal()"
            class="w-full sm:w-auto bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-xl font-semibold shadow-sm transition">
            + Add Menu Item
        </button>
    </div>

    <!-- Menu Items -->
    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <div class="p-4 sm:p-5 border-b">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold">Menu Items</h2>
                    <p class="text-sm text-gray-500">
                        View, filter, and manage menu items. Availability is based on linked ingredients and stock levels.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full xl:w-auto">
                    <input
                        id="menuSearch"
                        type="text"
                        placeholder="Search menu..."
                        class="border rounded-xl px-4 py-2.5 w-full xl:w-72"
                    >

                    <select id="categoryFilter" class="border rounded-xl px-4 py-2.5 w-full xl:w-64">
                        <option value="all">All Categories</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Desktop / Tablet Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[950px] text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Image</th>
                        <th class="text-left px-6 py-4 font-semibold">Item Name</th>
                        <th class="text-left px-6 py-4 font-semibold">Category</th>
                        <th class="text-left px-6 py-4 font-semibold">Price</th>
                        <th class="text-left px-6 py-4 font-semibold">Availability</th>
                        <th class="text-left px-6 py-4 font-semibold">Actions</th>
                    </tr>
                </thead>

                <tbody id="menuTableBody">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            Loading menu items...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div id="menuMobileList" class="md:hidden p-4 space-y-3">
            <div class="px-4 py-8 text-center text-gray-400">
                Loading menu items...
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Menu Item Modal -->
<div id="menuModal" class="fixed inset-0 bg-black/50 backdrop-blur-[2px] hidden items-center justify-center z-50 p-0 sm:p-4">
    <div class="bg-white rounded-none sm:rounded-[28px] shadow-2xl w-full sm:max-w-6xl h-[100dvh] sm:h-auto sm:max-h-[94dvh] overflow-hidden flex flex-col border border-orange-100">
        <!-- Modal Header -->
        <div class="relative px-5 sm:px-7 py-5 border-b bg-gradient-to-r from-orange-50 via-white to-amber-50 shrink-0 overflow-hidden">
            <div class="absolute -top-14 -right-10 w-40 h-40 rounded-full bg-orange-100/60"></div>
            <div class="absolute -bottom-16 -left-12 w-48 h-48 rounded-full bg-amber-100/50"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-[11px] font-bold uppercase tracking-wide">
                            Menu Item Setup
                        </span>

                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-orange-100 text-gray-600 text-[11px] font-semibold shadow-sm">
                            Ingredient-based availability
                        </span>
                    </div>

                    <h3 id="menuModalTitle" class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                        Add Menu Item
                    </h3>
                </div>

                <button type="button" onclick="closeMenuModal()"
                    class="w-10 h-10 rounded-full bg-white hover:bg-orange-50 border border-orange-100 flex items-center justify-center text-gray-500 hover:text-orange-700 text-xl shrink-0 transition shadow-sm">
                    &times;
                </button>
            </div>
        </div>

        <form id="menuForm" class="flex-1 overflow-y-auto bg-gray-50/60">
            <div class="p-4 sm:p-6 space-y-5">
                <!-- Basic Info Section -->
                <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b bg-white">
                        <h4 class="font-bold text-gray-900 text-lg">Basic Information</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Main information shown on the admin page and customer tablet menu.
                        </p>
                    </div>

                    <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Item Name</label>
                            <input id="itemName" type="text"
                                placeholder="Example: Anju Jjampong"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Price</label>
                            <input id="itemPrice" type="number" step="0.01"
                                placeholder="Example: 250.00"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                                required>
                            <p class="text-xs text-gray-400 mt-1">
                                Use 0.00 for Chef Oppa Special or custom requests with price to be confirmed.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Category</label>
                            <select id="itemCategory"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                                required>
                                <option value="">Select Category</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Used for menu grouping and filtering.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Meal Type <span class="text-gray-400 font-normal">(for AI recommendations)</span>
                            </label>

                            <select id="itemMealType"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition">
                                <option value="">Select Meal Type</option>
                            </select>

                            <p class="text-xs text-gray-400 mt-1">
                                Select one only, like main, side, drink, dessert, snack, or soup.
                            </p>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Description <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <textarea
                                id="itemDescription"
                                rows="4"
                                maxlength="1000"
                                placeholder="Enter menu item description for customer/mobile display..."
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 resize-none bg-white focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                            ></textarea>
                            <p class="text-xs text-gray-400 mt-1">Keep this short and readable for the tablet menu.</p>
                        </div>
                    </div>
                </div>

                <!-- Image / Availability Section -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b bg-white">
                            <h4 class="font-bold text-gray-900 text-lg">Menu Image</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                Upload a clear photo for the menu item.
                            </p>
                        </div>

                        <div class="p-5">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Upload Image <span class="text-gray-400 font-normal">(optional)</span>
                            </label>

                            <label class="block border-2 border-dashed border-orange-100 hover:border-orange-300 bg-orange-50/40 hover:bg-orange-50 rounded-3xl px-5 py-7 text-center cursor-pointer transition">
                            <input
                                id="itemImage"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                            >

                            <div id="imagePreviewBox" class="mx-auto w-full max-w-[320px] h-[180px] rounded-3xl bg-white border border-orange-100 shadow-sm flex items-center justify-center overflow-hidden">
                                <div id="imagePlaceholder" class="text-center">
                                    <div class="mx-auto w-14 h-14 rounded-2xl bg-orange-50 border border-orange-100 shadow-sm flex items-center justify-center text-orange-500 font-bold">
                                        IMG
                                    </div>

                                    <p class="text-sm font-semibold text-gray-800 mt-3">
                                        Choose menu image
                                    </p>

                                    <p class="text-xs text-gray-500 mt-1">
                                        JPG, PNG, or WEBP up to 4MB.
                                    </p>
                                </div>

                                <img id="imagePreview" src="" alt="Image Preview" class="hidden w-full h-full object-cover">
                            </div>

                            <p id="selectedImageName" class="text-xs text-gray-500 mt-3 hidden"></p>
                        </label>

                            <p id="currentImageText" class="text-xs text-gray-400 mt-2 hidden">
                                Current image will stay unless you upload a new one.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b bg-white">
                            <h4 class="font-bold text-gray-900 text-lg">Availability Setup</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                Manual availability still follows ingredient stock rules.
                            </p>
                        </div>

                        <div class="p-5 space-y-4">
                            <label class="flex items-center justify-between gap-4 border border-gray-200 rounded-3xl px-5 py-4 bg-gray-50">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900">Available for ordering</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Normal menu items still require enough linked ingredients. Chef Oppa Special stays available as a custom request.
                                    </p>
                                </div>

                                <input id="itemAvailable" type="checkbox" class="rounded shrink-0 scale-110" checked>
                            </label>

                            <label class="flex items-center justify-between gap-4 border border-blue-200 rounded-3xl px-5 py-4 bg-blue-50">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-blue-900">Unlimited Menu</p>
                                    <p class="text-xs text-blue-700 mt-1">
                                        Enable refill tracking for unlimited meat, cheese, drinks, and similar menu items.
                                    </p>
                                </div>

                                <input id="itemUnlimited" type="checkbox" class="rounded shrink-0 scale-110">
                            </label>

                            <div class="rounded-3xl border border-orange-100 bg-orange-50 px-5 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-2xl bg-white border border-orange-100 flex items-center justify-center text-orange-600 font-bold shrink-0">
                                        !
                                    </div>

                                    <div>
                                        <p class="text-sm font-bold text-orange-800">Ingredient reminder</p>
                                        <p class="text-xs text-orange-700 mt-1 leading-5">
                                            After saving a normal menu item, click the <span class="font-bold">Ingredients</span> button to link required ingredients and quantity usage. If one linked ingredient is insufficient, this item becomes unavailable.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-blue-100 bg-blue-50 px-5 py-4">
                                <p class="text-sm font-bold text-blue-800">Chef Oppa Special</p>
                                <p class="text-xs text-blue-700 mt-1 leading-5">
                                    Custom request items should use price 0.00. On mobile, price remains “To be confirmed” and quantity stays fixed to 1.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Flavor Tags Section -->
                <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b bg-white">
                        <h4 class="font-bold text-gray-900 text-lg">
                            Flavor Tags <span class="text-gray-400 font-normal text-sm">(for AI recommendations)</span>
                        </h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Select tags that describe the item’s taste, texture, or style.
                        </p>
                    </div>

                    <div class="p-5">
                        <div id="flavorTagsContainer" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                            <!-- Flavor tags will load here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Footer -->
            <div class="menu-modal-footer bg-white border-t px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:justify-end gap-2 sm:shadow-[0_-8px_24px_rgba(15,23,42,0.06)]">
                <button type="button" onclick="closeMenuModal()"
                    class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">
                    Cancel
                </button>

                <button id="menuSaveBtn" type="submit"
                    class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-bold disabled:opacity-70 disabled:cursor-not-allowed shadow-sm transition">
                    Save Menu Item
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ingredients Modal -->
<div id="ingredientsModal" class="fixed inset-0 bg-black/50 backdrop-blur-[2px] hidden items-center justify-center z-50 p-0 sm:p-4">
    <div class="bg-white rounded-none sm:rounded-[26px] shadow-2xl w-full sm:max-w-5xl h-[100dvh] sm:h-auto sm:max-h-[94dvh] overflow-hidden flex flex-col border border-gray-100">
        <!-- Header -->
        <div class="relative px-5 sm:px-7 py-5 border-b bg-gradient-to-r from-orange-50 via-white to-amber-50 shrink-0">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-[11px] font-semibold uppercase tracking-wide">
                            Ingredient Setup
                        </span>

                        <span id="ingredientsAvailabilityBadge"
                            class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold">
                            Status
                        </span>
                    </div>

                    <h3 id="ingredientsModalTitle" class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                        Manage Ingredients
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Link ingredients and set required quantity per order.
                    </p>
                </div>

                <button type="button" onclick="closeIngredientsModal()"
                    class="w-10 h-10 rounded-full hover:bg-white/80 border border-transparent hover:border-gray-200 flex items-center justify-center text-gray-500 hover:text-black text-xl shrink-0 transition">
                    &times;
                </button>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-5">
                <div class="rounded-2xl border border-orange-100 bg-white/90 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Linked Ingredients</p>
                    <p id="ingredientsSummaryCount" class="mt-1 text-2xl font-bold text-gray-900">0</p>
                    <p class="text-xs text-gray-500 mt-1">Total ingredients connected to this item</p>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-white/90 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Possible Orders</p>
                    <p id="ingredientsSummaryPossibleOrders" class="mt-1 text-2xl font-bold text-blue-700">0</p>
                    <p class="text-xs text-gray-500 mt-1">Based on current stock and required usage</p>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-white/90 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Stock Status</p>
                    <p id="ingredientsStockLabel" class="mt-1 text-sm font-semibold text-emerald-700">
                        No stock data
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Live availability summary for this menu item</p>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-5 space-y-5 bg-gray-50/60">
            <input type="hidden" id="ingredientsMenuItemId">

            <!-- Add Ingredient Card -->
            <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b bg-gray-50/80">
                    <h4 class="font-bold text-gray-900 text-lg">Add Ingredient Usage</h4>
                    <p class="text-sm text-gray-500 mt-1">
                        Choose an ingredient and define how much is consumed per one order.
                    </p>
                </div>

                <div class="p-5">
                    <form id="attachIngredientForm" class="grid grid-cols-1 xl:grid-cols-[1.25fr_220px_auto] gap-4 items-end">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Ingredient</label>
                            <select id="ingredientSelect"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                                required>
                                <option value="">Select Ingredient</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Quantity Required</label>
                            <input
                                id="quantityRequired"
                                type="number"
                                min="0.01"
                                step="0.01"
                                placeholder="Example: 0.25"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                                required
                            >
                        </div>

                        <button id="attachIngredientBtn" type="submit"
                            class="w-full xl:w-auto h-[50px] bg-orange-500 hover:bg-orange-600 text-white px-6 rounded-2xl font-semibold shadow-sm disabled:opacity-70 disabled:cursor-not-allowed transition">
                            Add Ingredient
                        </button>

                        <div id="refillConfigPanel"
                            class="hidden xl:col-span-3 rounded-2xl border border-blue-100 bg-blue-50 p-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    id="ingredientRefillable"
                                    type="checkbox"
                                    class="rounded text-blue-600 focus:ring-blue-400"
                                >

                                <span>
                                    <span class="block text-sm font-bold text-blue-900">Refillable Ingredient</span>
                                    <span class="block text-xs text-blue-700 mt-0.5">
                                        Enable this when the service staff may record a refill for this ingredient.
                                    </span>
                                </span>
                            </label>

                            <div id="refillQuantityWrapper" class="hidden mt-4">
                                <label class="block text-sm font-semibold mb-2 text-blue-900">
                                    Quantity Per Refill
                                </label>

                                <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3 items-center">
                                    <input
                                        id="refillQuantity"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        placeholder="Example: 0.20"
                                        class="w-full border border-blue-200 rounded-2xl px-4 py-3 bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition"
                                    >

                                    <span id="refillUnitLabel"
                                        class="inline-flex justify-center px-4 py-3 rounded-2xl bg-white border border-blue-100 text-sm font-bold text-blue-700">
                                        unit
                                    </span>
                                </div>

                                <p class="text-xs text-blue-700 mt-2">
                                    This amount will be deducted from inventory every time a refill is confirmed.
                                </p>
                            </div>
                        </div>
                    </form>

                    <div class="mt-4 rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3">
                        <p class="text-xs sm:text-sm text-orange-700">
                            <span class="font-semibold">Example:</span>
                            If one order uses <span class="font-semibold">0.25 kg beef</span>, enter <span class="font-semibold">0.25</span>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Linked Ingredients Card -->
            <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b bg-gray-50/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-lg">Linked Ingredients</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Review current stock, quantity required, and how many orders can still be prepared.
                        </p>
                    </div>

                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-gray-100 text-gray-600 text-xs font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Live ingredient usage overview
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[860px] text-sm">
                        <thead class="bg-white text-gray-500">
                            <tr>
                                <th class="text-left px-5 py-4 font-semibold">Ingredient</th>
                                <th class="text-left px-5 py-4 font-semibold">Current Stock</th>
                                <th class="text-left px-5 py-4 font-semibold">Required / Order</th>
                                <th class="text-left px-5 py-4 font-semibold">Possible Orders</th>
                                <th class="text-left px-5 py-4 font-semibold">Action</th>
                            </tr>
                        </thead>

                        <tbody id="linkedIngredientsBody">
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                                    No ingredients linked.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div id="linkedIngredientsMobileList" class="md:hidden p-4 space-y-3">
                    <div class="px-4 py-8 text-center text-gray-400">
                        No ingredients linked.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Premium Toasts -->
<div id="premiumToastContainer" class="fixed top-4 right-4 z-[100] w-[calc(100%-2rem)] max-w-sm space-y-3 pointer-events-none"></div>

<!-- Premium Confirmation Modal -->
<div id="premiumConfirmModal" class="fixed inset-0 z-[90] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="premium-modal-panel w-full max-w-md overflow-hidden rounded-[28px] border border-orange-100 bg-white shadow-2xl">
        <div class="bg-gradient-to-br from-orange-50 via-white to-amber-50 px-6 py-6 text-center">
            <div id="premiumConfirmIcon" class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-red-200 bg-red-100 text-3xl font-black text-red-600 shadow-sm">!</div>
            <h3 id="premiumConfirmTitle" class="mt-4 text-2xl font-extrabold text-gray-900">Confirm Action</h3>
            <p id="premiumConfirmMessage" class="mt-3 text-sm leading-6 text-gray-600">Are you sure?</p>
        </div>
        <div class="border-t border-gray-100 bg-white px-6 py-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button id="premiumConfirmCancel" type="button" class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 font-bold text-gray-700 transition hover:bg-gray-50">Cancel</button>
                <button id="premiumConfirmProceed" type="button" class="w-full rounded-2xl bg-red-600 px-4 py-3 font-bold text-white shadow-sm transition hover:bg-red-700">Continue</button>
            </div>
        </div>
    </div>
</div>

<script>
let menuItems = [];
let filteredMenuItems = [];
let allIngredients = [];

let categories = [
    'Authentic Ala Carte Meals',
    'Dishes',
    'Korean Kitchen Specials',
    'Chef Oppa Special',
    'Noodles',
    'Salad',
    'Maki & Sushi',
    'Jeon Series',
    'Tteokbokki Series',
    'Drinks',
    'Unlimited',
    'Extras',
];

let flavorTags = [
    'spicy', 'sweet', 'savory', 'mild', 'sour', 'creamy', 'refreshing', 'salty',
    'crispy', 'cheesy', 'rich', 'smoky', 'umami', 'tangy', 'fried', 'grilled',
    'seafood', 'meaty', 'broth', 'fermented'
];

let mealTypes = [
    'set', 'main', 'side', 'drink', 'dessert', 'snack', 'soup', 'hotpot',
    'noodle', 'sushi', 'salad', 'extra', 'alcohol'
];

let editingMenuItemId = null;
let activeIngredientsMenuItemId = null;
let ingredientMutationInProgress = false;
let ingredientMutationToken = 0;

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function showNotice(message, type = 'success', title = '') {
    const container = document.getElementById('premiumToastContainer');
    if (!container) return;

    const styles = {
        success: { icon: '✓', title: title || 'Success', classes: 'border-green-200 bg-green-50 text-green-800', iconClasses: 'bg-green-500 text-white' },
        error: { icon: '!', title: title || 'Something went wrong', classes: 'border-red-200 bg-red-50 text-red-800', iconClasses: 'bg-red-500 text-white' },
        warning: { icon: '!', title: title || 'Please check', classes: 'border-amber-200 bg-amber-50 text-amber-800', iconClasses: 'bg-amber-500 text-white' },
        info: { icon: 'i', title: title || 'Information', classes: 'border-blue-200 bg-blue-50 text-blue-800', iconClasses: 'bg-blue-500 text-white' },
    };

    const style = styles[type] || styles.info;
    const toast = document.createElement('div');
    toast.className = `premium-toast pointer-events-auto rounded-2xl border p-4 shadow-xl ${style.classes}`;
    toast.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl font-black ${style.iconClasses}">${style.icon}</div>
            <div class="min-w-0 flex-1">
                <p class="font-extrabold">${safeText(style.title)}</p>
                <p class="mt-1 text-sm leading-5">${safeText(message)}</p>
            </div>
            <button type="button" class="text-lg opacity-60 hover:opacity-100" aria-label="Close">&times;</button>
        </div>`;

    const remove = () => toast.remove();
    toast.querySelector('button').addEventListener('click', remove);
    container.appendChild(toast);
    setTimeout(remove, 4200);
}

function premiumConfirm({ title = 'Confirm Action', message = 'Are you sure?', confirmText = 'Continue', tone = 'danger' } = {}) {
    const modal = document.getElementById('premiumConfirmModal');
    const titleEl = document.getElementById('premiumConfirmTitle');
    const messageEl = document.getElementById('premiumConfirmMessage');
    const proceed = document.getElementById('premiumConfirmProceed');
    const cancel = document.getElementById('premiumConfirmCancel');
    const icon = document.getElementById('premiumConfirmIcon');

    titleEl.textContent = title;
    messageEl.textContent = message;
    proceed.textContent = confirmText;

    if (tone === 'warning') {
        proceed.className = 'w-full rounded-2xl bg-orange-500 px-4 py-3 font-bold text-white shadow-sm transition hover:bg-orange-600';
        icon.className = 'mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-orange-200 bg-orange-100 text-3xl font-black text-orange-600 shadow-sm';
    } else {
        proceed.className = 'w-full rounded-2xl bg-red-600 px-4 py-3 font-bold text-white shadow-sm transition hover:bg-red-700';
        icon.className = 'mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-red-200 bg-red-100 text-3xl font-black text-red-600 shadow-sm';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');

    return new Promise(resolve => {
        const finish = value => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            proceed.onclick = null;
            cancel.onclick = null;
            resolve(value);
        };
        proceed.onclick = () => finish(true);
        cancel.onclick = () => finish(false);
    });
}

function formatMoney(value) {
    return `₱${Number(value || 0).toFixed(2)}`;
}

function formatNumber(value) {
    const number = Number(value || 0);

    if (Number.isInteger(number)) {
        return number.toLocaleString();
    }

    return number.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatLabel(value) {
    return String(value || '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
}

function getImageSrc(item) {
    if (!item) return null;

    const image = item.image_url || item.image;

    if (!image) return null;

    if (image.startsWith('http://') || image.startsWith('https://')) {
        return image;
    }

    if (image.startsWith('/storage/')) {
        return image;
    }

    return `/storage/${image}`;
}

function setButtonLoading(button, isLoading, loadingText = 'Loading...') {
    if (!button) return;

    if (isLoading) {
        button.dataset.originalText = button.textContent;
        button.textContent = loadingText;
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-not-allowed');
    } else {
        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
        button.classList.remove('opacity-70', 'cursor-not-allowed');
    }
}

function findMenuItemFromResponse(data) {
    if (!data) return null;

    if (data.menu_item && data.menu_item.id) return data.menu_item;
    if (data.menuItem && data.menuItem.id) return data.menuItem;
    if (data.data && data.data.id) return data.data;
    if (data.id && data.name) return data;

    return null;
}

function replaceMenuItemInMemory(updatedItem) {
    if (!updatedItem || !updatedItem.id) return false;

    const index = menuItems.findIndex(item => Number(item.id) === Number(updatedItem.id));

    if (index >= 0) {
        menuItems[index] = updatedItem;
    } else {
        menuItems.push(updatedItem);
    }

    populateCategoryFilters();
    applyFilters();

    return true;
}

function removeMenuItemFromMemory(id) {
    menuItems = menuItems.filter(item => Number(item.id) !== Number(id));
    applyFilters();
}

async function silentReloadMenuItems() {
    try {
        const res = await fetch('/api/admin/menu-items', {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) return;

        const data = await res.json();

        if (Array.isArray(data)) {
            menuItems = data;
        } else {
            menuItems = data.menu_items ?? [];

            if (data.categories) categories = data.categories;
            if (data.flavor_tags_options) flavorTags = data.flavor_tags_options;
            if (data.meal_type_options) mealTypes = data.meal_type_options;
        }

        populateCategoryFilters();
        populateFlavorTags();
        populateMealTypes();
        applyFilters();

        if (activeIngredientsMenuItemId) {
            renderLinkedIngredients(activeIngredientsMenuItemId);
        }
    } catch (error) {
        console.error('Silent menu reload failed:', error);
    }
}

async function loadIngredientsList() {
    try {
        const res = await fetch('/api/admin/ingredients', {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) return;

        const data = await res.json();
        allIngredients = Array.isArray(data) ? data : (data.ingredients ?? data.data ?? []);
        populateIngredientSelect();
    } catch (error) {
        console.error('Load ingredients failed:', error);
    }
}

function populateIngredientSelect() {
    const select = document.getElementById('ingredientSelect');
    if (!select) return;

    const selected = select.value || '';

    select.innerHTML = '<option value="">Select Ingredient</option>';

    allIngredients
        .slice()
        .sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')))
        .forEach(ingredient => {
            const unit = ingredient.unit || 'unit';
            const stock = ingredient.total_stock ?? ingredient.current_stock ?? 0;

            select.innerHTML += `
                <option value="${safeText(ingredient.id)}">
                    ${safeText(ingredient.name)} - ${safeText(formatNumber(stock))} ${safeText(unit)}
                </option>
            `;
        });

    select.value = selected;
}

function getImageHtml(item) {
    const imageSrc = getImageSrc(item);

    if (imageSrc) {
        return `
            <img src="${safeText(imageSrc)}"
                class="w-14 h-14 object-cover rounded-xl border"
                onerror="this.outerHTML='<div class=&quot;w-14 h-14 rounded-xl bg-gray-100 border flex items-center justify-center text-gray-400 text-xs&quot;>No Image</div>'">
        `;
    }

    return `
        <div class="w-14 h-14 rounded-xl bg-gray-100 border flex items-center justify-center text-gray-400 text-xs">
            No Image
        </div>
    `;
}

function getMobileImageHtml(item) {
    const imageSrc = getImageSrc(item);

    if (imageSrc) {
        return `
            <img src="${safeText(imageSrc)}"
                class="w-20 h-20 object-cover rounded-2xl border"
                onerror="this.outerHTML='<div class=&quot;w-20 h-20 rounded-2xl bg-gray-100 border flex items-center justify-center text-gray-400 text-xs&quot;>No Image</div>'">
        `;
    }

    return `
        <div class="w-20 h-20 rounded-2xl bg-gray-100 border flex items-center justify-center text-gray-400 text-xs">
            No Image
        </div>
    `;
}

function availabilityBadge(item) {
    if (item.is_available) {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-600">Available</span>';
    }

    return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-600">Unavailable</span>';
}

function getIngredientSummary(item) {
    if (item.category === 'Chef Oppa Special' || item.inventory_type === 'custom') {
        return 'Custom request item. Staff confirms availability.';
    }

    const ingredients = Array.isArray(item.ingredients) ? item.ingredients : [];

    if (!ingredients.length) {
        return 'No ingredients linked';
    }

    return `${ingredients.length} linked ingredient${ingredients.length === 1 ? '' : 's'}`;
}

function getFlavorTagsHtml(item) {
    const tags = Array.isArray(item.flavor_tags) ? item.flavor_tags : [];

    if (!tags.length && !item.meal_type) {
        return `<p class="text-xs text-gray-400 mt-1 italic">No AI tags yet</p>`;
    }

    const tagsHtml = tags.slice(0, 6).map(tag => `
        <span class="bg-orange-50 text-orange-600 border border-orange-100 px-2 py-0.5 rounded-full text-[11px]">
            ${safeText(tag)}
        </span>
    `).join('');

    const mealTypeHtml = item.meal_type
        ? `
            <span class="bg-blue-50 text-blue-600 border border-blue-100 px-2 py-0.5 rounded-full text-[11px]">
                ${safeText(item.meal_type)}
            </span>
        `
        : '';

    return `
        <div class="flex flex-wrap gap-1 mt-2">
            ${mealTypeHtml}
            ${tagsHtml}
        </div>
    `;
}

async function loadMenuItems() {
    const tbody = document.getElementById('menuTableBody');
    const mobileList = document.getElementById('menuMobileList');

    try {
        const res = await fetch('/api/admin/menu-items', {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) {
            const message = `Failed to load menu items. API returned ${res.status}.`;

            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-red-500">
                        ${safeText(message)}
                    </td>
                </tr>
            `;

            mobileList.innerHTML = `
                <div class="px-4 py-8 text-center text-red-500">
                    ${safeText(message)}
                </div>
            `;

            return;
        }

        const data = await res.json();

        if (Array.isArray(data)) {
            menuItems = data;
        } else {
            menuItems = data.menu_items ?? [];

            if (data.categories) categories = data.categories;
            if (data.flavor_tags_options) flavorTags = data.flavor_tags_options;
            if (data.meal_type_options) mealTypes = data.meal_type_options;
        }

        populateCategoryFilters();
        populateFlavorTags();
        populateMealTypes();
        applyFilters();
    } catch (error) {
        console.error('Load menu items failed:', error);

        const errorMessage = error?.message
            ? `Failed to load menu items: ${error.message}`
            : 'Failed to load menu items. Please check your connection.';

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-red-500">
                    ${safeText(errorMessage)}
                </td>
            </tr>
        `;

        mobileList.innerHTML = `
            <div class="px-4 py-8 text-center text-red-500">
                ${safeText(errorMessage)}
            </div>
        `;
    }
}

function populateCategoryFilters() {
    const filter = document.getElementById('categoryFilter');
    const itemCategory = document.getElementById('itemCategory');

    const selectedFilter = filter.value || 'all';
    const selectedCategory = itemCategory.value || '';

    filter.innerHTML = '<option value="all">All Categories</option>';
    itemCategory.innerHTML = '<option value="">Select Category</option>';

    categories.forEach(category => {
        filter.innerHTML += `<option value="${safeText(category)}">${safeText(category)}</option>`;
        itemCategory.innerHTML += `<option value="${safeText(category)}">${safeText(category)}</option>`;
    });

    const extraCategories = [...new Set(menuItems.map(item => item.category).filter(Boolean))]
        .filter(category => !categories.includes(category));

    extraCategories.forEach(category => {
        filter.innerHTML += `<option value="${safeText(category)}">${safeText(category)}</option>`;
        itemCategory.innerHTML += `<option value="${safeText(category)}">${safeText(category)}</option>`;
    });

    filter.value = selectedFilter;
    itemCategory.value = selectedCategory;
}

function populateFlavorTags(selectedTags = []) {
    const container = document.getElementById('flavorTagsContainer');
    const selected = Array.isArray(selectedTags) ? selectedTags : [];

    container.innerHTML = '';

    flavorTags.forEach(tag => {
        const checked = selected.includes(tag) ? 'checked' : '';

        container.innerHTML += `
            <label class="group flex items-center gap-2 text-sm bg-gray-50 border border-gray-200 rounded-2xl px-3 py-2.5 cursor-pointer hover:bg-orange-50 hover:border-orange-200 transition">
                <input
                    type="checkbox"
                    class="flavor-tag-checkbox rounded text-orange-500 focus:ring-orange-400"
                    value="${safeText(tag)}"
                    ${checked}
                >
                <span class="text-gray-700 group-hover:text-orange-700 font-medium">${safeText(formatLabel(tag))}</span>
            </label>
        `;
    });
}

function populateMealTypes(selectedType = '') {
    const select = document.getElementById('itemMealType');

    select.innerHTML = '<option value="">Select Meal Type</option>';

    mealTypes.forEach(type => {
        const selected = selectedType === type ? 'selected' : '';

        select.innerHTML += `
            <option value="${safeText(type)}" ${selected}>
                ${safeText(formatLabel(type))}
            </option>
        `;
    });
}

function getSelectedFlavorTags() {
    return Array.from(document.querySelectorAll('.flavor-tag-checkbox:checked'))
        .map(checkbox => checkbox.value);
}

function applyFilters() {
    const search = document.getElementById('menuSearch').value.toLowerCase().trim();
    const category = document.getElementById('categoryFilter').value;

    filteredMenuItems = menuItems.filter(item => {
        const name = String(item.name || '').toLowerCase();
        const description = String(item.description || '').toLowerCase();
        const mealType = String(item.meal_type || '').toLowerCase();
        const tags = Array.isArray(item.flavor_tags) ? item.flavor_tags.join(' ').toLowerCase() : '';
        const ingredientText = Array.isArray(item.ingredients)
            ? item.ingredients.map(ingredient => ingredient.name).join(' ').toLowerCase()
            : '';

        const matchesSearch =
            name.includes(search) ||
            description.includes(search) ||
            mealType.includes(search) ||
            tags.includes(search) ||
            ingredientText.includes(search);

        const matchesCategory = category === 'all' ? true : item.category === category;

        return matchesSearch && matchesCategory;
    });

    renderMenuTable();
    renderMenuMobileList();
}

function renderMenuTable() {
    const tbody = document.getElementById('menuTableBody');

    if (!filteredMenuItems.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                    No menu items found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filteredMenuItems.map(item => `
        <tr class="border-t hover:bg-gray-50">
            <td class="px-6 py-4">
                ${getImageHtml(item)}
            </td>

            <td class="px-6 py-4 max-w-[360px]">
                <p class="font-semibold">${safeText(item.name)}</p>

                ${
                    item.description
                        ? `<p class="text-xs text-gray-500 mt-1 line-clamp-2">${safeText(item.description)}</p>`
                        : `<p class="text-xs text-gray-400 mt-1 italic">No description</p>`
                }

                <div class="mt-2 space-y-1">
                    <p class="text-xs text-gray-500">
                        Ingredients: <span class="font-medium">${safeText(getIngredientSummary(item))}</span>
                    </p>

                    <p class="text-xs text-gray-500">
                        ${safeText(item.stock_label || 'No stock data')}
                    </p>
                </div>

                ${getFlavorTagsHtml(item)}
            </td>

            <td class="px-6 py-4">
                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs">
                    ${safeText(item.category || 'Uncategorized')}
                </span>
            </td>

            <td class="px-6 py-4 font-semibold">
                ${item.inventory_type === 'custom' || item.category === 'Chef Oppa Special'
                    ? '<span class="text-orange-500">To be confirmed</span>'
                    : formatMoney(item.price)
                }
            </td>

            <td class="px-6 py-4">
                <button onclick="toggleAvailability(${item.id}, ${item.is_available ? 'true' : 'false'}, this)">
                    ${availabilityBadge(item)}
                </button>
            </td>

            <td class="px-6 py-4">
                <div class="flex flex-wrap items-center gap-2">
                    <button onclick="openMenuModal(${item.id})"
                        class="px-3 py-2 rounded-lg border text-gray-700 hover:bg-gray-50 text-xs">
                        Edit
                    </button>

                    <button onclick="openIngredientsModal(${item.id})"
                        class="px-3 py-2 rounded-lg border text-gray-700 hover:bg-gray-50 text-xs">
                        Ingredients
                    </button>

                    <button onclick="deleteMenuItem(${item.id}, this)"
                        class="px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-xs">
                        Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderMenuMobileList() {
    const container = document.getElementById('menuMobileList');

    if (!filteredMenuItems.length) {
        container.innerHTML = `
            <div class="px-4 py-8 text-center text-gray-400">
                No menu items found.
            </div>
        `;
        return;
    }

    container.innerHTML = filteredMenuItems.map(item => `
        <div class="rounded-3xl border bg-white shadow-sm p-4 overflow-hidden">
            <div class="menu-mobile-card-main flex items-start gap-4">
                <div class="menu-mobile-card-image shrink-0">
                    ${getMobileImageHtml(item)}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="menu-mobile-card-header flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-900 leading-snug text-base break-words">
                                ${safeText(item.name)}
                            </h3>

                            <p class="text-xs text-gray-500 mt-1 break-words">
                                ${safeText(item.category || 'Uncategorized')}
                            </p>
                        </div>

                        <button onclick="toggleAvailability(${item.id}, ${item.is_available ? 'true' : 'false'}, this)"
                            class="shrink-0">
                            ${availabilityBadge(item)}
                        </button>
                    </div>

                    ${
                        item.description
                            ? `<p class="text-xs text-gray-500 mt-2 leading-5 break-words">${safeText(item.description)}</p>`
                            : `<p class="text-xs text-gray-400 mt-2 italic">No description</p>`
                    }
                </div>
            </div>

            <div class="mt-4 rounded-2xl bg-gray-50 border px-4 py-3 space-y-1">
                <p class="text-xs text-gray-600">
                    <span class="font-semibold">Price:</span>
                    ${
                        item.inventory_type === 'custom' || item.category === 'Chef Oppa Special'
                            ? '<span class="text-orange-500 font-semibold">To be confirmed</span>'
                            : `<span class="font-semibold">${formatMoney(item.price)}</span>`
                    }
                </p>

                <p class="text-xs text-gray-600">
                    <span class="font-semibold">Ingredients:</span>
                    ${safeText(getIngredientSummary(item))}
                </p>

                <p class="text-xs text-gray-500">
                    ${safeText(item.stock_label || 'No stock data')}
                </p>
            </div>

            ${getFlavorTagsHtml(item)}

            <div class="menu-mobile-card-actions grid grid-cols-1 sm:grid-cols-3 gap-2 mt-4">
                <button onclick="openMenuModal(${item.id})"
                    class="px-3 py-2.5 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                    Edit
                </button>

                <button onclick="openIngredientsModal(${item.id})"
                    class="px-3 py-2.5 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                    Ingredients
                </button>

                <button onclick="deleteMenuItem(${item.id}, this)"
                    class="px-3 py-2.5 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold">
                    Delete
                </button>
            </div>
        </div>
    `).join('');
}


function resetImagePreview(previewSrc = '', label = '') {
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePlaceholder');
    const selectedName = document.getElementById('selectedImageName');

    if (!preview || !placeholder || !selectedName) {
        return;
    }

    if (previewSrc) {
        preview.src = previewSrc;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        selectedName.textContent = label || 'Selected image';
        selectedName.classList.remove('hidden');
        return;
    }

    preview.src = '';
    preview.classList.add('hidden');
    placeholder.classList.remove('hidden');
    selectedName.textContent = '';
    selectedName.classList.add('hidden');
}

function clearSelectedImageFile() {
    const imageInput = document.getElementById('itemImage');

    if (imageInput) {
        imageInput.value = '';
    }

    resetImagePreview();
}

function setupImagePreviewHandler() {
    const itemImageInput = document.getElementById('itemImage');

    if (!itemImageInput) {
        return;
    }

    itemImageInput.addEventListener('change', function () {
        const file = this.files && this.files[0] ? this.files[0] : null;

        if (!file) {
            resetImagePreview();
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!allowedTypes.includes(file.type)) {
            showNotice('Please upload a JPG, PNG, or WEBP image only.', 'warning', 'Invalid image type');
            this.value = '';
            resetImagePreview();
            return;
        }

        const maxSize = 4 * 1024 * 1024;

        if (file.size > maxSize) {
            showNotice('Image must not be larger than 4MB.', 'warning', 'Image is too large');
            this.value = '';
            resetImagePreview();
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            resetImagePreview(event.target.result, file.name);
        };

        reader.readAsDataURL(file);
    });
}

function openMenuModal(id = null) {
    editingMenuItemId = id;

    const imageInput = document.getElementById('itemImage');
    const currentImageText = document.getElementById('currentImageText');

    populateFlavorTags();
    populateMealTypes();

    if (id) {
        const item = menuItems.find(menuItem => Number(menuItem.id) === Number(id));
        if (!item) return;

        document.getElementById('menuModalTitle').textContent = 'Edit Menu Item';
        document.getElementById('menuSaveBtn').textContent = 'Update Menu Item';

        document.getElementById('itemName').value = item.name || '';
        document.getElementById('itemCategory').value = item.category || '';
        document.getElementById('itemDescription').value = item.description || '';
        document.getElementById('itemPrice').value = item.price || '';

        if (imageInput) {
            imageInput.value = '';
        }

        const currentImageSrc = getImageSrc(item);

        if (currentImageSrc) {
            resetImagePreview(currentImageSrc, 'Current image');
        } else {
            resetImagePreview();
        }

        currentImageText.classList.toggle('hidden', !item.image && !item.image_url);
        document.getElementById('itemAvailable').checked = Boolean(item.is_available);
        document.getElementById('itemUnlimited').checked = Boolean(item.is_unlimited);

        populateFlavorTags(item.flavor_tags || []);
        populateMealTypes(item.meal_type || '');
    } else {
        document.getElementById('menuModalTitle').textContent = 'Add Menu Item';
        document.getElementById('menuSaveBtn').textContent = 'Save Menu Item';
        document.getElementById('menuForm').reset();
        document.getElementById('itemDescription').value = '';

        if (imageInput) {
            imageInput.value = '';
        }

        resetImagePreview();
        currentImageText.classList.add('hidden');
        document.getElementById('itemAvailable').checked = true;
        document.getElementById('itemUnlimited').checked = false;

        populateFlavorTags([]);
        populateMealTypes('');
    }

    const modal = document.getElementById('menuModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeMenuModal() {
    editingMenuItemId = null;

    const modal = document.getElementById('menuModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('menuForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const saveBtn = document.getElementById('menuSaveBtn');
    const wasEditingMenuItem = Boolean(editingMenuItemId);
    const imageInput = document.getElementById('itemImage');

    const formData = new FormData();
    formData.append('name', document.getElementById('itemName').value);
    formData.append('category', document.getElementById('itemCategory').value);
    formData.append('description', document.getElementById('itemDescription').value);
    formData.append('price', document.getElementById('itemPrice').value);
    formData.append('is_available', document.getElementById('itemAvailable').checked ? '1' : '0');
    formData.append('is_unlimited', document.getElementById('itemUnlimited').checked ? '1' : '0');

    const selectedFlavorTags = getSelectedFlavorTags();

    selectedFlavorTags.forEach(tag => {
        formData.append('flavor_tags[]', tag);
    });

    formData.append('meal_type', document.getElementById('itemMealType').value);

    if (imageInput.files && imageInput.files[0]) {
        formData.append('image', imageInput.files[0]);
    }

    let url = '/api/admin/menu-items';
    let method = 'POST';

    if (editingMenuItemId) {
        url = `/api/admin/menu-items/${editingMenuItemId}`;
        method = 'POST';
        formData.append('_method', 'PUT');
    }

    setButtonLoading(saveBtn, true, editingMenuItemId ? 'Updating...' : 'Saving...');

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
            },
            body: formData
        });

        const data = await res.json();

        if (!res.ok) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                showNotice(firstError, 'error', 'Validation error');
            } else {
                showNotice(data.message || 'Failed to save menu item.', 'error');
            }
            return;
        }

        const updatedItem = findMenuItemFromResponse(data);

        if (updatedItem) {
            replaceMenuItemInMemory(updatedItem);
        } else {
            silentReloadMenuItems();
        }

        closeMenuModal();
        showNotice(wasEditingMenuItem ? 'Menu item updated successfully.' : 'Menu item added successfully.', 'success', wasEditingMenuItem ? 'Menu item updated' : 'Menu item created');
    } catch (error) {
        console.error('Save menu item failed:', error);
        showNotice('Failed to save menu item. Please check your connection.', 'error', 'Connection error');
    } finally {
        setButtonLoading(saveBtn, false);
    }
});

async function toggleAvailability(id, currentStatus, button) {
    setButtonLoading(button, true, 'Updating...');

    try {
        const res = await fetch(`/api/admin/menu-items/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                is_available: !currentStatus
            })
        });

        const data = await res.json();

        if (!res.ok) {
            showNotice(data.message || 'Failed to update availability.', 'error');
            return;
        }

        const updatedItem = findMenuItemFromResponse(data);

        if (updatedItem) {
            replaceMenuItemInMemory(updatedItem);
        } else {
            silentReloadMenuItems();
        }
        showNotice('Menu availability updated successfully.', 'success', 'Availability updated');
    } catch (error) {
        console.error('Availability update failed:', error);
        showNotice('Failed to update availability. Please check your connection.', 'error', 'Connection error');
    } finally {
        setButtonLoading(button, false);
    }
}

async function deleteMenuItem(id, button) {
    if (!await premiumConfirm({ title: 'Delete Menu Item?', message: 'This menu item will be permanently removed. This action cannot be undone.', confirmText: 'Yes, Delete Item' })) return;

    setButtonLoading(button, true, 'Deleting...');

    try {
        const res = await fetch(`/api/admin/menu-items/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) {
            showNotice('Failed to delete menu item.', 'error');
            return;
        }

        removeMenuItemFromMemory(id);
        silentReloadMenuItems();
        showNotice('Menu item deleted successfully.', 'success', 'Menu item deleted');
    } catch (error) {
        console.error('Delete menu item failed:', error);
        showNotice('Failed to delete menu item. Please check your connection.', 'error', 'Connection error');
    } finally {
        setButtonLoading(button, false);
    }
}

function setIngredientsAvailabilityBadge(item) {
    const badge = document.getElementById('ingredientsAvailabilityBadge');

    if (!badge) return;

    badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold';

    if (!item) {
        badge.textContent = 'Unknown';
        badge.classList.add('bg-gray-100', 'text-gray-600');
        return;
    }

    if (item.is_available) {
        badge.textContent = 'Available';
        badge.classList.add('bg-green-100', 'text-green-700');
        return;
    }

    badge.textContent = 'Unavailable';
    badge.classList.add('bg-red-100', 'text-red-600');
}

function setIngredientFormDisabled(isDisabled) {
    const form = document.getElementById('attachIngredientForm');
    const ingredientSelect = document.getElementById('ingredientSelect');
    const quantityInput = document.getElementById('quantityRequired');
    const refillableInput = document.getElementById('ingredientRefillable');
    const refillQuantityInput = document.getElementById('refillQuantity');
    const submitButton = document.getElementById('attachIngredientBtn');

    if (ingredientSelect) ingredientSelect.disabled = isDisabled;
    if (quantityInput) quantityInput.disabled = isDisabled;
    if (refillableInput) refillableInput.disabled = isDisabled;
    if (refillQuantityInput) refillQuantityInput.disabled = isDisabled;
    if (submitButton) submitButton.disabled = isDisabled;

    if (form) {
        form.classList.toggle('opacity-70', isDisabled);
        form.classList.toggle('pointer-events-none', isDisabled);
    }
}

function findIngredientFromList(ingredientId) {
    return allIngredients.find(item => Number(item.id) === Number(ingredientId)) || null;
}

function cloneMenuItem(item) {
    if (!item) return null;

    try {
        return JSON.parse(JSON.stringify(item));
    } catch (error) {
        return { ...item };
    }
}

function restoreMenuItemSnapshot(snapshot) {
    if (!snapshot || !snapshot.id) return;

    const index = menuItems.findIndex(item => Number(item.id) === Number(snapshot.id));

    if (index >= 0) {
        menuItems[index] = snapshot;
        applyFilters();

        if (Number(activeIngredientsMenuItemId) === Number(snapshot.id)) {
            setIngredientsAvailabilityBadge(snapshot);
            renderLinkedIngredients(snapshot.id);
        }
    }
}

function applyOptimisticIngredientLink(
    menuItemId,
    ingredientId,
    quantityRequired,
    isRefillable = false,
    refillQuantity = null
) {
    const item = menuItems.find(menuItem => Number(menuItem.id) === Number(menuItemId));
    const ingredient = findIngredientFromList(ingredientId);

    if (!item || !ingredient) return null;

    const snapshot = cloneMenuItem(item);
    const linkedIngredients = Array.isArray(item.ingredients) ? [...item.ingredients] : [];
    const existingIndex = linkedIngredients.findIndex(linked => Number(linked.id) === Number(ingredientId));

    const optimisticIngredient = {
        ...ingredient,
        quantity_required: Number(quantityRequired),
        is_refillable: Boolean(isRefillable),
        refill_quantity: isRefillable ? Number(refillQuantity) : null,
        pivot: {
            ...(ingredient.pivot || {}),
            menu_item_id: Number(menuItemId),
            ingredient_id: Number(ingredientId),
            quantity_required: Number(quantityRequired),
            is_refillable: Boolean(isRefillable),
            refill_quantity: isRefillable ? Number(refillQuantity) : null,
        },
    };

    if (existingIndex >= 0) {
        linkedIngredients[existingIndex] = {
            ...linkedIngredients[existingIndex],
            quantity_required: Number(quantityRequired),
            is_refillable: Boolean(isRefillable),
            refill_quantity: isRefillable ? Number(refillQuantity) : null,
            pivot: {
                ...(linkedIngredients[existingIndex].pivot || {}),
                quantity_required: Number(quantityRequired),
                is_refillable: Boolean(isRefillable),
                refill_quantity: isRefillable ? Number(refillQuantity) : null,
            },
        };
    } else {
        linkedIngredients.push(optimisticIngredient);
    }

    item.ingredients = linkedIngredients;
    item.stock_label = 'Updating ingredient availability...';

    applyFilters();

    if (Number(activeIngredientsMenuItemId) === Number(menuItemId)) {
        setIngredientsAvailabilityBadge(item);
        renderLinkedIngredients(menuItemId);
    }

    return snapshot;
}

function applyOptimisticIngredientDetach(menuItemId, ingredientId) {
    const item = menuItems.find(menuItem => Number(menuItem.id) === Number(menuItemId));

    if (!item) return null;

    const snapshot = cloneMenuItem(item);
    const linkedIngredients = Array.isArray(item.ingredients) ? item.ingredients : [];

    item.ingredients = linkedIngredients.filter(ingredient => Number(ingredient.id) !== Number(ingredientId));
    item.stock_label = 'Updating ingredient availability...';

    applyFilters();

    if (Number(activeIngredientsMenuItemId) === Number(menuItemId)) {
        setIngredientsAvailabilityBadge(item);
        renderLinkedIngredients(menuItemId);
    }

    return snapshot;
}

function openIngredientsModal(id) {
    if (ingredientMutationInProgress) {
        showNotice('Please wait for the current ingredient update to finish.', 'info');
        return;
    }

    activeIngredientsMenuItemId = id;

    const item = menuItems.find(menuItem => Number(menuItem.id) === Number(id));
    if (!item) return;

    document.getElementById('ingredientsMenuItemId').value = id;
    document.getElementById('ingredientsModalTitle').textContent = `Ingredients - ${item.name}`;
    document.getElementById('attachIngredientForm').reset();

    const refillPanel = document.getElementById('refillConfigPanel');
    const refillableInput = document.getElementById('ingredientRefillable');
    const refillQuantityInput = document.getElementById('refillQuantity');
    const refillQuantityWrapper = document.getElementById('refillQuantityWrapper');

    refillPanel.classList.toggle('hidden', !Boolean(item.is_unlimited));
    refillableInput.checked = false;
    refillQuantityInput.value = '';
    refillQuantityWrapper.classList.add('hidden');
    updateRefillUnitLabel();

    setIngredientsAvailabilityBadge(item);
    populateIngredientSelect();
    renderLinkedIngredients(id);

    const modal = document.getElementById('ingredientsModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeIngredientsModal() {
    activeIngredientsMenuItemId = null;

    const modal = document.getElementById('ingredientsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function getIngredientRequiredValue(ingredient) {
    if (ingredient.quantity_required !== undefined && ingredient.quantity_required !== null) {
        return Number(ingredient.quantity_required || 0);
    }

    if (ingredient.pivot && ingredient.pivot.quantity_required !== undefined) {
        return Number(ingredient.pivot.quantity_required || 0);
    }

    return 0;
}

function getIngredientRefillableValue(ingredient) {
    if (ingredient.is_refillable !== undefined && ingredient.is_refillable !== null) {
        return Boolean(ingredient.is_refillable);
    }

    return Boolean(ingredient.pivot?.is_refillable);
}

function getIngredientRefillQuantityValue(ingredient) {
    if (ingredient.refill_quantity !== undefined && ingredient.refill_quantity !== null) {
        return Number(ingredient.refill_quantity || 0);
    }

    if (ingredient.pivot?.refill_quantity !== undefined && ingredient.pivot.refill_quantity !== null) {
        return Number(ingredient.pivot.refill_quantity || 0);
    }

    return 0;
}

function updateRefillUnitLabel() {
    const ingredientId = document.getElementById('ingredientSelect')?.value;
    const ingredient = findIngredientFromList(ingredientId);
    const unitLabel = document.getElementById('refillUnitLabel');

    if (unitLabel) {
        unitLabel.textContent = ingredient?.unit || 'unit';
    }
}

function resetRefillIngredientFields() {
    const refillableInput = document.getElementById('ingredientRefillable');
    const refillQuantityInput = document.getElementById('refillQuantity');
    const refillQuantityWrapper = document.getElementById('refillQuantityWrapper');

    if (refillableInput) refillableInput.checked = false;
    if (refillQuantityInput) refillQuantityInput.value = '';
    if (refillQuantityWrapper) refillQuantityWrapper.classList.add('hidden');

    updateRefillUnitLabel();
}

function getIngredientStockValue(ingredient) {
    if (ingredient.total_stock !== undefined && ingredient.total_stock !== null) {
        return Number(ingredient.total_stock || 0);
    }

    return Number(ingredient.current_stock || 0);
}

function getPossibleOrdersForIngredient(ingredient) {
    const stock = getIngredientStockValue(ingredient);
    const required = getIngredientRequiredValue(ingredient);

    if (required <= 0) return 0;

    return Math.floor(stock / required);
}

function getStockChipHtml(possibleOrders) {
    if (possibleOrders <= 0) {
        return `<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-100 text-red-600 text-[11px] font-semibold">Insufficient</span>`;
    }

    if (possibleOrders <= 3) {
        return `<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 text-[11px] font-semibold">Low</span>`;
    }

    return `<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-[11px] font-semibold">Good</span>`;
}

function renderLinkedIngredients(id) {
    const item = menuItems.find(menuItem => Number(menuItem.id) === Number(id));
    const body = document.getElementById('linkedIngredientsBody');
    const mobileList = document.getElementById('linkedIngredientsMobileList');
    const stockLabel = document.getElementById('ingredientsStockLabel');
    const summaryCount = document.getElementById('ingredientsSummaryCount');
    const summaryPossibleOrders = document.getElementById('ingredientsSummaryPossibleOrders');

    if (!item) return;

    const linked = Array.isArray(item.ingredients) ? item.ingredients : [];

    stockLabel.textContent = item.stock_label || 'No stock data';
    summaryCount.textContent = linked.length;

    let overallPossibleOrders = 0;

    if (linked.length > 0) {
        overallPossibleOrders = Math.min(...linked.map(ingredient => getPossibleOrdersForIngredient(ingredient)));

        if (!Number.isFinite(overallPossibleOrders)) {
            overallPossibleOrders = 0;
        }
    }

    summaryPossibleOrders.textContent = overallPossibleOrders;

    if (!linked.length) {
        body.innerHTML = `
            <tr>
                <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                    No ingredients linked.
                </td>
            </tr>
        `;

        mobileList.innerHTML = `
            <div class="rounded-2xl border border-dashed bg-gray-50 px-4 py-10 text-center text-gray-400">
                No ingredients linked.
            </div>
        `;

        return;
    }

    body.innerHTML = linked.map(ingredient => {
        const stock = getIngredientStockValue(ingredient);
        const required = getIngredientRequiredValue(ingredient);
        const unit = ingredient.unit || 'unit';
        const possibleOrders = getPossibleOrdersForIngredient(ingredient);

        return `
            <tr class="border-t hover:bg-orange-50/30 transition">
                <td class="px-5 py-4">
                    <p class="font-semibold text-gray-900">${safeText(ingredient.name)}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Ingredient resource</p>
                </td>

                <td class="px-5 py-4">
                    <p class="font-semibold text-gray-900">${safeText(formatNumber(stock))} ${safeText(unit)}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Current usable stock</p>
                </td>

                <td class="px-5 py-4">
                    <p class="font-semibold text-gray-900">${safeText(formatNumber(required))} ${safeText(unit)}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Consumed per order</p>

                    ${
                        getIngredientRefillableValue(ingredient)
                            ? `
                                <div class="mt-2">
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[11px] font-semibold">
                                        Refill: ${safeText(formatNumber(getIngredientRefillQuantityValue(ingredient)))} ${safeText(unit)}
                                    </span>
                                </div>
                            `
                            : ''
                    }
                </td>

                <td class="px-5 py-4">
                    <div class="space-y-1">
                        <p class="font-bold text-gray-900">${safeText(possibleOrders)}</p>
                        ${getStockChipHtml(possibleOrders)}
                    </div>
                </td>

                <td class="px-5 py-4">
                    <button onclick="detachIngredient(${item.id}, ${ingredient.id}, this)"
                        class="px-4 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold transition">
                        Remove
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    mobileList.innerHTML = linked.map(ingredient => {
        const stock = getIngredientStockValue(ingredient);
        const required = getIngredientRequiredValue(ingredient);
        const unit = ingredient.unit || 'unit';
        const possibleOrders = getPossibleOrdersForIngredient(ingredient);

        return `
            <div class="rounded-2xl border bg-white shadow-sm p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="font-bold text-gray-900">${safeText(ingredient.name)}</h4>
                        <p class="text-xs text-gray-400">Ingredient resource</p>
                    </div>

                    <button onclick="detachIngredient(${item.id}, ${ingredient.id}, this)"
                        class="shrink-0 px-3 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold transition">
                        Remove
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-2">
                    <div class="rounded-xl bg-gray-50 border px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Current Stock</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            ${safeText(formatNumber(stock))} ${safeText(unit)}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 border px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Required / Order</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            ${safeText(formatNumber(required))} ${safeText(unit)}
                        </p>
                    </div>

                    ${
                        getIngredientRefillableValue(ingredient)
                            ? `
                                <div class="rounded-xl bg-blue-50 border border-blue-100 px-3 py-2">
                                    <p class="text-[11px] uppercase tracking-wide text-blue-500 font-semibold">Refill Quantity</p>
                                    <p class="text-sm font-semibold text-blue-900 mt-1">
                                        ${safeText(formatNumber(getIngredientRefillQuantityValue(ingredient)))} ${safeText(unit)}
                                    </p>
                                </div>
                            `
                            : ''
                    }

                    <div class="rounded-xl bg-gray-50 border px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Possible Orders</p>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <p class="text-sm font-bold text-gray-900">${safeText(possibleOrders)}</p>
                            ${getStockChipHtml(possibleOrders)}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

document.getElementById('attachIngredientForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (ingredientMutationInProgress) return;

    const saveBtn = document.getElementById('attachIngredientBtn');
    const form = document.getElementById('attachIngredientForm');
    const menuItemId = document.getElementById('ingredientsMenuItemId').value;
    const ingredientId = document.getElementById('ingredientSelect').value;
    const quantityRequired = document.getElementById('quantityRequired').value;
    const isRefillable = document.getElementById('ingredientRefillable').checked;
    const refillQuantity = document.getElementById('refillQuantity').value;

    if (!menuItemId || !ingredientId || !quantityRequired) {
        showNotice('Please select an ingredient and enter the quantity required.', 'warning', 'Missing information');
        return;
    }

    if (isRefillable && (!refillQuantity || Number(refillQuantity) <= 0)) {
        showNotice('Please enter a valid refill quantity.', 'warning', 'Invalid refill quantity');
        return;
    }

    const requestToken = ++ingredientMutationToken;
    ingredientMutationInProgress = true;
    const snapshot = applyOptimisticIngredientLink(
        menuItemId,
        ingredientId,
        quantityRequired,
        isRefillable,
        refillQuantity
    );

    setButtonLoading(saveBtn, true, 'Adding...');
    setIngredientFormDisabled(true);

    try {
        const res = await fetch(`/api/admin/menu-items/${menuItemId}/ingredients`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                ingredient_id: ingredientId,
                quantity_required: Number(quantityRequired),
                is_refillable: isRefillable,
                refill_quantity: isRefillable ? Number(refillQuantity) : null,
            }),
        });

        const data = await res.json();

        if (!res.ok) {
            restoreMenuItemSnapshot(snapshot);

            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                showNotice(firstError, 'error', 'Validation error');
            } else {
                showNotice(data.message || 'Failed to attach ingredient.', 'error');
            }
            return;
        }

        const updatedItem = findMenuItemFromResponse(data);

        if (updatedItem) {
            replaceMenuItemInMemory(updatedItem);

            if (Number(activeIngredientsMenuItemId) === Number(menuItemId) && requestToken === ingredientMutationToken) {
                setIngredientsAvailabilityBadge(updatedItem);
                renderLinkedIngredients(menuItemId);
            }
        } else if (Number(activeIngredientsMenuItemId) === Number(menuItemId) && requestToken === ingredientMutationToken) {
            renderLinkedIngredients(menuItemId);
        }

        form.reset();
        resetRefillIngredientFields();
        populateIngredientSelect();
    } catch (error) {
        console.error('Attach ingredient failed:', error);
        restoreMenuItemSnapshot(snapshot);
        showNotice('Failed to attach ingredient. Please check your connection.', 'error', 'Connection error');
    } finally {
        if (requestToken === ingredientMutationToken) {
            ingredientMutationInProgress = false;
            setIngredientFormDisabled(false);
            setButtonLoading(saveBtn, false);
        }
    }
});

async function detachIngredient(menuItemId, ingredientId, button) {
    if (ingredientMutationInProgress) return;
    if (!await premiumConfirm({ title: 'Remove Ingredient?', message: 'This ingredient link will be removed from the menu item and its availability calculation will be updated.', confirmText: 'Yes, Remove', tone: 'warning' })) return;

    const requestToken = ++ingredientMutationToken;
    ingredientMutationInProgress = true;
    const snapshot = applyOptimisticIngredientDetach(menuItemId, ingredientId);

    setButtonLoading(button, true, 'Removing...');
    setIngredientFormDisabled(true);

    try {
        const res = await fetch(`/api/admin/menu-items/${menuItemId}/ingredients/${ingredientId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            },
        });

        const data = await res.json();

        if (!res.ok) {
            restoreMenuItemSnapshot(snapshot);
            showNotice(data.message || 'Failed to remove ingredient.', 'error');
            return;
        }

        const updatedItem = findMenuItemFromResponse(data);

        if (updatedItem) {
            replaceMenuItemInMemory(updatedItem);

            if (Number(activeIngredientsMenuItemId) === Number(menuItemId) && requestToken === ingredientMutationToken) {
                setIngredientsAvailabilityBadge(updatedItem);
                renderLinkedIngredients(menuItemId);
            }
        } else if (Number(activeIngredientsMenuItemId) === Number(menuItemId) && requestToken === ingredientMutationToken) {
            renderLinkedIngredients(menuItemId);
        }
        showNotice('Ingredient removed successfully.', 'success', 'Ingredient removed');
    } catch (error) {
        console.error('Detach ingredient failed:', error);
        restoreMenuItemSnapshot(snapshot);
        showNotice('Failed to remove ingredient. Please check your connection.', 'error', 'Connection error');
    } finally {
        if (requestToken === ingredientMutationToken) {
            ingredientMutationInProgress = false;
            setIngredientFormDisabled(false);
            setButtonLoading(button, false);
        }
    }
}

document.getElementById('menuSearch').addEventListener('input', applyFilters);
document.getElementById('categoryFilter').addEventListener('change', applyFilters);

document.getElementById('itemCategory').addEventListener('change', function () {
    if (this.value === 'Unlimited') {
        document.getElementById('itemUnlimited').checked = true;
    }
});

document.getElementById('ingredientSelect').addEventListener('change', updateRefillUnitLabel);

document.getElementById('ingredientRefillable').addEventListener('change', function () {
    const wrapper = document.getElementById('refillQuantityWrapper');
    wrapper.classList.toggle('hidden', !this.checked);

    if (!this.checked) {
        document.getElementById('refillQuantity').value = '';
    }

    updateRefillUnitLabel();
});

setupImagePreviewHandler();
loadIngredientsList();
loadMenuItems();
</script>

@endsection