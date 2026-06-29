@extends('layouts.admin')

@section('content')

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
<div id="menuModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] overflow-hidden flex flex-col">
        <div class="flex items-start justify-between gap-3 px-4 sm:px-6 py-4 border-b bg-white shrink-0">
            <div class="min-w-0">
                <h3 id="menuModalTitle" class="text-lg sm:text-xl font-bold">Add Menu Item</h3>
                <p class="text-xs sm:text-sm text-gray-500">Set menu details, category, image, and AI recommendation tags.</p>
            </div>

            <button type="button" onclick="closeMenuModal()"
                class="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-500 hover:text-black text-xl shrink-0">
                &times;
            </button>
        </div>

        <form id="menuForm" class="flex-1 overflow-y-auto px-4 sm:px-6 py-5 space-y-5 pb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Item Name</label>
                        <input id="itemName" type="text" class="w-full border rounded-xl px-3 py-2.5" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Category</label>
                        <select id="itemCategory" class="w-full border rounded-xl px-3 py-2.5" required>
                            <option value="">Select Category</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Used for menu grouping and filtering.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Description <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <textarea
                            id="itemDescription"
                            rows="4"
                            maxlength="1000"
                            placeholder="Enter menu item description..."
                            class="w-full border rounded-xl px-3 py-2.5 resize-none"
                        ></textarea>
                        <p class="text-xs text-gray-400 mt-1">Short description for customer/mobile display.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Upload Image <span class="text-gray-400 font-normal">(optional)</span>
                        </label>

                        <input
                            id="itemImage"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="w-full border rounded-xl px-3 py-2.5 text-sm"
                        >

                        <p id="currentImageText" class="text-xs text-gray-400 mt-1 hidden">
                            Current image will stay unless you upload a new one.
                        </p>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Price</label>
                        <input id="itemPrice" type="number" step="0.01" class="w-full border rounded-xl px-3 py-2.5" required>
                        <p class="text-xs text-gray-400 mt-1">Use 0.00 for custom requests with price to be confirmed.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Meal Type <span class="text-gray-400 font-normal">(for AI recommendations)</span>
                        </label>

                        <select id="itemMealType" class="w-full border rounded-xl px-3 py-2.5">
                            <option value="">Select Meal Type</option>
                        </select>

                        <p class="text-xs text-gray-400 mt-1">
                            Select one only, like main, side, drink, dessert, snack, or soup.
                        </p>
                    </div>

                    <label class="flex items-center justify-between gap-3 border rounded-2xl px-4 py-3 bg-gray-50">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold">Availability</p>
                            <p class="text-xs text-gray-500">
                                Turning this on still requires enough linked ingredients, except Chef Oppa Special.
                            </p>
                        </div>

                        <input id="itemAvailable" type="checkbox" class="rounded shrink-0" checked>
                    </label>

                    <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3">
                        <p class="text-xs text-orange-700">
                            After saving a normal menu item, use the Ingredients button to link required ingredients and quantity usage.
                        </p>
                    </div>
                </div>
            </div>

            <div class="border rounded-2xl p-4 bg-white">
                <div class="mb-3">
                    <label class="block text-sm font-bold">
                        Flavor Tags <span class="text-gray-400 font-normal">(for AI recommendations)</span>
                    </label>
                    <p class="text-xs text-gray-400 mt-1">
                        Select tags that describe the item’s taste or style.
                    </p>
                </div>

                <div id="flavorTagsContainer" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 max-h-48 overflow-y-auto pr-1">
                    <!-- Flavor tags will load here -->
                </div>
            </div>

            <div class="border-t pt-4 flex flex-col sm:flex-row sm:justify-end gap-2">
                <button type="button" onclick="closeMenuModal()"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                    Cancel
                </button>

                <button id="menuSaveBtn" type="submit"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold disabled:opacity-70 disabled:cursor-not-allowed">
                    Save Menu Item
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ingredients Modal -->
<div id="ingredientsModal" class="fixed inset-0 bg-black/50 backdrop-blur-[2px] hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-[26px] shadow-2xl w-full max-w-5xl max-h-[94vh] overflow-hidden flex flex-col border border-gray-100">
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

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
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

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-red-500">
                    Failed to load menu items. Please check your connection.
                </td>
            </tr>
        `;

        mobileList.innerHTML = `
            <div class="px-4 py-8 text-center text-red-500">
                Failed to load menu items. Please check your connection.
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
            <label class="flex items-center gap-2 text-sm bg-gray-50 border rounded-xl px-3 py-2 cursor-pointer hover:bg-orange-50 hover:border-orange-200 transition">
                <input
                    type="checkbox"
                    class="flavor-tag-checkbox rounded text-orange-500"
                    value="${safeText(tag)}"
                    ${checked}
                >
                <span>${safeText(formatLabel(tag))}</span>
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
        <div class="rounded-2xl border bg-white shadow-sm p-4">
            <div class="flex items-start gap-3">
                <div class="shrink-0">
                    ${getImageHtml(item)}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-900 leading-snug">${safeText(item.name)}</h3>
                            <p class="text-xs text-gray-500 mt-1">${safeText(item.category || 'Uncategorized')}</p>
                        </div>

                        <button onclick="toggleAvailability(${item.id}, ${item.is_available ? 'true' : 'false'}, this)"
                            class="shrink-0">
                            ${availabilityBadge(item)}
                        </button>
                    </div>

                    ${
                        item.description
                            ? `<p class="text-xs text-gray-500 mt-2 line-clamp-2">${safeText(item.description)}</p>`
                            : `<p class="text-xs text-gray-400 mt-2 italic">No description</p>`
                    }

                    <div class="mt-3 rounded-xl bg-gray-50 border px-3 py-2 space-y-1">
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

                    <div class="grid grid-cols-3 gap-2 mt-4">
                        <button onclick="openMenuModal(${item.id})"
                            class="px-3 py-2 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                            Edit
                        </button>

                        <button onclick="openIngredientsModal(${item.id})"
                            class="px-3 py-2 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                            Ingredients
                        </button>

                        <button onclick="deleteMenuItem(${item.id}, this)"
                            class="px-3 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
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

        imageInput.value = '';
        currentImageText.classList.toggle('hidden', !item.image && !item.image_url);
        document.getElementById('itemAvailable').checked = Boolean(item.is_available);

        populateFlavorTags(item.flavor_tags || []);
        populateMealTypes(item.meal_type || '');
    } else {
        document.getElementById('menuModalTitle').textContent = 'Add Menu Item';
        document.getElementById('menuSaveBtn').textContent = 'Save Menu Item';
        document.getElementById('menuForm').reset();
        document.getElementById('itemDescription').value = '';
        imageInput.value = '';
        currentImageText.classList.add('hidden');
        document.getElementById('itemAvailable').checked = true;

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
    const imageInput = document.getElementById('itemImage');

    const formData = new FormData();
    formData.append('name', document.getElementById('itemName').value);
    formData.append('category', document.getElementById('itemCategory').value);
    formData.append('description', document.getElementById('itemDescription').value);
    formData.append('price', document.getElementById('itemPrice').value);
    formData.append('is_available', document.getElementById('itemAvailable').checked ? '1' : '0');

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
                alert(firstError);
            } else {
                alert(data.message || 'Failed to save menu item.');
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
    } catch (error) {
        console.error('Save menu item failed:', error);
        alert('Failed to save menu item. Please check your connection.');
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
            alert(data.message || 'Failed to update availability.');
            return;
        }

        const updatedItem = findMenuItemFromResponse(data);

        if (updatedItem) {
            replaceMenuItemInMemory(updatedItem);
        } else {
            silentReloadMenuItems();
        }
    } catch (error) {
        console.error('Availability update failed:', error);
        alert('Failed to update availability. Please check your connection.');
    } finally {
        setButtonLoading(button, false);
    }
}

