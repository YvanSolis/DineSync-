@extends('layouts.admin')

@section('content')

<h1 class="text-3xl font-bold mb-1">Dashboard Overview</h1>
<p class="text-gray-500 mb-6">Welcome back! Here is today’s restaurant summary.</p>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">Total Sales Today</p>
        <h2 id="sales" class="text-2xl font-bold mt-2">₱0</h2>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">Total Orders Today</p>
        <h2 id="orders" class="text-2xl font-bold mt-2">0</h2>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">Low Stock Items</p>
        <h2 id="low-count" class="text-2xl font-bold mt-2">0</h2>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-bold mb-3">Top Selling Items</h2>
        <div id="top-items"></div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-bold mb-3">Ingredient Usage Today</h2>
        <div id="usage"></div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-bold mb-3">Low Stock Alerts</h2>
        <div id="low-stock"></div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-bold mb-3">Restock Suggestions</h2>
        <div id="restock"></div>
    </div>
</div>

<div class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-bold mb-3">AI Demand Forecast (Facebook Prophet)</h2>
    <div id="forecast"></div>
</div>

<script>
async function loadDashboard() {
    const res = await fetch('/api/admin/dashboard');
    const data = await res.json();

    document.getElementById('sales').innerText = '₱' + Number(data.total_sales_today).toLocaleString();
    document.getElementById('orders').innerText = data.total_orders_today;
    document.getElementById('low-count').innerText = data.low_stock_items.length;

    let topHtml = '';
    data.top_selling_items.forEach(item => {
        topHtml += `
            <div class="flex justify-between border-b py-2">
                <span>${item.menu_item.name}</span>
                <span class="font-bold">${item.total_sold} sold</span>
            </div>
        `;
    });
    document.getElementById('top-items').innerHTML = topHtml || '<p class="text-gray-500">No orders yet.</p>';

    let usageHtml = '';
    data.ingredient_usage_today.forEach(item => {
        usageHtml += `
            <div class="flex justify-between border-b py-2">
                <span>${item.ingredient.name}</span>
                <span class="font-bold">${item.total_used} ${item.ingredient.unit}</span>
            </div>
        `;
    });
    document.getElementById('usage').innerHTML = usageHtml || '<p class="text-gray-500">No usage yet.</p>';

    let lowHtml = '';
    data.low_stock_items.forEach(item => {
        lowHtml += `
            <div class="bg-red-100 text-red-700 p-3 rounded mb-2">
                ⚠️ ${item.name} is low: ${item.current_stock} ${item.unit}
            </div>
        `;
    });
    document.getElementById('low-stock').innerHTML = lowHtml || '<p class="text-green-600">No low stock items.</p>';

    let restockHtml = '';
    data.restock_suggestions.forEach(item => {
        restockHtml += `
            <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-2">
                ${item.ingredient}: restock around ${item.suggested_restock} ${item.unit}
            </div>
        `;
    });
    document.getElementById('restock').innerHTML = restockHtml || '<p class="text-gray-500">No suggestion yet.</p>';

    let forecastHtml = '';

    if (data.prophet_forecast && !data.prophet_forecast.error) {
        forecastHtml = `
            <div class="bg-blue-100 text-blue-800 p-4 rounded">
                📊 Predicted Demand:
                <strong>${Number(data.prophet_forecast.prediction).toFixed(2)} kg</strong>
                <br>
                Date: ${data.prophet_forecast.date}
            </div>
        `;
    } else {
        forecastHtml = `
            <p class="text-gray-500">
                Not enough data yet for AI prediction.
            </p>
        `;
    }

    document.getElementById('forecast').innerHTML = forecastHtml;
}

loadDashboard();
</script>

@endsection