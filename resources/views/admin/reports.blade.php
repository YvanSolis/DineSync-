@extends('layouts.admin')

@section('content')

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">Reports & Forecast</h1>
            <p class="text-sm sm:text-base text-gray-500">
                Analytics based on sales, orders, and 7-day menu demand.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full sm:w-auto">
            <button type="button"
                class="border rounded-xl px-4 py-2.5 text-sm bg-white text-gray-700 font-semibold">
                Last 7 Days
            </button>

            <button onclick="refreshForecast(event)"
                class="border border-orange-200 bg-orange-50 text-orange-600 hover:bg-orange-100 px-4 py-2.5 rounded-xl font-semibold text-sm">
                Refresh Forecast
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <p class="text-sm text-gray-500 mb-2">Total Revenue (7d)</p>
            <h2 id="cardRevenue7d" class="text-2xl sm:text-3xl font-bold">₱0.00</h2>
            <p class="text-xs text-green-500 mt-2">Recent completed sales</p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <p class="text-sm text-gray-500 mb-2">Avg Order Value</p>
            <h2 id="cardAvgOrderValue" class="text-2xl sm:text-3xl font-bold">₱0.00</h2>
            <p class="text-xs text-green-500 mt-2">Average per order/payment</p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <p class="text-sm text-gray-500 mb-2">Total Orders (7d)</p>
            <h2 id="cardOrders7d" class="text-2xl sm:text-3xl font-bold">0</h2>
            <p class="text-xs text-green-500 mt-2">Based on recent orders</p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <p class="text-sm text-gray-500 mb-2">Forecasted Revenue</p>
            <h2 id="cardForecastRevenue" class="text-2xl sm:text-3xl font-bold">₱0.00</h2>
            <p id="forecastModeText" class="text-xs text-gray-500 mt-2">System forecast</p>
        </div>
    </div>

    <!-- Forecast Insight -->
    <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
            <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg">Menu Demand Forecast Insight</h3>
                <p id="forecastMode" class="text-sm text-gray-500">Loading forecast...</p>
            </div>

            <span id="confidenceBadge" class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-600 w-fit">
                Confidence: -
            </span>
        </div>

        <p id="summaryText" class="text-sm sm:text-base text-gray-700 leading-7">
            Waiting for forecast...
        </p>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-5">
            <div class="border rounded-2xl p-4 bg-orange-50 border-orange-100">
                <h4 class="font-bold mb-3">Preparation Recommendations</h4>
                <div id="recommendationsList" class="space-y-2 text-sm text-gray-700">
                    <p class="text-gray-400">No recommendations yet.</p>
                </div>
            </div>

            <div class="border rounded-2xl p-4 bg-blue-50 border-blue-100">
                <h4 class="font-bold mb-3">Next-Day Revenue Forecast</h4>
                <p class="text-sm text-gray-500">Estimated revenue for the next operating day.</p>
                <h2 id="revenueForecast" class="text-2xl sm:text-3xl font-bold mt-3">₱0.00</h2>
            </div>
        </div>
    </div>

    <!-- Main Trend Chart -->
    <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
        <div class="mb-4">
            <h3 class="font-bold text-base sm:text-lg">Sales & Order Trends</h3>
            <p class="text-sm text-gray-500">Recent sales and order performance over the last 7 days.</p>
        </div>

        <div class="h-72 sm:h-96">
            <canvas id="salesOrdersTrendChart"></canvas>
        </div>
    </div>

    <!-- Two Smaller Charts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Revenue by Category</h3>
                <p class="text-sm text-gray-500">Which menu categories contributed most to revenue in the last 7 days.</p>
            </div>

            <div class="h-72 sm:h-80">
                <canvas id="revenueByCategoryChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">7-Day Menu Demand Forecast</h3>
                <p class="text-sm text-gray-500">Sold in the last 7 days compared with forecasted demand.</p>
            </div>

            <div class="h-[360px] sm:h-[420px]">
                <canvas id="capacityForecastChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Forecast Tables -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <!-- Menu Demand Forecast -->
        <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b">
                <h3 class="font-bold text-base sm:text-lg">Menu Demand Forecast</h3>
                <p class="text-sm text-gray-500">Predicted demand for menu items based on recent orders.</p>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-6 py-4 font-semibold">Menu Item</th>
                            <th class="text-left px-6 py-4 font-semibold">7-Day Sold</th>
                            <th class="text-left px-6 py-4 font-semibold">Predicted Demand</th>
                            <th class="text-left px-6 py-4 font-semibold">Recommendation</th>
                        </tr>
                    </thead>
                    <tbody id="menuForecastBody">
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                Loading menu forecast...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div id="menuForecastMobileList" class="md:hidden p-4 space-y-3">
                <div class="px-4 py-8 text-center text-gray-400">
                    Loading menu forecast...
                </div>
            </div>
        </div>

        <!-- Daily Capacity Alerts -->
        <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b">
                <h3 class="font-bold text-base sm:text-lg">Daily Capacity Alerts</h3>
                <p class="text-sm text-gray-500">Low or sold-out daily menu capacity.</p>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[680px] text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-6 py-4 font-semibold">Menu Item</th>
                            <th class="text-left px-6 py-4 font-semibold">Type</th>
                            <th class="text-left px-6 py-4 font-semibold">Remaining</th>
                            <th class="text-left px-6 py-4 font-semibold">Risk</th>
                        </tr>
                    </thead>
                    <tbody id="capacityAlertsBody">
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                Loading capacity alerts...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div id="capacityAlertsMobileList" class="md:hidden p-4 space-y-3">
                <div class="px-4 py-8 text-center text-gray-400">
                    Loading capacity alerts...
                </div>
            </div>
        </div>
    </div>

    <!-- Forecast Detail Table -->
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b">
            <h3 class="font-bold text-base sm:text-lg">Detailed Forecast Recommendations</h3>
            <p class="text-sm text-gray-500">System-generated forecast based on sales, orders, and daily capacity.</p>
        </div>

        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full min-w-[1040px] text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Menu Item</th>
                        <th class="text-left px-6 py-4 font-semibold">Category</th>
                        <th class="text-left px-6 py-4 font-semibold">Sold (7d)</th>
                        <th class="text-left px-6 py-4 font-semibold">Predicted Demand</th>
                        <th class="text-left px-6 py-4 font-semibold">Confidence</th>
                        <th class="text-left px-6 py-4 font-semibold">Recommendation</th>
                    </tr>
                </thead>
                <tbody id="forecastDetailsBody">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            Loading forecast details...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile / Tablet Cards -->
        <div id="forecastDetailsMobileList" class="lg:hidden p-4 space-y-3">
            <div class="px-4 py-8 text-center text-gray-400">
                Loading forecast details...
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let reportsData = {};
let salesOrdersTrendChart = null;
let revenueByCategoryChart = null;
let capacityForecastChart = null;

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
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

