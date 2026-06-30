@extends('layouts.admin')

@section('content')

<div class="dashboard-print-area space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">Dashboard Overview</h1>
            <p class="text-sm sm:text-base text-gray-500">
                Today’s restaurant operations, sales, orders, and ingredient-based inventory status.
            </p>
        </div>

        <div class="no-print flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <button
                type="button"
                onclick="printDashboard()"
                class="inline-flex items-center justify-center rounded-2xl bg-orange-500 hover:bg-orange-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition"
            >
                Print / Save as PDF
            </button>

            <div class="bg-white border border-orange-100 rounded-2xl px-4 py-3 shadow-sm w-full sm:w-auto">
                <p class="text-xs text-gray-500">Dashboard Scope</p>
                <p class="text-sm font-bold text-orange-500">Today / Daily Monitoring</p>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Orders Today</p>
                    <h2 id="cardOrdersToday" class="text-2xl sm:text-3xl font-bold">0</h2>
                    <p class="text-xs text-gray-400 mt-2">Total orders created today</p>
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
                    <p class="text-xs text-gray-400 mt-2">Paid or completed sales today</p>
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
                    <p class="text-xs text-gray-400 mt-2">Pending, preparing, or ready today</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-yellow-50 flex items-center justify-center text-yellow-500 shrink-0">
                    ⏳
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Inventory Alerts</p>
                    <h2 id="cardInventoryAlerts" class="text-2xl sm:text-3xl font-bold">0</h2>
                    <p class="text-xs text-red-400 mt-2">Out of stock, low stock, or near expiry</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center text-red-500 shrink-0">
                    ⚠️
                </div>
            </div>
        </div>
    </div>

    <!-- Main Panels -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Top Selling Items Today</h3>
                <p class="text-sm text-gray-500">
                    Most ordered menu items for today’s operations.
                </p>
            </div>

            <div class="h-56 sm:h-64 lg:h-72">
                <canvas id="topItemsChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Recent Orders Today</h3>
                <p class="text-sm text-gray-500">
                    Latest orders created today.
                </p>
            </div>

            <div id="recentOrdersList" class="space-y-3">
                <p class="text-sm text-gray-400">No active or pending orders right now.</p>
            </div>
        </div>
    </div>

    <!-- Inventory Panels -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Inventory Alerts Today</h3>
                <p class="text-sm text-gray-500">
                    Ingredients that need attention based on usable stock and expiry.
                </p>
            </div>

            <div id="inventoryAlertsList" class="space-y-3">
                <p class="text-sm text-gray-400">No inventory alerts today.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Unavailable Menu Items</h3>
                <p class="text-sm text-gray-500">
                    Menu items currently unavailable because of insufficient linked ingredients.
                </p>
            </div>

            <div id="unavailableMenuList" class="space-y-3">
                <p class="text-sm text-gray-400">No unavailable menu items.</p>
            </div>
        </div>
    </div>

    <!-- Ingredient Usage -->
    <div class="grid grid-cols-1 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Ingredient Usage Today</h3>
                <p class="text-sm text-gray-500">
                    Ingredients deducted from stock based on today’s orders.
                </p>
            </div>

            <div id="ingredientUsageList" class="space-y-3">
                <p class="text-sm text-gray-400">No ingredient usage recorded today.</p>
            </div>
        </div>
    </div>
</div>


