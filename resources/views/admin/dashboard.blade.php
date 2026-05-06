@extends('layouts.admin')

@section('content')

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold mb-1">Dashboard Overview</h1>
        <p class="text-gray-500">Welcome back! Here’s what’s happening today.</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-2">Total Orders Today</p>
                    <h2 id="cardOrdersToday" class="text-3xl font-bold">0</h2>
                    <p id="cardOrdersSub" class="text-xs text-gray-400 mt-2">Updated from dashboard data</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-500">
                    📦
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-2">Total Sales Today</p>
                    <h2 id="cardSalesToday" class="text-3xl font-bold">₱0.00</h2>
                    <p id="cardSalesSub" class="text-xs text-gray-400 mt-2">Updated from dashboard data</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center text-green-500">
                    ₱
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-2">Active Orders</p>
                    <h2 id="cardActiveOrders" class="text-3xl font-bold">0</h2>
                    <p id="cardActiveSub" class="text-xs text-gray-400 mt-2">Currently active / pending orders</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center text-yellow-500">
                    ⏳
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-2">Low Stock Items</p>
                    <h2 id="cardLowStock" class="text-3xl font-bold">0</h2>
                    <p id="cardLowStockSub" class="text-xs text-red-400 mt-2">Needs attention</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center text-red-500">
                    ⚠️
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="mb-4">
                <h3 class="font-bold text-lg">Sales This Week</h3>
                <p class="text-sm text-gray-500">Daily sales performance overview.</p>
            </div>
            <div class="h-80">
                <canvas id="salesWeekChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="mb-4">
                <h3 class="font-bold text-lg">Top Selling Items</h3>
                <p class="text-sm text-gray-500">Most ordered menu items today / this week.</p>
            </div>
            <div class="h-80">
                <canvas id="topItemsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Middle Panels -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="mb-4">
                <h3 class="font-bold text-lg">Ingredient Usage Today</h3>
                <p class="text-sm text-gray-500">Ingredients consumed based on orders.</p>
            </div>
            <div id="ingredientUsageList" class="space-y-3">
                <p class="text-sm text-gray-400">No ingredient usage yet.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="mb-4">
                <h3 class="font-bold text-lg">Low Stock Alerts</h3>
                <p class="text-sm text-gray-500">Ingredients that need restocking.</p>
            </div>
            <div id="lowStockList" class="space-y-3">
                <p class="text-sm text-gray-400">No low stock alerts.</p>
            </div>
        </div>
    </div>

    <!-- Bottom Panels -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="mb-4">
                <h3 class="font-bold text-lg">Restock Suggestions</h3>
                <p class="text-sm text-gray-500">Recommended restocking based on current stock and usage.</p>
            </div>
            <div id="restockSuggestionsList" class="space-y-3">
                <p class="text-sm text-gray-400">No restock suggestions available.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="mb-4">
                <h3 class="font-bold text-lg">AI Demand Forecast</h3>
                <p class="text-sm text-gray-500">Forecasted item demand for the next day.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold">Item</th>
                            <th class="text-left px-4 py-3 font-semibold">Predicted Demand</th>
                            <th class="text-left px-4 py-3 font-semibold">Confidence</th>
                            <th class="text-left px-4 py-3 font-semibold">Recommendation</th>
                        </tr>
                    </thead>
                    <tbody id="forecastTableBody">
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                No forecast data available.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let salesWeekChart = null;
let topItemsChart = null;
let dashboardData = {};

function pick(obj, keys, fallback = null) {
    for (const key of keys) {
        if (obj && obj[key] !== undefined && obj[key] !== null) {
            return obj[key];
        }
    }

    return fallback;
}

function formatMoney(value) {
    return `₱${Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    })}`;
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString();
}

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function normalizeSalesWeek(data) {
    const raw = pick(data, ['sales_this_week', 'weekly_sales', 'sales_chart', 'sales_week'], []);

    if (Array.isArray(raw)) {
        if (raw.length && typeof raw[0] === 'object') {
            return {
                labels: raw.map(item => pick(item, ['label', 'day', 'date'], '')),
                values: raw.map(item => Number(pick(item, ['total', 'amount', 'sales', 'value'], 0)))
            };
        }

        return {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].slice(0, raw.length),
            values: raw.map(Number)
        };
    }

    if (raw && typeof raw === 'object') {
        return {
            labels: Object.keys(raw),
            values: Object.values(raw).map(Number)
        };
    }

    return {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        values: [0, 0, 0, 0, 0, 0, 0]
    };
}

