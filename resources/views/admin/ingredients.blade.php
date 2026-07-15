@extends('layouts.admin')

@section('content')

<style>
    /*
        Responsive modal rules:
        - Mobile: modals become true full-screen sheets.
        - Manage Stock modal: header and summary scroll together with the form,
          summary cards become compact 2-column cards, and the form is easier to reach.
        - AI modal: header and insight cards are compact on mobile; the whole modal scrolls naturally.
    */
    @media (max-width: 640px) {
        #aiInsightModal,
        #ingredientModal,
        #manageStockModal {
            align-items: stretch !important;
            justify-content: stretch !important;
            padding: 0 !important;
        }

        #aiInsightModal > div,
        #ingredientModal > div,
        #manageStockModal > div {
            width: 100% !important;
            max-width: 100% !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            border-radius: 0 !important;
        }

        #aiInsightModal > div {
            display: block !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }

        .ai-insight-header {
            position: relative !important;
            padding: 16px 18px 14px !important;
            overflow: hidden !important;
        }

        .ai-insight-header .absolute {
            opacity: 0.45 !important;
            transform: scale(0.78) !important;
        }

        .ai-insight-header > .relative:first-of-type {
            gap: 12px !important;
        }

        .ai-insight-header h3 {
            font-size: 22px !important;
            line-height: 1.18 !important;
            margin-top: 6px !important;
        }

        .ai-insight-header p.text-sm {
            font-size: 12.5px !important;
            line-height: 1.45 !important;
            margin-top: 8px !important;
        }

        .ai-insight-header .flex.flex-wrap {
            gap: 6px !important;
            margin-bottom: 8px !important;
        }

        .ai-insight-header span.inline-flex {
            font-size: 10px !important;
            line-height: 1.2 !important;
            padding: 5px 10px !important;
        }

        .ai-insight-close-btn {
            width: 38px !important;
            height: 38px !important;
            font-size: 20px !important;
        }

        .ai-insight-actions {
            margin-top: 14px !important;
            gap: 8px !important;
        }

        #generateAiInsightBtn {
            width: 100% !important;
            min-height: 46px !important;
            padding: 11px 14px !important;
            border-radius: 16px !important;
            font-size: 14px !important;
        }

        .ai-insight-actions p {
            font-size: 11.5px !important;
            line-height: 1.35 !important;
        }

        #aiInsightGeneratedAt,
        #aiInsightHealthBadge {
            max-width: 100% !important;
            white-space: normal !important;
            line-height: 15px !important;
        }

        .ai-insight-body {
            overflow: visible !important;
            padding: 14px !important;
            padding-bottom: calc(24px + env(safe-area-inset-bottom)) !important;
            min-height: auto !important;
        }

        #aiInsightLoading,
        #aiInsightEmpty {
            padding: 22px 16px !important;
            border-radius: 20px !important;
        }

        #aiInsightContent {
            padding-bottom: 0 !important;
        }

        .ai-health-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }

        .ai-health-card {
            padding: 13px !important;
            border-radius: 18px !important;
            min-height: 128px !important;
        }

        .ai-health-card p:first-child {
            font-size: 11px !important;
            line-height: 1.25 !important;
        }

        .ai-health-card span {
            width: 32px !important;
            height: 32px !important;
            border-radius: 14px !important;
            font-size: 13px !important;
        }

        .ai-health-card p[id^="aiMetric"] {
            font-size: 26px !important;
            line-height: 1.05 !important;
            margin-top: 12px !important;
        }

        .ai-health-card p:last-child {
            font-size: 10.5px !important;
            line-height: 1.2 !important;
        }

        #aiInsightContent .rounded-3xl {
            border-radius: 18px !important;
        }

        #aiInsightContent .px-5 {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

        #aiInsightContent .py-4 {
            padding-top: 12px !important;
            padding-bottom: 12px !important;
        }

        #aiInsightContent .p-5 {
            padding: 14px !important;
        }

        #aiInsightContent h4 {
            font-size: 15px !important;
            line-height: 1.25 !important;
        }

        #aiInsightContent p.text-sm {
            font-size: 12px !important;
            line-height: 1.4 !important;
        }

        #aiInsightSummary {
            font-size: 13px !important;
            line-height: 1.55 !important;
        }

        #ingredientModal > div {
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        #ingredientForm {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding-bottom: calc(24px + env(safe-area-inset-bottom)) !important;
        }

        #manageStockModal > div {
            display: block !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }

        .manage-stock-header {
            position: relative !important;
            padding: 18px !important;
        }

        .manage-stock-header > .flex {
            align-items: flex-start !important;
        }

        #manageStockModalTitle {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            font-size: 24px !important;
            line-height: 1.16 !important;
        }

        .manage-stock-header p.text-sm {
            font-size: 13px !important;
            line-height: 1.45 !important;
        }

        .manage-stock-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
            margin-top: 16px !important;
        }

        .manage-stock-summary-card {
            padding: 12px !important;
            border-radius: 18px !important;
            min-height: 88px !important;
        }

        .manage-stock-summary-card p:first-child {
            font-size: 10px !important;
            line-height: 1.2 !important;
        }

        .manage-stock-summary-card p[id] {
            font-size: 20px !important;
            line-height: 1.1 !important;
            word-break: break-word !important;
        }

        .manage-stock-summary-card p.text-xs {
            display: none !important;
        }

        .manage-stock-body {
            overflow: visible !important;
            padding: 16px !important;
            padding-bottom: calc(28px + env(safe-area-inset-bottom)) !important;
            min-height: auto !important;
        }

        .manage-stock-body .rounded-3xl {
            border-radius: 22px !important;
        }

        .manage-stock-body .px-5 {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .manage-stock-body .p-5 {
            padding: 16px !important;
        }

        #stockForm .grid {
            grid-template-columns: 1fr !important;
        }

        #stockForm button {
            min-height: 44px !important;
        }

        #batchMobileList button,
        #ingredientsMobileList button {
            min-height: 42px !important;
        }

        #manageStockModal input,
        #manageStockModal select,
        #manageStockModal textarea,
        #ingredientModal input {
            font-size: 16px !important;
        }
    }

    @media (max-width: 420px) {
        .manage-stock-header {
            padding: 14px 16px !important;
        }

        #manageStockModalTitle {
            font-size: 22px !important;
            line-height: 1.12 !important;
        }

        .manage-stock-header p.text-sm {
            font-size: 12px !important;
            line-height: 1.35 !important;
        }

        .manage-stock-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px !important;
            margin-top: 12px !important;
        }

        .manage-stock-summary-card {
            min-height: 78px !important;
            padding: 10px 11px !important;
            border-radius: 16px !important;
        }

        .manage-stock-summary-card p:first-child {
            font-size: 8.5px !important;
            letter-spacing: 0.06em !important;
            line-height: 1.1 !important;
        }

        .manage-stock-summary-card p[id] {
            font-size: 18px !important;
            line-height: 1.05 !important;
            margin-top: 5px !important;
        }

        .manage-stock-summary-card p.text-xs {
            display: none !important;
        }

        .manage-stock-body {
            padding: 12px !important;
            gap: 12px !important;
        }

        .manage-stock-body .rounded-3xl {
            border-radius: 18px !important;
        }

        .manage-stock-body .px-5,
        .manage-stock-body .p-5 {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

        .manage-stock-body h4 {
            font-size: 18px !important;
            line-height: 1.2 !important;
        }
    }

    @media (min-width: 641px) and (max-width: 1024px) {
        #aiInsightModal > div,
        #ingredientModal > div,
        #manageStockModal > div {
            max-width: calc(100vw - 32px) !important;
            max-height: calc(100dvh - 32px) !important;
        }
    }
</style>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">Inventory Management</h1>
            <p class="text-sm sm:text-base text-gray-500">
                Track ingredient stock levels, batches, low stock alerts, stock usage, and AI-assisted inventory insights.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full lg:w-auto">
            <button onclick="openAiInsightModal()"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-xl font-semibold shadow-sm transition">
                AI Inventory Insight
            </button>

            <button onclick="openIngredientModal()"
                class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2.5 rounded-xl font-semibold shadow-sm transition">
                + Add Ingredient
            </button>
        </div>
    </div>

    <!-- AI Quick Note -->
    <div class="rounded-2xl border border-orange-100 bg-gradient-to-r from-orange-50 via-white to-amber-50 p-4 sm:p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-gray-900">AI Inventory Assistant</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Generate a smart summary of restocking priorities, unavailable menu items, and inventory recommendations.
                </p>
            </div>

            <button onclick="openAiInsightModal()"
                class="w-full md:w-auto bg-white border border-orange-200 hover:bg-orange-50 text-orange-700 px-4 py-2.5 rounded-xl font-semibold text-sm transition">
                View Insights
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
            <p class="text-sm font-semibold text-red-700">Out of Stock</p>
            <p id="summaryOutOfStock" class="text-3xl font-bold text-red-900 mt-2">0</p>
            <p class="text-xs text-red-600 mt-1">Ingredients with no usable stock</p>
        </div>

        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5">
            <p class="text-sm font-semibold text-yellow-700">Low Stock</p>
            <p id="summaryLowStock" class="text-3xl font-bold text-yellow-900 mt-2">0</p>
            <p class="text-xs text-yellow-700 mt-1">Below threshold or needs reorder</p>
        </div>

        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-sm font-semibold text-blue-700">Near Expiry</p>
            <p id="summaryNearExpiry" class="text-3xl font-bold text-blue-900 mt-2">0</p>
            <p class="text-xs text-blue-700 mt-1">Stock batches expiring soon</p>
        </div>

        <div class="rounded-2xl border border-green-200 bg-green-50 p-5">
            <p class="text-sm font-semibold text-green-700">Healthy Stock</p>
            <p id="summaryHealthy" class="text-3xl font-bold text-green-900 mt-2">0</p>
            <p class="text-xs text-green-700 mt-1">Ingredients currently available</p>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <div class="p-4 sm:p-5 border-b">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold">Ingredient Inventory</h2>
                    <p class="text-sm text-gray-500">
                        Overview of current stock levels and status.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full xl:w-auto">
                    <input
                        id="ingredientSearch"
                        type="text"
                        placeholder="Search ingredients..."
                        class="border rounded-xl px-4 py-2.5 w-full xl:w-72"
                    >

                    <select id="statusFilter" class="border rounded-xl px-4 py-2.5 w-full xl:w-56">
                        <option value="all">All Status</option>
                        <option value="out_of_stock">Out of Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="reorder_soon">Reorder Soon</option>
                        <option value="near_expiry">Near Expiry</option>
                        <option value="active">Healthy Stock</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[1150px] text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Ingredient</th>
                        <th class="text-left px-6 py-4 font-semibold">Current Stock</th>
                        <th class="text-left px-6 py-4 font-semibold">Unit</th>
                        <th class="text-left px-6 py-4 font-semibold">Threshold</th>
                        <th class="text-left px-6 py-4 font-semibold">Nearest Expiry</th>
                        <th class="text-left px-6 py-4 font-semibold">Stock Value</th>
                        <th class="text-left px-6 py-4 font-semibold">Status</th>
                        <th class="text-left px-6 py-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="ingredientsTableBody">
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                            Loading ingredients...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile -->
        <div id="ingredientsMobileList" class="md:hidden p-4 space-y-3">
            <div class="px-4 py-8 text-center text-gray-400">
                Loading ingredients...
            </div>
        </div>
    </div>
