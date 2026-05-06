@extends('layouts.admin')

@section('content')

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-bold mb-1">Payment Management</h1>
            <p class="text-gray-500">Track all payment transactions.</p>
        </div>

        <button onclick="exportPaymentsReport()"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded font-medium">
            Export Report
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="bg-white rounded-xl border shadow-sm p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Transactions</p>
                <h2 id="cardTotalTransactions" class="text-3xl font-bold">0</h2>
            </div>

            <div>
                <p class="text-sm text-gray-500 mb-1">Completed</p>
                <h2 id="cardCompletedTransactions" class="text-3xl font-bold text-green-500">0</h2>
            </div>

            <div>
                <p class="text-sm text-gray-500 mb-1">Pending</p>
                <h2 id="cardPendingTransactions" class="text-3xl font-bold text-yellow-500">0</h2>
            </div>

            <div>
                <p class="text-sm text-gray-500 mb-1">Total Amount</p>
                <h2 id="cardTotalAmount" class="text-3xl font-bold">₱0.00</h2>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-xl border shadow-sm">
        <div class="p-5 border-b">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-lg font-bold">Transaction History</h2>
                    <p class="text-sm text-gray-500">View and monitor all payment records.</p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <input
                        id="paymentSearch"
                        type="text"
                        placeholder="Search transactions..."
                        class="border rounded px-3 py-2 w-64"
                    >

                    <select id="paymentStatusFilter" class="border rounded px-3 py-2">
                        <option value="all">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Transaction ID</th>
                        <th class="text-left px-6 py-4 font-semibold">Order ID</th>
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
    </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[92vh] overflow-y-auto">

        <div class="flex items-center justify-between p-4 border-b print:hidden">
            <h3 class="text-lg font-bold">Payment Receipt</h3>
            <button onclick="closeReceiptModal()" class="text-gray-500 hover:text-black text-xl">&times;</button>
        </div>

        <div id="receiptContent" class="p-6">
            <!-- Receipt Header -->
            <div class="text-center border-b border-dashed pb-4">
                <h2 class="text-2xl font-bold text-orange-500">DineSync+</h2>
                <p class="text-sm font-semibold text-gray-700 mt-1">Chef Oppa Admin System</p>
                <p class="text-xs text-gray-400 mt-1">Official Payment Receipt</p>
            </div>

            <!-- Receipt Details -->
            <div class="py-4 border-b border-dashed text-sm space-y-2">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Transaction ID</span>
                    <span id="receiptTransactionId" class="font-semibold text-right">-</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Order ID</span>
                    <span id="receiptOrderId" class="font-semibold text-right">-</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Date & Time</span>
                    <span id="receiptDate" class="font-semibold text-right">-</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Payment Method</span>
                    <span id="receiptMethod" class="font-semibold text-right">-</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Status</span>
                    <span id="receiptStatus" class="font-semibold text-right">-</span>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="py-4 border-b border-dashed">
                <h4 class="font-bold text-sm mb-3">Order Summary</h4>

                <div id="receiptItems" class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-600">Order Payment</span>
                        <span class="font-medium">₱0.00</span>
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div class="py-4 border-b border-dashed space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span id="receiptSubtotal" class="font-semibold">₱0.00</span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Discount</span>
                    <span id="receiptDiscount" class="font-semibold">₱0.00</span>
                </div>

                <div class="flex justify-between text-lg font-bold">
                    <span>Total Paid</span>
                    <span id="receiptTotal" class="text-orange-500">₱0.00</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-4 text-center">
                <p class="text-sm font-semibold">Thank you for your payment!</p>
                <p class="text-xs text-gray-400 mt-1">This receipt was generated by DineSync+.</p>
            </div>
        </div>

        <div class="p-4 border-t flex justify-end gap-2">
            <button onclick="closeReceiptModal()" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
                Close
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #receiptContent, #receiptContent * {
        visibility: visible;
    }

    #receiptContent {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 24px;
    }
}
</style>

<script>
let payments = [];
let filteredPayments = [];

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
    if (!value) return 'N/A';

    const date = new Date(value);

    if (isNaN(date.getTime())) {
        return 'N/A';
    }

    return date.toLocaleString();
}

