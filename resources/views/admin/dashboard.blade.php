@extends('layouts.admin')

@section('content')

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">Dashboard Overview</h1>
            <p class="text-sm sm:text-base text-gray-500">
                Today’s restaurant operations, sales, orders, and menu capacity status.
            </p>
        </div>

        <div class="bg-white border rounded-2xl px-4 py-3 shadow-sm w-full sm:w-auto">
            <p class="text-xs text-gray-500">Dashboard Scope</p>
            <p class="text-sm font-bold text-orange-500">Today / Daily Monitoring</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Orders Today</p>
                    <h2 id="cardOrdersToday" class="text-2xl sm:text-3xl font-bold">0</h2>
                    <p id="cardOrdersSub" class="text-xs text-gray-400 mt-2">Today's order count</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500 shrink-0">
                    📦
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Sales Today</p>
                    <h2 id="cardSalesToday" class="text-2xl sm:text-3xl font-bold">₱0.00</h2>
                    <p id="cardSalesSub" class="text-xs text-gray-400 mt-2">Today's completed sales</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center text-green-500 shrink-0">
                    ₱
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Active Orders</p>
                    <h2 id="cardActiveOrders" class="text-2xl sm:text-3xl font-bold">0</h2>
                    <p id="cardActiveSub" class="text-xs text-gray-400 mt-2">Pending / preparing orders</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-yellow-50 flex items-center justify-center text-yellow-500 shrink-0">
                    ⏳
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Low Capacity Alerts</p>
                    <h2 id="cardLowStock" class="text-2xl sm:text-3xl font-bold">0</h2>
                    <p id="cardLowStockSub" class="text-xs text-red-400 mt-2">Menu items low for today</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center text-red-500 shrink-0">
                    ⚠️
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">7-Day Sales</h3>
                <p class="text-sm text-gray-500">
                    Quick recent sales reference. Full analysis is available in Reports & Forecast.
                </p>
            </div>

            <div class="h-72 sm:h-80">
                <canvas id="salesWeekChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Top Selling Items Today</h3>
                <p class="text-sm text-gray-500">Most ordered menu items for today’s operations.</p>
            </div>

            <div class="h-72 sm:h-80">
                <canvas id="topItemsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Daily Operations Panels -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Menu Capacity Usage Today</h3>
                <p class="text-sm text-gray-500">Menu items consumed today based on orders, heads, and requests.</p>
            </div>

            <div id="menuCapacityList" class="space-y-3">
                <p class="text-sm text-gray-400">No menu capacity usage yet.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Low Capacity Alerts Today</h3>
                <p class="text-sm text-gray-500">Menu items with 5 or fewer orders/heads left today.</p>
            </div>

            <div id="lowStockList" class="space-y-3">
                <p class="text-sm text-gray-400">No low capacity alerts.</p>
            </div>
        </div>
    </div>

    <!-- Preparation + Quick Forecast -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Preparation Suggestions Today</h3>
                <p class="text-sm text-gray-500">Suggested preparation based on today’s remaining menu capacity.</p>
            </div>

            <div id="restockSuggestionsList" class="space-y-3">
                <p class="text-sm text-gray-400">No preparation suggestions available.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Quick Tomorrow Prep Snapshot</h3>
                <p class="text-sm text-gray-500">
                    Short demand preview. Use Reports & Forecast for complete 7-day analysis.
                </p>
            </div>

            <!-- Desktop / Tablet Table -->
            <div class="hidden sm:block overflow-x-auto rounded-xl border">
                <table class="w-full min-w-[680px] text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold">Item</th>
                            <th class="text-left px-4 py-3 font-semibold">Predicted</th>
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

            <!-- Mobile Cards -->
            <div id="forecastMobileList" class="sm:hidden space-y-3">
                <p class="text-sm text-gray-400">No forecast data available.</p>
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

