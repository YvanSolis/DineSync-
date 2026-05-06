@extends('layouts.admin')

@section('content')

<div class="space-y-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-bold mb-1">Menu Management</h1>
            <p class="text-gray-500">Manage your restaurant menu items.</p>
        </div>

        <button onclick="openMenuModal()"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded font-medium">
            + Add Menu Item
        </button>
    </div>

    <div class="bg-white rounded-lg shadow border">
        <div class="p-5 border-b">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-lg font-bold">Menu Items</h2>
                    <p class="text-sm text-gray-500">View, filter, and manage menu items.</p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <input
                        id="menuSearch"
                        type="text"
                        placeholder="Search menu..."
                        class="border rounded px-3 py-2 w-64"
                    >

                    <select id="categoryFilter" class="border rounded px-3 py-2">
                        <option value="all">All Categories</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
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
    </div>
</div>

<!-- Add/Edit Menu Item Modal -->
<div id="menuModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 id="menuModalTitle" class="text-lg font-bold">Add Menu Item</h3>
            <button onclick="closeMenuModal()" class="text-gray-500 hover:text-black text-xl">&times;</button>
        </div>

        <form id="menuForm" class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Item Name</label>
                <input id="itemName" type="text" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <select id="itemCategory" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select Category</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Price</label>
                <input id="itemPrice" type="number" step="0.01" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Image URL <span class="text-gray-400">(optional)</span></label>
                <input id="itemImage" type="text" class="w-full border rounded px-3 py-2" placeholder="https://example.com/image.jpg">
            </div>

            <div class="flex items-center gap-2">
                <input id="itemAvailable" type="checkbox" class="rounded" checked>
                <label for="itemAvailable" class="text-sm">Available</label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeMenuModal()" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
                    Cancel
                </button>
                <button id="menuSaveBtn" type="submit" class="px-4 py-2 rounded bg-orange-500 text-white">
                    Save Menu Item
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ingredients Modal -->
<div id="ingredientsModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between p-5 border-b">
            <div>
                <h3 id="ingredientsModalTitle" class="text-xl font-bold">Manage Ingredients</h3>
                <p id="ingredientsModalSubtitle" class="text-sm text-gray-500 mt-1"></p>
            </div>

            <button onclick="closeIngredientsModal()" class="text-gray-500 hover:text-black text-xl">&times;</button>
        </div>

        <div class="p-5 space-y-5">
            <div class="border rounded-lg p-4">
                <h4 class="font-bold mb-3">Link Ingredient</h4>

                <form id="ingredientLinkForm" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <select id="ingredientSelect" class="border rounded px-3 py-2" required>
                        <option value="">Select Ingredient</option>
                    </select>

                    <input id="quantityRequired" type="number" step="0.01" placeholder="Quantity Required" class="border rounded px-3 py-2" required>

                    <button class="bg-gray-800 text-white rounded px-4 py-2">
                        Link Ingredient
                    </button>
                </form>
            </div>

            <div class="border rounded-lg">
                <div class="p-4 border-b">
                    <h4 class="font-bold">Linked Ingredients</h4>
                    <p class="text-sm text-gray-500">These are used later for automatic inventory deduction.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-3">Ingredient</th>
                                <th class="text-left px-4 py-3">Unit</th>
                                <th class="text-left px-4 py-3">Quantity Required</th>
                                <th class="text-left px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody id="linkedIngredientsBody">
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                    No ingredients linked yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let menuItems = [];
let filteredMenuItems = [];
let ingredients = [];
let categories = [
    'Authentic Ala Carte Meals',
    'Dishes',
    'Korean Kitchen Specials',
    'Noodles',
    'Salad',
    'Maki & Sushi',
    'Jeon Series',
    'Tteokbokki Series'
];

let editingMenuItemId = null;
let currentIngredientMenuItemId = null;

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