</div>

<!-- AI Inventory Insight Modal -->
<div id="aiInsightModal" class="fixed inset-0 bg-black/55 backdrop-blur-[3px] hidden items-center justify-center z-50 p-0 sm:p-4">
    <div class="bg-white rounded-none sm:rounded-[32px] shadow-2xl w-full sm:max-w-5xl h-[100dvh] sm:h-auto sm:max-h-[94dvh] overflow-hidden flex flex-col border border-orange-100">
        <!-- Header -->
        <div class="ai-insight-header relative px-5 sm:px-7 py-6 border-b bg-gradient-to-br from-orange-50 via-white to-amber-50 shrink-0 overflow-hidden">
            <div class="absolute -top-12 -right-10 w-40 h-40 rounded-full bg-orange-100/60"></div>
            <div class="absolute -bottom-16 -left-10 w-48 h-48 rounded-full bg-amber-100/50"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-[11px] font-bold uppercase tracking-wide">
                            <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                            AI Inventory Assistant
                        </span>

                        <span id="aiInsightGeneratedAt"
                            class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-orange-100 text-gray-600 text-[11px] font-semibold shadow-sm">
                            Not generated yet
                        </span>

                        <span id="aiInsightHealthBadge"
                            class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-[11px] font-bold shadow-sm">
                            Status: Waiting
                        </span>
                    </div>

                    <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        AI Inventory Insight
                    </h3>

                    <p class="text-sm text-gray-500 mt-2 max-w-2xl">
                        Smart decision-support summary based on current stock, expiry dates, thresholds, and linked menu items.
                    </p>
                </div>

                <button type="button" onclick="closeAiInsightModal()"
                    class="ai-insight-close-btn w-11 h-11 rounded-full bg-white hover:bg-orange-50 border border-orange-100 flex items-center justify-center text-gray-500 hover:text-orange-700 text-xl shrink-0 transition shadow-sm">
                    &times;
                </button>
            </div>

            <div class="ai-insight-actions relative mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <button id="generateAiInsightBtn" onclick="generateAiInventoryInsight()"
                    class="w-full sm:w-auto bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-2xl font-bold shadow-sm disabled:opacity-70 disabled:cursor-not-allowed transition">
                    Generate Latest Insight
                </button>

                <p class="text-xs text-gray-500">
                    AI insight gives recommendations only. Actual menu availability is controlled by inventory stock logic.
                </p>
            </div>
        </div>

        <!-- Body -->
        <div class="ai-insight-body flex-1 overflow-y-auto px-4 sm:px-6 py-5 bg-gradient-to-b from-orange-50/40 to-white">
            <!-- Loading -->
            <div id="aiInsightLoading" class="hidden rounded-3xl border border-orange-100 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto w-14 h-14 rounded-full border-4 border-orange-100 border-t-orange-500 animate-spin"></div>
                <p class="mt-4 font-bold text-gray-900">Generating inventory insight...</p>
                <p class="text-sm text-gray-500 mt-1">Checking stock, expiry, affected menu items, and priority actions.</p>
            </div>

            <!-- Empty -->
            <div id="aiInsightEmpty" class="rounded-3xl border border-dashed border-orange-200 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto w-16 h-16 rounded-3xl bg-orange-50 flex items-center justify-center text-orange-600 font-extrabold text-xl shadow-sm">
                    AI
                </div>
                <h4 class="font-extrabold text-gray-900 mt-4">No insight generated yet</h4>
                <p class="text-sm text-gray-500 mt-1">
                    Click “Generate Latest Insight” to view inventory health, operational impact, and suggested actions.
                </p>
            </div>

            <!-- Content -->
            <div id="aiInsightContent" class="hidden space-y-5">
                <!-- AI Health Strip -->
                <div class="ai-health-grid grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="ai-health-card rounded-3xl border border-red-100 bg-red-50 p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-bold text-red-700">Out of Stock</p>
                            <span class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-red-500 shadow-sm">!</span>
                        </div>
                        <p id="aiMetricCritical" class="text-3xl font-extrabold text-red-900 mt-3">0</p>
                        <p class="text-xs text-red-600 mt-1">Immediate attention</p>
                    </div>

                    <div class="ai-health-card rounded-3xl border border-yellow-100 bg-yellow-50 p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-bold text-yellow-700">Low Stock</p>
                            <span class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-yellow-500 shadow-sm">↯</span>
                        </div>
                        <p id="aiMetricLowStock" class="text-3xl font-extrabold text-yellow-900 mt-3">0</p>
                        <p class="text-xs text-yellow-700 mt-1">Restock soon</p>
                    </div>

                    <div class="ai-health-card rounded-3xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-bold text-blue-700">Near Expiry</p>
                            <span class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-blue-500 shadow-sm">⏱</span>
                        </div>
                        <p id="aiMetricNearExpiry" class="text-3xl font-extrabold text-blue-900 mt-3">0</p>
                        <p class="text-xs text-blue-700 mt-1">Use soon</p>
                    </div>

                    <div class="ai-health-card rounded-3xl border border-orange-100 bg-orange-50 p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-bold text-orange-700">Affected Items</p>
                            <span class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-orange-500 shadow-sm">🍽</span>
                        </div>
                        <p id="aiMetricAffected" class="text-3xl font-extrabold text-orange-900 mt-3">0</p>
                        <p class="text-xs text-orange-700 mt-1">Unavailable menu items</p>
                    </div>
                </div>

                <!-- Summary -->
                <div class="rounded-3xl border border-orange-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b bg-gradient-to-r from-orange-50 to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-orange-100 text-orange-700 flex items-center justify-center font-extrabold">
                                AI
                            </div>
                            <div>
                                <h4 class="font-extrabold text-gray-900 text-lg">Overall Summary</h4>
                                <p class="text-sm text-gray-500">Human-readable inventory status generated from current records.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <p id="aiInsightSummary" class="text-gray-800 leading-7">
                            —
                        </p>
                    </div>
                </div>

                <!-- Operational Impact -->
                <div class="rounded-3xl border border-orange-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b bg-amber-50/80">
                        <h4 class="font-extrabold text-gray-900 text-lg">Operational Impact</h4>
                        <p class="text-sm text-gray-500 mt-1">What this means for menu availability and restaurant operations.</p>
                    </div>

                    <div id="aiInsightImpact" class="p-5 space-y-3">
                        <!-- impact -->
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="rounded-3xl border border-orange-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b bg-orange-50">
                        <h4 class="font-extrabold text-gray-900 text-lg">Priority Recommendations</h4>
                        <p class="text-sm text-gray-500 mt-1">Suggested actions ranked by urgency.</p>
                    </div>

                    <div id="aiInsightRecommendations" class="p-5 space-y-3">
                        <!-- recommendations -->
                    </div>
                </div>

                <!-- Affected Menu Items -->
                <div class="rounded-3xl border border-orange-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b bg-gray-50">
                        <h4 class="font-extrabold text-gray-900 text-lg">Affected Menu Items</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Menu items currently unavailable due to insufficient linked ingredients.
                        </p>
                    </div>

                    <div id="aiInsightAffectedMenuItems" class="p-5 space-y-3">
                        <!-- affected menu items -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Ingredient Modal -->
