@extends('layouts.admin')

@section('content')

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-bold mb-1">Reports & Forecast</h1>
            <p class="text-gray-500">Analytics and predictive insights.</p>
        </div>

        <div class="flex items-center gap-3">
            <button class="border rounded px-4 py-2 text-sm bg-white text-gray-700">
                Last 7 Days
            </button>

            <button onclick="downloadReportsCsv()"
                class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded font-medium text-sm">
                Download Report
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-5">
            <p class="text-sm text-gray-500 mb-2">Total Revenue (7d)</p>
            <h2 id="cardRevenue7d" class="text-3xl font-bold">₱0.00</h2>
            <p class="text-xs text-green-500 mt-2">Recent revenue summary</p>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <p class="text-sm text-gray-500 mb-2">Avg Order Value</p>
            <h2 id="cardAvgOrderValue" class="text-3xl font-bold">₱0.00</h2>
            <p class="text-xs text-green-500 mt-2">Average per transaction/order</p>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <p class="text-sm text-gray-500 mb-2">Total Orders (7d)</p>
            <h2 id="cardOrders7d" class="text-3xl font-bold">0</h2>
            <p class="text-xs text-green-500 mt-2">Based on recent activity</p>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <p class="text-sm text-gray-500 mb-2">Forecasted Revenue</p>
            <h2 id="cardForecastRevenue" class="text-3xl font-bold">₱0.00</h2>
            <p id="forecastModeText" class="text-xs text-gray-500 mt-2">Smart estimate mode</p>
        </div>
    </div>

    <!-- OpenAI Forecast Insight -->
    <div class="bg-white rounded-xl border shadow-sm p-5">
        <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
            <div>
                <h3 class="font-bold text-lg">OpenAI Forecast Insight</h3>
                <p id="aiForecastMode" class="text-sm text-gray-500">Loading AI forecast...</p>
            </div>

            <span id="aiConfidenceBadge" class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-600">
                Confidence: -
            </span>
        </div>

        <p id="aiSummaryText" class="text-gray-700 leading-7">
            Waiting for forecast...
        </p>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-5">
            <div class="border rounded-lg p-4 bg-orange-50 border-orange-100">
                <h4 class="font-bold mb-3">AI Recommendations</h4>
                <div id="aiRecommendationsList" class="space-y-2 text-sm text-gray-700">
                    <p class="text-gray-400">No recommendations yet.</p>
                </div>
            </div>

            <div class="border rounded-lg p-4 bg-blue-50 border-blue-100">
                <h4 class="font-bold mb-3">Next-Day Revenue Forecast</h4>
                <p class="text-sm text-gray-500">OpenAI predicted revenue for the next operating day.</p>
                <h2 id="aiRevenueForecast" class="text-3xl font-bold mt-3">₱0.00</h2>
            </div>
        </div>
    </div>

    <!-- Main Trend Chart -->
    <div class="bg-white rounded-xl border shadow-sm p-5">
        <div class="mb-4">
            <h3 class="font-bold text-lg">Sales & Order Trends</h3>
            <p class="text-sm text-gray-500">Recent sales and order performance over the last 7 days.</p>
        </div>

        <div class="h-96">
            <canvas id="salesOrdersTrendChart"></canvas>
        </div>
    </div>

    <!-- Two Smaller Charts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="mb-4">
                <h3 class="font-bold text-lg">Revenue by Category</h3>
                <p class="text-sm text-gray-500">Which menu categories contribute most to revenue.</p>
            </div>

            <div class="h-80">
                <canvas id="revenueByCategoryChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <div class="mb-4">
                <h3 class="font-bold text-lg">Inventory Usage vs Forecast</h3>
                <p class="text-sm text-gray-500">Actual recent ingredient usage compared with forecasted demand.</p>
            </div>

            <div class="h-80">
                <canvas id="inventoryForecastChart"></canvas>
            </div>
        </div>
    </div>

    <!-- AI Tables -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border shadow-sm">
            <div class="p-5 border-b">
                <h3 class="font-bold text-lg">AI Menu Demand Forecast</h3>
                <p class="text-sm text-gray-500">Predicted demand for menu items.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-6 py-4 font-semibold">Menu Item</th>
                            <th class="text-left px-6 py-4 font-semibold">Predicted Demand</th>
                            <th class="text-left px-6 py-4 font-semibold">Reason</th>
                        </tr>
                    </thead>
                    <tbody id="aiMenuForecastBody">
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                                Loading AI menu forecast...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm">
            <div class="p-5 border-b">
                <h3 class="font-bold text-lg">AI Ingredient Restock Forecast</h3>
                <p class="text-sm text-gray-500">Suggested restocking for fresh daily operations.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-6 py-4 font-semibold">Ingredient</th>
                            <th class="text-left px-6 py-4 font-semibold">Current Stock</th>
                            <th class="text-left px-6 py-4 font-semibold">Suggested Restock</th>
                            <th class="text-left px-6 py-4 font-semibold">Risk</th>
                        </tr>
                    </thead>
                    <tbody id="aiIngredientForecastBody">
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                Loading AI ingredient forecast...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Forecast Detail Table -->
    <div class="bg-white rounded-xl border shadow-sm">
        <div class="p-5 border-b">
            <h3 class="font-bold text-lg">Detailed Forecast Recommendations</h3>
            <p class="text-sm text-gray-500">System-generated forecast based on recent activity.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Menu Item</th>
                        <th class="text-left px-6 py-4 font-semibold">Category</th>
                        <th class="text-left px-6 py-4 font-semibold">Predicted Demand</th>
                        <th class="text-left px-6 py-4 font-semibold">Confidence</th>
                        <th class="text-left px-6 py-4 font-semibold">Recommendation</th>
                    </tr>
                </thead>
                <tbody id="forecastDetailsBody">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            Loading forecast details...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let reportsData = {};
