<!DOCTYPE html>
<html>
<head>
    <title>Menu Items</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-10 bg-gray-100">

<h1 class="text-2xl font-bold mb-5">Menu Management</h1>

<form id="form" class="mb-5 flex gap-2">
    <input class="border p-2" placeholder="Item Name" id="name">
    <input class="border p-2" placeholder="Category" id="category">
    <input class="border p-2" placeholder="Price" id="price">
    <button class="bg-orange-500 text-white px-4 py-2">Add</button>
</form>

<div id="list"></div>

<script>
async function load() {
    let res = await fetch('/api/admin/menu-items');
    let data = await res.json();

    let html = '';
    data.forEach(item => {
        html += `<div class="bg-white p-3 mb-2 flex justify-between">
            <span>${item.name} - ${item.category ?? 'No category'} - ₱${item.price}</span>
            <span>${item.is_available ? 'Available' : 'Unavailable'}</span>
        </div>`;
    });

    document.getElementById('list').innerHTML = html;
}

document.getElementById('form').onsubmit = async (e) => {
    e.preventDefault();

    await fetch('/api/admin/menu-items', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            name: document.getElementById('name').value,
            category: document.getElementById('category').value,
            price: document.getElementById('price').value,
            is_available: true
        })
    });

    document.getElementById('form').reset();
    load();
};

load();
</script>

</body>
</html>