function normalizeTopItems(data) {
    const raw = pick(data, ['top_selling_items', 'popular_menu_items', 'top_items'], []);

    if (!Array.isArray(raw)) {
        return {
            labels: [],
            values: []
        };
    }

    return {
        labels: raw.map(item => pick(item, ['name', 'item_name', 'menu_item'], 'Unknown')),
        values: raw.map(item => Number(pick(item, ['quantity', 'total_sold', 'count', 'orders'], 0)))
    };
}

function renderCards(data) {
    const totalOrders = pick(data, ['total_orders_today', 'orders_today', 'today_orders'], 0);
    const totalSales = pick(data, ['total_sales_today', 'sales_today', 'today_sales'], 0);
    const activeOrders = pick(data, ['active_orders', 'pending_orders', 'ongoing_orders'], 0);

    const lowStockItems = pick(data, ['low_stock_count', 'low_stock_items_count'], null);
    const lowStockList = pick(data, ['low_stock_alerts', 'low_stock_items'], []);
    const lowStockCount = lowStockItems !== null
        ? lowStockItems
        : (Array.isArray(lowStockList) ? lowStockList.length : 0);

    document.getElementById('cardOrdersToday').textContent = formatNumber(totalOrders);
    document.getElementById('cardSalesToday').textContent = formatMoney(totalSales);
    document.getElementById('cardActiveOrders').textContent = formatNumber(activeOrders);
    document.getElementById('cardLowStock').textContent = formatNumber(lowStockCount);

    const ordersChange = pick(data, ['orders_change_percentage', 'orders_growth'], null);
    const salesChange = pick(data, ['sales_change_percentage', 'sales_growth'], null);
    const activeChange = pick(data, ['active_orders_change', 'active_orders_growth'], null);

    document.getElementById('cardOrdersSub').textContent =
        ordersChange !== null ? `${ordersChange}% vs yesterday` : 'Updated from dashboard data';

    document.getElementById('cardSalesSub').textContent =
        salesChange !== null ? `${salesChange}% vs yesterday` : 'Updated from dashboard data';

    document.getElementById('cardActiveSub').textContent =
        activeChange !== null ? `${activeChange}% from last hour` : 'Currently active / pending orders';
}

function renderSalesChart(data) {
    const normalized = normalizeSalesWeek(data);
    const canvas = document.getElementById('salesWeekChart');

    if (!canvas || typeof Chart === 'undefined') return;

    const ctx = canvas.getContext('2d');

    if (salesWeekChart) {
        salesWeekChart.destroy();
    }

    salesWeekChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: normalized.labels,
            datasets: [{
                label: 'Sales',
                data: normalized.values,
                borderColor: '#fb923c',
                backgroundColor: 'rgba(251, 146, 60, 0.12)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#fb923c'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => `₱${value}`
                    }
                }
            }
        }
    });
}