let salesOrdersTrendChart = null;
let revenueByCategoryChart = null;
let inventoryForecastChart = null;

function formatMoney(value) {
    return `₱${Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    })}`;
}

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function riskBadge(risk) {
    const value = String(risk || 'Low').toLowerCase();

    if (value === 'high') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-600">High</span>';
    }

    if (value === 'medium') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Medium</span>';
    }

    return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-600">Low</span>';
}

function renderSummaryCards(data) {
    document.getElementById('cardRevenue7d').textContent = formatMoney(data.total_revenue_7d);
    document.getElementById('cardAvgOrderValue').textContent = formatMoney(data.avg_order_value);
    document.getElementById('cardOrders7d').textContent = Number(data.total_orders_7d || 0).toLocaleString();

    const forecastRevenue = data.ai_forecasted_revenue_next_day || data.forecasted_revenue || 0;
    document.getElementById('cardForecastRevenue').textContent = formatMoney(forecastRevenue);

    document.getElementById('forecastModeText').textContent = data.forecast_mode || 'Smart estimate mode';
}

function renderAIForecast(data) {
    document.getElementById('aiForecastMode').textContent =
        data.forecast_mode || 'OpenAI-powered forecast';

    document.getElementById('aiSummaryText').textContent =
        data.ai_summary || 'No AI summary available yet.';

    document.getElementById('aiConfidenceBadge').textContent =
        `Confidence: ${data.ai_forecast_confidence || 'Low'}`;

    document.getElementById('aiRevenueForecast').textContent =
        formatMoney(data.ai_forecasted_revenue_next_day || 0);

    const list = document.getElementById('aiRecommendationsList');
    const recommendations = data.ai_recommendations || [];

    if (!recommendations.length) {
        list.innerHTML = '<p class="text-gray-400">No recommendations yet.</p>';
        return;
    }

    list.innerHTML = recommendations.map(item => `
        <div class="p-3 rounded-lg bg-white border border-orange-100">
            ${safeText(item)}
        </div>
    `).join('');
}

function renderAIMenuForecast(data) {
    const tbody = document.getElementById('aiMenuForecastBody');
    const rows = data.ai_menu_forecast || [];

    if (!rows.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                    No AI menu forecast available yet.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = rows.map(item => `
        <tr class="border-t hover:bg-gray-50">
            <td class="px-6 py-4 font-medium">${safeText(item.menu_item)}</td>
            <td class="px-6 py-4">${safeText(item.predicted_demand)}</td>
            <td class="px-6 py-4">${safeText(item.reason)}</td>
        </tr>
    `).join('');
}

function renderAIIngredientForecast(data) {
    const tbody = document.getElementById('aiIngredientForecastBody');
    const rows = data.ai_ingredient_forecast || [];

    if (!rows.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                    No AI ingredient forecast available yet.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = rows.map(item => `
        <tr class="border-t hover:bg-gray-50">
            <td class="px-6 py-4 font-medium">${safeText(item.ingredient)}</td>
            <td class="px-6 py-4">${Number(item.current_stock || 0).toFixed(2)} ${safeText(item.unit)}</td>
            <td class="px-6 py-4">${Number(item.suggested_restock || 0).toFixed(2)} ${safeText(item.unit)}</td>
            <td class="px-6 py-4">${riskBadge(item.risk_level)}</td>
        </tr>
    `).join('');
}

function renderSalesOrdersTrend(data) {
    const labels = (data.sales_order_trends || []).map(item => item.label);
    const sales = (data.sales_order_trends || []).map(item => Number(item.sales || 0));
    const orders = (data.sales_order_trends || []).map(item => Number(item.orders || 0));

    const ctx = document.getElementById('salesOrdersTrendChart').getContext('2d');

    if (salesOrdersTrendChart) {
        salesOrdersTrendChart.destroy();
    }

    salesOrdersTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Sales (₱)',
                    data: sales,
                    borderColor: '#fb923c',
                    backgroundColor: 'rgba(251, 146, 60, 0.18)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4
                },
                {
                    label: 'Orders',
                    data: orders,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.10)',
                    fill: false,
                    tension: 0.35,
                    pointRadius: 4,
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
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => `₱${value}`
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

function renderRevenueByCategory(data) {
    const labels = (data.revenue_by_category || []).map(item => item.category);
    const values = (data.revenue_by_category || []).map(item => Number(item.revenue || 0));

    const ctx = document.getElementById('revenueByCategoryChart').getContext('2d');

    if (revenueByCategoryChart) {
        revenueByCategoryChart.destroy();
    }

    revenueByCategoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Revenue',
                data: values,
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
                    beginAtZero: true,
                    ticks: {
                        callback: value => `₱${value}`
                    }
                }
            }
        }
    });
}