async function deleteMenuItem(id, button) {
    if (!confirm('Delete this menu item?')) return;

    setButtonLoading(button, true, 'Deleting...');

    try {
        const res = await fetch(`/api/admin/menu-items/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) {
            alert('Failed to delete menu item.');
            return;
        }

        removeMenuItemFromMemory(id);
        silentReloadMenuItems();
    } catch (error) {
        console.error('Delete menu item failed:', error);
        alert('Failed to delete menu item. Please check your connection.');
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

function openIngredientsModal(id) {
    activeIngredientsMenuItemId = id;

    const item = menuItems.find(menuItem => Number(menuItem.id) === Number(id));
    if (!item) return;

    document.getElementById('ingredientsMenuItemId').value = id;
    document.getElementById('ingredientsModalTitle').textContent = `Ingredients - ${item.name}`;
    document.getElementById('attachIngredientForm').reset();

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

    const saveBtn = document.getElementById('attachIngredientBtn');
    const menuItemId = document.getElementById('ingredientsMenuItemId').value;
    const ingredientId = document.getElementById('ingredientSelect').value;
    const quantityRequired = document.getElementById('quantityRequired').value;

    if (!menuItemId || !ingredientId || !quantityRequired) {
        alert('Please select an ingredient and enter quantity required.');
        return;
    }

    setButtonLoading(saveBtn, true, 'Adding...');

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
            }),
        });

        const data = await res.json();

        if (!res.ok) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                alert(firstError);
            } else {
                alert(data.message || 'Failed to attach ingredient.');
            }
            return;
        }

        const updatedItem = findMenuItemFromResponse(data);

        if (updatedItem) {
            replaceMenuItemInMemory(updatedItem);
        } else {
            await silentReloadMenuItems();
        }

        document.getElementById('attachIngredientForm').reset();
        await loadIngredientsList();
        await silentReloadMenuItems();
        renderLinkedIngredients(menuItemId);
    } catch (error) {
        console.error('Attach ingredient failed:', error);
        alert('Failed to attach ingredient. Please check your connection.');
    } finally {
        setButtonLoading(saveBtn, false);
    }
});

async function detachIngredient(menuItemId, ingredientId, button) {
    if (!confirm('Remove this ingredient from the menu item?')) return;

    setButtonLoading(button, true, 'Removing...');

    try {
        const res = await fetch(`/api/admin/menu-items/${menuItemId}/ingredients/${ingredientId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            },
        });

        const data = await res.json();

        if (!res.ok) {
            alert(data.message || 'Failed to remove ingredient.');
            return;
        }

        const updatedItem = findMenuItemFromResponse(data);

        if (updatedItem) {
            replaceMenuItemInMemory(updatedItem);
        } else {
            await silentReloadMenuItems();
        }

        await loadIngredientsList();
        await silentReloadMenuItems();
        renderLinkedIngredients(menuItemId);
    } catch (error) {
        console.error('Detach ingredient failed:', error);
        alert('Failed to remove ingredient. Please check your connection.');
    } finally {
        setButtonLoading(button, false);
    }
}

document.getElementById('menuSearch').addEventListener('input', applyFilters);
document.getElementById('categoryFilter').addEventListener('change', applyFilters);

loadIngredientsList();
loadMenuItems();
</script>

@endsection