function shortLabel(value, max = 18) {
    const text = String(value ?? 'Unknown');

    if (text.length <= max) {
        return text;
    }

    return text.substring(0, max - 3) + '...';
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
            originalLabels: [],
            values: []
        };
    }

    const originalLabels = raw.map(item => pick(item, ['name', 'item_name', 'menu_item'], 'Unknown'));

    return {
        labels: originalLabels.map(label => shortLabel(label, 24)),
        originalLabels,
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
        ordersChange !== null ? `${ordersChange}% vs yesterday` : 'Today’s order count';

    document.getElementById('cardSalesSub').textContent =
        salesChange !== null ? `${salesChange}% vs yesterday` : 'Today’s completed sales';

    document.getElementById('cardActiveSub').textContent =
        activeChange !== null ? `${activeChange}% from last hour` : 'Pending / preparing orders';
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
                },
                tooltip: {
                    callbacks: {
                        label: item => `Sales: ${formatMoney(item.raw)}`
                    }
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

    const labels = normalized.labels.length ? normalized.labels : ['No items yet'];
    const values = normalized.values.length ? normalized.values : [0];

    topItemsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Orders Today',
                data: values,
                backgroundColor: '#fb923c',
                borderRadius: 6,
                maxBarThickness: 34
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: items => normalized.originalLabels[items[0].dataIndex] || items[0].label,
                        label: item => `Orders Today: ${item.raw}`
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                y: {
                    ticks: {
                        autoSkip: false
                    }
                }
            }
        }
    });
}

function renderMenuCapacityUsage(data) {
    const container = document.getElementById('menuCapacityList');
    const usage = pick(data, ['menu_usage_today', 'menu_capacity_usage', 'ingredient_usage_today', 'ingredient_usage'], []);

    if (!Array.isArray(usage) || usage.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No menu capacity usage yet.</p>';
        return;
    }

    container.innerHTML = usage.slice(0, 6).map(item => {
        const type = pick(item, ['inventory_type'], 'per_order');
        const unit = pick(item, ['unit'], type === 'per_head' ? 'heads' : type === 'custom' ? 'requests' : 'orders');
        const used = pick(item, ['quantity_used', 'used', 'sold_today'], 0);
        const name = pick(item, ['name', 'item_name', 'menu_item'], 'Unknown Item');
        const category = pick(item, ['category'], 'Menu item');

        return `
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-xl border bg-gray-50">
                <div class="min-w-0">
                    <p class="font-medium truncate">${safeText(name)}</p>
                    <p class="text-xs text-gray-500 truncate">${safeText(category)}</p>
                </div>

                <div class="sm:text-right shrink-0">
                    <p class="text-sm font-semibold">
                        ${formatNumber(used)} ${safeText(unit)}
                    </p>
                    <p class="text-xs text-gray-500">Used today</p>
                </div>
            </div>
        `;
    }).join('');
}

function renderLowStockAlerts(data) {
    const container = document.getElementById('lowStockList');
    const alerts = pick(data, ['low_stock_alerts', 'low_stock_items'], []);

    if (!Array.isArray(alerts) || alerts.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No low capacity alerts.</p>';
        return;
    }

    container.innerHTML = alerts.slice(0, 6).map(item => {
        const unit = pick(item, ['unit'], 'orders');
        const remaining = pick(item, ['remaining_today', 'remaining', 'current_stock', 'stock'], 0);
        const limit = pick(item, ['daily_limit'], null);
        const sold = pick(item, ['sold_today'], 0);
        const name = pick(item, ['name', 'item_name', 'menu_item'], 'Unknown Item');

        return `
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-xl border bg-red-50 border-red-100">
                <div class="min-w-0">
                    <p class="font-medium truncate">${safeText(name)}</p>
                    <p class="text-xs text-gray-500">
                        ${limit !== null ? `Sold: ${formatNumber(sold)} / Limit: ${formatNumber(limit)}` : 'Daily capacity alert'}
                    </p>
                </div>

                <div class="sm:text-right shrink-0">
                    <p class="text-sm font-semibold text-red-600">
                        ${formatNumber(remaining)} ${safeText(unit)} left
                    </p>
                    <p class="text-xs text-red-500">Low capacity</p>
                </div>
            </div>
        `;
    }).join('');
}

