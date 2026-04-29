@extends('layouts.admin')

@section('content')

<h1 class="text-3xl font-bold mb-1">Menu Management</h1>
<p class="text-gray-500 mb-6">Manage menu items, prices, and required ingredients.</p>

<div class="bg-white p-5 rounded shadow mb-6">
    <form id="form" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input class="border p-2 rounded" placeholder="Item Name" id="name" required>
        <input class="border p-2 rounded" placeholder="Category" id="category">
        <input class="border p-2 rounded" placeholder="Price" id="price" type="number" step="0.01" required>
        <button id="submitBtn" class="bg-orange-500 text-white rounded px-4 py-2">Add Menu Item</button>
    </form>
</div>

<div class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-bold mb-3">Menu Items</h2>
    <div id="list"></div>
</div>

<script>
let allIngredients = [];
let editingId = null;

async function loadIngredients() {
    const res = await fetch('/api/admin/ingredients');
    allIngredients = await res.json();
}

async function loadMenuItems() {
    const res = await fetch('/api/admin/menu-items');
    const data = await res.json();

    let html = '';

    data.forEach(item => {
        let ingredients = '';

        if (item.ingredients.length > 0) {
            item.ingredients.forEach(ing => {
                ingredients += `
                    <span class="text-xs bg-gray-100 px-2 py-1 rounded mr-1 mb-1 inline-flex items-center gap-1">
                        ${ing.name} (${ing.pivot.quantity_required} ${ing.unit})
                        <button onclick="removeIngredient(${item.id}, ${ing.id})"
                            class="text-red-500 font-bold">×</button>
                    </span>
                `;
            });
        } else {
            ingredients = '<span class="text-xs text-gray-400">No ingredients linked</span>';
        }

        let ingredientOptions = allIngredients.map(ing =>
            `<option value="${ing.id}">${ing.name} (${ing.unit})</option>`
        ).join('');

        html += `
            <div class="border-b py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold">${item.name}</p>
                        <p class="text-sm text-gray-500">${item.category || 'No category'} | ₱${item.price}</p>
                    </div>

                    <div class="flex gap-2 items-center">
                        <button onclick="toggleAvailability(${item.id}, ${item.is_available})"
                            class="${item.is_available ? 'bg-green-500' : 'bg-gray-500'} text-white px-2 py-1 rounded text-xs">
                            ${item.is_available ? 'Available' : 'Unavailable'}
                        </button>

                        <button onclick="editMenu(${item.id}, '${item.name}', '${item.category}', ${item.price})"
                            class="bg-blue-500 text-white px-2 py-1 rounded text-xs">
                            Edit
                        </button>

                        <button onclick="deleteMenu(${item.id})"
                            class="bg-red-500 text-white px-2 py-1 rounded text-xs">
                            Delete
                        </button>
                    </div>
                </div>

                <div class="mt-2 flex flex-wrap">${ingredients}</div>

                <form onsubmit="addIngredient(event, ${item.id})" class="mt-3 flex gap-2">
                    <select class="border p-2 rounded" id="ingredient-${item.id}" required>
                        <option value="">Select ingredient</option>
                        ${ingredientOptions}
                    </select>

                    <input class="border p-2 rounded" id="qty-${item.id}" type="number" step="0.01" placeholder="Qty required" required>

                    <button class="bg-gray-800 text-white rounded px-3 py-2">
                        Link Ingredient
                    </button>
                </form>
            </div>
        `;
    });

    document.getElementById('list').innerHTML = html;
}

document.getElementById('form').onsubmit = async (e) => {
    e.preventDefault();

    const payload = {
        name: document.getElementById('name').value,
        category: document.getElementById('category').value,
        price: document.getElementById('price').value,
        is_available: true
    };

    const url = editingId
        ? `/api/admin/menu-items/${editingId}`
        : '/api/admin/menu-items';

    const method = editingId ? 'PUT' : 'POST';

    await fetch(url, {
        method: method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });

    editingId = null;
    document.getElementById('submitBtn').textContent = 'Add Menu Item';
    document.getElementById('form').reset();

    loadMenuItems();
};

function editMenu(id, name, category, price) {
    editingId = id;

    document.getElementById('name').value = name;
    document.getElementById('category').value = category;
    document.getElementById('price').value = price;

    document.getElementById('submitBtn').textContent = 'Update Menu Item';
}

async function deleteMenu(id) {
    if (!confirm('Delete this menu item?')) return;

    await fetch(`/api/admin/menu-items/${id}`, {
        method: 'DELETE'
    });

    loadMenuItems();
}

async function toggleAvailability(id, current) {
    await fetch(`/api/admin/menu-items/${id}`, {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            is_available: !current
        })
    });

    loadMenuItems();
}

async function addIngredient(e, menuItemId) {
    e.preventDefault();

    const ingredientId = document.getElementById(`ingredient-${menuItemId}`).value;
    const quantity = document.getElementById(`qty-${menuItemId}`).value;

    await fetch(`/api/admin/menu-items/${menuItemId}/ingredients`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            ingredient_id: ingredientId,
            quantity_required: quantity
        })
    });

    loadMenuItems();
}

async function removeIngredient(menuItemId, ingredientId) {
    if (!confirm('Remove this ingredient?')) return;

    await fetch(`/api/admin/menu-items/${menuItemId}/ingredients/${ingredientId}`, {
        method: 'DELETE'
    });

    loadMenuItems();
}

async function init() {
    await loadIngredients();
    await loadMenuItems();
}

init();
</script>

@endsection 