function renderTopItemsChart(data) {
    const normalized = normalizeTopItems(data);
    const canvas = document.getElementById('topItemsChart');

    if (!canvas || typeof Chart === 'undefined') return;

    const ctx = canvas.getContext('2d');

    if (topItemsChart) {
        topItemsChart.destroy();
    }

    topItemsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: normalized.labels,
            datasets: [{
                label: 'Orders',
                data: normalized.values,
                backgroundColor: '#fb923c',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function renderIngredientUsage(data) {
    const container = document.getElementById('ingredientUsageList');
    const usage = pick(data, ['ingredient_usage_today', 'ingredient_usage'], []);

    if (!Array.isArray(usage) || usage.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No ingredient usage yet.</p>';
        return;
    }

    container.innerHTML = usage.map(item => `
        <div class="flex items-center justify-between p-3 rounded-lg border bg-gray-50">
            <div>
                <p class="font-medium">${safeText(pick(item, ['name', 'ingredient_name'], 'Unknown Ingredient'))}</p>
                <p class="text-xs text-gray-500">Used today</p>
            </div>
            <div class="text-sm font-semibold">
                ${Number(pick(item, ['quantity_used', 'quantity', 'used'], 0)).toFixed(2)}
                ${safeText(pick(item, ['unit'], ''))}
            </div>
        </div>
    `).join('');
}

function renderLowStockAlerts(data) {
    const container = document.getElementById('lowStockList');
    const alerts = pick(data, ['low_stock_alerts', 'low_stock_items'], []);

    if (!Array.isArray(alerts) || alerts.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No low stock alerts.</p>';
        return;
    }

    container.innerHTML = alerts.map(item => `
        <div class="flex items-center justify-between p-3 rounded-lg border bg-red-50 border-red-100">
            <div>
                <p class="font-medium">${safeText(pick(item, ['name', 'ingredient_name'], 'Unknown Ingredient'))}</p>
                <p class="text-xs text-gray-500">
                    Threshold:
                    ${Number(pick(item, ['threshold'], 0)).toFixed(2)}
                    ${safeText(pick(item, ['unit'], ''))}
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold text-red-600">
                    ${Number(pick(item, ['current_stock', 'stock', 'remaining'], 0)).toFixed(2)}
                    ${safeText(pick(item, ['unit'], ''))}
                </p>
                <p class="text-xs text-red-500">Low Stock</p>
            </div>
        </div>
    `).join('');
}

function renderRestockSuggestions(data) {
    const container = document.getElementById('restockSuggestionsList');
    const suggestions = pick(data, ['restock_suggestions', 'restock'], []);

    if (!Array.isArray(suggestions) || suggestions.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No restock suggestions available.</p>';
        return;
    }

    container.innerHTML = suggestions.map(item => `
        <div class="flex items-center justify-between p-3 rounded-lg border bg-gray-50">
            <div>
                <p class="font-medium">${safeText(pick(item, ['name', 'ingredient_name'], 'Unknown Ingredient'))}</p>
                <p class="text-xs text-gray-500">
                    ${safeText(pick(item, ['reason'], 'Suggested for restocking'))}
                </p>
            </div>
            <div class="text-sm font-semibold text-orange-600">
                ${Number(pick(item, ['suggested_quantity', 'quantity', 'recommended_quantity'], 0)).toFixed(2)}
                ${safeText(pick(item, ['unit'], ''))}
            </div>
        </div>
    `).join('');
}

function renderForecast(data) {
    const tbody = document.getElementById('forecastTableBody');
    const forecast = pick(data, ['ai_demand_forecast', 'forecast', 'demand_forecast', 'simple_forecast'], []);

    if (!Array.isArray(forecast) || forecast.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                    No forecast data available.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = forecast.map(item => `
        <tr class="border-t">
            <td class="px-4 py-3 font-medium">
                ${safeText(pick(item, ['name', 'item_name', 'menu_item'], 'Unknown Item'))}
            </td>
            <td class="px-4 py-3">
                ${Number(pick(item, ['predicted_demand', 'forecast_quantity', 'prediction'], 0)).toFixed(0)}
            </td>
            <td class="px-4 py-3">
                ${safeText(pick(item, ['confidence', 'confidence_level'], 'N/A'))}
            </td>
            <td class="px-4 py-3">
                ${safeText(pick(item, ['recommendation', 'suggestion'], 'Monitor demand'))}
            </td>
        </tr>
    `).join('');
}

async function loadDashboard() {
    try {
        const res = await fetch('/api/admin/dashboard', {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            console.error('Dashboard API failed:', res.status);
            return;
        }

        const data = await res.json();

        console.log('Dashboard API data:', data);

        dashboardData = data;

        renderCards(data);

        try {
            renderSalesChart(data);
        } catch (e) {
            console.error('Sales chart error:', e);
        }

        try {
            renderTopItemsChart(data);
        } catch (e) {
            console.error('Top items chart error:', e);
        }

        try {
            renderIngredientUsage(data);
        } catch (e) {
            console.error('Ingredient usage error:', e);
        }

        try {
            renderLowStockAlerts(data);
        } catch (e) {
            console.error('Low stock error:', e);
        }

        try {
            renderRestockSuggestions(data);
        } catch (e) {
            console.error('Restock suggestions error:', e);
        }

        try {
            renderForecast(data);
        } catch (e) {
            console.error('Forecast error:', e);
        }

    } catch (error) {
        console.error('Dashboard load failed:', error);
    }
}

loadDashboard();

setInterval(() => {
    loadDashboard();
}, 10000);
</script>

@endsection