function renderRestockSuggestions(data) {
    const container = document.getElementById('restockSuggestionsList');
    const suggestions = pick(data, ['preparation_suggestions', 'restock_suggestions', 'restock'], []);

    if (!Array.isArray(suggestions) || suggestions.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No preparation suggestions available.</p>';
        return;
    }

    container.innerHTML = suggestions.slice(0, 6).map(item => {
        const unit = pick(item, ['unit'], 'orders');
        const suggested = pick(item, ['suggested_quantity', 'quantity', 'recommended_quantity'], 0);
        const name = pick(item, ['name', 'item_name', 'menu_item'], 'Unknown Item');
        const reason = pick(item, ['reason', 'recommendation'], 'Prepare additional capacity if demand continues.');

        return `
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-xl border bg-gray-50">
                <div class="min-w-0">
                    <p class="font-medium truncate">${safeText(name)}</p>
                    <p class="text-xs text-gray-500">${safeText(reason)}</p>
                </div>

                <div class="sm:text-right shrink-0">
                    <p class="text-sm font-semibold text-orange-600">
                        +${formatNumber(suggested)} ${safeText(unit)}
                    </p>
                    <p class="text-xs text-gray-500">Suggested</p>
                </div>
            </div>
        `;
    }).join('');
}

function renderForecast(data) {
    const tbody = document.getElementById('forecastTableBody');
    const mobileList = document.getElementById('forecastMobileList');
    const forecast = pick(data, ['ai_demand_forecast', 'forecast', 'demand_forecast', 'simple_forecast'], []);

    if (!Array.isArray(forecast) || forecast.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                    No forecast data available.
                </td>
            </tr>
        `;

        mobileList.innerHTML = '<p class="text-sm text-gray-400">No forecast data available.</p>';
        return;
    }

    const rows = forecast.slice(0, 5);

    tbody.innerHTML = rows.map(item => {
        const unit = pick(item, ['unit'], 'orders');
        const name = pick(item, ['name', 'item_name', 'menu_item'], 'Unknown Item');
        const predicted = Number(pick(item, ['predicted_demand', 'forecast_quantity', 'prediction'], 0)).toFixed(0);
        const confidence = pick(item, ['confidence', 'confidence_level'], 'N/A');
        const recommendation = pick(item, ['recommendation', 'suggestion'], 'Monitor demand');

        return `
            <tr class="border-t">
                <td class="px-4 py-3 font-medium">${safeText(name)}</td>
                <td class="px-4 py-3">${predicted} ${safeText(unit)}</td>
                <td class="px-4 py-3">${safeText(confidence)}</td>
                <td class="px-4 py-3">${safeText(recommendation)}</td>
            </tr>
        `;
    }).join('');

    mobileList.innerHTML = rows.map(item => {
        const unit = pick(item, ['unit'], 'orders');
        const name = pick(item, ['name', 'item_name', 'menu_item'], 'Unknown Item');
        const predicted = Number(pick(item, ['predicted_demand', 'forecast_quantity', 'prediction'], 0)).toFixed(0);
        const confidence = pick(item, ['confidence', 'confidence_level'], 'N/A');
        const recommendation = pick(item, ['recommendation', 'suggestion'], 'Monitor demand');

        return `
            <div class="rounded-xl border bg-gray-50 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900">${safeText(name)}</p>
                        <p class="text-xs text-gray-500 mt-1">${safeText(recommendation)}</p>
                    </div>

                    <span class="shrink-0 px-2.5 py-1 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">
                        ${safeText(confidence)}
                    </span>
                </div>

                <p class="text-sm font-semibold text-orange-600 mt-3">
                    Predicted: ${predicted} ${safeText(unit)}
                </p>
            </div>
        `;
    }).join('');
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

        try { renderCards(data); } catch (e) { console.error('Cards render error:', e); }
        try { renderSalesChart(data); } catch (e) { console.error('Sales chart error:', e); }
        try { renderTopItemsChart(data); } catch (e) { console.error('Top items chart error:', e); }
        try { renderMenuCapacityUsage(data); } catch (e) { console.error('Menu capacity usage error:', e); }
        try { renderLowStockAlerts(data); } catch (e) { console.error('Low stock error:', e); }
        try { renderRestockSuggestions(data); } catch (e) { console.error('Preparation suggestions error:', e); }
        try { renderForecast(data); } catch (e) { console.error('Forecast error:', e); }

    } catch (error) {
        console.error('Dashboard load failed:', error);
    }
}

loadDashboard();

setInterval(() => {
    loadDashboard();
}, 30000);
</script>

@endsection