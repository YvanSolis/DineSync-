@extends('layouts.admin')

@section('content')

<h1 class="text-3xl font-bold mb-1">Inventory Management</h1>
<p class="text-gray-500 mb-6">Manage ingredients, stock levels, units, and thresholds.</p>

<div class="bg-white p-5 rounded shadow mb-6">
    <form id="form" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input class="border p-2 rounded" placeholder="Ingredient Name" id="name">
        <input class="border p-2 rounded" placeholder="Stock" id="stock" type="number" step="0.01">
        <input class="border p-2 rounded" placeholder="Unit" id="unit" value="kg">
        <input class="border p-2 rounded" placeholder="Threshold" id="threshold" type="number" step="0.01">
        <button class="bg-orange-500 text-white rounded px-4 py-2">Add Ingredient</button>
    </form>
</div>

<div class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-bold mb-3">Ingredient List</h2>
    <div id="list"></div>
</div>

<script>
async function loadIngredients() {
    const res = await fetch('/api/admin/ingredients');
    const data = await res.json();

    let html = '';

    data.forEach(item => {
        const status = Number(item.current_stock) <= Number(item.threshold)
            ? '<span class="text-red-600 font-bold">Low Stock</span>'
            : '<span class="text-green-600 font-bold">Normal</span>';

        html += `
            <div class="flex justify-between items-center border-b py-3">
                <div>
                    <p class="font-semibold">${item.name}</p>
                    <p class="text-sm text-gray-500">
                        Stock: ${item.current_stock} ${item.unit} | Threshold: ${item.threshold} ${item.unit}
                    </p>
                </div>
                <div>${status}</div>
            </div>
        `;
    });

    document.getElementById('list').innerHTML = html || '<p class="text-gray-500">No ingredients yet.</p>';
}

document.getElementById('form').onsubmit = async (e) => {
    e.preventDefault();

    await fetch('/api/admin/ingredients', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            name: document.getElementById('name').value,
            current_stock: document.getElementById('stock').value,
            unit: document.getElementById('unit').value,
            threshold: document.getElementById('threshold').value
        })
    });

    document.getElementById('form').reset();
    loadIngredients();
};

loadIngredients();
</script>

@endsection