<div id="ingredientModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-0 sm:p-4">
    <div class="bg-white rounded-none sm:rounded-2xl shadow-2xl w-full sm:max-w-xl h-[100dvh] sm:h-auto sm:max-h-[92dvh] overflow-hidden flex flex-col">
        <div class="flex items-start justify-between gap-3 px-5 sm:px-6 py-4 border-b shrink-0">
            <div>
                <h3 id="ingredientModalTitle" class="text-lg sm:text-xl font-bold">Add Ingredient</h3>
                <p class="text-xs sm:text-sm text-gray-500">Create or update ingredient details.</p>
            </div>

            <button type="button" onclick="closeIngredientModal()"
                class="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-500 hover:text-black text-xl shrink-0">
                &times;
            </button>
        </div>

        <form id="ingredientForm" class="flex-1 overflow-y-auto px-5 sm:px-6 py-5 space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Ingredient Name</label>
                <input id="ingredientName" type="text" class="w-full border rounded-xl px-4 py-2.5" required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Low Stock Threshold</label>
                <input id="ingredientThreshold" type="number" min="0" step="0.01" class="w-full border rounded-xl px-4 py-2.5" required>
                <p class="text-xs text-gray-400 mt-1">Example: 2 means low stock warning when stock is below 2 units/kg/etc.</p>
            </div>

            <div class="border-t pt-4 flex flex-col sm:flex-row sm:justify-end gap-2">
                <button type="button" onclick="closeIngredientModal()"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                    Cancel
                </button>

                <button id="ingredientSaveBtn" type="submit"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold disabled:opacity-70 disabled:cursor-not-allowed">
                    Save Ingredient
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Combined Manage Stock Modal -->
<div id="manageStockModal" class="fixed inset-0 bg-black/50 backdrop-blur-[2px] hidden items-center justify-center z-50 p-0 sm:p-4">
    <div class="bg-white rounded-none sm:rounded-[28px] shadow-2xl w-full sm:max-w-6xl h-[100dvh] sm:h-auto sm:max-h-[94dvh] overflow-hidden flex flex-col border border-gray-100">
        <!-- Header -->
        <div class="manage-stock-header px-5 sm:px-7 py-5 border-b bg-gradient-to-r from-orange-50 via-white to-amber-50 shrink-0">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-[11px] font-semibold uppercase tracking-wide">
                            Stock Management
                        </span>

                        <span id="manageStockStatusBadge"
                            class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold">
                            Status
                        </span>
                    </div>

                    <h3 id="manageStockModalTitle" class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                        Manage Stock
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Add a new stock batch and review existing stock batches in one place.
                    </p>
                </div>

                <button type="button" onclick="closeManageStockModal()"
                    class="w-10 h-10 rounded-full hover:bg-white/80 border border-transparent hover:border-gray-200 flex items-center justify-center text-gray-500 hover:text-black text-xl shrink-0 transition">
                    &times;
                </button>
            </div>

            <!-- Summary -->
            <div class="manage-stock-summary-grid grid grid-cols-1 md:grid-cols-4 gap-3 mt-5">
                <div class="manage-stock-summary-card rounded-2xl border border-orange-100 bg-white/90 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Current Stock</p>
                    <p id="manageStockCurrentStock" class="mt-1 text-2xl font-bold text-gray-900">0</p>
                    <p class="text-xs text-gray-500 mt-1">Usable stock on hand</p>
                </div>

                <div class="manage-stock-summary-card rounded-2xl border border-blue-100 bg-white/90 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Unit</p>
                    <p id="manageStockUnit" class="mt-1 text-2xl font-bold text-blue-700">unit</p>
                    <p class="text-xs text-gray-500 mt-1">Measurement unit for this ingredient</p>
                </div>

                <div class="manage-stock-summary-card rounded-2xl border border-yellow-100 bg-white/90 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Threshold</p>
                    <p id="manageStockThreshold" class="mt-1 text-2xl font-bold text-yellow-700">0</p>
                    <p class="text-xs text-gray-500 mt-1">Low stock alert basis</p>
                </div>

                <div class="manage-stock-summary-card rounded-2xl border border-emerald-100 bg-white/90 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Stock Batches</p>
                    <p id="manageStockBatchCount" class="mt-1 text-2xl font-bold text-emerald-700">0</p>
                    <p class="text-xs text-gray-500 mt-1">Recorded stock batches</p>
                </div>
            </div>
        </div>

        <div class="manage-stock-body flex-1 overflow-y-auto px-4 sm:px-6 py-5 space-y-5 bg-gray-50/60">
            <input type="hidden" id="manageStockIngredientId">

            <!-- Add / Edit Stock Form -->
            <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b bg-gray-50/80">
                    <h4 id="stockFormTitle" class="font-bold text-gray-900 text-lg">Add New Stock Batch</h4>
                    <p id="stockFormSubtitle" class="text-sm text-gray-500 mt-1">
                        Record new inventory received for this ingredient.
                    </p>
                </div>

                <form id="stockForm" class="p-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Quantity Received</label>
                            <input id="stockQuantityReceived" type="number" min="0.01" step="0.01"
                                placeholder="Example: 5"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">Unit</label>
                            <select id="stockUnit"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                                required>
                                <option value="unit">Unit</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="g">Gram (g)</option>
                                <option value="L">Liter (L)</option>
                                <option value="ml">Milliliter (ml)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="pack">Pack</option>
                                <option value="packs">Packs</option>
                                <option value="bottle">Bottle</option>
                                <option value="bottles">Bottles</option>
                                <option value="can">Can</option>
                                <option value="cans">Cans</option>
                                <option value="tray">Tray</option>
                                <option value="trays">Trays</option>
                                <option value="box">Box</option>
                                <option value="boxes">Boxes</option>
                                <option value="sack">Sack</option>
                                <option value="sacks">Sacks</option>
                                <option value="serving">Serving</option>
                                <option value="servings">Servings</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Auto-selected based on this ingredient’s current unit.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">Unit Cost</label>
                            <input id="stockUnitCost" type="number" min="0" step="0.01"
                                placeholder="Example: 150"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">Received Date</label>
                            <input id="stockReceivedDate" type="date"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">Expiry Date</label>
                            <input id="stockExpiryDate" type="date"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">Supplier</label>
                            <input id="stockSupplier" type="text"
                                placeholder="Optional"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition">
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:justify-end gap-2 border-t pt-4">
                        <button id="cancelEditBatchBtn" type="button" onclick="cancelEditBatch()"
                            class="hidden w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                            Cancel Edit
                        </button>

                        <button type="button" onclick="resetStockForm()"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                            Clear
                        </button>

                        <button id="stockSaveBtn" type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold disabled:opacity-70 disabled:cursor-not-allowed">
                            Add Stock
                        </button>
                    </div>
                </form>
            </div>

            <!-- Record Stock Loss -->
            <div class="rounded-3xl border border-red-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b bg-gradient-to-r from-red-50 to-white">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-lg">Record Stock Loss</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                Record damaged, wasted, missing, or manually used stock. The system deducts from the nearest-expiry usable batch first.
                            </p>
                        </div>

                        <span id="stockLossAvailableBadge"
                            class="inline-flex items-center px-3 py-1.5 rounded-full bg-red-50 text-red-700 border border-red-100 text-xs font-bold shrink-0">
                            Available: 0 unit
                        </span>
                    </div>
                </div>

                <form id="stockLossForm" class="p-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Loss Type</label>
                            <select id="stockLossType"
                                class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white focus:border-red-400 focus:ring-2 focus:ring-red-100 outline-none transition"
                                required>
                                <option value="">Select type</option>
                                <option value="damaged">Damaged</option>
                                <option value="waste">Waste</option>
                                <option value="missing">Missing</option>
                                <option value="manual_usage">Manual Usage</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">Quantity to Deduct</label>
                            <div class="grid grid-cols-[1fr_auto] gap-3 items-center">
                                <input id="stockLossQuantity" type="number" min="0.01" step="0.01"
                                    placeholder="Example: 2"
                                    class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:border-red-400 focus:ring-2 focus:ring-red-100 outline-none transition"
                                    required>

                                <span id="stockLossUnitLabel"
                                    class="inline-flex justify-center min-w-[76px] px-4 py-3 rounded-2xl bg-red-50 border border-red-100 text-sm font-bold text-red-700">
                                    unit
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Reason / Remarks</label>
                        <textarea id="stockLossRemarks" rows="3" maxlength="1000"
                            placeholder="Example: Eggs broke during unloading."
                            class="w-full border border-gray-300 rounded-2xl px-4 py-3 resize-y focus:border-red-400 focus:ring-2 focus:ring-red-100 outline-none transition"
                            required></textarea>

                        <div class="flex items-center justify-between gap-3 mt-2">
                            <p class="text-xs text-gray-400">
                                This action cannot be reversed automatically.
                            </p>

                            <p id="stockLossRemarksCounter" class="text-xs text-gray-400">
                                0 / 1000
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:justify-end gap-2 border-t pt-4">
                        <button type="button" onclick="resetStockLossForm()"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                            Clear
                        </button>

                        <button id="stockLossSaveBtn" type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold disabled:opacity-70 disabled:cursor-not-allowed">
                            Record Stock Loss
                        </button>
                    </div>
                </form>
            </div>

            <!-- Batch List -->
            <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b bg-gray-50/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-lg">Stock Batches</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            View, edit, or delete stock batches for this ingredient.
                        </p>
                    </div>

                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-gray-100 text-gray-600 text-xs font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Batch overview
                    </div>
                </div>

                <!-- Desktop -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[980px] text-sm">
                        <thead class="bg-white text-gray-500">
                            <tr>
                                <th class="text-left px-5 py-4 font-semibold">Received</th>
                                <th class="text-left px-5 py-4 font-semibold">Remaining</th>
                                <th class="text-left px-5 py-4 font-semibold">Unit Cost</th>
                                <th class="text-left px-5 py-4 font-semibold">Received Date</th>
                                <th class="text-left px-5 py-4 font-semibold">Expiry Date</th>
                                <th class="text-left px-5 py-4 font-semibold">Supplier</th>
                                <th class="text-left px-5 py-4 font-semibold">Status</th>
                                <th class="text-left px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="batchTableBody">
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-gray-400">
                                    No stock batches recorded for this ingredient.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile -->
                <div id="batchMobileList" class="md:hidden p-4 space-y-3">
                    <div class="px-4 py-8 text-center text-gray-400">
                        No stock batches recorded for this ingredient.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Designed Stock Loss Confirmation Modal -->
