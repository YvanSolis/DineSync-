@extends('layouts.admin')

@section('content')


<style>
    @keyframes reportsFadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes reportsPulse {
        0%, 100% { opacity: .55; }
        50% { opacity: 1; }
    }

    @keyframes toastSlideIn {
        from { opacity: 0; transform: translate3d(24px, -8px, 0) scale(.96); }
        to { opacity: 1; transform: translate3d(0, 0, 0) scale(1); }
    }

    @keyframes toastSlideOut {
        from { opacity: 1; transform: translate3d(0, 0, 0) scale(1); }
        to { opacity: 0; transform: translate3d(24px, -8px, 0) scale(.96); }
    }

    .reports-card {
        position: relative;
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .reports-card::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        right: -52px;
        bottom: -68px;
        border-radius: 999px;
        background: rgba(249, 115, 22, .06);
        pointer-events: none;
    }

    .reports-card:hover {
        transform: translateY(-3px);
        border-color: rgba(249, 115, 22, .25);
        box-shadow: 0 18px 40px rgba(15, 23, 42, .09);
    }

    .reports-panel {
        animation: reportsFadeUp .35s ease both;
    }

    .reports-skeleton {
        position: relative;
        overflow: hidden;
        background: #f3f4f6;
        animation: reportsPulse 1.15s ease-in-out infinite;
    }

    .reports-chart-shell {
        position: relative;
        border-radius: 1.25rem;
        background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(249,250,251,.9));
    }

    .reports-chart-loading {
        position: absolute;
        inset: 0;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, .82);
        backdrop-filter: blur(4px);
    }

    .reports-chart-loading.hidden { display: none; }

    .reports-toast {
        animation: toastSlideIn .22s ease both;
    }

    .reports-toast.is-hiding {
        animation: toastSlideOut .2s ease both;
    }

    @media (max-width: 640px) {
        #reportsToastContainer {
            left: 1rem;
            right: 1rem;
            top: 5rem;
        }

        .reports-toast {
            width: 100%;
        }
    }
</style>