function pick(obj, keys, fallback = null) {
    for (const key of keys) {
        if (obj && obj[key] !== undefined && obj[key] !== null) {
            return obj[key];
        }
    }

    return fallback;
}

function shortLabel(value, max = 20) {
    const text = String(value ?? 'Unknown');

    if (text.length <= max) {
        return text;
    }

    return text.substring(0, max - 3) + '...';
}

function formatInventoryType(value) {
    switch (value) {
        case 'per_head':
            return 'Per Head';
        case 'custom':
            return 'Custom';
        case 'per_order':
        default:
            return 'Per Order';
    }
}

function renderCards(data) {
    const revenue = pick(data, ['total_revenue_7d', 'total_revenue', 'revenue_7d'], 0);
    const avgOrder = pick(data, ['avg_order_value', 'average_order_value'], 0);
    const orders = pick(data, ['total_orders_7d', 'total_orders', 'orders_7d'], 0);
    const forecastRevenue = pick(data, ['forecasted_revenue', 'revenue_forecast', 'next_day_revenue_forecast'], 0);

    document.getElementById('cardRevenue7d').textContent = formatMoney(revenue);
    document.getElementById('cardAvgOrderValue').textContent = formatMoney(avgOrder);
    document.getElementById('cardOrders7d').textContent = formatNumber(orders);
    document.getElementById('cardForecastRevenue').textContent = formatMoney(forecastRevenue);
    document.getElementById('revenueForecast').textContent = formatMoney(forecastRevenue);

    const mode = pick(data, ['forecast_mode'], 'System forecast');
    document.getElementById('forecastModeText').textContent = mode;
}

