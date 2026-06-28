@extends('layouts.admin')

@section('content')

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">Payment Management</h1>
            <p class="text-sm sm:text-base text-gray-500">
                Track payment transactions by selected date.
            </p>
        </div>

        <div class="bg-white border rounded-2xl px-4 py-3 shadow-sm w-full sm:w-auto">
            <p class="text-xs text-gray-500">Payment Scope</p>
            <p id="paymentScopeLabel" class="text-sm font-bold text-orange-500">Selected Date</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-1">Transactions</p>
                    <h2 id="cardTotalTransactions" class="text-2xl sm:text-3xl font-bold">0</h2>
                    <p id="cardSelectedDateLabel" class="text-xs text-gray-400 mt-2">Selected date</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center font-bold shrink-0">
                    #
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-1">Completed</p>
                    <h2 id="cardCompletedTransactions" class="text-2xl sm:text-3xl font-bold text-green-500">0</h2>
                    <p class="text-xs text-gray-400 mt-2">Paid / successful</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-green-50 text-green-500 flex items-center justify-center font-bold shrink-0">
                    ✓
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-1">Pending</p>
                    <h2 id="cardPendingTransactions" class="text-2xl sm:text-3xl font-bold text-yellow-500">0</h2>
                    <p class="text-xs text-gray-400 mt-2">Awaiting completion</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-yellow-50 text-yellow-500 flex items-center justify-center font-bold shrink-0">
                    …
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-1">Amount Collected</p>
                    <h2 id="cardTotalAmount" class="text-2xl sm:text-3xl font-bold">₱0.00</h2>
                    <p id="cardAmountLabel" class="text-xs text-gray-400 mt-2">For selected date</p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center font-bold shrink-0">
                    ₱
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b">
            <div class="flex flex-col 2xl:flex-row 2xl:items-center 2xl:justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold">Transaction History</h2>
                    <p id="transactionSubtitle" class="text-sm text-gray-500">
                        Showing payment records for the selected date only.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2 w-full 2xl:w-auto">
                    <input
                        id="paymentDateFilter"
                        type="date"
                        class="border border-gray-300 rounded-xl px-4 py-2.5 text-sm w-full focus:ring-2 focus:ring-orange-100 focus:border-orange-400 outline-none"
                    >

                    <button
                        type="button"
                        onclick="setPaymentViewDate()"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold w-full"
                    >
                        View Date
                    </button>

                    <button
                        type="button"
                        onclick="setPaymentDateToday()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold w-full"
                    >
                        Today
                    </button>

                    <button
                        type="button"
                        onclick="setPaymentDateTomorrow()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold w-full"
                    >
                        Tomorrow
                    </button>

                    <input id="paymentSearch" type="hidden" value="">

                    <select id="paymentStatusFilter" class="hidden">
                        <option value="all" selected>All Status</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Desktop / Tablet Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[980px] text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Transaction ID</th>
                        <th class="text-left px-6 py-4 font-semibold">Order</th>
                        <th class="text-left px-6 py-4 font-semibold">Payment Method</th>
                        <th class="text-left px-6 py-4 font-semibold">Amount</th>
                        <th class="text-left px-6 py-4 font-semibold">Status</th>
                        <th class="text-left px-6 py-4 font-semibold">Date & Time</th>
                        <th class="text-left px-6 py-4 font-semibold">Actions</th>
                    </tr>
                </thead>

                <tbody id="paymentsTableBody">
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                            Loading payment records...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div id="paymentsMobileList" class="md:hidden p-4 space-y-3">
            <div class="px-4 py-8 text-center text-gray-400">
                Loading payment records...
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[94vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-4 border-b print:hidden shrink-0">
            <div class="min-w-0">
                <h3 class="text-lg font-bold">Payment Receipt</h3>
                <p class="text-xs text-gray-500">Official transaction summary</p>
            </div>

            <button onclick="closeReceiptModal()"
                class="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-500 hover:text-black text-xl shrink-0">
                &times;
            </button>
        </div>

        <div class="overflow-y-auto">
            <div id="receiptContent" class="p-4 sm:p-6">
                <!-- Receipt Header -->
                <div class="rounded-2xl overflow-hidden border border-orange-100 shadow-sm mb-5">
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white px-6 py-8 text-center">
                        <h2 class="text-3xl sm:text-4xl font-black tracking-tight leading-none">
                            Chef Oppa
                        </h2>
                        <p class="text-sm text-orange-50 font-medium mt-2">
                            Official Payment Receipt
                        </p>
                    </div>

                    <div class="bg-white px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Transaction</p>
                            <p id="receiptTransactionId" class="font-bold text-gray-800 break-words">-</p>
                        </div>

                        <div class="sm:text-right">
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Order</p>
                            <p id="receiptOrderId" class="font-bold text-gray-800 break-words">-</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Date & Time</p>
                            <p id="receiptDate" class="font-semibold text-gray-700">-</p>
                        </div>

                        <div class="sm:text-right">
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Status</p>
                            <div id="receiptStatus" class="mt-1">-</div>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                    <div class="rounded-xl border bg-gray-50 p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Payment Method</p>
                        <p id="receiptMethod" class="font-bold text-gray-800">-</p>
                    </div>

                    <div class="rounded-xl border bg-gray-50 p-4 sm:text-right">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Amount Paid</p>
                        <p id="receiptTopTotal" class="font-extrabold text-orange-500 text-xl">₱0.00</p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="rounded-2xl border overflow-hidden mb-5">
                    <div class="px-5 py-3 bg-gray-50 border-b flex items-center justify-between">
                        <h4 class="font-bold text-sm">Order Summary</h4>
                        <span id="receiptItemCount" class="text-xs text-gray-500">0 item(s)</span>
                    </div>

                    <div id="receiptItems" class="divide-y">
                        <div class="px-5 py-4 text-sm text-gray-400 text-center">
                            No order items available.
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="rounded-2xl bg-orange-50 border border-orange-100 p-5">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold text-gray-700">Total Amount</p>
                        <p id="receiptBottomTotal" class="text-2xl font-black text-orange-500">₱0.00</p>
                    </div>
                </div>

                <p class="text-center text-xs text-gray-400 mt-5">
                    Thank you for dining with Chef Oppa.
                </p>
            </div>
        </div>

        <div class="border-t px-4 sm:px-5 py-4 flex flex-col sm:flex-row sm:justify-end gap-2 print:hidden shrink-0">
            <button onclick="closeReceiptModal()"
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                Close
            </button>

            <button onclick="window.print()"
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold">
                Print Receipt
            </button>
        </div>
    </div>
</div>

<script>
let payments = [];
let filteredPayments = [];
let paymentViewMode = 'daily';

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

function formatDateTime(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return date.toLocaleString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
}

function todayDateString() {
    return new Date().toISOString().slice(0, 10);
}

function tomorrowDateString() {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return tomorrow.toISOString().slice(0, 10);
}

function normalizeStatus(status) {
    const value = String(status || '').toLowerCase();

    if (['completed', 'paid', 'success', 'successful'].includes(value)) {
        return 'completed';
    }

    if (['pending', 'processing'].includes(value)) {
        return 'pending';
    }

    if (['failed', 'expired', 'cancelled', 'canceled'].includes(value)) {
        return 'failed';
    }

    return value || 'pending';
}

function statusBadge(status) {
    const normalized = normalizeStatus(status);

    if (normalized === 'completed') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-600">Completed</span>';
    }

    if (normalized === 'pending') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Pending</span>';
    }

    return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-600">Failed</span>';
}

