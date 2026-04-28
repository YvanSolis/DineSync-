<!DOCTYPE html>
<html>
<head>
    <title>Ingredients</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-10 bg-gray-100">

<h1 class="text-2xl font-bold mb-5">Inventory</h1>

<form id="form" class="mb-5">
    <input class="border p-2" placeholder="Name" id="name">
    <input class="border p-2" placeholder="Stock" id="stock">
    <button class="bg-blue-500 text-white px-4 py-2">Add</button>
</form>

<div id="list"></div>

<script>
async function load() {
    let res = await fetch('/api/admin/ingredients');
    let data = await res.json();

    let html = '';
    data.forEach(i => {
        html += `<div class="bg-white p-3 mb-2">
            ${i.name} - ${i.current_stock} ${i.unit}
        </div>`;
    });

    document.getElementById('list').innerHTML = html;
}

document.getElementById('form').onsubmit = async (e) => {
    e.preventDefault();

    await fetch('/api/admin/ingredients', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            name: document.getElementById('name').value,
            current_stock: document.getElementById('stock').value,
            unit: 'kg',
            threshold: 5
        })
    });

    load();
};

load();
</script>

</body>
</html>