function renderInsight(data) {
    const summary = pick(data, ['summary', 'ai_summary'], 'No forecast summary available yet.');
    const mode = pick(data, ['forecast_mode'], 'Based on last 7 days of available order and sales data.');
    const confidence = pick(data, ['forecast_confidence', 'ai_forecast_confidence'], 'N/A');
    const recommendations = pick(data, ['recommendations', 'ai_recommendations'], []);

    document.getElementById('summaryText').textContent = summary;
    document.getElementById('forecastMode').textContent = mode;
    document.getElementById('confidenceBadge').textContent = `Confidence: ${confidence}`;

    const list = document.getElementById('recommendationsList');

    if (!Array.isArray(recommendations) || recommendations.length === 0) {
        list.innerHTML = '<p class="text-gray-400">No recommendations yet.</p>';
        return;
    }

    list.innerHTML = recommendations.map(item => `
        <div class="rounded-xl bg-white/70 border border-orange-100 px-3 py-2">
            ${safeText(item)}
        </div>
    `).join('');
}

function normalizeTrendData(data) {
    const raw = pick(data, ['sales_order_trends', 'sales_orders_trend', 'trend_data'], []);

    if (Array.isArray(raw)) {
        return {
            labels: raw.map(item => pick(item, ['label', 'date', 'day'], '')),
            sales: raw.map(item => Number(pick(item, ['sales', 'revenue', 'total_sales'], 0))),
            orders: raw.map(item => Number(pick(item, ['orders', 'order_count', 'total_orders'], 0)))
        };
    }

    return {
        labels: [],
        sales: [],
        orders: []
    };
}