<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        html,
        body {
            background: #ffffff !important;
            color: #111827 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .no-print,
        aside,
        nav,
        header,
        footer,
        [class*="sidebar"],
        [class*="Sidebar"],
        button {
            display: none !important;
        }

        main,
        .content,
        .dashboard-print-area {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .space-y-5,
        .space-y-6 {
            gap: 12px !important;
        }

        .grid {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .rounded-2xl,
        .rounded-xl {
            border-radius: 10px !important;
        }

        .shadow-sm,
        .shadow,
        .shadow-lg,
        .shadow-xl,
        .shadow-2xl {
            box-shadow: none !important;
        }

        .bg-white {
            background: #ffffff !important;
        }

        canvas {
            max-width: 100% !important;
        }

        a[href]::after {
            content: "" !important;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
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
        minimumFractionDigits: 2,
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

function shortLabel(value, max = 24) {
    const text = String(value ?? 'Unknown');

    if (text.length <= max) {
        return text;
    }

    return text.substring(0, max - 3) + '...';
}

function statusBadge(status) {
    const normalized = String(status || '').toLowerCase();

    const map = {
        pending: 'bg-yellow-100 text-yellow-700',
        preparing: 'bg-orange-100 text-orange-700',
        ready: 'bg-blue-100 text-blue-700',
        served: 'bg-green-100 text-green-700',
        completed: 'bg-green-100 text-green-700',
        paid: 'bg-green-100 text-green-700',
        unpaid: 'bg-red-100 text-red-700',
        out_of_stock: 'bg-red-100 text-red-700',
        low_stock: 'bg-yellow-100 text-yellow-700',
        reorder_soon: 'bg-orange-100 text-orange-700',
        near_expiry: 'bg-blue-100 text-blue-700',
        active: 'bg-green-100 text-green-700',
        awaiting_payment: 'bg-blue-100 text-blue-700',
    };

    const labelMap = {
        out_of_stock: 'Out of Stock',
        low_stock: 'Low Stock',
        reorder_soon: 'Reorder Soon',
        near_expiry: 'Near Expiry',
        active: 'Healthy',
        awaiting_payment: 'Awaiting Payment',
    };

    return `
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold ${map[normalized] || 'bg-gray-100 text-gray-600'}">
            ${safeText(labelMap[normalized] || status || 'Unknown')}
        </span>
    `;
}

function renderCards(data) {
    const totalOrders = pick(data, ['orders_today', 'total_orders_today', 'today_orders'], 0);
    const totalSales = pick(data, ['sales_today', 'total_sales_today', 'today_sales'], 0);
    const activeOrders = pick(data, ['active_orders', 'pending_orders', 'ongoing_orders'], 0);
    const inventoryAlerts = pick(data, ['inventory_alert_count', 'low_stock_count'], 0);

    document.getElementById('cardOrdersToday').textContent = formatNumber(totalOrders);
    document.getElementById('cardSalesToday').textContent = formatMoney(totalSales);
    document.getElementById('cardActiveOrders').textContent = formatNumber(activeOrders);
    document.getElementById('cardInventoryAlerts').textContent = formatNumber(inventoryAlerts);
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
                backgroundColor: '#f97316',
                borderRadius: 8,
                barPercentage: 0.72,
                categoryPercentage: 0.72,
                maxBarThickness: 28
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 150,
            layout: {
                padding: {
                    top: 4,
                    right: 8,
                    bottom: 4,
                    left: 0
                }
            },
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
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0,
                        stepSize: 5,
                        font: {
                            size: window.innerWidth < 640 ? 10 : 11
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        autoSkip: false,
                        font: {
                            size: window.innerWidth < 640 ? 10 : 11
                        },
                        callback: function(value) {
                            const label = this.getLabelForValue(value);
                            return window.innerWidth < 640 ? shortLabel(label, 16) : shortLabel(label, 24);
                        }
                    }
                }
            }
        }
    });
}

function renderRecentOrders(data) {
    const container = document.getElementById('recentOrdersList');
    const orders = pick(data, ['recent_orders_today', 'recent_orders'], []);

    if (!Array.isArray(orders) || orders.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No active or pending orders right now.</p>';
        return;
    }

    container.innerHTML = orders.slice(0, 8).map(order => {
        const orderNumber = pick(order, ['order_number'], `Order #${pick(order, ['id'], '')}`);
        const tableNumber = pick(order, ['table_number'], '—');
        const status = pick(order, ['status'], 'pending');
        const total = pick(order, ['total_amount', 'amount'], 0);
        const time = pick(order, ['time', 'created_time'], '');

        const paymentStatus = pick(order, ['payment_status'], null);
        const method = pick(order, ['payment_method'], null);
        const itemsSummary = pick(order, ['items_summary', 'items_text'], 'No items listed');
        const shownStatus = String(status || '').toLowerCase() === 'awaiting_payment'
            ? 'awaiting_payment'
            : status;

        return `
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 p-3 rounded-xl border bg-gray-50 hover:bg-orange-50/40 transition">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold truncate">${safeText(orderNumber)}</p>
                    <p class="text-xs text-gray-500 truncate">
                        Table ${safeText(tableNumber)} ${time ? '• ' + safeText(time) : ''}
                        ${method ? '• ' + safeText(method) : ''}
                    </p>
                    <p class="text-xs sm:text-sm text-gray-700 mt-1 line-clamp-2">
                        ${safeText(itemsSummary)}
                    </p>
                </div>

                <div class="sm:text-right shrink-0 flex sm:block items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-gray-900">${formatMoney(total)}</p>
                        ${paymentStatus ? `<p class="text-[11px] text-gray-400">${safeText(paymentStatus)}</p>` : ''}
                    </div>
                    <div class="mt-0 sm:mt-1">${statusBadge(shownStatus)}</div>
                </div>
            </div>
        `;
    }).join('');
}