<div id="reportsToastContainer" class="fixed top-5 right-5 z-[100] space-y-3 pointer-events-none"></div>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">Reports & Forecast</h1>
            <p class="text-sm sm:text-base text-gray-500">
                Review the last 7 days of sales, order trends, category revenue, and menu demand forecast.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full sm:w-auto">
            <button type="button"
                class="border border-orange-100 rounded-xl px-4 py-2.5 text-sm bg-white text-gray-700 font-semibold">
                Last 7 Days
            </button>

            <button id="refreshForecastBtn" onclick="refreshForecast(event)"
                class="inline-flex items-center justify-center gap-2 border border-orange-200 bg-orange-500 text-white hover:bg-orange-600 px-4 py-2.5 rounded-xl font-semibold text-sm shadow-sm transition disabled:opacity-70 disabled:cursor-not-allowed">
                <span id="refreshForecastIcon">↻</span>
                <span id="refreshForecastText">Refresh Forecast</span>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="reports-card reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-gray-500 mb-2">Total Revenue</p>
                    <h2 id="cardRevenue7d" class="text-2xl sm:text-3xl font-bold">₱0.00</h2>
                    <p class="text-xs text-gray-400 mt-2">Revenue from the last 7 days</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center text-green-500 shrink-0">
                    ₱
                </div>
            </div>
        </div>

        <div class="reports-card reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-gray-500 mb-2">Average Order Value</p>
                    <h2 id="cardAvgOrderValue" class="text-2xl sm:text-3xl font-bold">₱0.00</h2>
                    <p class="text-xs text-gray-400 mt-2">Average revenue per order</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500 shrink-0">
                    📊
                </div>
            </div>
        </div>

        <div class="reports-card reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-gray-500 mb-2">Total Orders</p>
                    <h2 id="cardOrders7d" class="text-2xl sm:text-3xl font-bold">0</h2>
                    <p class="text-xs text-gray-400 mt-2">Orders from the last 7 days</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                    🧾
                </div>
            </div>
        </div>

        <div class="reports-card reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-gray-500 mb-2">Forecasted Revenue</p>
                    <h2 id="cardForecastRevenue" class="text-2xl sm:text-3xl font-bold">₱0.00</h2>
                    <p id="forecastModeText" class="text-xs text-gray-400 mt-2">System forecast</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 shrink-0">
                    🔮
                </div>
            </div>
        </div>
    </div>

    <!-- Forecast Insight -->
    <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b bg-gradient-to-r from-orange-50 via-white to-amber-50">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-[11px] font-semibold uppercase tracking-wide">
                            Forecast Insight
                        </span>

                        <span id="confidenceBadge" class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-600 w-fit">
                            Confidence: -
                        </span>
                    </div>

                    <h3 class="font-bold text-base sm:text-lg">7-Day Performance Summary</h3>
                    <p id="forecastMode" class="text-sm text-gray-500 mt-1">Loading forecast...</p>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-5">
            <p id="summaryText" class="text-sm sm:text-base text-gray-700 leading-7">
                Waiting for forecast...
            </p>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-5">
                <div class="border rounded-2xl p-4 bg-orange-50 border-orange-100">
                    <h4 class="font-bold mb-3">System Recommendations</h4>
                    <div id="recommendationsList" class="space-y-2 text-sm text-gray-700">
                        <p class="text-gray-400">No recommendations yet.</p>
                    </div>
                </div>

                <div class="border rounded-2xl p-4 bg-green-50 border-green-100">
                    <h4 class="font-bold mb-3">Next-Day Revenue Forecast</h4>
                    <p class="text-sm text-gray-500">Estimated revenue for the next operating day based on recent 7-day performance.</p>
                    <h2 id="revenueForecast" class="text-2xl sm:text-3xl font-bold mt-3">₱0.00</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales & Orders Trend -->
    <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 min-w-0">
        <div class="mb-4">
            <h3 class="font-bold text-base sm:text-lg">Sales & Order Trends</h3>
            <p class="text-sm text-gray-500">Sales and order performance over the last 7 days.</p>
        </div>

        <div class="reports-chart-shell h-72 sm:h-96">
            <div id="salesOrdersTrendLoading" class="reports-chart-loading">
                <div class="text-center">
                    <div class="mx-auto h-10 w-10 rounded-full border-4 border-orange-100 border-t-orange-500 animate-spin"></div>
                    <p class="mt-3 text-xs font-semibold text-gray-500">Loading sales trend...</p>
                </div>
            </div>
            <canvas id="salesOrdersTrendChart"></canvas>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Revenue by Category</h3>
                <p class="text-sm text-gray-500">Menu categories that contributed most to revenue in the last 7 days.</p>
            </div>

            <div class="reports-chart-shell h-72 sm:h-80">
                <div id="revenueByCategoryLoading" class="reports-chart-loading">
                    <div class="text-center">
                        <div class="mx-auto h-10 w-10 rounded-full border-4 border-orange-100 border-t-orange-500 animate-spin"></div>
                        <p class="mt-3 text-xs font-semibold text-gray-500">Loading category revenue...</p>
                    </div>
                </div>
                <canvas id="revenueByCategoryChart"></canvas>
            </div>
        </div>

        <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4">
                <h3 class="font-bold text-base sm:text-lg">Menu Demand Forecast Chart</h3>
                <p class="text-sm text-gray-500">Actual 7-day sales compared with predicted next-day demand.</p>
            </div>

            <div class="reports-chart-shell h-[360px] sm:h-[420px]">
                <div id="demandForecastLoading" class="reports-chart-loading">
                    <div class="text-center">
                        <div class="mx-auto h-10 w-10 rounded-full border-4 border-orange-100 border-t-orange-500 animate-spin"></div>
                        <p class="mt-3 text-xs font-semibold text-gray-500">Loading demand forecast...</p>
                    </div>
                </div>
                <canvas id="demandForecastChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Single Forecast Table -->
    <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b">
            <h3 class="font-bold text-base sm:text-lg">Menu Demand Forecast</h3>
            <p class="text-sm text-gray-500">
                Use this to prepare menu items for the next operating day based on the last 7 days of orders.
            </p>
        </div>

        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full min-w-[1040px] text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Menu Item</th>
                        <th class="text-left px-6 py-4 font-semibold">Category</th>
                        <th class="text-left px-6 py-4 font-semibold">Sold 7 Days</th>
                        <th class="text-left px-6 py-4 font-semibold">Predicted Demand</th>
                        <th class="text-left px-6 py-4 font-semibold">Confidence</th>
                        <th class="text-left px-6 py-4 font-semibold">Recommendation</th>
                    </tr>
                </thead>
                <tbody id="menuDemandForecastBody">
                    <tr>
                        <td colspan="6" class="px-6 py-5">
                            <div class="space-y-3">
                                <div class="reports-skeleton h-4 rounded-lg w-11/12"></div>
                                <div class="reports-skeleton h-4 rounded-lg w-9/12"></div>
                                <div class="reports-skeleton h-4 rounded-lg w-10/12"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile / Tablet Cards -->
        <div id="menuDemandForecastMobileList" class="lg:hidden p-4 space-y-3">
            <div class="space-y-3">
                <div class="reports-skeleton h-28 rounded-2xl"></div>
                <div class="reports-skeleton h-28 rounded-2xl"></div>
                <div class="reports-skeleton h-28 rounded-2xl"></div>
            </div>
        </div>
    </div>

    <!-- Operational Analytics -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

        <!-- Ingredient Consumption -->
        <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b bg-gradient-to-r from-orange-50 via-white to-amber-50">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg">Ingredient Consumption</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Actual usage, daily average, and tomorrow's forecast.
                        </p>
                    </div>

                    <span class="inline-flex rounded-full border border-orange-200 bg-orange-100 px-3 py-1 text-[11px] font-bold text-orange-700">
                        Last 7 Days
                    </span>
                </div>
            </div>

            <div id="ingredientConsumptionList" class="p-4 sm:p-5 space-y-3">
                <div class="reports-skeleton h-24 rounded-2xl"></div>
                <div class="reports-skeleton h-24 rounded-2xl"></div>
                <div class="reports-skeleton h-24 rounded-2xl"></div>
            </div>
        </div>

        <!-- Stock Risk -->
        <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b bg-gradient-to-r from-red-50 via-white to-orange-50">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg">Stock Risk</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Ingredients that may not cover forecasted demand.
                        </p>
                    </div>

                    <span id="stockRiskCountBadge"
                        class="inline-flex rounded-full border border-red-200 bg-red-100 px-3 py-1 text-[11px] font-bold text-red-700">
                        0 risk
                    </span>
                </div>
            </div>

            <div id="stockRiskList" class="p-4 sm:p-5 space-y-3">
                <div class="reports-skeleton h-24 rounded-2xl"></div>
                <div class="reports-skeleton h-24 rounded-2xl"></div>
            </div>
        </div>
    </div>

    <!-- Peak Hours and Refill Analytics -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

        <!-- Peak Hours -->
        <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 min-w-0">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base sm:text-lg">Peak Hours</h3>
                    <p class="text-sm text-gray-500">
                        Order volume by hour for the last 7 days.
                    </p>
                </div>

                <div id="busiestHourBadge"
                    class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    Busiest: -
                </div>
            </div>

            <div class="reports-chart-shell h-72 sm:h-80">
                <div id="peakHoursLoading" class="reports-chart-loading">
                    <div class="text-center">
                        <div class="mx-auto h-10 w-10 rounded-full border-4 border-orange-100 border-t-orange-500 animate-spin"></div>
                        <p class="mt-3 text-xs font-semibold text-gray-500">Loading peak hours...</p>
                    </div>
                </div>
                <canvas id="peakHoursChart"></canvas>
            </div>
        </div>

        <!-- Refill Analytics -->
        <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b bg-gradient-to-r from-blue-50 via-white to-orange-50">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg">Unlimited Refill Analytics</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Refill volume and current status distribution.
                        </p>
                    </div>

                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-100 px-3 py-1 text-[11px] font-bold text-blue-700">
                        Refill Tracking
                    </span>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-orange-600">Total Requests</p>
                        <p id="refillTotalRequests" class="mt-2 text-2xl font-black text-gray-900">0</p>
                    </div>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Requested</p>
                        <p id="refillRequestedCount" class="mt-2 text-2xl font-black text-gray-900">0</p>
                    </div>

                    <div class="rounded-2xl border border-yellow-100 bg-yellow-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-yellow-700">Preparing</p>
                        <p id="refillPreparingCount" class="mt-2 text-2xl font-black text-gray-900">0</p>
                    </div>

                    <div class="rounded-2xl border border-green-100 bg-green-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-green-700">Ready</p>
                        <p id="refillReadyCount" class="mt-2 text-2xl font-black text-gray-900">0</p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-600">Served</p>
                        <p id="refillServedCount" class="mt-2 text-2xl font-black text-gray-900">0</p>
                    </div>

                    <div class="rounded-2xl border border-purple-100 bg-purple-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-purple-700">Avg / Order</p>
                        <p id="refillAveragePerOrder" class="mt-2 text-2xl font-black text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refill Rankings -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b">
                <h3 class="font-bold text-base sm:text-lg">Top Refill Ingredients</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Ingredients most frequently requested for refill.
                </p>
            </div>

            <div id="topRefillIngredientsList" class="p-4 sm:p-5 space-y-3">
                <div class="reports-skeleton h-16 rounded-2xl"></div>
                <div class="reports-skeleton h-16 rounded-2xl"></div>
            </div>
        </div>

        <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b">
                <h3 class="font-bold text-base sm:text-lg">Top Unlimited Items</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Unlimited packages with the most refill requests.
                </p>
            </div>

            <div id="topUnlimitedItemsList" class="p-4 sm:p-5 space-y-3">
                <div class="reports-skeleton h-16 rounded-2xl"></div>
                <div class="reports-skeleton h-16 rounded-2xl"></div>
            </div>
        </div>
    </div>

    <!-- Refill History -->
    <div class="reports-panel bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b">
            <h3 class="font-bold text-base sm:text-lg">Recent Refill History</h3>
            <p class="text-sm text-gray-500 mt-1">
                Latest unlimited refill requests and status updates.
            </p>
        </div>

        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full min-w-[980px] text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Refill</th>
                        <th class="text-left px-6 py-4 font-semibold">Order</th>
                        <th class="text-left px-6 py-4 font-semibold">Table</th>
                        <th class="text-left px-6 py-4 font-semibold">Unlimited Item</th>
                        <th class="text-left px-6 py-4 font-semibold">Status</th>
                        <th class="text-left px-6 py-4 font-semibold">Requested</th>
                    </tr>
                </thead>

                <tbody id="refillHistoryBody">
                    <tr>
                        <td colspan="6" class="px-6 py-5">
                            <div class="space-y-3">
                                <div class="reports-skeleton h-4 rounded-lg w-11/12"></div>
                                <div class="reports-skeleton h-4 rounded-lg w-9/12"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="refillHistoryMobileList" class="lg:hidden p-4 space-y-3">
            <div class="reports-skeleton h-28 rounded-2xl"></div>
            <div class="reports-skeleton h-28 rounded-2xl"></div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let reportsData = {};