function renderSalesOrdersTrendChart(data) {
    const normalized = normalizeTrendData(data);
    const canvas = document.getElementById('salesOrdersTrendChart');

    if (!canvas || typeof Chart === 'undefined') return;

    if (salesOrdersTrendChart) {
        salesOrdersTrendChart.destroy();
    }

    salesOrdersTrendChart = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: normalized.labels,
            datasets: [
                {
                    label: 'Sales',
                    data: normalized.sales,
                    borderColor: '#fb923c',
                    backgroundColor: 'rgba(251, 146, 60, 0.12)',
                    fill: true,
                    tension: 0.35,
                    yAxisID: 'y'
                },
                {
                    label: 'Orders',
                    data: normalized.orders,
                    borderColor: '#60a5fa',
                    backgroundColor: 'rgba(96, 165, 250, 0.12)',
                    fill: false,
                    tension: 0.35,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: item => {
                            if (item.dataset.label === 'Sales') {
                                return `Sales: ${formatMoney(item.raw)}`;
                            }

                            return `Orders: ${item.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        callback: value => `₱${value}`
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

function renderRevenueByCategoryChart(data) {
    const raw = pick(data, ['revenue_by_category', 'category_revenue'], []);
    const canvas = document.getElementById('revenueByCategoryChart');

    if (!canvas || typeof Chart === 'undefined') return;

    let labels = [];
    let values = [];

    if (Array.isArray(raw)) {
        labels = raw.map(item => pick(item, ['category', 'name'], 'Unknown'));
        values = raw.map(item => Number(pick(item, ['revenue', 'total', 'amount'], 0)));
    } else if (raw && typeof raw === 'object') {
        labels = Object.keys(raw);
        values = Object.values(raw).map(Number);
    }

    if (!labels.length) {
        labels = ['No data'];
        values = [0];
    }

    if (revenueByCategoryChart) {
        revenueByCategoryChart.destroy();
    }

    revenueByCategoryChart = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels.map(label => shortLabel(label, 18)),
            datasets: [{
                label: 'Revenue',
                data: values,
                backgroundColor: '#fb923c',
                borderRadius: 8,
                maxBarThickness: 42
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
                        title: items => labels[items[0].dataIndex] || items[0].label,
                        label: item => `Revenue: ${formatMoney(item.raw)}`
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

function getMenuForecastRows(data) {
    const rows = pick(data, [
        'menu_demand_forecast',
        'menu_capacity_forecast',
        'forecast_details',
        'capacity_forecast'
    ], []);

    return Array.isArray(rows) ? rows : [];
}

function getSold7d(item) {
    return Number(pick(item, [
        'recent_sold_7d',
        'sold_7d',
        'total_sold_7d',
        'total_sold',
        'quantity_sold',
        'sold_today'
    ], 0));
}

function getPredictedDemand(item) {
    return Number(pick(item, [
        'predicted_demand',
        'forecast_demand',
        'forecast_quantity',
        'prediction',
        'recommended_quantity'
    ], 0));
}

function renderCapacityForecastChart(data) {
    const rows = getMenuForecastRows(data)
        .filter(item => getSold7d(item) > 0 || getPredictedDemand(item) > 0)
        .slice(0, 8);

    const canvas = document.getElementById('capacityForecastChart');

    if (!canvas || typeof Chart === 'undefined') return;

    const originalLabels = rows.map(item => pick(item, ['menu_item', 'item_name', 'name'], 'Unknown Item'));
    const labels = originalLabels.map(label => shortLabel(label, 24));
    const sold = rows.map(item => getSold7d(item));
    const predicted = rows.map(item => getPredictedDemand(item));

    if (capacityForecastChart) {
        capacityForecastChart.destroy();
    }

    capacityForecastChart = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels.length ? labels : ['No forecast data'],
            datasets: [
                {
                    label: 'Sold in Last 7 Days',
                    data: sold.length ? sold : [0],
                    backgroundColor: '#60a5fa',
                    borderRadius: 8,
                    maxBarThickness: 24
                },
                {
                    label: 'Forecast Demand',
                    data: predicted.length ? predicted : [0],
                    backgroundColor: '#fb923c',
                    borderRadius: 8,
                    maxBarThickness: 24
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        title: items => originalLabels[items[0].dataIndex] || items[0].label
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

function renderMenuForecast(data) {
    const tbody = document.getElementById('menuForecastBody');
    const mobileList = document.getElementById('menuForecastMobileList');

    const rows = getMenuForecastRows(data).slice(0, 8);

    if (!rows.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                    No menu forecast data available.
                </td>
            </tr>
        `;

        mobileList.innerHTML = `
            <div class="px-4 py-8 text-center text-gray-400">
                No menu forecast data available.
            </div>
        `;
        return;
    }

    tbody.innerHTML = rows.map(item => {
        const name = pick(item, ['menu_item', 'item_name', 'name'], 'Unknown Item');
        const sold = getSold7d(item);
        const predicted = getPredictedDemand(item);
        const unit = pick(item, ['unit'], 'orders');
        const recommendation = pick(item, ['recommendation', 'suggestion'], 'Monitor demand');

        return `
            <tr class="border-t hover:bg-gray-50">
                <td class="px-6 py-4 font-semibold">${safeText(name)}</td>
                <td class="px-6 py-4">${formatNumber(sold)}</td>
                <td class="px-6 py-4">${formatNumber(predicted)} ${safeText(unit)}</td>
                <td class="px-6 py-4">${safeText(recommendation)}</td>
            </tr>
        `;
    }).join('');

    mobileList.innerHTML = rows.map(item => {
        const name = pick(item, ['menu_item', 'item_name', 'name'], 'Unknown Item');
        const sold = getSold7d(item);
        const predicted = getPredictedDemand(item);
        const unit = pick(item, ['unit'], 'orders');
        const recommendation = pick(item, ['recommendation', 'suggestion'], 'Monitor demand');

        return `
            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                <h4 class="font-bold text-gray-900 leading-snug">${safeText(name)}</h4>

                <div class="mt-3 rounded-xl bg-gray-50 border px-3 py-2 space-y-1">
                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">7-Day Sold:</span>
                        ${formatNumber(sold)}
                    </p>

                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">Predicted Demand:</span>
                        ${formatNumber(predicted)} ${safeText(unit)}
                    </p>

                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">Recommendation:</span>
                        ${safeText(recommendation)}
                    </p>
                </div>
            </div>
        `;
    }).join('');
}

function renderCapacityAlerts(data) {
    const tbody = document.getElementById('capacityAlertsBody');
    const mobileList = document.getElementById('capacityAlertsMobileList');

    const alerts = pick(data, ['capacity_alerts', 'low_capacity_alerts', 'low_stock_alerts'], []);

    if (!Array.isArray(alerts) || !alerts.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                    No daily capacity alerts.
                </td>
            </tr>
        `;

        mobileList.innerHTML = `
            <div class="px-4 py-8 text-center text-gray-400">
                No daily capacity alerts.
            </div>
        `;
        return;
    }

    tbody.innerHTML = alerts.map(item => {
        const name = pick(item, ['menu_item', 'item_name', 'name'], 'Unknown Item');
        const type = pick(item, ['inventory_type', 'type'], 'per_order');
        const remaining = pick(item, ['remaining_today', 'remaining', 'stock'], 0);
        const unit = pick(item, ['unit'], type === 'per_head' ? 'heads' : 'orders');
        const risk = pick(item, ['risk', 'status'], Number(remaining) <= 0 ? 'High' : 'Low');

        return `
            <tr class="border-t hover:bg-gray-50">
                <td class="px-6 py-4 font-semibold">${safeText(name)}</td>
                <td class="px-6 py-4">${safeText(formatInventoryType(type))}</td>
                <td class="px-6 py-4">${safeText(remaining)} ${safeText(unit)}</td>
                <td class="px-6 py-4">${riskBadge(risk)}</td>
            </tr>
        `;
    }).join('');

    mobileList.innerHTML = alerts.map(item => {
        const name = pick(item, ['menu_item', 'item_name', 'name'], 'Unknown Item');
        const type = pick(item, ['inventory_type', 'type'], 'per_order');
        const remaining = pick(item, ['remaining_today', 'remaining', 'stock'], 0);
        const unit = pick(item, ['unit'], type === 'per_head' ? 'heads' : 'orders');
        const risk = pick(item, ['risk', 'status'], Number(remaining) <= 0 ? 'High' : 'Low');

        return `
            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <h4 class="font-bold text-gray-900 leading-snug">${safeText(name)}</h4>
                    ${riskBadge(risk)}
                </div>

                <div class="mt-3 rounded-xl bg-gray-50 border px-3 py-2 space-y-1">
                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">Type:</span>
                        ${safeText(formatInventoryType(type))}
                    </p>

                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">Remaining:</span>
                        ${safeText(remaining)} ${safeText(unit)}
                    </p>
                </div>
            </div>
        `;
    }).join('');
}

function riskBadge(risk) {
    const value = String(risk || '').toLowerCase();

    if (value.includes('high') || value.includes('sold')) {
        return '<span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-semibold">High Risk</span>';
    }

    if (value.includes('low')) {
        return '<span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">Low</span>';
    }

    return '<span class="px-3 py-1 rounded-full bg-green-100 text-green-600 text-xs font-semibold">Normal</span>';
}

function renderForecastDetails(data) {
    const tbody = document.getElementById('forecastDetailsBody');
    const mobileList = document.getElementById('forecastDetailsMobileList');

    const rows = pick(data, ['forecast_details', 'detailed_forecast', 'menu_demand_forecast'], []);

    if (!Array.isArray(rows) || !rows.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                    No detailed forecast data available.
                </td>
            </tr>
        `;

        mobileList.innerHTML = `
            <div class="px-4 py-8 text-center text-gray-400">
                No detailed forecast data available.
            </div>
        `;
        return;
    }

    tbody.innerHTML = rows.map(item => {
        const name = pick(item, ['menu_item', 'item_name', 'name'], 'Unknown Item');
        const category = pick(item, ['category'], 'Uncategorized');
        const sold = getSold7d(item);
        const predicted = getPredictedDemand(item);
        const unit = pick(item, ['unit'], 'orders');
        const confidence = pick(item, ['confidence', 'confidence_level'], 'N/A');
        const recommendation = pick(item, ['recommendation', 'suggestion'], 'Monitor demand');

        return `
            <tr class="border-t hover:bg-gray-50">
                <td class="px-6 py-4 font-semibold">${safeText(name)}</td>
                <td class="px-6 py-4">${safeText(category)}</td>
                <td class="px-6 py-4">${formatNumber(sold)}</td>
                <td class="px-6 py-4">${formatNumber(predicted)} ${safeText(unit)}</td>
                <td class="px-6 py-4">${safeText(confidence)}</td>
                <td class="px-6 py-4">${safeText(recommendation)}</td>
            </tr>
        `;
    }).join('');

    mobileList.innerHTML = rows.map(item => {
        const name = pick(item, ['menu_item', 'item_name', 'name'], 'Unknown Item');
        const category = pick(item, ['category'], 'Uncategorized');
        const sold = getSold7d(item);
        const predicted = getPredictedDemand(item);
        const unit = pick(item, ['unit'], 'orders');
        const confidence = pick(item, ['confidence', 'confidence_level'], 'N/A');
        const recommendation = pick(item, ['recommendation', 'suggestion'], 'Monitor demand');

        return `
            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="font-bold text-gray-900 leading-snug">${safeText(name)}</h4>
                        <p class="text-xs text-gray-500 mt-1">${safeText(category)}</p>
                    </div>

                    <span class="shrink-0 px-3 py-1 rounded-full bg-blue-100 text-blue-600 text-xs font-semibold">
                        ${safeText(confidence)}
                    </span>
                </div>

                <div class="mt-3 rounded-xl bg-gray-50 border px-3 py-2 space-y-1">
                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">Sold (7d):</span>
                        ${formatNumber(sold)}
                    </p>

                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">Predicted Demand:</span>
                        ${formatNumber(predicted)} ${safeText(unit)}
                    </p>

                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">Recommendation:</span>
                        ${safeText(recommendation)}
                    </p>
                </div>
            </div>
        `;
    }).join('');
}

async function loadReportsForecast() {
    try {
        const res = await fetch('/api/admin/reports-forecast', {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            console.error('Reports Forecast API failed:', res.status);
            return;
        }

        const data = await res.json();
        reportsData = data;

        renderCards(data);
        renderInsight(data);
        renderSalesOrdersTrendChart(data);
        renderRevenueByCategoryChart(data);
        renderCapacityForecastChart(data);
        renderMenuForecast(data);
        renderCapacityAlerts(data);
        renderForecastDetails(data);
    } catch (error) {
        console.error('Reports Forecast load failed:', error);
    }
}

function refreshForecast(event) {
    const button = event?.currentTarget;

    if (button) {
        button.dataset.originalText = button.textContent;
        button.textContent = 'Refreshing...';
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-not-allowed');
    }

    loadReportsForecast().finally(() => {
        if (button) {
            button.textContent = button.dataset.originalText || 'Refresh Forecast';
            button.disabled = false;
            button.classList.remove('opacity-70', 'cursor-not-allowed');
        }
    });
}

loadReportsForecast();
</script>

@endsection