<div id="stockLossConfirmModal"
    class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">

    <div class="w-full max-w-md overflow-hidden rounded-[28px] border border-orange-100 bg-white shadow-2xl">
        <div class="bg-gradient-to-br from-orange-50 via-white to-amber-50 px-6 py-6 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-orange-200 bg-orange-100 text-3xl text-orange-600 shadow-sm">
                !
            </div>

            <h3 class="mt-4 text-2xl font-extrabold text-gray-900">
                Confirm Stock Loss
            </h3>

            <p id="stockLossConfirmMessage" class="mt-3 text-sm leading-6 text-gray-600">
                Are you sure you want to record this stock loss?
            </p>
        </div>

        <div class="border-t border-gray-100 bg-white px-6 py-4">
            <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3">
                <p class="text-sm text-orange-700">
                    This action cannot be undone. The system will deduct the specified quantity from the nearest-expiry usable stock batch.
                </p>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button id="stockLossConfirmCancelBtn" type="button"
                    class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 font-bold text-gray-700 transition hover:bg-gray-50">
                    Cancel
                </button>

                <button id="stockLossConfirmProceedBtn" type="button"
                    class="w-full rounded-2xl bg-orange-500 px-4 py-3 font-bold text-white shadow-sm transition hover:bg-orange-600">
                    Yes, Record Loss
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Global Designed Alert / Confirmation Modal -->
<div id="appMessageModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div id="appMessageCard" class="w-full max-w-md overflow-hidden rounded-[28px] border border-gray-100 bg-white shadow-2xl">
        <div id="appMessageHeader" class="bg-gradient-to-br from-orange-50 via-white to-amber-50 px-6 py-7 text-center">
            <div id="appMessageIcon" class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-orange-200 bg-orange-100 text-3xl font-black text-orange-600 shadow-sm">!</div>
            <h3 id="appMessageTitle" class="mt-4 text-2xl font-extrabold text-gray-900">Notice</h3>
            <p id="appMessageText" class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-600"></p>
        </div>
        <div class="border-t border-gray-100 bg-white px-6 py-5">
            <div id="appMessageActions" class="grid grid-cols-1 gap-3">
                <button id="appMessageCancelBtn" type="button" class="hidden w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 font-bold text-gray-700 transition hover:bg-gray-50">Cancel</button>
                <button id="appMessageOkBtn" type="button" class="w-full rounded-2xl bg-orange-500 px-4 py-3 font-bold text-white shadow-sm transition hover:bg-orange-600">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
let appMessageResolve = null;

function getAppMessageTheme(type = 'info') {
    const themes = {
        success: {
            title: 'Success!', icon: '✓',
            header: 'bg-gradient-to-br from-green-50 via-white to-emerald-50',
            iconClass: 'border-green-200 bg-green-100 text-green-600',
            button: 'bg-green-600 hover:bg-green-700'
        },
        error: {
            title: 'Something Went Wrong', icon: '×',
            header: 'bg-gradient-to-br from-red-50 via-white to-rose-50',
            iconClass: 'border-red-200 bg-red-100 text-red-600',
            button: 'bg-red-600 hover:bg-red-700'
        },
        warning: {
            title: 'Please Check', icon: '!',
            header: 'bg-gradient-to-br from-yellow-50 via-white to-amber-50',
            iconClass: 'border-yellow-200 bg-yellow-100 text-yellow-700',
            button: 'bg-amber-500 hover:bg-amber-600'
        },
        danger: {
            title: 'Confirm Action', icon: '!',
            header: 'bg-gradient-to-br from-red-50 via-white to-orange-50',
            iconClass: 'border-red-200 bg-red-100 text-red-600',
            button: 'bg-red-600 hover:bg-red-700'
        },
        info: {
            title: 'Notice', icon: 'i',
            header: 'bg-gradient-to-br from-orange-50 via-white to-amber-50',
            iconClass: 'border-orange-200 bg-orange-100 text-orange-600',
            button: 'bg-orange-500 hover:bg-orange-600'
        }
    };
    return themes[type] || themes.info;
}

function inferAppMessageType(message) {
    const text = String(message || '').toLowerCase();
    if (text.includes('success') || text.includes('recorded') || text.includes('saved') || text.includes('updated') || text.includes('added') || text.includes('deleted')) return 'success';
    if (text.includes('failed') || text.includes('error') || text.includes('not found') || text.includes('connection')) return 'error';
    if (text.includes('please') || text.includes('not enough') || text.includes('wait') || text.includes('changed')) return 'warning';
    return 'info';
}

function applyAppMessageTheme(type, customTitle = '') {
    const theme = getAppMessageTheme(type);
    const header = document.getElementById('appMessageHeader');
    const icon = document.getElementById('appMessageIcon');
    const title = document.getElementById('appMessageTitle');
    const okBtn = document.getElementById('appMessageOkBtn');

    header.className = `px-6 py-7 text-center ${theme.header}`;
    icon.className = `mx-auto flex h-16 w-16 items-center justify-center rounded-full border text-3xl font-black shadow-sm ${theme.iconClass}`;
    icon.textContent = theme.icon;
    title.textContent = customTitle || theme.title;
    okBtn.className = `w-full rounded-2xl px-4 py-3 font-bold text-white shadow-sm transition ${theme.button}`;
}

