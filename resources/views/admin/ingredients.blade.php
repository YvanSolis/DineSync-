@extends('layouts.admin')

@section('content')

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">Daily Menu Inventory</h1>
            <p class="text-sm sm:text-base text-gray-500">
                Track today’s menu capacity by category, type, and daily availability.
            </p>
        </div>

        <button onclick="loadMenuInventory()"
            class="w-full sm:w-auto bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-xl font-semibold shadow-sm">
            Refresh
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="border border-green-200 bg-green-50 rounded-2xl p-4 sm:p-5">
            <p class="font-semibold text-green-700">Available Items</p>
            <p class="text-2xl sm:text-3xl font-bold mt-1" id="availableCount">0</p>
            <p class="text-sm text-gray-600 mt-1">Items still available today</p>
        </div>

        <div class="border border-yellow-200 bg-yellow-50 rounded-2xl p-4 sm:p-5">
            <p class="font-semibold text-yellow-700">Low Capacity Alerts</p>
            <p class="text-2xl sm:text-3xl font-bold mt-1" id="limitedCount">0</p>
            <p class="text-sm text-gray-600 mt-1">Items with 5 or fewer orders/heads left today</p>
        </div>

        <div class="border border-red-200 bg-red-50 rounded-2xl p-4 sm:p-5">
            <p class="font-semibold text-red-700">Sold Out</p>
            <p class="text-2xl sm:text-3xl font-bold mt-1" id="soldOutCount">0</p>
            <p class="text-sm text-gray-600 mt-1">Items unavailable for today</p>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="bg-white rounded-2xl shadow-sm border p-4 sm:p-5">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-4">
            <div class="min-w-0">
                <h2 class="text-lg font-bold">Menu Capacity</h2>
                <p class="text-sm text-gray-500">View menu inventory grouped by category for easier monitoring.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            <input
                id="inventorySearch"
                type="text"
                placeholder="Search menu item..."
                class="border rounded-xl px-4 py-2.5 w-full"
            >

            <select id="categoryFilter" class="border rounded-xl px-4 py-2.5 w-full">
                <option value="all">All Categories</option>
            </select>

            <select id="typeFilter" class="border rounded-xl px-4 py-2.5 w-full">
                <option value="all">All Types</option>
                <option value="per_order">Per Order</option>
                <option value="per_head">Per Head</option>
                <option value="custom">Custom</option>
            </select>

            <select id="statusFilter" class="border rounded-xl px-4 py-2.5 w-full">
                <option value="all">All Status</option>
                <option value="available">Available</option>
                <option value="limited">Low Capacity</option>
                <option value="sold_out">Sold Out</option>
            </select>
        </div>
    </div>

    <!-- Grouped Inventory -->
    <div id="inventoryGroupsContainer" class="space-y-5">
        <div class="bg-white rounded-2xl shadow-sm border">
            <div class="px-6 py-8 text-center text-gray-400">
                Loading daily menu inventory...
            </div>
        </div>
    </div>
</div>

<!-- Edit Daily Limit Modal -->
<div id="limitModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[92vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between gap-3 p-4 sm:p-5 border-b shrink-0">
            <div class="min-w-0">
                <h3 id="limitModalTitle" class="text-lg font-bold truncate">Update Daily Limit</h3>
                <p class="text-xs text-gray-500 mt-1">Update capacity type and daily limit.</p>
            </div>

            <button onclick="closeLimitModal()"
                class="w-9 h-9 rounded-full hover:bg-gray-100 text-gray-500 hover:text-black text-xl shrink-0">
                &times;
            </button>
        </div>

        <form id="limitForm" class="p-4 sm:p-5 space-y-4 overflow-y-auto">
            <input type="hidden" id="limitMenuItemId">

            <div>
                <label class="block text-sm font-semibold mb-1">Inventory Type</label>
                <select id="limitInventoryType" class="w-full border rounded-xl px-3 py-2.5" required>
                    <option value="per_order">Per Order / Ala Carte</option>
                    <option value="per_head">Per Head / Unlimited</option>
                    <option value="custom">Custom / No Fixed Limit</option>
                </select>
            </div>

            <div id="limitInputWrapper">
                <label class="block text-sm font-semibold mb-1">Daily Limit</label>
                <input
                    id="limitDailyLimit"
                    type="number"
                    min="0"
                    step="1"
                    placeholder="Example: 30"
                    class="w-full border rounded-xl px-3 py-2.5"
                >
                <p id="limitHelpText" class="text-xs text-gray-400 mt-1">
                    Leave blank for no limit.
                </p>
            </div>

            <div class="border-t pt-4 flex flex-col sm:flex-row sm:justify-end gap-2">
                <button type="button" onclick="closeLimitModal()"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                    Cancel
                </button>

                <button id="limitSaveBtn" type="submit"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold disabled:opacity-70 disabled:cursor-not-allowed">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let menuItems = [];