function getTransactionId(payment) {
    return payment.transaction_id
        || payment.xendit_invoice_id
        || payment.invoice_id
        || payment.reference_id
        || `PAY-${payment.id}`;
}

function getOrderLabel(payment) {
    if (payment.order?.order_number) {
        return payment.order.order_number;
    }

    if (payment.order_number) {
        return payment.order_number;
    }

    if (payment.order_id) {
        return `Order #${payment.order_id}`;
    }

    return '-';
}

function getPaymentMethod(payment) {
    return payment.payment_method
        || payment.method
        || payment.order?.payment_method
        || 'N/A';
}

function formatReadableDate(dateValue) {
    const date = new Date(dateValue);

    if (Number.isNaN(date.getTime())) {
        return 'Selected date';
    }

    return date.toLocaleDateString('en-US', {
        month: 'long',
        day: '2-digit',
        year: 'numeric'
    });
}

function setPaymentViewDate() {
    paymentViewMode = 'daily';
    loadPayments();
}

function setPaymentDateToday() {
    paymentViewMode = 'daily';

    const dateInput = document.getElementById('paymentDateFilter');
    dateInput.value = todayDateString();

    loadPayments();
}

function setPaymentDateTomorrow() {
    paymentViewMode = 'daily';

    const dateInput = document.getElementById('paymentDateFilter');
    dateInput.value = tomorrowDateString();

    loadPayments();
}