function openAppMessageModal() {
    const modal = document.getElementById('appMessageModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeAppMessageModal(result = true) {
    const modal = document.getElementById('appMessageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
    if (appMessageResolve) {
        const resolve = appMessageResolve;
        appMessageResolve = null;
        resolve(result);
    }
}

function showAppAlert(message, type = null, title = '') {
    const finalType = type || inferAppMessageType(message);
    applyAppMessageTheme(finalType, title);
    document.getElementById('appMessageText').textContent = String(message || '');
    document.getElementById('appMessageCancelBtn').classList.add('hidden');
    document.getElementById('appMessageActions').className = 'grid grid-cols-1 gap-3';
    document.getElementById('appMessageOkBtn').textContent = 'OK';
    document.getElementById('appMessageOkBtn').onclick = () => closeAppMessageModal(true);
    openAppMessageModal();
}

function showAppConfirm(title, message, confirmText = 'Confirm') {
    applyAppMessageTheme('danger', title);
    document.getElementById('appMessageText').textContent = String(message || '');
    const cancelBtn = document.getElementById('appMessageCancelBtn');
    const okBtn = document.getElementById('appMessageOkBtn');
    cancelBtn.classList.remove('hidden');
    document.getElementById('appMessageActions').className = 'grid grid-cols-1 gap-3 sm:grid-cols-2';
    cancelBtn.textContent = 'Cancel';
    okBtn.textContent = confirmText;
    openAppMessageModal();

    return new Promise(resolve => {
        appMessageResolve = resolve;
        cancelBtn.onclick = () => closeAppMessageModal(false);
        okBtn.onclick = () => closeAppMessageModal(true);
    });
}

// Replace every browser alert on this page with the designed modal.
window.alert = function(message) {
    showAppAlert(message);
};

document.getElementById('appMessageModal').addEventListener('click', function(event) {
    if (event.target === this && !document.getElementById('appMessageCancelBtn').classList.contains('hidden')) {
        closeAppMessageModal(false);
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && !document.getElementById('appMessageModal').classList.contains('hidden')) {
        const isConfirm = !document.getElementById('appMessageCancelBtn').classList.contains('hidden');
        closeAppMessageModal(isConfirm ? false : true);
    }
});
let ingredients = [];
let filteredIngredients = [];
let editingIngredientId = null;
let activeManageStockIngredientId = null;
let currentManageStockData = null;
let editingBatchId = null;
let manageStockRequestToken = 0;
let stockSaveInProgress = false;
let stockLossInProgress = false;
let ingredientSaveInProgress = false;
let ingredientRequestToken = 0;

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatMoney(value) {
    return `₱${Number(value || 0).toFixed(2)}`;
}

function formatNumber(value) {
    const number = Number(value || 0);

    if (Number.isInteger(number)) {
        return number.toLocaleString();
    }

    return number.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDate(value) {
    if (!value) return '—';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleDateString();
}

function toInputDate(value) {
    if (!value) return '';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return String(value).slice(0, 10);
    }

    return date.toISOString().split('T')[0];
}

function getStockValue(ingredient) {
    if (ingredient.total_stock !== undefined && ingredient.total_stock !== null) {
        return Number(ingredient.total_stock || 0);
    }

    return Number(ingredient.current_stock || 0);
}

function getBatchCount(ingredient) {
    if (!ingredient) return 0;

    if (ingredient.batches_count !== undefined && ingredient.batches_count !== null) {
        return Number(ingredient.batches_count || 0);
    }

    if (ingredient.stock_batches_count !== undefined && ingredient.stock_batches_count !== null) {
        return Number(ingredient.stock_batches_count || 0);
    }

    if (ingredient.batch_count !== undefined && ingredient.batch_count !== null) {
        return Number(ingredient.batch_count || 0);
    }

    if (Array.isArray(ingredient.batches)) {
        return ingredient.batches.length;
    }

    return 0;
}

function getStatusMeta(status) {
    const map = {
        out_of_stock: {
            label: 'Out of Stock',
            className: 'bg-red-100 text-red-600',
        },
        low_stock: {
            label: 'Low Stock',
            className: 'bg-yellow-100 text-yellow-700',
        },
        reorder_soon: {
            label: 'Reorder Soon',
            className: 'bg-orange-100 text-orange-700',
        },
        near_expiry: {
            label: 'Near Expiry',
            className: 'bg-blue-100 text-blue-700',
        },
        active: {
            label: 'Healthy',
            className: 'bg-green-100 text-green-700',
        },
    };

    return map[status] || {
        label: 'Unknown',
        className: 'bg-gray-100 text-gray-600',
    };
}

function getStatusBadgeHtml(status) {
    const meta = getStatusMeta(status);

    return `
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${meta.className}">
            ${safeText(meta.label)}
        </span>
    `;
}

function getBatchStatusBadgeHtml(status) {
    const metaMap = {
        active: 'bg-green-100 text-green-700',
        used_up: 'bg-gray-100 text-gray-600',
        expired: 'bg-red-100 text-red-600',
    };

    const labelMap = {
        active: 'Active',
        used_up: 'Used Up',
        expired: 'Expired',
    };

    return `
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${metaMap[status] || 'bg-gray-100 text-gray-600'}">
            ${safeText(labelMap[status] || 'Unknown')}
        </span>
    `;
}

function setButtonLoading(button, isLoading, loadingText = 'Loading...') {
    if (!button) return;

    if (isLoading) {
        button.dataset.originalText = button.textContent;
        button.textContent = loadingText;
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-not-allowed');
    } else {
        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
        button.classList.remove('opacity-70', 'cursor-not-allowed');
    }
}

function setStockUnitValue(unit) {
    const select = document.getElementById('stockUnit');
    const value = unit || 'unit';

    if (!select) return;

    const existingOption = Array.from(select.options).find(option => option.value === value);

    if (!existingOption) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value;
        select.appendChild(option);
    }

    select.value = value;
}

function openAiInsightModal() {
    const modal = document.getElementById('aiInsightModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAiInsightModal() {
    const modal = document.getElementById('aiInsightModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function getAiHealthMeta(data) {
    const criticalCount = Number(data.critical_count || (data.critical || []).length || 0);
    const lowCount = Number(data.low_stock_count || (data.low_stock || []).length || 0);
    const nearExpiryCount = Number(data.near_expiry_count || (data.near_expiry || []).length || 0);
    const affectedCount = Number(data.affected_menu_count || (data.affected_menu_items || []).length || 0);

    if (criticalCount > 0 || affectedCount > 0) {
        return {
            label: 'Critical',
            className: 'bg-red-100 text-red-700 border-red-200',
        };
    }

    if (lowCount > 0 || nearExpiryCount > 0) {
        return {
            label: 'Needs Attention',
            className: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        };
    }

    return {
        label: 'Stable',
        className: 'bg-green-100 text-green-700 border-green-200',
    };
}

function renderAiSmartList(containerId, items, emptyText, tone = 'orange') {
    const container = document.getElementById(containerId);

    if (!container) return;

    const toneMap = {
        red: {
            border: 'border-red-100',
            bg: 'bg-red-50/40',
            dot: 'bg-red-500',
            text: 'text-red-700',
        },
        yellow: {
            border: 'border-yellow-100',
            bg: 'bg-yellow-50/50',
            dot: 'bg-yellow-500',
            text: 'text-yellow-700',
        },
        blue: {
            border: 'border-blue-100',
            bg: 'bg-blue-50/50',
            dot: 'bg-blue-500',
            text: 'text-blue-700',
        },
        orange: {
            border: 'border-orange-100',
            bg: 'bg-orange-50/40',
            dot: 'bg-orange-500',
            text: 'text-gray-800',
        },
        gray: {
            border: 'border-gray-100',
            bg: 'bg-gray-50',
            dot: 'bg-gray-400',
            text: 'text-gray-700',
        },
    };

    const style = toneMap[tone] || toneMap.orange;

    if (!items || !items.length) {
        container.innerHTML = `
            <div class="rounded-2xl border border-dashed ${style.border} ${style.bg} px-4 py-5 text-center text-sm text-gray-400">
                ${safeText(emptyText)}
            </div>
        `;
        return;
    }

    container.innerHTML = items.map(item => {
        const priority = item.priority || item.level || '';
        const title = item.title || '';
        const message = item.message || item.text || item;

        return `
            <div class="rounded-2xl border ${style.border} bg-white px-4 py-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="mt-1 w-2.5 h-2.5 rounded-full ${style.dot} shrink-0"></span>

                    <div class="min-w-0">
                        ${title ? `<p class="text-sm font-bold ${style.text} mb-1">${safeText(title)}</p>` : ''}
                        <p class="text-sm leading-relaxed text-gray-700">${safeText(message)}</p>
                        ${priority ? `
                            <span class="inline-flex mt-2 px-2.5 py-1 rounded-full bg-orange-50 text-orange-700 text-[11px] font-bold border border-orange-100">
                                ${safeText(priority)}
                            </span>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

async function generateAiInventoryInsight() {
    const button = document.getElementById('generateAiInsightBtn');
    const loading = document.getElementById('aiInsightLoading');
    const empty = document.getElementById('aiInsightEmpty');
    const content = document.getElementById('aiInsightContent');

    setButtonLoading(button, true, 'Analyzing...');
    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    content.classList.add('hidden');

    try {
        const res = await fetch('/api/admin/inventory-insights', {
            headers: {
                'Accept': 'application/json',
            }
        });

        const data = await res.json();

        if (!res.ok || !data.success) {
            alert(data.message || 'Failed to generate AI inventory insight.');
            empty.classList.remove('hidden');
            return;
        }

        const health = getAiHealthMeta(data);
        const healthBadge = document.getElementById('aiInsightHealthBadge');

        if (healthBadge) {
            healthBadge.className = `inline-flex items-center px-3 py-1 rounded-full border text-[11px] font-bold shadow-sm ${health.className}`;
            healthBadge.textContent = `Status: ${health.label}`;
        }

        document.getElementById('aiInsightGeneratedAt').textContent = data.generated_at
            ? `Generated: ${data.generated_at}`
            : 'Generated now';

        document.getElementById('aiMetricCritical').textContent = data.critical_count ?? (data.critical || []).length ?? 0;
        document.getElementById('aiMetricLowStock').textContent = data.low_stock_count ?? (data.low_stock || []).length ?? 0;
        document.getElementById('aiMetricNearExpiry').textContent = data.near_expiry_count ?? (data.near_expiry || []).length ?? 0;
        document.getElementById('aiMetricAffected').textContent = data.affected_menu_count ?? (data.affected_menu_items || []).length ?? 0;

        document.getElementById('aiInsightSummary').textContent = data.summary || 'No summary available.';

        renderAiSmartList(
            'aiInsightImpact',
            data.operational_impact || [],
            'No operational impact detected right now.',
            'orange'
        );

        renderAiSmartList(
            'aiInsightRecommendations',
            data.recommendations || [],
            'No recommendation needed right now.',
            health.label === 'Critical' ? 'red' : 'orange'
        );

        renderAiSmartList(
            'aiInsightAffectedMenuItems',
            data.affected_menu_items || [],
            'No affected menu items.',
            'red'
        );

        content.classList.remove('hidden');
    } catch (error) {
        console.error('Generate AI insight failed:', error);
        alert('Failed to generate AI insight. Please check your connection.');
        empty.classList.remove('hidden');
    } finally {
        loading.classList.add('hidden');
        setButtonLoading(button, false);
    }
}

async function loadIngredients() {
    const tbody = document.getElementById('ingredientsTableBody');
    const mobileList = document.getElementById('ingredientsMobileList');

    try {
        const res = await fetch('/api/admin/ingredients', {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) {
            const message = `Failed to load ingredients. API returned ${res.status}.`;

            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-red-500">
                        ${safeText(message)}
                    </td>
                </tr>
            `;

            mobileList.innerHTML = `
                <div class="px-4 py-8 text-center text-red-500">
                    ${safeText(message)}
                </div>
            `;
            return;
        }

        ingredients = await res.json();
        updateSummaryCards();
        applyFilters();
    } catch (error) {
        console.error('Load ingredients failed:', error);

        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-red-500">
                    Failed to load ingredients. Please check your connection.
                </td>
            </tr>
        `;

        mobileList.innerHTML = `
            <div class="px-4 py-8 text-center text-red-500">
                Failed to load ingredients. Please check your connection.
            </div>
        `;
    }
}

function updateSummaryCards() {
    const outOfStock = ingredients.filter(item => item.stock_status === 'out_of_stock').length;
    const lowStock = ingredients.filter(item => ['low_stock', 'reorder_soon'].includes(item.stock_status)).length;
    const nearExpiry = ingredients.filter(item => item.stock_status === 'near_expiry').length;
    const healthy = ingredients.filter(item => item.stock_status === 'active').length;

    document.getElementById('summaryOutOfStock').textContent = outOfStock;
    document.getElementById('summaryLowStock').textContent = lowStock;
    document.getElementById('summaryNearExpiry').textContent = nearExpiry;
    document.getElementById('summaryHealthy').textContent = healthy;
}

function applyFilters() {
    const search = document.getElementById('ingredientSearch').value.toLowerCase().trim();
    const status = document.getElementById('statusFilter').value;

    filteredIngredients = ingredients.filter(item => {
        const name = String(item.name || '').toLowerCase();
        const unit = String(item.unit || '').toLowerCase();

        const matchesSearch = name.includes(search) || unit.includes(search);
        const matchesStatus = status === 'all' ? true : item.stock_status === status;

        return matchesSearch && matchesStatus;
    });

    renderIngredientsTable();
    renderIngredientsMobileList();
}

function renderIngredientsTable() {
    const tbody = document.getElementById('ingredientsTableBody');

    if (!filteredIngredients.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                    No ingredients found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filteredIngredients.map(item => {
        const stock = getStockValue(item);

        return `
            <tr class="border-t hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-semibold text-gray-900">${safeText(item.name)}</p>
                    <p class="text-xs text-gray-400 mt-0.5">${safeText(getBatchCount(item))} stock batch(es)</p>
                </td>

                <td class="px-6 py-4 font-semibold text-gray-900">
                    ${safeText(formatNumber(stock))} ${safeText(item.unit || 'unit')}
                </td>

                <td class="px-6 py-4 text-gray-600">
                    ${safeText(item.unit || 'unit')}
                </td>

                <td class="px-6 py-4 text-gray-600">
                    ${safeText(formatNumber(item.threshold || 0))}
                </td>

                <td class="px-6 py-4 text-gray-600">
                    ${safeText(formatDate(item.nearest_expiry_date))}
                </td>

                <td class="px-6 py-4 text-gray-600">
                    ${safeText(formatMoney(item.stock_value || 0))}
                </td>

                <td class="px-6 py-4">
                    ${getStatusBadgeHtml(item.stock_status)}
                </td>

                <td class="px-6 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="openManageStockModal(${item.id})"
                            class="px-3 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold">
                            Manage Stock
                        </button>

                        <button onclick="openIngredientModal(${item.id})"
                            class="px-3 py-2 rounded-lg border text-gray-700 hover:bg-gray-50 text-xs">
                            Edit
                        </button>

                        <button onclick="deleteIngredient(${item.id}, this)"
                            class="px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-xs">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderIngredientsMobileList() {
    const container = document.getElementById('ingredientsMobileList');

    if (!filteredIngredients.length) {
        container.innerHTML = `
            <div class="px-4 py-8 text-center text-gray-400">
                No ingredients found.
            </div>
        `;
        return;
    }

    container.innerHTML = filteredIngredients.map(item => {
        const stock = getStockValue(item);

        return `
            <div class="rounded-2xl border bg-white shadow-sm p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-900">${safeText(item.name)}</h3>
                        <p class="text-xs text-gray-400 mt-1">${safeText(getBatchCount(item))} stock batch(es)</p>
                    </div>

                    <div class="shrink-0">
                        ${getStatusBadgeHtml(item.stock_status)}
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-2">
                    <div class="rounded-xl bg-gray-50 border px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Current Stock</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            ${safeText(formatNumber(stock))} ${safeText(item.unit || 'unit')}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 border px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Threshold</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            ${safeText(formatNumber(item.threshold || 0))}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 border px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Nearest Expiry</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            ${safeText(formatDate(item.nearest_expiry_date))}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 border px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Stock Value</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            ${safeText(formatMoney(item.stock_value || 0))}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 mt-4">
                    <button onclick="openManageStockModal(${item.id})"
                        class="px-3 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold">
                        Manage
                    </button>

                    <button onclick="openIngredientModal(${item.id})"
                        class="px-3 py-2 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                        Edit
                    </button>

                    <button onclick="deleteIngredient(${item.id}, this)"
                        class="px-3 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold">
                        Delete
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function lockIngredientForm(isLocked, text = 'Saving...') {
    const saveBtn = document.getElementById('ingredientSaveBtn');
    const inputs = document.querySelectorAll('#ingredientForm input, #ingredientForm button');

    inputs.forEach(input => {
        input.disabled = isLocked;
        input.classList.toggle('opacity-70', isLocked);
        input.classList.toggle('cursor-not-allowed', isLocked);
    });

    if (saveBtn) {
        if (isLocked) {
            saveBtn.dataset.originalText = saveBtn.dataset.originalText || saveBtn.textContent;
            saveBtn.textContent = text;
        } else {
            saveBtn.textContent = saveBtn.dataset.originalText || saveBtn.textContent;
        }
    }
}

function openIngredientModal(id = null) {
    if (ingredientSaveInProgress) {
        alert('Please wait until the current ingredient update is finished.');
        return;
    }

    ingredientRequestToken++;
    editingIngredientId = id;

    if (id) {
        const ingredient = ingredients.find(item => Number(item.id) === Number(id));
        if (!ingredient) return;

        document.getElementById('ingredientModalTitle').textContent = 'Edit Ingredient';
        document.getElementById('ingredientSaveBtn').textContent = 'Update Ingredient';
        document.getElementById('ingredientName').value = ingredient.name || '';
        document.getElementById('ingredientThreshold').value = ingredient.threshold || 0;
    } else {
        document.getElementById('ingredientModalTitle').textContent = 'Add Ingredient';
        document.getElementById('ingredientSaveBtn').textContent = 'Save Ingredient';
        document.getElementById('ingredientForm').reset();
    }

    const modal = document.getElementById('ingredientModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeIngredientModal() {
    if (ingredientSaveInProgress) {
        return;
    }

    ingredientRequestToken++;
    editingIngredientId = null;

    const modal = document.getElementById('ingredientModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('ingredientForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (ingredientSaveInProgress) {
        return;
    }

    const requestToken = ingredientRequestToken;
    const isEditing = Boolean(editingIngredientId);
    const editingId = editingIngredientId;

    const payload = {
        name: document.getElementById('ingredientName').value.trim(),
        threshold: Number(document.getElementById('ingredientThreshold').value),
    };

    if (!payload.name) {
        alert('Please enter ingredient name.');
        return;
    }

    let url = '/api/admin/ingredients';
    let method = 'POST';

    if (isEditing) {
        url = `/api/admin/ingredients/${editingId}`;
        method = 'PUT';
    }

    ingredientSaveInProgress = true;
    lockIngredientForm(true, isEditing ? 'Updating...' : 'Saving...');

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!res.ok) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                alert(firstError);
            } else {
                alert(data.message || 'Failed to save ingredient.');
            }
            return;
        }

        const updatedIngredient = data?.ingredient || data?.data || data;

        if (updatedIngredient && updatedIngredient.id) {
            updateIngredientInMemory(updatedIngredient);
        } else if (!isEditing) {
            const tempIngredient = {
                id: `temp-${Date.now()}`,
                name: payload.name,
                threshold: payload.threshold,
                current_stock: 0,
                total_stock: 0,
                unit: 'unit',
                stock_status: 'out_of_stock',
                batches: [],
                nearest_expiry_date: null,
                stock_value: 0,
            };

            updateIngredientInMemory(tempIngredient);
            loadIngredients();
        } else {
            const existingIndex = ingredients.findIndex(item => Number(item.id) === Number(editingId));

            if (existingIndex >= 0) {
                ingredients[existingIndex] = {
                    ...ingredients[existingIndex],
                    name: payload.name,
                    threshold: payload.threshold,
                };

                updateSummaryCards();
                applyFilters();
            }
        }

        if (requestToken === ingredientRequestToken) {
            const modal = document.getElementById('ingredientModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            editingIngredientId = null;
            ingredientRequestToken++;
        }
    } catch (error) {
        console.error('Save ingredient failed:', error);
        alert('Failed to save ingredient. Please check your connection.');
    } finally {
        ingredientSaveInProgress = false;
        lockIngredientForm(false);
    }
});

function setManageStockStatusBadge(status) {
    const badge = document.getElementById('manageStockStatusBadge');
    const meta = getStatusMeta(status);

    badge.className = `inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold ${meta.className}`;
    badge.textContent = meta.label;
}


function updateIngredientInMemory(updatedIngredient) {
    if (!updatedIngredient || !updatedIngredient.id) return;

    const index = ingredients.findIndex(item => Number(item.id) === Number(updatedIngredient.id));

    if (index >= 0) {
        ingredients[index] = updatedIngredient;
    } else {
        ingredients.push(updatedIngredient);
    }

    updateSummaryCards();
    applyFilters();
}

function updateManageStockModalFromData(data) {
    if (!data || !data.id) return;

    currentManageStockData = data;
    activeManageStockIngredientId = data.id;
    document.getElementById('manageStockIngredientId').value = data.id;

    const stock = getStockValue(data);

    document.getElementById('manageStockModalTitle').textContent = `Manage Stock - ${data.name}`;
    document.getElementById('manageStockCurrentStock').textContent = `${formatNumber(stock)} ${data.unit || 'unit'}`;
    document.getElementById('manageStockUnit').textContent = data.unit || 'unit';
    document.getElementById('manageStockThreshold').textContent = formatNumber(data.threshold || 0);
    document.getElementById('manageStockBatchCount').textContent = Array.isArray(data.batches) ? data.batches.length : getBatchCount(data);

    const stockLossAvailableBadge = document.getElementById('stockLossAvailableBadge');
    const stockLossUnitLabel = document.getElementById('stockLossUnitLabel');

    if (stockLossAvailableBadge) {
        stockLossAvailableBadge.textContent = `Available: ${formatNumber(stock)} ${data.unit || 'unit'}`;
    }

    if (stockLossUnitLabel) {
        stockLossUnitLabel.textContent = data.unit || 'unit';
    }

    setManageStockStatusBadge(data.stock_status);
    renderBatches(data.batches || []);
    setStockUnitValue(data.unit || 'unit');
}

function lockManageStockActions(isLocked, text = 'Processing...') {
    const stockSaveBtn = document.getElementById('stockSaveBtn');
    const clearBtn = document.querySelector('#stockForm button[onclick="resetStockForm()"]');
    const cancelEditBtn = document.getElementById('cancelEditBatchBtn');
    const stockInputs = document.querySelectorAll('#stockForm input, #stockForm select, #stockForm button');

    stockInputs.forEach(input => {
        input.disabled = isLocked;
        input.classList.toggle('opacity-70', isLocked);
        input.classList.toggle('cursor-not-allowed', isLocked);
    });

    if (stockSaveBtn) {
        if (isLocked) {
            stockSaveBtn.dataset.originalText = stockSaveBtn.dataset.originalText || stockSaveBtn.textContent;
            stockSaveBtn.textContent = text;
        } else {
            stockSaveBtn.textContent = stockSaveBtn.dataset.originalText || stockSaveBtn.textContent;
        }
    }

    if (clearBtn) clearBtn.disabled = isLocked;
    if (cancelEditBtn) cancelEditBtn.disabled = isLocked;
}

function lockStockLossActions(isLocked, text = 'Recording...') {
    const saveBtn = document.getElementById('stockLossSaveBtn');
    const inputs = document.querySelectorAll('#stockLossForm input, #stockLossForm select, #stockLossForm textarea, #stockLossForm button');

    inputs.forEach(input => {
        input.disabled = isLocked;
        input.classList.toggle('opacity-70', isLocked);
        input.classList.toggle('cursor-not-allowed', isLocked);
    });

    if (saveBtn) {
        if (isLocked) {
            saveBtn.dataset.originalText = saveBtn.dataset.originalText || saveBtn.textContent;
            saveBtn.textContent = text;
        } else {
            saveBtn.textContent = saveBtn.dataset.originalText || 'Record Stock Loss';
        }
    }
}

function resetStockLossForm() {
    const form = document.getElementById('stockLossForm');

    if (form) {
        form.reset();
    }

    const counter = document.getElementById('stockLossRemarksCounter');

    if (counter) {
        counter.textContent = '0 / 1000';
    }

    const unitLabel = document.getElementById('stockLossUnitLabel');

    if (unitLabel) {
        unitLabel.textContent = currentManageStockData?.unit || 'unit';
    }
}

async function refreshManageStockDetails(ingredientId, requestToken = manageStockRequestToken) {
    const res = await fetch(`/api/admin/ingredients/${ingredientId}`, {
        headers: {
            'Accept': 'application/json',
        }
    });

    const data = await res.json();

    if (!res.ok) {
        throw new Error(data.message || 'Failed to load stock details.');
    }

    if (requestToken !== manageStockRequestToken || Number(activeManageStockIngredientId) !== Number(ingredientId)) {
        return null;
    }

    updateIngredientInMemory(data);
    updateManageStockModalFromData(data);

    return data;
}

function resetStockForm() {
    editingBatchId = null;

    document.getElementById('stockForm').reset();
    document.getElementById('stockFormTitle').textContent = 'Add New Stock Batch';
    document.getElementById('stockFormSubtitle').textContent = 'Record new inventory received for this ingredient.';
    document.getElementById('stockSaveBtn').textContent = 'Add Stock';
    document.getElementById('cancelEditBatchBtn').classList.add('hidden');

    const ingredient = ingredients.find(item => Number(item.id) === Number(activeManageStockIngredientId));

    if (ingredient) {
        setStockUnitValue(ingredient.unit || 'unit');
    } else {
        setStockUnitValue('unit');
    }

    document.getElementById('stockReceivedDate').value = new Date().toISOString().split('T')[0];
}

async function openManageStockModal(id) {
    if (stockSaveInProgress) {
        alert('Please wait until the current stock update is finished.');
        return;
    }

    const requestToken = ++manageStockRequestToken;
    activeManageStockIngredientId = id;
    currentManageStockData = null;
    editingBatchId = null;

    const modal = document.getElementById('manageStockModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.getElementById('manageStockIngredientId').value = id;
    document.getElementById('manageStockModalTitle').textContent = 'Manage Stock';
    document.getElementById('manageStockCurrentStock').textContent = 'Loading...';
    document.getElementById('manageStockUnit').textContent = 'Loading...';
    document.getElementById('manageStockThreshold').textContent = 'Loading...';
    document.getElementById('manageStockBatchCount').textContent = 'Loading...';
    document.getElementById('stockFormTitle').textContent = 'Add New Stock Batch';
    document.getElementById('stockFormSubtitle').textContent = 'Record new inventory received for this ingredient.';
    document.getElementById('stockSaveBtn').textContent = 'Add Stock';
    document.getElementById('cancelEditBatchBtn').classList.add('hidden');
    setManageStockStatusBadge(null);
    resetStockLossForm();
    lockManageStockActions(true, 'Loading...');
    lockStockLossActions(true, 'Loading...');

    document.getElementById('batchTableBody').innerHTML = `
        <tr>
            <td colspan="8" class="px-5 py-10 text-center text-gray-400">
                Loading stock batches...
            </td>
        </tr>
    `;
    document.getElementById('batchMobileList').innerHTML = `
        <div class="px-4 py-8 text-center text-gray-400">
            Loading stock batches...
        </div>
    `;

    try {
        const data = await refreshManageStockDetails(id, requestToken);

        if (!data) return;

        resetStockForm();
        resetStockLossForm();
        lockManageStockActions(false);
        lockStockLossActions(false);
    } catch (error) {
        if (requestToken !== manageStockRequestToken) return;

        console.error('Load manage stock failed:', error);
        alert(error.message || 'Failed to load stock details.');
        lockManageStockActions(false);
        lockStockLossActions(false);
    }
}

function closeManageStockModal() {
    if (stockSaveInProgress || stockLossInProgress) {
        return;
    }

    manageStockRequestToken++;
    stockSaveInProgress = false;
    stockLossInProgress = false;
    lockManageStockActions(false);
    lockStockLossActions(false);
    resetStockLossForm();
    activeManageStockIngredientId = null;
    currentManageStockData = null;
    editingBatchId = null;

    const modal = document.getElementById('manageStockModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function renderBatches(batches) {
    const tbody = document.getElementById('batchTableBody');
    const mobileList = document.getElementById('batchMobileList');

    if (!batches.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-5 py-10 text-center text-gray-400">
                    No stock batches recorded for this ingredient.
                </td>
            </tr>
        `;

        mobileList.innerHTML = `
            <div class="rounded-2xl border border-dashed bg-gray-50 px-4 py-10 text-center text-gray-400">
                No stock batches recorded for this ingredient.
            </div>
        `;
        return;
    }

    tbody.innerHTML = batches.map(batch => `
        <tr class="border-t hover:bg-orange-50/30 transition">
            <td class="px-5 py-4">${safeText(formatNumber(batch.quantity_received || 0))}</td>
            <td class="px-5 py-4 font-semibold text-gray-900">${safeText(formatNumber(batch.quantity_remaining || 0))}</td>
            <td class="px-5 py-4">${safeText(formatMoney(batch.unit_cost || 0))}</td>
            <td class="px-5 py-4">${safeText(formatDate(batch.received_date))}</td>
            <td class="px-5 py-4">${safeText(formatDate(batch.expiry_date))}</td>
            <td class="px-5 py-4">${safeText(batch.supplier || '—')}</td>
            <td class="px-5 py-4">${getBatchStatusBadgeHtml(batch.status)}</td>
            <td class="px-5 py-4">
                <div class="flex flex-wrap gap-2">
                    <button onclick="editBatch(${batch.id})"
                        class="px-3 py-2 rounded-lg border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                        Edit
                    </button>

                    <button onclick="deleteBatch(${batch.id}, this)"
                        class="px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold">
                        Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    mobileList.innerHTML = batches.map(batch => `
        <div class="rounded-2xl border bg-white shadow-sm p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h4 class="font-bold text-gray-900">${safeText(formatNumber(batch.quantity_remaining || 0))} remaining</h4>
                    <p class="text-xs text-gray-400 mt-1">Batch stock record</p>
                </div>

                <div class="shrink-0">
                    ${getBatchStatusBadgeHtml(batch.status)}
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2">
                <div class="rounded-xl bg-gray-50 border px-3 py-2">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Quantity Received</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">${safeText(formatNumber(batch.quantity_received || 0))}</p>
                </div>

                <div class="rounded-xl bg-gray-50 border px-3 py-2">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Remaining</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">${safeText(formatNumber(batch.quantity_remaining || 0))}</p>
                </div>

                <div class="rounded-xl bg-gray-50 border px-3 py-2">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Unit Cost</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">${safeText(formatMoney(batch.unit_cost || 0))}</p>
                </div>

                <div class="rounded-xl bg-gray-50 border px-3 py-2">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Received Date</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">${safeText(formatDate(batch.received_date))}</p>
                </div>

                <div class="rounded-xl bg-gray-50 border px-3 py-2">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Expiry Date</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">${safeText(formatDate(batch.expiry_date))}</p>
                </div>

                <div class="rounded-xl bg-gray-50 border px-3 py-2">
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Supplier</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">${safeText(batch.supplier || '—')}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-4">
                <button onclick="editBatch(${batch.id})"
                    class="px-3 py-2 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                    Edit
                </button>

                <button onclick="deleteBatch(${batch.id}, this)"
                    class="px-3 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold">
                    Delete
                </button>
            </div>
        </div>
    `).join('');
}

function editBatch(batchId) {
    if (!currentManageStockData || !Array.isArray(currentManageStockData.batches)) return;

    const batch = currentManageStockData.batches.find(item => Number(item.id) === Number(batchId));
    if (!batch) return;

    editingBatchId = batch.id;

    document.getElementById('stockQuantityReceived').value = batch.quantity_received || '';
    document.getElementById('stockUnitCost').value = batch.unit_cost || '';
    document.getElementById('stockReceivedDate').value = toInputDate(batch.received_date);
    document.getElementById('stockExpiryDate').value = toInputDate(batch.expiry_date);
    document.getElementById('stockSupplier').value = batch.supplier || '';

    setStockUnitValue(currentManageStockData.unit || 'unit');

    document.getElementById('stockFormTitle').textContent = 'Edit Stock Batch';
    document.getElementById('stockFormSubtitle').textContent = 'Update the selected stock batch details.';
    document.getElementById('stockSaveBtn').textContent = 'Update Batch';
    document.getElementById('cancelEditBatchBtn').classList.remove('hidden');

    document.getElementById('stockForm').scrollIntoView({
        behavior: 'smooth',
        block: 'center',
    });
}

function cancelEditBatch() {
    editingBatchId = null;
    document.getElementById('stockFormTitle').textContent = 'Add New Stock Batch';
    document.getElementById('stockFormSubtitle').textContent = 'Record new inventory received for this ingredient.';
    document.getElementById('stockSaveBtn').textContent = 'Add Stock';
    document.getElementById('cancelEditBatchBtn').classList.add('hidden');
    resetStockForm();
}

let stockLossConfirmResolver = null;

function openStockLossConfirmModal(message) {
    const modal = document.getElementById('stockLossConfirmModal');
    const messageElement = document.getElementById('stockLossConfirmMessage');

    messageElement.textContent = message;
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    return new Promise(resolve => {
        stockLossConfirmResolver = resolve;
    });
}

function closeStockLossConfirmModal(result) {
    const modal = document.getElementById('stockLossConfirmModal');

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    if (stockLossConfirmResolver) {
        stockLossConfirmResolver(result);
        stockLossConfirmResolver = null;
    }
}

document.getElementById('stockLossConfirmCancelBtn').addEventListener('click', function() {
    closeStockLossConfirmModal(false);
});

document.getElementById('stockLossConfirmProceedBtn').addEventListener('click', function() {
    closeStockLossConfirmModal(true);
});

document.getElementById('stockLossConfirmModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeStockLossConfirmModal(false);
    }
});

document.addEventListener('keydown', function(event) {
    if (
        event.key === 'Escape'
        && !document.getElementById('stockLossConfirmModal').classList.contains('hidden')
    ) {
        closeStockLossConfirmModal(false);
    }
});

document.getElementById('stockLossRemarks').addEventListener('input', function() {
    document.getElementById('stockLossRemarksCounter').textContent = `${this.value.length} / 1000`;
});

document.getElementById('stockLossForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (stockLossInProgress) {
        return;
    }

    const ingredientId = document.getElementById('manageStockIngredientId').value;
    const requestToken = manageStockRequestToken;
    const type = document.getElementById('stockLossType').value;
    const quantity = Number(document.getElementById('stockLossQuantity').value);
    const remarks = document.getElementById('stockLossRemarks').value.trim();
    const availableStock = getStockValue(currentManageStockData || {});

    if (!ingredientId) {
        alert('Ingredient not found.');
        return;
    }

    if (Number(activeManageStockIngredientId) !== Number(ingredientId)) {
        alert('Stock panel changed. Please reopen the correct ingredient before recording a stock loss.');
        return;
    }

    if (!type) {
        alert('Please select a stock loss type.');
        return;
    }

    if (!Number.isFinite(quantity) || quantity <= 0) {
        alert('Please enter a valid quantity.');
        return;
    }

    if (quantity > availableStock) {
        alert(`Not enough usable stock. Available: ${formatNumber(availableStock)} ${currentManageStockData?.unit || 'unit'}.`);
        return;
    }

    if (!remarks) {
        alert('Please enter the reason or remarks.');
        return;
    }

    const typeLabels = {
        damaged: 'Damaged',
        waste: 'Waste',
        missing: 'Missing',
        manual_usage: 'Manual Usage',
    };

    const confirmed = await openStockLossConfirmModal(
        `Record ${typeLabels[type] || 'stock loss'} of ${formatNumber(quantity)} ${currentManageStockData?.unit || 'unit'}?`
    );

    if (!confirmed) {
        return;
    }

    stockLossInProgress = true;
    lockStockLossActions(true, 'Recording...');

    try {
        const res = await fetch(`/api/admin/ingredients/${ingredientId}/stock-loss`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                type,
                quantity,
                remarks,
            }),
        });

        const data = await res.json();

        if (!res.ok) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                alert(firstError);
            } else {
                alert(data.message || 'Failed to record stock loss.');
            }
            return;
        }

        if (
            requestToken !== manageStockRequestToken
            || Number(activeManageStockIngredientId) !== Number(ingredientId)
        ) {
            alert('Stock loss was recorded, but the panel changed. Please reopen the ingredient.');
            return;
        }

        const updatedIngredient = data?.ingredient || data?.data || null;

        if (updatedIngredient?.id) {
            updateIngredientInMemory(updatedIngredient);
            updateManageStockModalFromData(updatedIngredient);
        } else {
            await refreshManageStockDetails(ingredientId, requestToken);
        }

        resetStockLossForm();
        alert(data.message || 'Stock loss recorded successfully.');
    } catch (error) {
        console.error('Record stock loss failed:', error);
        alert('Failed to record stock loss. Please check your connection.');
    } finally {
        stockLossInProgress = false;
        lockStockLossActions(false);
    }
});

document.getElementById('stockForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (stockSaveInProgress) return;

    const saveBtn = document.getElementById('stockSaveBtn');
    const ingredientId = document.getElementById('manageStockIngredientId').value;
    const requestToken = manageStockRequestToken;

    if (!ingredientId) {
        alert('Ingredient not found.');
        return;
    }

    if (Number(activeManageStockIngredientId) !== Number(ingredientId)) {
        alert('Stock panel changed. Please reopen the correct ingredient before saving.');
        return;
    }

    const payload = {
        quantity_received: Number(document.getElementById('stockQuantityReceived').value),
        unit: document.getElementById('stockUnit').value,
        unit_cost: Number(document.getElementById('stockUnitCost').value),
        received_date: document.getElementById('stockReceivedDate').value || null,
        expiry_date: document.getElementById('stockExpiryDate').value,
        supplier: document.getElementById('stockSupplier').value || null,
        remarks: null,
    };

    const isEditing = Boolean(editingBatchId);
    const url = isEditing
        ? `/api/admin/ingredients/${ingredientId}/batches/${editingBatchId}`
        : `/api/admin/ingredients/${ingredientId}/stock`;

    const method = isEditing ? 'PUT' : 'POST';

    stockSaveInProgress = true;
    lockManageStockActions(true, isEditing ? 'Updating...' : 'Adding...');
    lockStockLossActions(true, 'Please wait...');

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (!res.ok) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                alert(firstError);
            } else {
                alert(data.message || (isEditing ? 'Failed to update stock batch.' : 'Failed to add stock.'));
            }
            return;
        }

        if (requestToken !== manageStockRequestToken || Number(activeManageStockIngredientId) !== Number(ingredientId)) {
            alert('Stock was saved, but the panel changed while saving. Please reopen the correct ingredient.');
            return;
        }

        const updatedIngredient = data && data.id ? data : (data.data && data.data.id ? data.data : null);

        editingBatchId = null;
        document.getElementById('stockFormTitle').textContent = 'Add New Stock Batch';
        document.getElementById('stockFormSubtitle').textContent = 'Record new inventory received for this ingredient.';
        document.getElementById('stockSaveBtn').dataset.originalText = 'Add Stock';
        document.getElementById('stockSaveBtn').textContent = 'Add Stock';
        document.getElementById('cancelEditBatchBtn').classList.add('hidden');

        if (updatedIngredient) {
            updateIngredientInMemory(updatedIngredient);
            updateManageStockModalFromData(updatedIngredient);
        } else {
            await refreshManageStockDetails(ingredientId, requestToken);
        }

        resetStockForm();
    } catch (error) {
        console.error('Save stock failed:', error);
        alert('Failed to save stock. Please check your connection.');
    } finally {
        stockSaveInProgress = false;
        lockManageStockActions(false);
        lockStockLossActions(false);
    }
});