function normalizeStatus(status) {
    const value = String(status ?? '').toLowerCase().trim();

    if (['completed', 'paid', 'success', 'successful'].includes(value)) {
        return 'completed';
    }

    if (['pending', 'processing'].includes(value)) {
        return 'pending';
    }

    if (['failed', 'cancelled', 'canceled', 'rejected'].includes(value)) {
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

    if (normalized === 'failed') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-600">Failed</span>';
    }

    return `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">${safeText(status || 'Unknown')}</span>`;
}

function getTransactionId(payment) {
    return payment.transaction_id
        || payment.reference_number
        || payment.reference
        || `TXN-${String(payment.id ?? '').padStart(4, '0')}`;
}

function getOrderId(payment) {
    return payment.order_id
        || payment.order_number
        || payment.order_reference
        || `ORD-${String(payment.id ?? '').padStart(4, '0')}`;
}

function getPaymentMethod(payment) {
    return payment.payment_method || payment.method || 'Unknown';
}

function getAmount(payment) {
    return Number(payment.amount ?? payment.total_amount ?? payment.payment_amount ?? 0);
}

async function loadPayments() {
    const tbody = document.getElementById('paymentsTableBody');

    try {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                    Loading payment records...
                </td>
            </tr>
        `;

        const res = await fetch('/api/admin/payments', {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-red-500">
                        Failed to load payments. API returned ${res.status}.
                    </td>
                </tr>
            `;
            return;
        }

        const data = await res.json();

        if (Array.isArray(data)) {
            payments = data;
        } else if (Array.isArray(data.data)) {
            payments = data.data;
        } else if (Array.isArray(data.payments)) {
            payments = data.payments;
        } else {
            payments = [];
        }

        renderSummaryCards();
        applyPaymentFilters();

    } catch (error) {
        console.error('Failed to load payments:', error);

        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-red-500">
                    JavaScript error while loading payments.
                </td>
            </tr>
        `;
    }
}

function renderSummaryCards() {
    const totalTransactions = payments.length;

    const completedCount = payments.filter(p =>
        normalizeStatus(p.status) === 'completed'
    ).length;

    const pendingCount = payments.filter(p =>
        normalizeStatus(p.status) === 'pending'
    ).length;

    const totalAmount = payments.reduce((sum, p) => sum + getAmount(p), 0);

    document.getElementById('cardTotalTransactions').textContent = totalTransactions;
    document.getElementById('cardCompletedTransactions').textContent = completedCount;
    document.getElementById('cardPendingTransactions').textContent = pendingCount;
    document.getElementById('cardTotalAmount').textContent = formatMoney(totalAmount);
}

function applyPaymentFilters() {
    const search = document.getElementById('paymentSearch').value.toLowerCase().trim();
    const status = document.getElementById('paymentStatusFilter').value;

    filteredPayments = payments.filter(payment => {
        const transactionId = String(getTransactionId(payment)).toLowerCase();
        const orderId = String(getOrderId(payment)).toLowerCase();
        const method = String(getPaymentMethod(payment)).toLowerCase();
        const paymentStatus = normalizeStatus(payment.status);

        const matchesSearch =
            transactionId.includes(search) ||
            orderId.includes(search) ||
            method.includes(search);

        const matchesStatus =
            status === 'all' ? true : paymentStatus === status;

        return matchesSearch && matchesStatus;
    });

    renderPaymentsTable();
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

    tbody.innerHTML = filteredPayments.map(payment => {
        const transactionId = getTransactionId(payment);
        const orderId = getOrderId(payment);
        const method = getPaymentMethod(payment);
        const amount = getAmount(payment);
        const createdAt = payment.created_at || payment.date || payment.paid_at;

        return `
            <tr class="border-t hover:bg-gray-50">
                <td class="px-6 py-4 font-medium">${safeText(transactionId)}</td>
                <td class="px-6 py-4">${safeText(orderId)}</td>
                <td class="px-6 py-4">${safeText(method)}</td>
                <td class="px-6 py-4 font-semibold">${formatMoney(amount)}</td>
                <td class="px-6 py-4">${statusBadge(payment.status)}</td>
                <td class="px-6 py-4">${safeText(formatDateTime(createdAt))}</td>
                <td class="px-6 py-4">
                    <button onclick="openReceiptModal(${Number(payment.id)})"
                        class="px-3 py-2 rounded border text-gray-700 hover:bg-gray-50 text-xs">
                        View
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

async function openReceiptModal(paymentId) {
    const payment = payments.find(p => Number(p.id) === Number(paymentId));
    if (!payment) return;

    const amount = getAmount(payment);
    const transactionId = getTransactionId(payment);
    const orderId = getOrderId(payment);
    const createdAt = payment.created_at || payment.date || payment.paid_at;

    document.getElementById('receiptTransactionId').textContent = transactionId;
    document.getElementById('receiptOrderId').textContent = orderId;
    document.getElementById('receiptDate').textContent = formatDateTime(createdAt);
    document.getElementById('receiptMethod').textContent = getPaymentMethod(payment);
    document.getElementById('receiptStatus').innerHTML = statusBadge(payment.status);

    document.getElementById('receiptSubtotal').textContent = formatMoney(amount);
    document.getElementById('receiptDiscount').textContent = formatMoney(0);
    document.getElementById('receiptTotal').textContent = formatMoney(amount);

    document.getElementById('receiptItems').innerHTML = `
        <div class="flex justify-between gap-4">
            <span class="text-gray-600">Order Payment</span>
            <span class="font-medium">${formatMoney(amount)}</span>
        </div>
    `;

    const modal = document.getElementById('receiptModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    loadReceiptOrderItems(payment);
}

async function loadReceiptOrderItems(payment) {
    const container = document.getElementById('receiptItems');
    const amount = getAmount(payment);
    const orderId = payment.order_id;

    if (!orderId) {
        container.innerHTML = `
            <div class="flex justify-between gap-4">
                <span class="text-gray-600">Order Payment</span>
                <span class="font-medium">${formatMoney(amount)}</span>
            </div>
        `;
        return;
    }

    try {
        const res = await fetch(`/api/admin/orders/${orderId}`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            container.innerHTML = `
                <div class="flex justify-between gap-4">
                    <span class="text-gray-600">Order Payment</span>
                    <span class="font-medium">${formatMoney(amount)}</span>
                </div>
            `;
            return;
        }

        const order = await res.json();
        const items = order.items || order.order_items || [];

        if (!Array.isArray(items) || items.length === 0) {
            container.innerHTML = `
                <div class="flex justify-between gap-4">
                    <span class="text-gray-600">Order Payment</span>
                    <span class="font-medium">${formatMoney(amount)}</span>
                </div>
            `;
            return;
        }

        container.innerHTML = items.map(item => {
            const name = item.menu_item?.name || item.name || item.item_name || 'Menu Item';
            const quantity = Number(item.quantity || 1);
            const price = Number(item.price || item.menu_item?.price || 0);
            const total = quantity * price;

            return `
                <div class="flex justify-between gap-4">
                    <div>
                        <p class="text-gray-700">${safeText(name)}</p>
                        <p class="text-xs text-gray-400">Qty: ${quantity}</p>
                    </div>
                    <span class="font-medium">${formatMoney(total)}</span>
                </div>
            `;
        }).join('');

    } catch (error) {
        container.innerHTML = `
            <div class="flex justify-between gap-4">
                <span class="text-gray-600">Order Payment</span>
                <span class="font-medium">${formatMoney(amount)}</span>
            </div>
        `;
    }
}

function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    document.getElementById('receiptItems').innerHTML = '';
}

function printReceipt() {
    window.print();
}

function exportPaymentsReport() {
    if (!filteredPayments.length) {
        alert('No payment records to export.');
        return;
    }

    const rows = [
        ['Transaction ID', 'Order ID', 'Payment Method', 'Amount', 'Status', 'Date & Time']
    ];

    filteredPayments.forEach(payment => {
        rows.push([
            getTransactionId(payment),
            getOrderId(payment),
            getPaymentMethod(payment),
            getAmount(payment),
            normalizeStatus(payment.status),
            formatDateTime(payment.created_at || payment.date || payment.paid_at)
        ]);
    });

    const csv = rows.map(row =>
        row.map(value => `"${String(value).replaceAll('"', '""')}"`).join(',')
    ).join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'payments_report.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

document.getElementById('paymentSearch').addEventListener('input', applyPaymentFilters);
document.getElementById('paymentStatusFilter').addEventListener('change', applyPaymentFilters);

loadPayments();
</script>

@endsection