async function loadPayments() {
    const dateInput = document.getElementById('paymentDateFilter');
    const date = dateInput.value || todayDateString();

    if (!dateInput.value) {
        dateInput.value = date;
    }

    setLoadingRows();

    try {
        const url = `/api/admin/payments?date=${encodeURIComponent(date)}&mode=${encodeURIComponent(paymentViewMode)}`;

        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            setErrorRows(`Failed to load payments. API returned ${res.status}.`);
            return;
        }

        const data = await res.json();

        payments = Array.isArray(data)
            ? data
            : (data.payments || data.data || []);

        renderSummary(data.summary || null);
        applyFilters();
        updateScopeLabels(date);
    } catch (error) {
        console.error('Payment load failed:', error);
        setErrorRows('Failed to load payment records. Please check your connection.');
    }
}

function updateScopeLabels(date) {
    const readable = formatReadableDate(date);

    document.getElementById('paymentScopeLabel').textContent = 'Selected Date';
    document.getElementById('cardSelectedDateLabel').textContent = readable;
    document.getElementById('cardAmountLabel').textContent = 'For selected date';

    document.getElementById('transactionSubtitle').textContent =
        `Showing payment records for ${readable} only.`;
}

function setLoadingRows() {
    document.getElementById('paymentsTableBody').innerHTML = `
        <tr>
            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                Loading payment records...
            </td>
        </tr>
    `;

    document.getElementById('paymentsMobileList').innerHTML = `
        <div class="px-4 py-8 text-center text-gray-400">
            Loading payment records...
        </div>
    `;
}

function setErrorRows(message) {
    document.getElementById('paymentsTableBody').innerHTML = `
        <tr>
            <td colspan="7" class="px-6 py-8 text-center text-red-500">
                ${safeText(message)}
            </td>
        </tr>
    `;

    document.getElementById('paymentsMobileList').innerHTML = `
        <div class="px-4 py-8 text-center text-red-500">
            ${safeText(message)}
        </div>
    `;
}

function renderSummary(summary = null) {
    if (summary) {
        document.getElementById('cardTotalTransactions').textContent = Number(summary.total_transactions || 0).toLocaleString();
        document.getElementById('cardCompletedTransactions').textContent = Number(summary.completed || 0).toLocaleString();
        document.getElementById('cardPendingTransactions').textContent = Number(summary.pending || 0).toLocaleString();
        document.getElementById('cardTotalAmount').textContent = formatMoney(summary.total_amount || 0);
        return;
    }

    const total = payments.length;
    const completed = payments.filter(payment => normalizeStatus(payment.status) === 'completed').length;
    const pending = payments.filter(payment => normalizeStatus(payment.status) === 'pending').length;
    const amount = payments.reduce((sum, payment) => sum + Number(payment.amount || 0), 0);

    document.getElementById('cardTotalTransactions').textContent = Number(total).toLocaleString();
    document.getElementById('cardCompletedTransactions').textContent = Number(completed).toLocaleString();
    document.getElementById('cardPendingTransactions').textContent = Number(pending).toLocaleString();
    document.getElementById('cardTotalAmount').textContent = formatMoney(amount);
}

function applyFilters() {
    const searchInput = document.getElementById('paymentSearch');
    const statusSelect = document.getElementById('paymentStatusFilter');

    const search = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const status = statusSelect ? statusSelect.value : 'all';

    filteredPayments = payments.filter(payment => {
        const transaction = String(getTransactionId(payment)).toLowerCase();
        const order = String(getOrderLabel(payment)).toLowerCase();
        const method = String(getPaymentMethod(payment)).toLowerCase();
        const paymentStatus = normalizeStatus(payment.status);

        const matchesSearch =
            transaction.includes(search) ||
            order.includes(search) ||
            method.includes(search);

        const matchesStatus = status === 'all' ? true : paymentStatus === status;

        return matchesSearch && matchesStatus;
    });

    renderPaymentsTable();
    renderPaymentsMobileList();
}