async function deleteBatch(batchId, button) {
    const ingredientId = document.getElementById('manageStockIngredientId').value;

    if (!ingredientId || !batchId) {
        alert('Batch not found.');
        return;
    }

    if (!(await showAppConfirm('Delete Stock Batch', 'Are you sure you want to delete this stock batch? This action cannot be undone.', 'Delete Batch'))) return;

    setButtonLoading(button, true, 'Deleting...');

    try {
        const res = await fetch(`/api/admin/ingredients/${ingredientId}/batches/${batchId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            },
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            alert(data.message || 'Failed to delete stock batch.');
            return;
        }

        editingBatchId = null;
        await refreshManageStockDetails(ingredientId, manageStockRequestToken);
    } catch (error) {
        console.error('Delete batch failed:', error);
        alert('Failed to delete stock batch. Please check your connection.');
    } finally {
        setButtonLoading(button, false);
    }
}

async function deleteIngredient(id, button) {
    if (!(await showAppConfirm('Delete Ingredient', 'Are you sure you want to delete this ingredient? This action cannot be undone.', 'Delete Ingredient'))) return;

    setButtonLoading(button, true, 'Deleting...');

    try {
        const res = await fetch(`/api/admin/ingredients/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            }
        });

        const data = await res.json();

        if (!res.ok) {
            alert(data.message || 'Failed to delete ingredient.');
            return;
        }

        await loadIngredients();
    } catch (error) {
        console.error('Delete ingredient failed:', error);
        alert('Failed to delete ingredient. Please check your connection.');
    } finally {
        setButtonLoading(button, false);
    }
}

document.getElementById('ingredientSearch').addEventListener('input', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

loadIngredients();
</script>
@endsection