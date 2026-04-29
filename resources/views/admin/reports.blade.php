@extends('layouts.admin')

@section('content')

<h1 class="text-3xl font-bold mb-1">Reports & Forecast</h1>
<p class="text-gray-500 mb-6">View sales, ingredient usage, low stock reports, and forecast summaries.</p>

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

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-bold mb-3">Low Stock Report</h2>
        <div id="low-stock"></div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-bold mb-3">Simple Forecast Summary</h2>
        <div id="forecast"></div>
    </div>
</div>

<script>
async function loadReports() {
    const res = await fetch('/api/admin/dashboard');
    const data = await res.json();

    document.getElementById('sales').innerText = '₱' + Number(data.total_sales_today).toLocaleString();
    document.getElementById('orders').innerText = data.total_orders_today;
    document.getElementById('low-count').innerText = data.low_stock_items.length;

    let topHtml = '';
    data.top_selling_items.forEach(item => {
        topHtml += `
            <div class="flex justify-between border-b py-2">
                <span>${item.menu_item ? item.menu_item.name : 'Deleted item'}</span>
                <span class="font-bold">${item.total_sold} sold</span>
            </div>
        `;
    });
    document.getElementById('top-items').innerHTML = topHtml || '<p class="text-gray-500">No sales data yet.</p>';

    let usageHtml = '';
    data.ingredient_usage_today.forEach(item => {
        usageHtml += `
            <div class="flex justify-between border-b py-2">
                <span>${item.ingredient ? item.ingredient.name : 'Deleted ingredient'}</span>
                <span class="font-bold">${item.total_used} ${item.ingredient ? item.ingredient.unit : ''}</span>
            </div>
        `;
    });
    document.getElementById('usage').innerHTML = usageHtml || '<p class="text-gray-500">No ingredient usage yet.</p>';

    let lowHtml = '';
    data.low_stock_items.forEach(item => {
        lowHtml += `
            <div class="bg-red-100 text-red-700 p-3 rounded mb-2">
                ⚠️ ${item.name}: ${item.current_stock} ${item.unit} remaining 
                | Threshold: ${item.threshold} ${item.unit}
            </div>
        `;
    });
    document.getElementById('low-stock').innerHTML = lowHtml || '<p class="text-green-600">No low stock items.</p>';

    let forecastHtml = '';
    data.simple_forecast.forEach(item => {
        const color = item.status === 'Restock Needed'
            ? 'bg-yellow-100 text-yellow-800'
            : 'bg-green-100 text-green-700';

        forecastHtml += `
            <div class="${color} p-3 rounded mb-2">
                ${item.ingredient}: estimated need tomorrow is 
                <strong>${item.forecasted_need_tomorrow} ${item.unit}</strong>
                <br>Status: ${item.status}
            </div>
        `;
    });
    document.getElementById('forecast').innerHTML = forecastHtml || '<p class="text-gray-500">Not enough usage data yet for forecast.</p>';
}

loadReports();
</script>

@endsection