let filteredItems = [];

let categories = [
    'Authentic Ala Carte Meals',
    'Dishes',
    'Korean Kitchen Specials',
    'Chef Oppa Special',
    'Noodles',
    'Salad',
    'Maki & Sushi',
    'Jeon Series',
    'Tteokbokki Series'
];

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString();
}

function formatInventoryType(value) {
    switch (value) {
        case 'per_head':
            return 'Per Head';
        case 'custom':
            return 'Custom';
        case 'per_order':
        default:
            return 'Per Order';
    }
}

function getUnitLabel(item) {
    const type = item.inventory_type || 'per_order';

    if (type === 'per_head') {
        return 'heads';
    }

    if (type === 'custom') {
        return 'requests';
    }

    return 'orders';
}

function getStatus(item) {
    const type = item.inventory_type || 'per_order';

    if (type === 'custom') {
        return item.is_available ? 'available' : 'sold_out';
    }

    if (!item.is_available) {
        return 'sold_out';
    }

    if (item.daily_limit === null || item.daily_limit === undefined) {
        return 'available';
    }

    const remaining = Number(item.remaining_today ?? 0);

    if (remaining <= 0) {
        return 'sold_out';
    }

    if (remaining <= 5) {
        return 'limited';
    }

    return 'available';
}

function getStatusBadge(item) {
    const status = getStatus(item);

    if (status === 'sold_out') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-600">Sold Out</span>';
    }

    if (status === 'limited') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Low Capacity</span>';
    }

    return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-600">Available</span>';
}

