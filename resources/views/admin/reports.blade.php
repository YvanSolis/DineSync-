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

    <!-- Forecast Detail Table -->
    <div class="bg-white rounded-xl border shadow-sm">
        <div class="p-5 border-b">
            <h3 class="font-bold text-lg">Detailed Forecast Recommendations</h3>
            <p class="text-sm text-gray-500">Presentation-friendly summary while OpenAI integration is not yet connected.</p>
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

function renderSummaryCards(data) {
    document.getElementById('cardRevenue7d').textContent = formatMoney(data.total_revenue_7d);
    document.getElementById('cardAvgOrderValue').textContent = formatMoney(data.avg_order_value);
    document.getElementById('cardOrders7d').textContent = Number(data.total_orders_7d || 0).toLocaleString();
    document.getElementById('cardForecastRevenue').textContent = formatMoney(data.forecasted_revenue);
    document.getElementById('forecastModeText').textContent = data.forecast_mode || 'Smart estimate mode';
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
    if (!reportsData || !reportsData.forecast_details) {
        alert('No report data to export yet.');
        return;
    }

    const rows = [
        ['Reports & Forecast'],
        ['Total Revenue (7d)', reportsData.total_revenue_7d || 0],
        ['Avg Order Value', reportsData.avg_order_value || 0],
        ['Total Orders (7d)', reportsData.total_orders_7d || 0],
        ['Forecasted Revenue', reportsData.forecasted_revenue || 0],
        ['Forecast Mode', reportsData.forecast_mode || 'Smart estimate'],
        [],
        ['Menu Item', 'Category', 'Predicted Demand', 'Confidence', 'Recommendation']
    ];

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

        const data = await res.json();

        console.log('Reports & Forecast API:', data);

        reportsData = data;

        renderSummaryCards(data);

        try { renderSalesOrdersTrend(data); } catch (e) { console.error('Trend chart error:', e); }
        try { renderRevenueByCategory(data); } catch (e) { console.error('Category chart error:', e); }
        try { renderInventoryForecast(data); } catch (e) { console.error('Inventory forecast chart error:', e); }
        try { renderForecastTable(data); } catch (e) { console.error('Forecast table error:', e); }

    } catch (error) {
        console.error('Failed to load reports & forecast:', error);
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