function getImageHtml(item) {
    if (item.image) {
        return `
            <img src="${safeText(item.image)}"
                class="w-14 h-14 object-cover rounded-lg border"
                onerror="this.outerHTML='<div class=&quot;w-14 h-14 rounded-lg bg-gray-100 border flex items-center justify-center text-gray-400 text-xs&quot;>No Image</div>'">
        `;
    }

    return `
        <div class="w-14 h-14 rounded-lg bg-gray-100 border flex items-center justify-center text-gray-400 text-xs">
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

async function loadIngredients() {
    const res = await fetch('/api/admin/ingredients');
    ingredients = await res.json();
    populateIngredientDropdown();
}

async function loadMenuItems() {
    const res = await fetch('/api/admin/menu-items');
    const data = await res.json();

    if (Array.isArray(data)) {
        menuItems = data;
    } else {
        menuItems = data.menu_items ?? [];
        if (data.categories) {
            categories = data.categories;
        }
    }

    populateCategoryFilters();
    applyFilters();
}

function populateCategoryFilters() {
    const filter = document.getElementById('categoryFilter');
    const itemCategory = document.getElementById('itemCategory');

    filter.innerHTML = '<option value="all">All Categories</option>';
    itemCategory.innerHTML = '<option value="">Select Category</option>';

    categories.forEach(category => {
        filter.innerHTML += `<option value="${safeText(category)}">${safeText(category)}</option>`;
        itemCategory.innerHTML += `<option value="${safeText(category)}">${safeText(category)}</option>`;
    });
}

function populateIngredientDropdown() {
    const select = document.getElementById('ingredientSelect');
    select.innerHTML = '<option value="">Select Ingredient</option>';

    ingredients.forEach(ingredient => {
        select.innerHTML += `
            <option value="${ingredient.id}">
                ${safeText(ingredient.name)} (${safeText(ingredient.unit || 'unit')})
            </option>
        `;
    });
}

function applyFilters() {
    const search = document.getElementById('menuSearch').value.toLowerCase().trim();
    const category = document.getElementById('categoryFilter').value;

    filteredMenuItems = menuItems.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(search);
        const matchesCategory = category === 'all' ? true : item.category === category;

        return matchesSearch && matchesCategory;
    });

    renderMenuTable();
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

            <td class="px-6 py-4">
                <p class="font-semibold">${safeText(item.name)}</p>
                <p class="text-xs text-gray-400">${item.ingredients?.length || 0} linked ingredient(s)</p>
            </td>

            <td class="px-6 py-4">
                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs">
                    ${safeText(item.category || 'Uncategorized')}
                </span>
            </td>

            <td class="px-6 py-4 font-semibold">
                ${formatMoney(item.price)}
            </td>

            <td class="px-6 py-4">
                <button onclick="toggleAvailability(${item.id}, ${item.is_available})">
                    ${availabilityBadge(item)}
                </button>
            </td>

            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <button onclick="openMenuModal(${item.id})"
                        class="px-3 py-2 rounded border text-gray-700 hover:bg-gray-50 text-xs">
                        Edit
                    </button>

                    <button onclick="openIngredientsModal(${item.id})"
                        class="px-3 py-2 rounded border text-gray-700 hover:bg-gray-50 text-xs">
                        Ingredients
                    </button>

                    <button onclick="deleteMenuItem(${item.id})"
                        class="px-3 py-2 rounded border border-red-200 text-red-600 hover:bg-red-50 text-xs">
                        Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openMenuModal(id = null) {
    editingMenuItemId = id;

    if (id) {
        const item = menuItems.find(menuItem => menuItem.id === id);
        if (!item) return;

        document.getElementById('menuModalTitle').textContent = 'Edit Menu Item';
        document.getElementById('menuSaveBtn').textContent = 'Update Menu Item';

        document.getElementById('itemName').value = item.name;
        document.getElementById('itemCategory').value = item.category;
        document.getElementById('itemPrice').value = item.price;
        document.getElementById('itemImage').value = item.image || '';
        document.getElementById('itemAvailable').checked = Boolean(item.is_available);
    } else {
        document.getElementById('menuModalTitle').textContent = 'Add Menu Item';
        document.getElementById('menuSaveBtn').textContent = 'Save Menu Item';
        document.getElementById('menuForm').reset();
        document.getElementById('itemAvailable').checked = true;
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

    const payload = {
        name: document.getElementById('itemName').value,
        category: document.getElementById('itemCategory').value,
        price: document.getElementById('itemPrice').value,
        image: document.getElementById('itemImage').value,
        is_available: document.getElementById('itemAvailable').checked
    };

    const url = editingMenuItemId
        ? `/api/admin/menu-items/${editingMenuItemId}`
        : '/api/admin/menu-items';

    const method = editingMenuItemId ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (!res.ok) {
        alert(data.message || 'Failed to save menu item.');
        return;
    }

    closeMenuModal();
    loadMenuItems();
});

async function toggleAvailability(id, currentStatus) {
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

    if (!res.ok) {
        alert('Failed to update availability.');
        return;
    }

    loadMenuItems();
}

async function deleteMenuItem(id) {
    if (!confirm('Delete this menu item?')) return;

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

    loadMenuItems();
}

function openIngredientsModal(menuItemId) {
    currentIngredientMenuItemId = menuItemId;

    const item = menuItems.find(menuItem => menuItem.id === menuItemId);
    if (!item) return;

    document.getElementById('ingredientsModalTitle').textContent = `Ingredients - ${item.name}`;
    document.getElementById('ingredientsModalSubtitle').textContent =
        `Link ingredients and required quantity for ${item.name}.`;

    document.getElementById('ingredientLinkForm').reset();

    renderLinkedIngredients(item);

    const modal = document.getElementById('ingredientsModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeIngredientsModal() {
    currentIngredientMenuItemId = null;

    const modal = document.getElementById('ingredientsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function renderLinkedIngredients(item) {
    const tbody = document.getElementById('linkedIngredientsBody');
    const linked = item.ingredients || [];

    if (!linked.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                    No ingredients linked yet.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = linked.map(ingredient => `
        <tr class="border-t">
            <td class="px-4 py-3 font-medium">${safeText(ingredient.name)}</td>
            <td class="px-4 py-3">${safeText(ingredient.unit || 'unit')}</td>
            <td class="px-4 py-3">${safeText(ingredient.pivot?.quantity_required || 0)}</td>
            <td class="px-4 py-3">
                <button onclick="removeIngredient(${item.id}, ${ingredient.id})"
                    class="px-3 py-2 rounded border border-red-200 text-red-600 hover:bg-red-50 text-xs">
                    Remove
                </button>
            </td>
        </tr>
    `).join('');
}

document.getElementById('ingredientLinkForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!currentIngredientMenuItemId) return;

    const payload = {
        ingredient_id: document.getElementById('ingredientSelect').value,
        quantity_required: document.getElementById('quantityRequired').value
    };

    const res = await fetch(`/api/admin/menu-items/${currentIngredientMenuItemId}/ingredients`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (!res.ok) {
        alert(data.message || 'Failed to link ingredient.');
        return;
    }

    await loadMenuItems();

    const item = menuItems.find(menuItem => menuItem.id === currentIngredientMenuItemId);
    if (item) {
        document.getElementById('ingredientLinkForm').reset();
        renderLinkedIngredients(item);
    }
});

async function removeIngredient(menuItemId, ingredientId) {
    if (!confirm('Remove this linked ingredient?')) return;

    const res = await fetch(`/api/admin/menu-items/${menuItemId}/ingredients/${ingredientId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
        }
    });

    if (!res.ok) {
        alert('Failed to remove ingredient.');
        return;
    }

    await loadMenuItems();

    const item = menuItems.find(menuItem => menuItem.id === menuItemId);
    if (item) {
        renderLinkedIngredients(item);
    }
}

document.getElementById('menuSearch').addEventListener('input', applyFilters);
document.getElementById('categoryFilter').addEventListener('change', applyFilters);

async function init() {
    await loadIngredients();
    await loadMenuItems();
}

init();
</script>

@endsection