function renderInventoryAlerts(data) {
    const container = document.getElementById('inventoryAlertsList');
    const alerts = pick(data, ['inventory_alerts', 'low_stock_alerts'], []);

    if (!Array.isArray(alerts) || alerts.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No inventory alerts today.</p>';
        return;
    }

    container.innerHTML = alerts.slice(0, 8).map(item => {
        const name = pick(item, ['name', 'ingredient_name'], 'Unknown Ingredient');
        const stock = pick(item, ['current_stock', 'total_stock', 'stock'], 0);
        const unit = pick(item, ['unit'], 'unit');
        const threshold = pick(item, ['threshold'], 0);
        const status = pick(item, ['stock_status', 'status'], 'low_stock');
        const expiry = pick(item, ['nearest_expiry_date', 'expiry_date'], null);

        return `
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-xl border bg-orange-50/50 border-orange-100">
                <div class="min-w-0">
                    <p class="font-semibold truncate">${safeText(name)}</p>
                    <p class="text-xs text-gray-500">
                        Stock: ${formatNumber(stock)} ${safeText(unit)}
                        ${threshold ? `• Threshold: ${formatNumber(threshold)}` : ''}
                        ${expiry ? `• Expiry: ${safeText(expiry)}` : ''}
                    </p>
                </div>

                <div class="shrink-0">
                    ${statusBadge(status)}
                </div>
            </div>
        `;
    }).join('');
}

function renderUnavailableMenuItems(data) {
    const container = document.getElementById('unavailableMenuList');
    const items = pick(data, ['unavailable_menu_items', 'affected_menu_items'], []);

    if (!Array.isArray(items) || items.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No unavailable menu items.</p>';
        return;
    }

    container.innerHTML = items.slice(0, 8).map(item => {
        const name = pick(item, ['name', 'menu_item', 'item_name'], 'Unknown Item');
        const category = pick(item, ['category'], 'Uncategorized');
        const reason = pick(item, ['reason', 'stock_label'], 'Insufficient linked ingredients.');

        return `
            <div class="p-3 rounded-xl border bg-red-50 border-red-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-red-700 truncate">${safeText(name)}</p>
                        <p class="text-xs text-gray-500 truncate">${safeText(category)}</p>
                    </div>

                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-100 text-red-700">
                        Unavailable
                    </span>
                </div>

                <p class="text-xs text-red-600 mt-2">${safeText(reason)}</p>
            </div>
        `;
    }).join('');
}

function renderIngredientUsage(data) {
    const container = document.getElementById('ingredientUsageList');
    const usage = pick(data, ['ingredient_usage_today', 'ingredient_usage'], []);

    if (!Array.isArray(usage) || usage.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No ingredient usage recorded today.</p>';
        return;
    }

    container.innerHTML = usage.slice(0, 10).map(item => {
        const name = pick(item, ['name', 'ingredient_name'], 'Unknown Ingredient');
        const used = pick(item, ['quantity_used', 'used', 'quantity'], 0);
        const unit = pick(item, ['unit'], 'unit');
        const menuItems = pick(item, ['menu_items', 'used_by'], []);

        return `
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-xl border bg-gray-50">
                <div class="min-w-0">
                    <p class="font-semibold truncate">${safeText(name)}</p>
                    <p class="text-xs text-gray-500 truncate">
                        ${Array.isArray(menuItems) && menuItems.length ? 'Used by: ' + safeText(menuItems.join(', ')) : 'Used in today’s orders'}
                    </p>
                </div>

                <div class="sm:text-right shrink-0">
                    <p class="text-sm font-bold text-orange-600">
                        ${formatNumber(used)} ${safeText(unit)}
                    </p>
                    <p class="text-xs text-gray-500">Used today</p>
                </div>
            </div>
        `;
    }).join('');
}


function printDashboard() {
    setTimeout(() => {
        window.print();
    }, 100);
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
        dashboardData = data;

        try { renderCards(data); } catch (e) { console.error('Cards render error:', e); }
        try { renderTopItemsChart(data); } catch (e) { console.error('Top items chart error:', e); }
        try { renderRecentOrders(data); } catch (e) { console.error('Recent orders error:', e); }
        try { renderInventoryAlerts(data); } catch (e) { console.error('Inventory alerts error:', e); }
        try { renderUnavailableMenuItems(data); } catch (e) { console.error('Unavailable menu error:', e); }
        try { renderIngredientUsage(data); } catch (e) { console.error('Ingredient usage error:', e); }

    } catch (error) {
        console.error('Dashboard load failed:', error);
    }
}

function startDashboardLoading() {
    loadDashboard();

    setTimeout(() => {
        loadDashboard();
    }, 700);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startDashboardLoading);
} else {
    startDashboardLoading();
}

window.addEventListener('load', () => {
    loadDashboard();
});

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        loadDashboard();
    }
});

window.addEventListener('focus', () => {
    loadDashboard();
});

setInterval(() => {
    loadDashboard();
}, 30000);
</script>

@endsection