function setButtonLoading(button, isLoading, loadingText = 'Saving...') {
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

function populateCategoryFilter() {
    const categoryFilter = document.getElementById('categoryFilter');
    const currentValue = categoryFilter.value || 'all';

    categoryFilter.innerHTML = '<option value="all">All Categories</option>';

    categories.forEach(category => {
        categoryFilter.innerHTML += `
            <option value="${safeText(category)}">${safeText(category)}</option>
        `;
    });

    const extraCategories = [...new Set(menuItems.map(item => item.category).filter(Boolean))]
        .filter(category => !categories.includes(category));

    extraCategories.forEach(category => {
        categoryFilter.innerHTML += `
            <option value="${safeText(category)}">${safeText(category)}</option>
        `;
    });

    categoryFilter.value = currentValue;
}

async function loadMenuInventory() {
    const container = document.getElementById('inventoryGroupsContainer');

    container.innerHTML = `
        <div class="bg-white rounded-2xl shadow-sm border">
            <div class="px-6 py-8 text-center text-gray-400">
                Loading daily menu inventory...
            </div>
        </div>
    `;

    try {
        const res = await fetch('/api/admin/menu-items', {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) {
            container.innerHTML = `
                <div class="bg-white rounded-2xl shadow-sm border">
                    <div class="px-6 py-8 text-center text-red-500">
                        Failed to load daily inventory. API returned ${res.status}.
                    </div>
                </div>
            `;
            return;
        }

        const data = await res.json();
        menuItems = Array.isArray(data) ? data : (data.menu_items ?? []);

        populateCategoryFilter();
        applyFilters();
        renderSummary();
    } catch (error) {
        console.error('Load menu inventory failed:', error);

        container.innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border">
                <div class="px-6 py-8 text-center text-red-500">
                    Failed to load daily inventory. Please check your connection.
                </div>
            </div>
        `;
    }
}

function applyFilters() {
    const search = document.getElementById('inventorySearch').value.toLowerCase().trim();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;

    filteredItems = menuItems.filter(item => {
        const matchesSearch =
            String(item.name || '').toLowerCase().includes(search) ||
            String(item.category || '').toLowerCase().includes(search) ||
            String(item.daily_inventory_label || '').toLowerCase().includes(search) ||
            String(item.stock_label || '').toLowerCase().includes(search);

        const matchesCategory = categoryFilter === 'all'
            ? true
            : (item.category || '') === categoryFilter;

        const itemType = item.inventory_type || 'per_order';
        const matchesType = typeFilter === 'all' ? true : itemType === typeFilter;

        const itemStatus = getStatus(item);
        const matchesStatus = statusFilter === 'all' ? true : itemStatus === statusFilter;

        return matchesSearch && matchesCategory && matchesType && matchesStatus;
    });

    renderGroupedInventory();
    renderSummary();
}

function renderSummary() {
    const available = menuItems.filter(item => getStatus(item) === 'available').length;
    const limited = menuItems.filter(item => getStatus(item) === 'limited').length;
    const soldOut = menuItems.filter(item => getStatus(item) === 'sold_out').length;

    document.getElementById('availableCount').textContent = formatNumber(available);
    document.getElementById('limitedCount').textContent = formatNumber(limited);
    document.getElementById('soldOutCount').textContent = formatNumber(soldOut);
}

function getDailyLimitText(item) {
    const type = item.inventory_type || 'per_order';

    if (type === 'custom') {
        return 'No fixed limit';
    }

    if (item.daily_limit === null || item.daily_limit === undefined) {
        return 'No limit';
    }

    return `${formatNumber(item.daily_limit)} ${getUnitLabel(item)}`;
}

function getRemainingText(item) {
    const type = item.inventory_type || 'per_order';

    if (type === 'custom') {
        return 'Staff confirms';
    }

    if (item.daily_limit === null || item.daily_limit === undefined) {
        return 'No limit';
    }

    return `${formatNumber(item.remaining_today ?? 0)} ${getUnitLabel(item)}`;
}

function renderGroupedInventory() {
    const container = document.getElementById('inventoryGroupsContainer');

    if (!filteredItems.length) {
        container.innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border">
                <div class="px-6 py-8 text-center text-gray-400">
                    No menu inventory records found.
                </div>
            </div>
        `;
        return;
    }

    const grouped = filteredItems.reduce((groups, item) => {
        const category = item.category || 'Uncategorized';

        if (!groups[category]) {
            groups[category] = [];
        }

        groups[category].push(item);

        return groups;
    }, {});

    container.innerHTML = Object.entries(grouped).map(([category, items]) => {
        return `
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="p-4 sm:p-5 border-b bg-gray-50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-bold text-lg text-gray-900">${safeText(category)}</h3>
                            <p class="text-sm text-gray-500">${items.length} menu item${items.length === 1 ? '' : 's'}</p>
                        </div>

                        <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-600 text-xs font-bold w-fit">
                            Daily Capacity
                        </span>
                    </div>
                </div>

                <!-- Desktop / Tablet Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[860px] text-sm">
                        <thead class="bg-white text-gray-600 border-b">
                            <tr>
                                <th class="text-left px-6 py-4 font-semibold">Menu Item</th>
                                <th class="text-left px-6 py-4 font-semibold">Type</th>
                                <th class="text-left px-6 py-4 font-semibold">Daily Limit</th>
                                <th class="text-left px-6 py-4 font-semibold">Sold Today</th>
                                <th class="text-left px-6 py-4 font-semibold">Remaining</th>
                                <th class="text-left px-6 py-4 font-semibold">Status</th>
                                <th class="text-left px-6 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            ${items.map(item => {
                                const type = item.inventory_type || 'per_order';
                                const unit = getUnitLabel(item);

                                return `
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-900">${safeText(item.name)}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                ${safeText(item.daily_inventory_label || item.stock_label || '')}
                                            </p>
                                        </td>

                                        <td class="px-6 py-4">
                                            ${safeText(formatInventoryType(type))}
                                        </td>

                                        <td class="px-6 py-4">
                                            ${safeText(getDailyLimitText(item))}
                                        </td>

                                        <td class="px-6 py-4">
                                            ${safeText(item.sold_today ?? 0)} ${safeText(unit)}
                                        </td>

                                        <td class="px-6 py-4">
                                            ${safeText(getRemainingText(item))}
                                        </td>

                                        <td class="px-6 py-4">
                                            ${getStatusBadge(item)}
                                        </td>

                                        <td class="px-6 py-4">
                                            <button onclick="openLimitModal(${item.id})"
                                                class="px-3 py-2 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                                                Update Limit
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden p-4 space-y-3">
                    ${items.map(item => {
                        const type = item.inventory_type || 'per_order';
                        const unit = getUnitLabel(item);

                        return `
                            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-gray-900 leading-snug">${safeText(item.name)}</h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            ${safeText(item.daily_inventory_label || item.stock_label || 'No inventory data')}
                                        </p>
                                    </div>

                                    <div class="shrink-0">
                                        ${getStatusBadge(item)}
                                    </div>
                                </div>

                                <div class="mt-3 rounded-xl bg-gray-50 border px-3 py-2 space-y-1">
                                    <p class="text-xs text-gray-600">
                                        <span class="font-semibold">Type:</span>
                                        ${safeText(formatInventoryType(type))}
                                    </p>

                                    <p class="text-xs text-gray-600">
                                        <span class="font-semibold">Daily Limit:</span>
                                        ${safeText(getDailyLimitText(item))}
                                    </p>

                                    <p class="text-xs text-gray-600">
                                        <span class="font-semibold">Sold Today:</span>
                                        ${safeText(item.sold_today ?? 0)} ${safeText(unit)}
                                    </p>

                                    <p class="text-xs text-gray-600">
                                        <span class="font-semibold">Remaining:</span>
                                        ${safeText(getRemainingText(item))}
                                    </p>
                                </div>

                                <button onclick="openLimitModal(${item.id})"
                                    class="w-full mt-3 px-3 py-2.5 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                                    Update Limit
                                </button>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }).join('');
}

function openLimitModal(id) {
    const item = menuItems.find(menuItem => Number(menuItem.id) === Number(id));
    if (!item) return;

    document.getElementById('limitMenuItemId').value = item.id;
    document.getElementById('limitModalTitle').textContent = `Update Limit - ${item.name}`;
    document.getElementById('limitInventoryType').value = item.inventory_type || (
        item.category === 'Chef Oppa Special' ? 'custom' : 'per_order'
    );
    document.getElementById('limitDailyLimit').value = item.daily_limit ?? '';

    updateLimitVisibility();

    const modal = document.getElementById('limitModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeLimitModal() {
    const modal = document.getElementById('limitModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function updateLimitVisibility() {
    const type = document.getElementById('limitInventoryType').value;
    const wrapper = document.getElementById('limitInputWrapper');
    const input = document.getElementById('limitDailyLimit');
    const helpText = document.getElementById('limitHelpText');

    if (type === 'custom') {
        input.value = '';
        input.disabled = true;
        wrapper.classList.add('opacity-60');
        helpText.textContent = 'Custom items do not use a daily limit.';
        return;
    }

    input.disabled = false;
    wrapper.classList.remove('opacity-60');

    if (type === 'per_head') {
        helpText.textContent = 'Set how many heads/persons can be served today.';
    } else {
        helpText.textContent = 'Set how many orders can be served today. Leave blank for no limit.';
    }
}

document.getElementById('limitForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const saveBtn = document.getElementById('limitSaveBtn');
    const id = document.getElementById('limitMenuItemId').value;
    const inventoryType = document.getElementById('limitInventoryType').value;
    const dailyLimit = document.getElementById('limitDailyLimit').value;

    const payload = {
        inventory_type: inventoryType,
        daily_limit: inventoryType === 'custom' || dailyLimit === '' ? null : Number(dailyLimit),
    };

    setButtonLoading(saveBtn, true, 'Saving...');

    try {
        const res = await fetch(`/api/admin/menu-items/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!res.ok) {
            alert(data.message || 'Failed to update daily limit.');
            return;
        }

        closeLimitModal();
        await loadMenuInventory();
    } catch (error) {
        console.error('Update daily limit failed:', error);
        alert('Failed to update daily limit. Please check your connection.');
    } finally {
        setButtonLoading(saveBtn, false);
    }
});

document.getElementById('inventorySearch').addEventListener('input', applyFilters);
document.getElementById('categoryFilter').addEventListener('change', applyFilters);
document.getElementById('typeFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('limitInventoryType').addEventListener('change', updateLimitVisibility);

loadMenuInventory();

setInterval(() => {
    const limitModal = document.getElementById('limitModal');
    const modalOpen = limitModal && !limitModal.classList.contains('hidden');

    if (document.hidden || modalOpen) {
        return;
    }

    loadMenuInventory();
}, 30000);
</script>

@endsection