let salesOrdersTrendChart = null;
let revenueByCategoryChart = null;
let demandForecastChart = null;
let peakHoursChart = null;

let reportsLoading = false;

function showReportsToast(message, type = 'success') {
    const container = document.getElementById('reportsToastContainer');
    if (!container) return;

    const styles = {
        success: {
            icon: '✓',
            iconClass: 'bg-green-100 text-green-700',
            borderClass: 'border-green-200',
            title: 'Success'
        },
        error: {
            icon: '!',
            iconClass: 'bg-red-100 text-red-700',
            borderClass: 'border-red-200',
            title: 'Unable to Load'
        },
        info: {
            icon: 'i',
            iconClass: 'bg-blue-100 text-blue-700',
            borderClass: 'border-blue-200',
            title: 'Reports Update'
        }
    };

    const meta = styles[type] || styles.info;
    const toast = document.createElement('div');
    toast.className = `reports-toast pointer-events-auto w-[360px] max-w-full rounded-2xl border ${meta.borderClass} bg-white p-4 shadow-2xl`;
    toast.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl ${meta.iconClass} font-black">${meta.icon}</div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-extrabold text-gray-900">${meta.title}</p>
                <p class="mt-1 text-xs leading-5 text-gray-600">${safeText(message)}</p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-700" aria-label="Close">×</button>
        </div>
    `;

    const close = () => {
        if (!toast.isConnected) return;
        toast.classList.add('is-hiding');
        setTimeout(() => toast.remove(), 200);
    };

    toast.querySelector('button')?.addEventListener('click', close);
    container.appendChild(toast);
    setTimeout(close, 3600);
}

function animateNumber(elementId, endValue, options = {}) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const duration = options.duration || 650;
    const money = Boolean(options.money);
    const startTime = performance.now();
    const end = Number(endValue || 0);

    function frame(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = end * eased;

        element.textContent = money ? formatMoney(current) : Math.round(current).toLocaleString();

        if (progress < 1) requestAnimationFrame(frame);
    }

    requestAnimationFrame(frame);
}

function setReportsLoading(isLoading) {
    reportsLoading = isLoading;

    ['salesOrdersTrendLoading', 'revenueByCategoryLoading', 'demandForecastLoading', 'peakHoursLoading'].forEach(id => {
        document.getElementById(id)?.classList.toggle('hidden', !isLoading);
    });

    const button = document.getElementById('refreshForecastBtn');
    const icon = document.getElementById('refreshForecastIcon');
    const text = document.getElementById('refreshForecastText');

    if (button) button.disabled = isLoading;
    if (icon) icon.classList.toggle('animate-spin', isLoading);
    if (text) text.textContent = isLoading ? 'Refreshing...' : 'Refresh Forecast';
}

function renderForecastLoadingState() {
    document.getElementById('summaryText').innerHTML = '<span class="inline-block reports-skeleton h-4 w-full rounded-lg"></span><span class="mt-2 inline-block reports-skeleton h-4 w-10/12 rounded-lg"></span>';
    document.getElementById('forecastMode').textContent = 'Analyzing recent restaurant data...';
    document.getElementById('confidenceBadge').textContent = 'Confidence: -';
    document.getElementById('recommendationsList').innerHTML = `
        <div class="reports-skeleton h-12 rounded-xl"></div>
        <div class="reports-skeleton h-12 rounded-xl"></div>
        <div class="reports-skeleton h-12 rounded-xl"></div>
    `;
}

function renderReportsError(message) {
    document.getElementById('summaryText').textContent = message;
    document.getElementById('forecastMode').textContent = 'Forecast is temporarily unavailable.';
    document.getElementById('confidenceBadge').textContent = 'Confidence: N/A';
    document.getElementById('recommendationsList').innerHTML = `
        <div class="rounded-2xl border border-red-100 bg-red-50 px-4 py-4 text-sm text-red-700">
            ${safeText(message)}
        </div>
    `;

    const empty = `
        <div class="flex flex-col items-center rounded-2xl border border-dashed border-red-200 bg-red-50/50 px-6 py-10 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-xl font-black text-red-500 shadow-sm">!</div>
            <h4 class="mt-4 font-bold text-gray-900">Forecast data unavailable</h4>
            <p class="mt-1 max-w-md text-sm text-gray-500">${safeText(message)}</p>
        </div>
    `;

    document.getElementById('menuDemandForecastBody').innerHTML = `<tr><td colspan="6" class="px-6 py-8">${empty}</td></tr>`;
    document.getElementById('menuDemandForecastMobileList').innerHTML = empty;
}

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
        minimumFractionDigits: 2,
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

function confidenceBadge(confidence) {
    const value = String(confidence || '').toLowerCase();

    if (value === 'high') {
        return '<span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">High</span>';
    }

    if (value === 'medium') {
        return '<span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">Medium</span>';
    }

    if (value === 'low') {
        return '<span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Low</span>';
    }

    return '<span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold">N/A</span>';
}

function renderCards(data) {
    const revenue = pick(data, ['total_revenue_7d', 'total_revenue', 'revenue_7d'], 0);
    const avgOrder = pick(data, ['avg_order_value', 'average_order_value'], 0);
    const orders = pick(data, ['total_orders_7d', 'total_orders', 'orders_7d'], 0);
    const forecastRevenue = pick(data, ['forecasted_revenue', 'revenue_forecast', 'next_day_revenue_forecast'], 0);

    animateNumber('cardRevenue7d', revenue, { money: true });
    animateNumber('cardAvgOrderValue', avgOrder, { money: true });
    animateNumber('cardOrders7d', orders);
    animateNumber('cardForecastRevenue', forecastRevenue, { money: true });
    animateNumber('revenueForecast', forecastRevenue, { money: true });

    const mode = pick(data, ['forecast_mode'], '7-day system forecast');
    document.getElementById('forecastModeText').textContent = mode;
}

function renderInsight(data) {
    const summary = pick(data, ['summary', 'ai_summary'], 'No forecast summary available yet.');
    const mode = pick(data, ['forecast_mode'], 'Based on last 7 days of available order and sales data.');
    const confidence = pick(data, ['forecast_confidence', 'ai_forecast_confidence'], 'N/A');
    const recommendations = pick(data, ['recommendations', 'ai_recommendations'], []);

    document.getElementById('summaryText').textContent = summary;
    document.getElementById('forecastMode').textContent = mode;
    const confidenceElement = document.getElementById('confidenceBadge');
    const confidenceValue = String(confidence || '').toLowerCase();
    confidenceElement.textContent = `Confidence: ${confidence}`;
    confidenceElement.className = 'px-3 py-1 rounded-full text-xs font-bold w-fit border';

    if (confidenceValue === 'high') {
        confidenceElement.classList.add('bg-green-100', 'text-green-700', 'border-green-200');
    } else if (confidenceValue === 'medium') {
        confidenceElement.classList.add('bg-yellow-100', 'text-yellow-700', 'border-yellow-200');
    } else if (confidenceValue === 'low') {
        confidenceElement.classList.add('bg-red-100', 'text-red-700', 'border-red-200');
    } else {
        confidenceElement.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
    }

    const list = document.getElementById('recommendationsList');

    if (!Array.isArray(recommendations) || recommendations.length === 0) {
        list.innerHTML = '<p class="text-gray-400">No recommendations yet.</p>';
        return;
    }

    list.innerHTML = recommendations.map((item, index) => `
        <div class="rounded-2xl bg-white border border-orange-100 px-4 py-3 shadow-sm">
            <div class="flex items-start gap-3">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-xs font-black text-orange-700">${index + 1}</span>
                <p class="text-sm leading-6 text-gray-700">${safeText(item)}</p>
            </div>
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
            labels: normalized.labels.length ? normalized.labels : ['No Data'],
            datasets: [
                {
                    label: 'Sales',
                    data: normalized.sales.length ? normalized.sales : [0],
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.12)',
                    fill: true,
                    tension: 0.35,
                    yAxisID: 'y'
                },
                {
                    label: 'Orders',
                    data: normalized.orders.length ? normalized.orders : [0],
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
            animation: { duration: 700, easing: 'easeOutQuart' },
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
            labels: labels.map(label => shortLabel(label, 28)),
            datasets: [{
                label: 'Revenue',
                data: values,
                backgroundColor: '#f97316',
                borderRadius: 8,
                maxBarThickness: 42
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700, easing: 'easeOutQuart' },
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
                x: {
                    ticks: {
                        maxRotation: 0,
                        minRotation: 0,
                        autoSkip: false,
                        callback: function(value) {
                            const label = this.getLabelForValue(value);
                            return label.length > 14 ? label.match(/.{1,14}/g) : label;
                        }
                    }
                },
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
        'quantity_sold'
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

function renderDemandForecastChart(data) {
    const rows = getMenuForecastRows(data)
        .filter(item => getSold7d(item) > 0 || getPredictedDemand(item) > 0)
        .slice(0, 8);

    const canvas = document.getElementById('demandForecastChart');

    if (!canvas || typeof Chart === 'undefined') return;

    const originalLabels = rows.map(item => pick(item, ['menu_item', 'item_name', 'name'], 'Unknown Item'));
    const labels = originalLabels.map(label => shortLabel(label, 24));
    const sold = rows.map(item => getSold7d(item));
    const predicted = rows.map(item => getPredictedDemand(item));

    if (demandForecastChart) {
        demandForecastChart.destroy();
    }

    demandForecastChart = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels.length ? labels : ['No forecast data'],
            datasets: [
                {
                    label: 'Sold 7 Days',
                    data: sold.length ? sold : [0],
                    backgroundColor: '#60a5fa',
                    borderRadius: 8,
                    maxBarThickness: 24
                },
                {
                    label: 'Predicted Demand',
                    data: predicted.length ? predicted : [0],
                    backgroundColor: '#f97316',
                    borderRadius: 8,
                    maxBarThickness: 24
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700, easing: 'easeOutQuart' },
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

function renderMenuDemandForecast(data) {
    const tbody = document.getElementById('menuDemandForecastBody');
    const mobileList = document.getElementById('menuDemandForecastMobileList');

    const rows = getMenuForecastRows(data);

    if (!rows.length) {
        const emptyState = `
            <div class="flex flex-col items-center rounded-2xl border border-dashed border-orange-200 bg-orange-50/50 px-6 py-10 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-2xl shadow-sm">📊</div>
                <h4 class="mt-4 font-extrabold text-gray-900">No forecast data yet</h4>
                <p class="mt-1 max-w-md text-sm leading-6 text-gray-500">Forecast results will appear after enough recent order history is available.</p>
            </div>
        `;

        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8">${emptyState}</td></tr>`;
        mobileList.innerHTML = emptyState;
        return;
    }

    tbody.innerHTML = rows.map(item => {
        const name = pick(item, ['menu_item', 'item_name', 'name'], 'Unknown Item');
        const category = pick(item, ['category'], 'Uncategorized');
        const sold = getSold7d(item);
        const predicted = getPredictedDemand(item);
        const unit = pick(item, ['unit'], 'orders');
        const confidence = pick(item, ['confidence', 'confidence_level'], 'N/A');
        const recommendation = pick(item, ['recommendation', 'suggestion'], 'Monitor demand.');

        return `
            <tr class="border-t hover:bg-orange-50/40 transition">
                <td class="px-6 py-4 font-semibold">${safeText(name)}</td>
                <td class="px-6 py-4">${safeText(category)}</td>
                <td class="px-6 py-4">${formatNumber(sold)}</td>
                <td class="px-6 py-4">${formatNumber(predicted)} ${safeText(unit)}</td>
                <td class="px-6 py-4">${confidenceBadge(confidence)}</td>
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
        const recommendation = pick(item, ['recommendation', 'suggestion'], 'Monitor demand.');

        return `
            <div class="rounded-3xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-200 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="font-bold text-gray-900 leading-snug">${safeText(name)}</h4>
                        <p class="text-xs text-gray-500 mt-1">${safeText(category)}</p>
                    </div>

                    ${confidenceBadge(confidence)}
                </div>

                <div class="mt-3 rounded-xl bg-gray-50 border px-3 py-2 space-y-1">
                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">Sold 7 Days:</span>
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


function statusBadgeHtml(status) {
    const value = String(status || 'requested').toLowerCase();

    const styles = {
        requested: 'bg-orange-100 text-orange-700 border-orange-200',
        preparing: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        ready: 'bg-green-100 text-green-700 border-green-200',
        served: 'bg-gray-100 text-gray-600 border-gray-200',
        cancelled: 'bg-red-100 text-red-700 border-red-200',
    };

    const style = styles[value] || styles.requested;

    return `<span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold ${style}">
        ${safeText(value.charAt(0).toUpperCase() + value.slice(1))}
    </span>`;
}

function formatDateTime(value) {
    if (!value) return 'No date';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return safeText(value);

    return new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        month: 'short',
        day: '2-digit',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }).format(date);
}

function renderIngredientConsumption(data) {
    const rows = pick(data, ['ingredient_consumption_7d', 'ingredient_usage_7d'], []);
    const list = document.getElementById('ingredientConsumptionList');

    if (!Array.isArray(rows) || rows.length === 0) {
        list.innerHTML = `
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-10 text-center">
                <p class="font-bold text-gray-900">No ingredient usage yet</p>
                <p class="mt-1 text-sm text-gray-500">Usage will appear after inventory deductions are recorded.</p>
            </div>
        `;
        return;
    }

    list.innerHTML = rows.slice(0, 10).map(item => {
        const risk = String(item.risk_level || 'Safe');
        const riskClass = risk === 'Critical'
            ? 'bg-red-100 text-red-700 border-red-200'
            : risk === 'Warning'
                ? 'bg-yellow-100 text-yellow-700 border-yellow-200'
                : 'bg-green-100 text-green-700 border-green-200';

        return `
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-bold text-gray-900">${safeText(item.name || item.ingredient_name)}</p>
                        <p class="mt-1 text-xs text-gray-500">${safeText(item.unit || 'unit')}</p>
                    </div>

                    <span class="rounded-full border px-3 py-1 text-[11px] font-bold ${riskClass}">
                        ${safeText(risk)}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl bg-orange-50 p-3">
                        <p class="text-[10px] font-bold uppercase text-orange-600">Used 7 Days</p>
                        <p class="mt-1 font-black text-gray-900">${formatNumber(item.quantity_used_7d)} ${safeText(item.unit || '')}</p>
                    </div>

                    <div class="rounded-xl bg-blue-50 p-3">
                        <p class="text-[10px] font-bold uppercase text-blue-600">Daily Average</p>
                        <p class="mt-1 font-black text-gray-900">${formatNumber(item.daily_average)} ${safeText(item.unit || '')}</p>
                    </div>

                    <div class="rounded-xl bg-purple-50 p-3">
                        <p class="text-[10px] font-bold uppercase text-purple-600">Tomorrow</p>
                        <p class="mt-1 font-black text-gray-900">${formatNumber(item.forecast_tomorrow)} ${safeText(item.unit || '')}</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-[10px] font-bold uppercase text-gray-500">Current Stock</p>
                        <p class="mt-1 font-black text-gray-900">${formatNumber(item.current_stock)} ${safeText(item.unit || '')}</p>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function renderStockRisk(data) {
    const rows = pick(data, ['stock_risk'], []);
    const count = Number(pick(data, ['stock_risk_count'], Array.isArray(rows) ? rows.length : 0));
    const list = document.getElementById('stockRiskList');
    const badge = document.getElementById('stockRiskCountBadge');

    badge.textContent = `${count} risk${count === 1 ? '' : 's'}`;

    if (!Array.isArray(rows) || rows.length === 0) {
        list.innerHTML = `
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-10 text-center">
                <p class="font-bold text-green-800">No immediate stock risk</p>
                <p class="mt-1 text-sm text-green-700">Current stock can cover the recent forecast.</p>
            </div>
        `;
        return;
    }

    list.innerHTML = rows.map(item => {
        const critical = item.risk_level === 'Critical';

        return `
            <div class="rounded-2xl border ${critical ? 'border-red-200 bg-red-50' : 'border-yellow-200 bg-yellow-50'} p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-bold text-gray-900">${safeText(item.name || item.ingredient_name)}</p>
                        <p class="mt-1 text-xs text-gray-600">${safeText(item.recommendation || '')}</p>
                    </div>

                    <span class="rounded-full border px-3 py-1 text-[11px] font-bold ${critical ? 'border-red-200 bg-white text-red-700' : 'border-yellow-200 bg-white text-yellow-700'}">
                        ${safeText(item.risk_level)}
                    </span>
                </div>

                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-lg bg-white px-3 py-1.5 font-bold text-gray-700">
                        Stock: ${formatNumber(item.current_stock)} ${safeText(item.unit || '')}
                    </span>

                    <span class="rounded-lg bg-white px-3 py-1.5 font-bold text-gray-700">
                        Tomorrow: ${formatNumber(item.forecast_tomorrow)} ${safeText(item.unit || '')}
                    </span>
                </div>
            </div>
        `;
    }).join('');
}

function renderPeakHours(data) {
    const rows = pick(data, ['peak_hours'], []);
    const busiest = pick(data, ['busiest_hour'], null);
    const canvas = document.getElementById('peakHoursChart');
    const badge = document.getElementById('busiestHourBadge');

    badge.textContent = busiest
        ? `Busiest: ${busiest.label} (${busiest.orders} orders)`
        : 'Busiest: No data';

    if (!canvas || typeof Chart === 'undefined') return;

    if (peakHoursChart) peakHoursChart.destroy();

    const labels = Array.isArray(rows) && rows.length
        ? rows.map(item => item.label)
        : ['No data'];

    const orders = Array.isArray(rows) && rows.length
        ? rows.map(item => Number(item.orders || 0))
        : [0];

    peakHoursChart = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Orders',
                data: orders,
                backgroundColor: '#f97316',
                borderRadius: 8,
                maxBarThickness: 34
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700 },
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
}

function renderRefillAnalytics(data) {
    const analytics = pick(data, ['refill_analytics'], {});

    document.getElementById('refillTotalRequests').textContent = formatNumber(analytics.total_requests || 0);
    document.getElementById('refillRequestedCount').textContent = formatNumber(analytics.requested || 0);
    document.getElementById('refillPreparingCount').textContent = formatNumber(analytics.preparing || 0);
    document.getElementById('refillReadyCount').textContent = formatNumber(analytics.ready || 0);
    document.getElementById('refillServedCount').textContent = formatNumber(analytics.served || 0);
    document.getElementById('refillAveragePerOrder').textContent = Number(analytics.average_per_order || 0).toFixed(2);
}

function renderRefillRankings(data) {
    const ingredients = pick(data, ['top_refill_ingredients'], []);
    const unlimitedItems = pick(data, ['top_unlimited_items'], []);

    const ingredientList = document.getElementById('topRefillIngredientsList');
    const unlimitedList = document.getElementById('topUnlimitedItemsList');

    ingredientList.innerHTML = Array.isArray(ingredients) && ingredients.length
        ? ingredients.map((item, index) => `
            <div class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-sm font-black text-orange-700">${index + 1}</span>
                    <div class="min-w-0">
                        <p class="truncate font-bold text-gray-900">${safeText(item.name || item.ingredient_name)}</p>
                        <p class="mt-1 text-xs text-gray-500">${formatNumber(item.total_quantity)} ${safeText(item.unit || 'unit')}</p>
                    </div>
                </div>

                <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">
                    ${formatNumber(item.request_count)} requests
                </span>
            </div>
        `).join('')
        : '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-10 text-center text-sm text-gray-500">No refill ingredient data yet.</div>';

    unlimitedList.innerHTML = Array.isArray(unlimitedItems) && unlimitedItems.length
        ? unlimitedItems.map((item, index) => `
            <div class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-black text-blue-700">${index + 1}</span>
                    <p class="truncate font-bold text-gray-900">${safeText(item.name || item.menu_item)}</p>
                </div>

                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    ${formatNumber(item.request_count)} requests
                </span>
            </div>
        `).join('')
        : '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-10 text-center text-sm text-gray-500">No unlimited item refill data yet.</div>';
}

function renderRefillHistory(data) {
    const rows = pick(data, ['refill_history'], []);
    const tbody = document.getElementById('refillHistoryBody');
    const mobile = document.getElementById('refillHistoryMobileList');

    if (!Array.isArray(rows) || rows.length === 0) {
        const empty = `
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-10 text-center">
                <p class="font-bold text-gray-900">No refill history yet</p>
                <p class="mt-1 text-sm text-gray-500">Completed and active refill requests will appear here.</p>
            </div>
        `;

        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8">${empty}</td></tr>`;
        mobile.innerHTML = empty;
        return;
    }

    tbody.innerHTML = rows.map(item => `
        <tr class="border-t hover:bg-orange-50/30 transition">
            <td class="px-6 py-4 font-bold text-gray-900">#${safeText(item.id)}</td>
            <td class="px-6 py-4">${safeText(item.order_number)}</td>
            <td class="px-6 py-4">Table ${safeText(item.table_number)}</td>
            <td class="px-6 py-4">${safeText(item.menu_name)}</td>
            <td class="px-6 py-4">${statusBadgeHtml(item.status)}</td>
            <td class="px-6 py-4">${safeText(formatDateTime(item.requested_at || item.created_at))}</td>
        </tr>
    `).join('');

    mobile.innerHTML = rows.map(item => `
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-bold text-gray-900">Refill #${safeText(item.id)}</p>
                    <p class="mt-1 text-xs text-gray-500">${safeText(item.order_number)} · Table ${safeText(item.table_number)}</p>
                </div>
                ${statusBadgeHtml(item.status)}
            </div>

            <div class="mt-3 rounded-xl bg-gray-50 p-3">
                <p class="text-sm font-bold text-gray-900">${safeText(item.menu_name)}</p>
                <p class="mt-1 text-xs text-gray-500">${safeText(formatDateTime(item.requested_at || item.created_at))}</p>
            </div>
        </div>
    `).join('');
}


async function loadReportsForecast(options = {}) {
    const showToast = Boolean(options.showToast);
    setReportsLoading(true);
    renderForecastLoadingState();

    try {
        const res = await fetch('/api/admin/reports-forecast', {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            throw new Error(`Reports API returned ${res.status}.`);
        }

        const data = await res.json();
        reportsData = data;

        renderCards(data);
        renderInsight(data);
        renderSalesOrdersTrendChart(data);
        renderRevenueByCategoryChart(data);
        renderDemandForecastChart(data);
        renderMenuDemandForecast(data);
        renderIngredientConsumption(data);
        renderStockRisk(data);
        renderPeakHours(data);
        renderRefillAnalytics(data);
        renderRefillRankings(data);
        renderRefillHistory(data);

        if (showToast) {
            showReportsToast('Forecast and report data were refreshed successfully.', 'success');
        }

        return data;
    } catch (error) {
        console.error('Reports Forecast load failed:', error);
        const message = error?.message || 'Failed to load reports and forecast. Please check your connection.';
        renderReportsError(message);
        showReportsToast(message, 'error');
        throw error;
    } finally {
        setReportsLoading(false);
    }
}

function refreshForecast(event) {
    if (reportsLoading) return;
    loadReportsForecast({ showToast: true }).catch(() => {});
}

loadReportsForecast().catch(() => {});
</script>

@endsection