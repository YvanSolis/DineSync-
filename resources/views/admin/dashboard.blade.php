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

            <button
                id="dashboardRefreshBtn"
                type="button"
                onclick="manualRefreshDashboard()"
                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white hover:bg-orange-50 px-5 py-3 text-sm font-bold text-orange-600 shadow-sm transition disabled:opacity-60"
            >
                <span id="dashboardRefreshIcon" class="inline-block">↻</span>
                Refresh
            </button>

            <div class="dashboard-live-card bg-white border border-orange-100 rounded-2xl px-4 py-3 shadow-sm w-full sm:w-auto">
                <div class="flex items-center gap-2">
                    <span id="dashboardLiveDot" class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <p id="dashboardLiveLabel" class="text-xs font-bold text-emerald-600 uppercase tracking-wide">Live</p>
                </div>
                <p id="dashboardUpdatedAt" class="text-sm font-bold text-gray-800 mt-1">Loading dashboard...</p>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="dashboard-summary-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Orders Today</p>
                    <h2 id="cardOrdersToday" class="dashboard-counter text-2xl sm:text-3xl font-extrabold text-gray-900"><span class="dashboard-skeleton dashboard-skeleton-value"></span></h2>
                    <p class="text-xs text-gray-400 mt-2">Total orders created today</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500 shrink-0">
                    📦
                </div>
            </div>
        </div>

        <div class="dashboard-summary-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Sales Today</p>
                    <h2 id="cardSalesToday" class="dashboard-counter text-2xl sm:text-3xl font-extrabold text-gray-900"><span class="dashboard-skeleton dashboard-skeleton-value"></span></h2>
                    <p class="text-xs text-gray-400 mt-2">Paid or completed sales today</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center text-green-500 shrink-0">
                    ₱
                </div>
            </div>
        </div>

        <div class="dashboard-summary-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Active Orders</p>
                    <h2 id="cardActiveOrders" class="dashboard-counter text-2xl sm:text-3xl font-extrabold text-gray-900"><span class="dashboard-skeleton dashboard-skeleton-value"></span></h2>
                    <p class="text-xs text-gray-400 mt-2">Pending, preparing, or ready today</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-yellow-50 flex items-center justify-center text-yellow-500 shrink-0">
                    ⏳
                </div>
            </div>
        </div>

        <div class="dashboard-summary-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-2">Inventory Alerts</p>
                    <h2 id="cardInventoryAlerts" class="dashboard-counter text-2xl sm:text-3xl font-extrabold text-gray-900"><span class="dashboard-skeleton dashboard-skeleton-value"></span></h2>
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
                <div class="dashboard-list-skeleton space-y-3">
                    <div class="dashboard-skeleton-row"></div>
                    <div class="dashboard-skeleton-row"></div>
                    <div class="dashboard-skeleton-row"></div>
                </div>
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
                <div class="dashboard-list-skeleton space-y-3">
                    <div class="dashboard-skeleton-row"></div>
                    <div class="dashboard-skeleton-row"></div>
                </div>
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
                <div class="dashboard-list-skeleton space-y-3">
                    <div class="dashboard-skeleton-row"></div>
                    <div class="dashboard-skeleton-row"></div>
                </div>
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
                <div class="dashboard-list-skeleton space-y-3">
                    <div class="dashboard-skeleton-row"></div>
                    <div class="dashboard-skeleton-row"></div>
                    <div class="dashboard-skeleton-row"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
    .dashboard-summary-card {
        position: relative;
        overflow: hidden;
        transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
    }

    .dashboard-summary-card::after {
        content: "";
        position: absolute;
        width: 110px;
        height: 110px;
        right: -52px;
        bottom: -58px;
        border-radius: 9999px;
        background: rgba(249, 115, 22, 0.06);
        pointer-events: none;
    }

    .dashboard-summary-card:hover {
        transform: translateY(-2px);
        border-color: rgba(249, 115, 22, 0.22);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
    }

    .dashboard-skeleton {
        display: inline-block;
        border-radius: 10px;
        background: linear-gradient(90deg, #f3f4f6 25%, #ffffff 50%, #f3f4f6 75%);
        background-size: 200% 100%;
        animation: dashboardShimmer 1.25s infinite linear;
    }

    .dashboard-skeleton-value {
        width: 110px;
        height: 34px;
        vertical-align: middle;
    }

    .dashboard-skeleton-row {
        height: 72px;
        border-radius: 16px;
        border: 1px solid #f3f4f6;
        background: linear-gradient(90deg, #f9fafb 25%, #ffffff 50%, #f9fafb 75%);
        background-size: 200% 100%;
        animation: dashboardShimmer 1.25s infinite linear;
    }

    .dashboard-empty-state {
        padding: 28px 18px;
        border: 1px dashed #fed7aa;
        border-radius: 18px;
        background: linear-gradient(135deg, #fff7ed, #ffffff);
        text-align: center;
    }

    .dashboard-empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: #ffedd5;
        font-size: 22px;
    }

    .dashboard-live-card {
        min-width: 190px;
    }

    @keyframes dashboardShimmer {
        from { background-position: 200% 0; }
        to { background-position: -200% 0; }
    }

    @keyframes dashboardSpin {
        to { transform: rotate(360deg); }
    }

    .dashboard-spin {
        animation: dashboardSpin 0.8s linear infinite;
    }

    @media (max-width: 640px) {
        .dashboard-live-card { min-width: 0; }
        .dashboard-summary-card:hover { transform: none; }
    }

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
let dashboardLoading = false;
let dashboardLastUpdated = null;
let dashboardLastValues = {
    orders: 0,
    sales: 0,
    active: 0,
    alerts: 0,
};

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

function animateNumber(element, from, to, formatter, duration = 550) {
    if (!element) return;

    const startTime = performance.now();
    const difference = to - from;

    function frame(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        element.textContent = formatter(from + (difference * eased));

        if (progress < 1) {
            requestAnimationFrame(frame);
        }
    }

    requestAnimationFrame(frame);
}

function renderCards(data) {
    const totalOrders = Number(pick(data, ['orders_today', 'total_orders_today', 'today_orders'], 0));
    const totalSales = Number(pick(data, ['sales_today', 'total_sales_today', 'today_sales'], 0));
    const activeOrders = Number(pick(data, ['active_orders', 'pending_orders', 'ongoing_orders'], 0));
    const inventoryAlerts = Number(pick(data, ['inventory_alert_count', 'low_stock_count'], 0));

    animateNumber(document.getElementById('cardOrdersToday'), dashboardLastValues.orders, totalOrders, value => formatNumber(Math.round(value)));
    animateNumber(document.getElementById('cardSalesToday'), dashboardLastValues.sales, totalSales, value => formatMoney(value));
    animateNumber(document.getElementById('cardActiveOrders'), dashboardLastValues.active, activeOrders, value => formatNumber(Math.round(value)));
    animateNumber(document.getElementById('cardInventoryAlerts'), dashboardLastValues.alerts, inventoryAlerts, value => formatNumber(Math.round(value)));

    dashboardLastValues = {
        orders: totalOrders,
        sales: totalSales,
        active: activeOrders,
        alerts: inventoryAlerts,
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
        container.innerHTML = `
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-icon">📦</div>
                <p class="font-bold text-gray-800">No recent orders yet</p>
                <p class="text-sm text-gray-500 mt-1">New orders created today will appear here.</p>
            </div>
        `;
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
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 p-4 rounded-2xl border border-gray-100 bg-white hover:border-orange-200 hover:shadow-sm transition">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold truncate">${safeText(orderNumber)}</p>
                    <p class="text-xs text-gray-500 truncate">
                        <span class="inline-flex items-center rounded-full bg-orange-50 px-2 py-0.5 font-semibold text-orange-700">Table ${safeText(tableNumber)}</span> ${time ? '• ' + safeText(time) : ''}
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
        container.innerHTML = `
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-icon">✓</div>
                <p class="font-bold text-gray-800">Inventory looks healthy</p>
                <p class="text-sm text-gray-500 mt-1">No low-stock or expiry alerts were found.</p>
            </div>
        `;
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
        container.innerHTML = `
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-icon">🍽</div>
                <p class="font-bold text-gray-800">All menu items are available</p>
                <p class="text-sm text-gray-500 mt-1">No item is blocked by insufficient ingredients.</p>
            </div>
        `;
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
        container.innerHTML = `
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-icon">◈</div>
                <p class="font-bold text-gray-800">No ingredient usage yet</p>
                <p class="text-sm text-gray-500 mt-1">Stock deductions from today's orders will appear here.</p>
            </div>
        `;
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

function setDashboardConnectionState(state, message = '') {
    const dot = document.getElementById('dashboardLiveDot');
    const label = document.getElementById('dashboardLiveLabel');
    const updated = document.getElementById('dashboardUpdatedAt');

    if (!dot || !label || !updated) return;

    dot.className = 'w-2.5 h-2.5 rounded-full';

    if (state === 'loading') {
        dot.classList.add('bg-orange-400', 'animate-pulse');
        label.className = 'text-xs font-bold text-orange-600 uppercase tracking-wide';
        label.textContent = 'Updating';
        updated.textContent = message || 'Fetching latest data...';
        return;
    }

    if (state === 'error') {
        dot.classList.add('bg-red-500');
        label.className = 'text-xs font-bold text-red-600 uppercase tracking-wide';
        label.textContent = 'Offline';
        updated.textContent = message || 'Could not refresh dashboard';
        return;
    }

    dot.classList.add('bg-emerald-500', 'animate-pulse');
    label.className = 'text-xs font-bold text-emerald-600 uppercase tracking-wide';
    label.textContent = 'Live';
    updated.textContent = message || 'Updated just now';
}

function setDashboardRefreshLoading(isLoading) {
    const button = document.getElementById('dashboardRefreshBtn');
    const icon = document.getElementById('dashboardRefreshIcon');

    if (button) button.disabled = isLoading;
    if (icon) icon.classList.toggle('dashboard-spin', isLoading);
}

async function loadDashboard(showLoadingState = false) {
    if (dashboardLoading) return;

    dashboardLoading = true;
    setDashboardRefreshLoading(true);

    if (showLoadingState || !dashboardLastUpdated) {
        setDashboardConnectionState('loading');
    }

    try {
        const res = await fetch('/api/admin/dashboard', {
            headers: {
                'Accept': 'application/json'
            },
            cache: 'no-store'
        });

        if (!res.ok) {
            throw new Error(`Dashboard API returned ${res.status}`);
        }

        const data = await res.json();
        dashboardData = data;

        try { renderCards(data); } catch (e) { console.error('Cards render error:', e); }
        try { renderTopItemsChart(data); } catch (e) { console.error('Top items chart error:', e); }
        try { renderRecentOrders(data); } catch (e) { console.error('Recent orders error:', e); }
        try { renderInventoryAlerts(data); } catch (e) { console.error('Inventory alerts error:', e); }
        try { renderUnavailableMenuItems(data); } catch (e) { console.error('Unavailable menu error:', e); }
        try { renderIngredientUsage(data); } catch (e) { console.error('Ingredient usage error:', e); }

        dashboardLastUpdated = new Date();
        setDashboardConnectionState('success', `Updated ${dashboardLastUpdated.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}`);
    } catch (error) {
        console.error('Dashboard load failed:', error);
        setDashboardConnectionState('error', 'Refresh failed. Existing data kept.');
    } finally {
        dashboardLoading = false;
        setDashboardRefreshLoading(false);
    }
}

function manualRefreshDashboard() {
    loadDashboard(true);
}

function startDashboardLoading() {
    loadDashboard(true);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startDashboardLoading);
} else {
    startDashboardLoading();
}

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        loadDashboard(false);
    }
});

window.addEventListener('focus', () => {
    loadDashboard(false);
});

setInterval(() => {
    if (!document.hidden) {
        loadDashboard(false);
    }
}, 30000);
</script>

@endsection