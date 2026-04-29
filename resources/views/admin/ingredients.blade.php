@extends('layouts.admin')

@section('content')

<h1 class="text-3xl font-bold mb-1">Inventory Management</h1>
<p class="text-gray-500 mb-6">Manage ingredients, stock levels, units, and thresholds.</p>

<div class="bg-white p-5 rounded shadow mb-6">
    <form id="form" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input class="border p-2 rounded" placeholder="Ingredient Name" id="name" required>
        <input class="border p-2 rounded" placeholder="Stock" id="stock" type="number" step="0.01" required>
        <input class="border p-2 rounded" placeholder="Unit" id="unit" value="kg" required>
        <input class="border p-2 rounded" placeholder="Threshold" id="threshold" type="number" step="0.01" required>
        <button id="submitBtn" class="bg-orange-500 text-white rounded px-4 py-2">Add Ingredient</button>
    </form>
</div>

<div class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-bold mb-3">Ingredient List</h2>
    <div id="list"></div>
</div>

<script>
let editingId = null;

async function loadIngredients() {
    const res = await fetch('/api/admin/ingredients');
    const data = await res.json();

    let html = '';

    data.forEach(item => {
        const isLow = Number(item.current_stock) <= Number(item.threshold);

        const status = isLow
            ? '<span class="text-red-600 font-bold bg-red-50 px-2 py-1 rounded">Low Stock</span>'
            : '<span class="text-green-600 font-bold bg-green-50 px-2 py-1 rounded">Normal</span>';

        html += `
            <div class="flex justify-between items-center border-b py-3">
                <div>
                    <p class="font-semibold">${item.name}</p>
                    <p class="text-sm text-gray-500">
                        Stock: ${item.current_stock} ${item.unit} | Threshold: ${item.threshold} ${item.unit}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    ${status}
                    <button onclick="editIngredient(${item.id}, '${item.name}', ${item.current_stock}, '${item.unit}', ${item.threshold})"
                        class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                        Edit
                    </button>
                    <button onclick="deleteIngredient(${item.id})"
                        class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                        Delete
                    </button>
                </div>
            </div>
        `;
    });

    document.getElementById('list').innerHTML = html || '<p class="text-gray-500">No ingredients yet.</p>';
}

document.getElementById('form').onsubmit = async (e) => {
    e.preventDefault();

    const payload = {
        name: document.getElementById('name').value,
        current_stock: document.getElementById('stock').value,
        unit: document.getElementById('unit').value,
        threshold: document.getElementById('threshold').value
    };

    const url = editingId
        ? `/api/admin/ingredients/${editingId}`
        : '/api/admin/ingredients';

    const method = editingId ? 'PUT' : 'POST';

    await fetch(url, {
        method: method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });

    editingId = null;
    document.getElementById('submitBtn').textContent = 'Add Ingredient';
    document.getElementById('form').reset();
    document.getElementById('unit').value = 'kg';

    loadIngredients();
};

function editIngredient(id, name, stock, unit, threshold) {
    editingId = id;

    document.getElementById('name').value = name;
    document.getElementById('stock').value = stock;
    document.getElementById('unit').value = unit;
    document.getElementById('threshold').value = threshold;

    document.getElementById('submitBtn').textContent = 'Update Ingredient';
}

async function deleteIngredient(id) {
    if (!confirm('Delete this ingredient?')) return;

    await fetch(`/api/admin/ingredients/${id}`, {
        method: 'DELETE'
    });

    loadIngredients();
}

loadIngredients();
</script>

@endsection