function renderInventoryForecast(data) {
    const labels = (data.inventory_usage_forecast || []).map(item => item.ingredient);
    const used = (data.inventory_usage_forecast || []).map(item => Number(item.used_quantity || 0));
    const forecast = (data.inventory_usage_forecast || []).map(item => Number(item.forecast_quantity || 0));

    const ctx = document.getElementById('inventoryForecastChart').getContext('2d');

    if (inventoryForecastChart) {
        inventoryForecastChart.destroy();
    }

    inventoryForecastChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Used Quantity',
                    data: used,
                    backgroundColor: '#d6a46f',
                    borderRadius: 6
                },
                {
                    label: 'Forecast Quantity',
                    data: forecast,
                    backgroundColor: '#fb923c',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
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

function renderForecastTable(data) {
    const tbody = document.getElementById('forecastDetailsBody');
    const rows = data.forecast_details || [];

    if (!rows.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                    No forecast details available yet.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = rows.map(item => `
        <tr class="border-t hover:bg-gray-50">
            <td class="px-6 py-4 font-medium">${safeText(item.name)}</td>
            <td class="px-6 py-4">${safeText(item.category)}</td>
            <td class="px-6 py-4">${safeText(item.predicted_demand)}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-600">
                    ${safeText(item.confidence)}
                </span>
            </td>
            <td class="px-6 py-4">${safeText(item.recommendation)}</td>
        </tr>
    `).join('');
}

function downloadReportsCsv() {
    if (!reportsData) {
        alert('No report data to export yet.');
        return;
    }

    const rows = [
        ['Reports & Forecast'],
        ['Total Revenue (7d)', reportsData.total_revenue_7d || 0],
        ['Avg Order Value', reportsData.avg_order_value || 0],
        ['Total Orders (7d)', reportsData.total_orders_7d || 0],
        ['Forecasted Revenue', reportsData.ai_forecasted_revenue_next_day || reportsData.forecasted_revenue || 0],
        ['Forecast Mode', reportsData.forecast_mode || 'Smart estimate'],
        ['AI Summary', reportsData.ai_summary || ''],
        [],
        ['AI Recommendations']
    ];

    (reportsData.ai_recommendations || []).forEach(item => {
        rows.push([item]);
    });

    rows.push([]);
    rows.push(['Menu Item', 'Category', 'Predicted Demand', 'Confidence', 'Recommendation']);

    (reportsData.forecast_details || []).forEach(item => {
        rows.push([
            item.name,
            item.category,
            item.predicted_demand,
            item.confidence,
            item.recommendation
        ]);
    });

    const csv = rows.map(row =>
        row.map(value => `"${String(value ?? '').replaceAll('"', '""')}"`).join(',')
    ).join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'reports_forecast.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function loadReportsForecast() {
    try {
        const res = await fetch('/api/admin/reports-forecast', {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            throw new Error(`API returned ${res.status}`);
        }

        const data = await res.json();

        console.log('Reports & Forecast API:', data);

        reportsData = data;

        renderSummaryCards(data);

        try { renderAIForecast(data); } catch (e) { console.error('AI forecast render error:', e); }
        try { renderAIMenuForecast(data); } catch (e) { console.error('AI menu forecast render error:', e); }
        try { renderAIIngredientForecast(data); } catch (e) { console.error('AI ingredient forecast render error:', e); }

        try { renderSalesOrdersTrend(data); } catch (e) { console.error('Trend chart error:', e); }
        try { renderRevenueByCategory(data); } catch (e) { console.error('Category chart error:', e); }
        try { renderInventoryForecast(data); } catch (e) { console.error('Inventory forecast chart error:', e); }
        try { renderForecastTable(data); } catch (e) { console.error('Forecast table error:', e); }

    } catch (error) {
        console.error('Failed to load reports & forecast:', error);

        document.getElementById('aiSummaryText').textContent = 'Failed to load AI forecast.';
        document.getElementById('forecastDetailsBody').innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-red-500">
                    Failed to load reports & forecast data.
                </td>
            </tr>
        `;
    }
}

loadReportsForecast();
</script>

@endsection