function renderPaymentsTable() {
    const tbody = document.getElementById('paymentsTableBody');

    if (!filteredPayments.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                    No payment records found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filteredPayments.map(payment => `
        <tr class="border-t hover:bg-gray-50">
            <td class="px-6 py-4 font-medium break-words max-w-[180px]">
                ${safeText(getTransactionId(payment))}
            </td>

            <td class="px-6 py-4">
                ${safeText(getOrderLabel(payment))}
            </td>

            <td class="px-6 py-4">
                ${safeText(getPaymentMethod(payment))}
            </td>

            <td class="px-6 py-4 font-semibold">
                ${formatMoney(payment.amount)}
            </td>

            <td class="px-6 py-4">
                ${statusBadge(payment.status)}
            </td>

            <td class="px-6 py-4">
                ${safeText(formatDateTime(payment.created_at || payment.paid_at))}
            </td>

            <td class="px-6 py-4">
                <button onclick="openReceiptModal(${Number(payment.id)})"
                    class="px-3 py-2 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                    View Receipt
                </button>
            </td>
        </tr>
    `).join('');
}

function renderPaymentsMobileList() {
    const container = document.getElementById('paymentsMobileList');

    if (!filteredPayments.length) {
        container.innerHTML = `
            <div class="px-4 py-8 text-center text-gray-400">
                No payment records found.
            </div>
        `;
        return;
    }

    container.innerHTML = filteredPayments.map(payment => `
        <div class="rounded-2xl border bg-white shadow-sm p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-gray-400">Transaction</p>
                    <h3 class="font-bold text-gray-900 break-words">${safeText(getTransactionId(payment))}</h3>
                </div>

                <div class="shrink-0">
                    ${statusBadge(payment.status)}
                </div>
            </div>

            <div class="mt-3 rounded-xl bg-gray-50 border px-3 py-2 space-y-1">
                <p class="text-xs text-gray-600">
                    <span class="font-semibold">Order:</span>
                    ${safeText(getOrderLabel(payment))}
                </p>

                <p class="text-xs text-gray-600">
                    <span class="font-semibold">Method:</span>
                    ${safeText(getPaymentMethod(payment))}
                </p>

                <p class="text-xs text-gray-600">
                    <span class="font-semibold">Amount:</span>
                    <span class="font-bold text-orange-500">${formatMoney(payment.amount)}</span>
                </p>

                <p class="text-xs text-gray-600">
                    <span class="font-semibold">Date:</span>
                    ${safeText(formatDateTime(payment.created_at || payment.paid_at))}
                </p>
            </div>

            <button onclick="openReceiptModal(${Number(payment.id)})"
                class="w-full mt-3 px-3 py-2.5 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                View Receipt
            </button>
        </div>
    `).join('');
}

function openReceiptModal(paymentId) {
    const payment = payments.find(item => Number(item.id) === Number(paymentId));

    if (!payment) {
        alert('Payment record not found.');
        return;
    }

    document.getElementById('receiptTransactionId').textContent = getTransactionId(payment);
    document.getElementById('receiptOrderId').textContent = getOrderLabel(payment);
    document.getElementById('receiptDate').textContent = formatDateTime(payment.created_at || payment.paid_at);
    document.getElementById('receiptStatus').innerHTML = statusBadge(payment.status);
    document.getElementById('receiptMethod').textContent = getPaymentMethod(payment);
    document.getElementById('receiptTopTotal').textContent = formatMoney(payment.amount);
    document.getElementById('receiptBottomTotal').textContent = formatMoney(payment.amount);

    renderReceiptItems(payment);

    const modal = document.getElementById('receiptModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function renderReceiptItems(payment) {
    const container = document.getElementById('receiptItems');
    const countLabel = document.getElementById('receiptItemCount');

    const items =
        payment.order?.items ||
        payment.order?.order_items ||
        payment.items ||
        payment.order_items ||
        [];

    if (!Array.isArray(items) || !items.length) {
        countLabel.textContent = '0 item(s)';
        container.innerHTML = `
            <div class="px-5 py-4 text-sm text-gray-400 text-center">
                No order items available.
            </div>
        `;
        return;
    }

    countLabel.textContent = `${items.length} item${items.length === 1 ? '' : 's'}`;

    container.innerHTML = items.map(item => {
        const name =
            item.menu_item?.name ||
            item.menuItem?.name ||
            item.name ||
            item.item_name ||
            'Menu Item';

        const quantity = item.quantity || 1;
        const price = item.price || item.unit_price || 0;
        const subtotal = Number(price) * Number(quantity);
        const notes = item.notes || item.special_request || '';

        return `
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900">${safeText(name)}</p>
                        <p class="text-xs text-gray-500 mt-1">Qty: ${safeText(quantity)}</p>
                        ${
                            notes
                                ? `<p class="text-xs text-gray-500 mt-2"><span class="font-semibold">Request:</span> ${safeText(notes)}</p>`
                                : ''
                        }
                    </div>

                    <p class="font-bold text-gray-800 shrink-0">
                        ${Number(price) <= 0 ? 'To be confirmed' : formatMoney(subtotal)}
                    </p>
                </div>
            </div>
        `;
    }).join('');
}

const paymentSearchInput = document.getElementById('paymentSearch');
const paymentStatusSelect = document.getElementById('paymentStatusFilter');
const paymentDateInput = document.getElementById('paymentDateFilter');

if (paymentSearchInput) {
    paymentSearchInput.addEventListener('input', applyFilters);
}

if (paymentStatusSelect) {
    paymentStatusSelect.addEventListener('change', applyFilters);
}

if (paymentDateInput) {
    paymentDateInput.addEventListener('change', function () {
        paymentViewMode = 'daily';
        loadPayments();
    });

    paymentDateInput.value = todayDateString();
}

loadPayments();
</script>

@endsection