@extends('layouts.admin')

@section('content')


<style>
    @keyframes paymentShimmer { 0% { background-position: -700px 0; } 100% { background-position: 700px 0; } }
    @keyframes paymentFadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes paymentModalIn { from { opacity: 0; transform: scale(.96) translateY(12px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    @keyframes paymentToastIn { from { opacity: 0; transform: translateX(24px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes paymentToastOut { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(24px); } }

    .payment-summary-card { transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .payment-summary-card:hover { transform: translateY(-3px); box-shadow: 0 16px 35px rgba(15, 23, 42, .08); border-color: rgba(249,115,22,.28); }
    .payment-record-row, .payment-mobile-card { animation: paymentFadeUp .28s ease both; }
    .payment-skeleton { background: linear-gradient(90deg,#f3f4f6 20%,#fff 45%,#f3f4f6 70%); background-size: 700px 100%; animation: paymentShimmer 1.35s infinite linear; }
    .payment-modal-panel { animation: paymentModalIn .22s ease both; }
    .payment-toast { animation: paymentToastIn .24s ease both; }
    .payment-toast.hiding { animation: paymentToastOut .2s ease both; }

    @media (max-width: 640px) {
        #receiptModal { align-items: stretch !important; justify-content: stretch !important; padding: 0 !important; }
        #receiptModal > div { width: 100% !important; max-width: 100% !important; height: 100dvh !important; max-height: 100dvh !important; border-radius: 0 !important; }
    }


    .thermal-receipt {
        width: 100%;
        max-width: 380px;
        margin: 0 auto;
        background: #fff;
        color: #111827;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 12px;
        line-height: 1.45;
    }

    .receipt-rule {
        border-top: 1px dashed #111827;
        margin: 12px 0;
    }

    .receipt-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .receipt-row > :last-child {
        text-align: right;
        flex-shrink: 0;
    }

    .receipt-label {
        color: #4b5563;
    }

    .receipt-total-row {
        font-size: 16px;
        font-weight: 900;
    }
</style>


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
        <div class="payment-summary-card bg-white/95 rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5">
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

        <div class="payment-summary-card bg-white/95 rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5">
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

        <div class="payment-summary-card bg-white/95 rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5">
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

        <div class="payment-summary-card bg-white/95 rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5">
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
    <div class="bg-white/95 rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
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

<!-- Thermal Receipt Modal -->
<div id="receiptModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-3 backdrop-blur-sm sm:p-5">
    <div class="payment-modal-panel flex max-h-[94vh] w-full max-w-md flex-col overflow-hidden rounded-[28px] border border-orange-100 bg-white shadow-2xl">
        <div class="receipt-screen-only flex shrink-0 items-center justify-between gap-3 border-b bg-gradient-to-r from-orange-50 via-white to-amber-50 px-5 py-4">
            <div>
                <h3 class="text-lg font-black text-gray-950">Payment Receipt</h3>
                <p class="text-xs text-gray-500">Customer copy · Thermal receipt format</p>
            </div>

            <button type="button" onclick="closeReceiptModal()"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-orange-100 bg-white text-xl text-gray-500 shadow-sm transition hover:bg-orange-50 hover:text-orange-700">
                &times;
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto bg-gray-100 p-3 sm:p-5">
            <div id="receiptContent" class="thermal-receipt rounded-sm bg-white px-5 py-6 shadow-sm">
                <header class="text-center">
                    <img id="receiptLogo" src="{{ asset('images/customer-menu/chef-oppa-logo.png') }}"
                        alt="Restaurant Logo"
                        class="mx-auto mb-2 h-16 w-16 rounded-full object-cover grayscale">

                    <h2 id="receiptRestaurantName" class="text-[20px] font-black uppercase tracking-wide">CHEF OPPA</h2>
                    <p id="receiptBranch" class="mt-0.5 font-bold uppercase">Main Branch</p>
                    <p id="receiptAddress" class="mt-1 leading-4 text-gray-700">-</p>
                    <p id="receiptContact" class="leading-4 text-gray-700">-</p>
                    <p id="receiptWebsite" class="leading-4 text-gray-700">dinesync.shop</p>
                </header>

                <div class="receipt-rule"></div>

                <p class="text-center text-[13px] font-black uppercase tracking-[0.18em]">Payment Receipt</p>
                <p class="text-center text-[10px] uppercase text-gray-500">Customer Copy</p>

                <div class="receipt-rule"></div>

                <section class="space-y-1">
                    <div class="receipt-row"><span class="receipt-label">Receipt No.</span><strong id="receiptTransactionId">-</strong></div>
                    <div class="receipt-row"><span class="receipt-label">Order No.</span><strong id="receiptOrderId">-</strong></div>
                    <div class="receipt-row"><span class="receipt-label">Date / Time</span><span id="receiptDate">-</span></div>
                    <div class="receipt-row"><span class="receipt-label">Table</span><span id="receiptTable">-</span></div>
                    <div class="receipt-row"><span class="receipt-label">Processed By</span><span id="receiptProcessedBy">Service Staff</span></div>
                    <div class="receipt-row"><span class="receipt-label">Payment</span><span id="receiptMethod">-</span></div>
                    <div class="receipt-row"><span class="receipt-label">Status</span><strong id="receiptStatusText">PAID</strong></div>
                </section>

                <div class="receipt-rule"></div>

                <section>
                    <div class="receipt-row mb-2 font-black uppercase">
                        <span>Qty / Item</span>
                        <span>Amount</span>
                    </div>
                    <div id="receiptItems" class="space-y-2"></div>
                </section>

                <div class="receipt-rule"></div>

                <section class="space-y-1">
                    <div class="receipt-row">
                        <span>Subtotal</span>
                        <strong id="receiptSubtotal">₱0.00</strong>
                    </div>

                    <div id="receiptDiscountSection" class="hidden">
                        <div class="my-2 border-t border-dotted border-gray-400"></div>
                        <p class="mb-1 font-black uppercase">Government Discount</p>
                        <div class="receipt-row"><span>Type</span><strong id="receiptDiscountType">-</strong></div>
                        <div class="receipt-row"><span>Qualified Diners</span><span id="receiptQualifiedDiners">-</span></div>
                        <div class="receipt-row"><span>ID Holder(s)</span><span id="receiptHolderName" class="max-w-[210px] break-words">-</span></div>
                        <div class="receipt-row"><span>ID Number(s)</span><span id="receiptIdNumber" class="max-w-[210px] break-words">-</span></div>
                        <div class="receipt-row"><span>VAT Exemption</span><strong id="receiptVatExempt">-₱0.00</strong></div>
                        <div class="receipt-row"><span>20% Discount</span><strong id="receiptDiscountAmount">-₱0.00</strong></div>
                    </div>
                </section>

                <div class="receipt-rule"></div>

                <div class="receipt-row receipt-total-row">
                    <span>TOTAL</span>
                    <span id="receiptBottomTotal">₱0.00</span>
                </div>

                <div class="receipt-rule"></div>

                <footer class="text-center">
                    <p class="font-black uppercase">Thank you for dining with us!</p>
                    <p>Please come again.</p>
                    <p class="mt-2 text-[10px] text-gray-500">This is a system-generated customer payment receipt.</p>
                </footer>
            </div>
        </div>

        <div class="receipt-screen-only flex shrink-0 flex-col-reverse gap-2 border-t bg-white px-4 py-4 sm:flex-row sm:justify-end">
            <button type="button" onclick="closeReceiptModal()"
                class="rounded-xl bg-gray-100 px-5 py-2.5 font-bold text-gray-700 hover:bg-gray-200">
                Close
            </button>

            <button type="button" onclick="printThermalReceipt()"
                class="rounded-xl bg-orange-500 px-5 py-2.5 font-black text-white hover:bg-orange-600">
                Print Receipt
            </button>
        </div>
    </div>
</div>

<!-- Premium Toasts -->
<div id="paymentToastContainer" class="fixed top-4 right-4 z-[80] w-[calc(100%-2rem)] max-w-sm space-y-3 pointer-events-none"></div>

<script>
let payments = [];
let filteredPayments = [];
let receiptRestaurant = {};
let paymentViewMode = 'daily';
let paymentLoadInProgress = false;
let paymentLoadToken = 0;

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function showPaymentToast(message, type = 'info', duration = 3200) {
    const container = document.getElementById('paymentToastContainer');
    if (!container) return;

    const tones = {
        success: { icon: '✓', iconClass: 'bg-green-100 text-green-700', border: 'border-green-200' },
        error: { icon: '!', iconClass: 'bg-red-100 text-red-700', border: 'border-red-200' },
        warning: { icon: '!', iconClass: 'bg-yellow-100 text-yellow-700', border: 'border-yellow-200' },
        info: { icon: 'i', iconClass: 'bg-blue-100 text-blue-700', border: 'border-blue-200' },
    };
    const tone = tones[type] || tones.info;
    const toast = document.createElement('div');
    toast.className = `payment-toast pointer-events-auto rounded-2xl border ${tone.border} bg-white p-4 shadow-2xl`;
    toast.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl font-black ${tone.iconClass}">${tone.icon}</div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-900">${safeText(type === 'success' ? 'Success' : type === 'error' ? 'Something went wrong' : type === 'warning' ? 'Please check' : 'Notice')}</p>
                <p class="mt-1 text-sm leading-5 text-gray-600">${safeText(message)}</p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-700" aria-label="Close">×</button>
        </div>`;
    const remove = () => { toast.classList.add('hiding'); setTimeout(() => toast.remove(), 190); };
    toast.querySelector('button').addEventListener('click', remove);
    container.appendChild(toast);
    setTimeout(remove, duration);
}

function animateValue(element, endValue, options = {}) {
    if (!element) return;
    const duration = options.duration || 550;
    const isMoney = Boolean(options.money);
    const start = performance.now();
    const end = Number(endValue || 0);
    const frame = now => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = end * eased;
        element.textContent = isMoney ? formatMoney(current) : Math.round(current).toLocaleString();
        if (progress < 1) requestAnimationFrame(frame);
    };
    requestAnimationFrame(frame);
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
    const raw = String(status || '').toLowerCase();
    const normalized = normalizeStatus(raw);

    if (normalized === 'completed') {
        return '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Paid</span>';
    }

    if (raw === 'awaiting_payment') {
        return '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Awaiting Payment</span>';
    }

    if (normalized === 'pending') {
        return '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 border border-yellow-200"><span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>Pending</span>';
    }

    if (raw === 'expired') {
        return '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-200 text-gray-700 border border-gray-300"><span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>Expired</span>';
    }

    return '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Failed</span>';
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
    const requestToken = ++paymentLoadToken;
    paymentLoadInProgress = true;
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
            if (requestToken !== paymentLoadToken) return;
            setErrorRows(`Failed to load payments. API returned ${res.status}.`);
            showPaymentToast('Unable to load payment records for this date.', 'error');
            return;
        }

        const data = await res.json();
        if (requestToken !== paymentLoadToken) return;

        payments = Array.isArray(data)
            ? data
            : (data.payments || data.data || []);

        receiptRestaurant = data.restaurant || {};

        renderSummary(data.summary || null);
        applyFilters();
        updateScopeLabels(date);
    } catch (error) {
        console.error('Payment load failed:', error);
        if (requestToken !== paymentLoadToken) return;
        setErrorRows('Failed to load payment records. Please check your connection.');
        showPaymentToast('Failed to load payment records. Please check your connection.', 'error');
    } finally {
        if (requestToken === paymentLoadToken) paymentLoadInProgress = false;
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
    document.getElementById('paymentsTableBody').innerHTML = Array.from({ length: 5 }).map(() => `
        <tr class="border-t">
            ${Array.from({ length: 7 }).map(() => '<td class="px-6 py-4"><div class="payment-skeleton h-4 rounded-full"></div></td>').join('')}
        </tr>
    `).join('');

    document.getElementById('paymentsMobileList').innerHTML = Array.from({ length: 3 }).map(() => `
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
            <div class="flex justify-between gap-4"><div class="payment-skeleton h-5 w-2/3 rounded-full"></div><div class="payment-skeleton h-7 w-20 rounded-full"></div></div>
            <div class="mt-4 space-y-2 rounded-xl border bg-gray-50 p-3"><div class="payment-skeleton h-4 rounded-full"></div><div class="payment-skeleton h-4 w-4/5 rounded-full"></div><div class="payment-skeleton h-4 w-3/5 rounded-full"></div></div>
            <div class="payment-skeleton mt-3 h-10 rounded-xl"></div>
        </div>
    `).join('');
}
function setErrorRows(message) {
    const empty = `
        <div class="mx-auto max-w-md rounded-3xl border border-red-100 bg-red-50/60 px-6 py-8 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-xl font-black text-red-600 shadow-sm">!</div>
            <h3 class="mt-4 font-extrabold text-gray-900">Payments could not be loaded</h3>
            <p class="mt-2 text-sm leading-6 text-gray-500">${safeText(message)}</p>
            <button type="button" onclick="loadPayments()" class="mt-5 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">Try Again</button>
        </div>`;
    document.getElementById('paymentsTableBody').innerHTML = `<tr><td colspan="7" class="px-6 py-10">${empty}</td></tr>`;
    document.getElementById('paymentsMobileList').innerHTML = empty;
}
function renderSummary(summary = null) {
    if (summary) {
        animateValue(document.getElementById('cardTotalTransactions'), summary.total_transactions || 0);
        animateValue(document.getElementById('cardCompletedTransactions'), summary.completed || 0);
        animateValue(document.getElementById('cardPendingTransactions'), summary.pending || 0);
        animateValue(document.getElementById('cardTotalAmount'), summary.total_amount || 0, { money: true });
        return;
    }

    const total = payments.length;
    const completed = payments.filter(payment => normalizeStatus(payment.status) === 'completed').length;
    const pending = payments.filter(payment => normalizeStatus(payment.status) === 'pending').length;
    const amount = payments.reduce((sum, payment) => sum + Number(payment.amount || 0), 0);

    animateValue(document.getElementById('cardTotalTransactions'), total);
    animateValue(document.getElementById('cardCompletedTransactions'), completed);
    animateValue(document.getElementById('cardPendingTransactions'), pending);
    animateValue(document.getElementById('cardTotalAmount'), amount, { money: true });
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
            <tr><td colspan="7" class="px-6 py-12"><div class="mx-auto max-w-md rounded-3xl border border-dashed border-orange-200 bg-orange-50/40 px-6 py-9 text-center"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-2xl shadow-sm">💳</div><h3 class="mt-4 font-extrabold text-gray-900">No payments found</h3><p class="mt-2 text-sm text-gray-500">There are no payment records for the selected date. Try another date.</p></div></td></tr>
        `;
        return;
    }

    tbody.innerHTML = filteredPayments.map(payment => `
        <tr class="payment-record-row border-t hover:bg-orange-50/40 transition">
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
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-orange-50 border border-orange-200 text-orange-700 hover:bg-orange-100 text-xs font-bold transition">
                    <span>🧾</span> Receipt
                </button>
            </td>
        </tr>
    `).join('');
}

function renderPaymentsMobileList() {
    const container = document.getElementById('paymentsMobileList');

    if (!filteredPayments.length) {
        container.innerHTML = `
            <div class="rounded-3xl border border-dashed border-orange-200 bg-orange-50/40 px-6 py-9 text-center"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-2xl shadow-sm">💳</div><h3 class="mt-4 font-extrabold text-gray-900">No payments found</h3><p class="mt-2 text-sm text-gray-500">There are no payment records for the selected date. Try another date.</p></div>
        `;
        return;
    }

    container.innerHTML = filteredPayments.map(payment => `
        <div class="payment-mobile-card rounded-3xl border border-gray-200 bg-white shadow-sm p-4">
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
                class="w-full mt-3 inline-flex items-center justify-center gap-2 px-3 py-3 rounded-xl bg-orange-50 border border-orange-200 text-orange-700 hover:bg-orange-100 text-xs font-bold transition">
                <span>🧾</span> View Receipt
            </button>
        </div>
    `).join('');
}

function openReceiptModal(paymentId) {
    const payment = payments.find(item => Number(item.id) === Number(paymentId));

    if (!payment) {
        showPaymentToast('Payment record not found.', 'error');
        return;
    }

    const order = payment.order || {};
    const discount = order.discount || {};
    const originalAmount = Number(
        order.original_amount ?? order.total_amount ?? payment.amount ?? 0
    );
    const finalAmount = Number(
        order.final_amount ?? order.amount_paid ?? payment.amount ?? originalAmount
    );

    document.getElementById('receiptLogo').src =
        receiptRestaurant.logo_url || '{{ asset('images/customer-menu/chef-oppa-logo.png') }}';
    document.getElementById('receiptRestaurantName').textContent =
        receiptRestaurant.name || 'Chef Oppa';
    document.getElementById('receiptBranch').textContent =
        receiptRestaurant.branch || 'Main Branch';
    document.getElementById('receiptAddress').textContent =
        receiptRestaurant.address || '';
    document.getElementById('receiptContact').textContent =
        receiptRestaurant.contact_number || '';
    document.getElementById('receiptWebsite').textContent =
        receiptRestaurant.website || 'dinesync.shop';

    document.getElementById('receiptTransactionId').textContent = getTransactionId(payment);
    document.getElementById('receiptOrderId').textContent = getOrderLabel(payment);
    document.getElementById('receiptDate').textContent = formatDateTime(
        payment.paid_at || payment.transaction_date || payment.created_at
    );
    document.getElementById('receiptTable').textContent =
        order.table_number ? `Table ${order.table_number}` : '-';
    document.getElementById('receiptProcessedBy').textContent =
        order.processed_by || discount.verified_by || 'Service Staff';
    document.getElementById('receiptMethod').textContent = getPaymentMethod(payment);
    document.getElementById('receiptStatusText').textContent =
        normalizeStatus(payment.status) === 'completed' ? 'PAID' : String(payment.status || 'PENDING').toUpperCase();
    document.getElementById('receiptSubtotal').textContent = formatMoney(originalAmount);
    document.getElementById('receiptBottomTotal').textContent = formatMoney(finalAmount);

    const discountSection = document.getElementById('receiptDiscountSection');

    if (discount.applied) {
        discountSection.classList.remove('hidden');
        document.getElementById('receiptDiscountType').textContent = discount.label || '-';
        document.getElementById('receiptQualifiedDiners').textContent =
            `${discount.qualified_diners || 0} of ${discount.total_diners || 0}`;
        document.getElementById('receiptHolderName').textContent = discount.holder_name || '-';
        document.getElementById('receiptIdNumber').textContent = discount.id_number || '-';
        document.getElementById('receiptVatExempt').textContent =
            '-' + formatMoney(discount.vat_exempt_amount || 0);
        document.getElementById('receiptDiscountAmount').textContent =
            '-' + formatMoney(discount.discount_amount || 0);
    } else {
        discountSection.classList.add('hidden');
    }

    renderReceiptItems(payment);

    const modal = document.getElementById('receiptModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function renderReceiptItems(payment) {
    const container = document.getElementById('receiptItems');

    const items =
        payment.order?.items ||
        payment.order?.order_items ||
        payment.items ||
        payment.order_items ||
        [];

    if (!Array.isArray(items) || !items.length) {
        container.innerHTML = `
            <p class="py-2 text-center text-gray-500">
                No order items available.
            </p>
        `;
        return;
    }

    container.innerHTML = items.map(item => {
        const name =
            item.menu_item?.name ||
            item.menuItem?.name ||
            item.name ||
            item.item_name ||
            'Menu Item';

        const quantity = Number(item.quantity || 1);
        const price = Number(item.price || item.unit_price || 0);
        const subtotal = price * quantity;
        const notes = item.notes || item.special_request || '';

        return `
            <div>
                <div class="receipt-row">
                    <span class="min-w-0 break-words">${safeText(quantity)}x ${safeText(name)}</span>
                    <strong>${formatMoney(subtotal)}</strong>
                </div>
                ${notes ? `<p class="pl-3 text-[10px] text-gray-500">Note: ${safeText(notes)}</p>` : ''}
            </div>
        `;
    }).join('');
}


function printThermalReceipt() {
    const receipt = document.getElementById('receiptContent');

    if (!receipt) {
        showPaymentToast('Receipt content is not available.', 'error');
        return;
    }

    const printWindow = window.open(
        '',
        'DineSyncReceipt',
        'width=420,height=760,resizable=yes,scrollbars=yes'
    );

    if (!printWindow) {
        showPaymentToast(
            'The print window was blocked. Please allow pop-ups for this site.',
            'warning'
        );
        return;
    }

    const receiptClone = receipt.cloneNode(true);

    receiptClone.removeAttribute('id');
    receiptClone.classList.remove(
        'rounded-sm',
        'shadow-sm'
    );

    const logo = receiptClone.querySelector('#receiptLogo');

    if (logo) {
        logo.removeAttribute('id');
    }

    const documentHtml = `
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <title>Chef Oppa Payment Receipt</title>

            <style>
                @page {
                    size: 80mm auto;
                    margin: 3mm;
                }

                * {
                    box-sizing: border-box;
                }

                html,
                body {
                    margin: 0;
                    padding: 0;
                    background: #ffffff;
                    color: #000000;
                    font-family:
                        "Courier New",
                        Courier,
                        monospace;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                body {
                    width: 74mm;
                    margin: 0 auto;
                }

                .thermal-receipt {
                    width: 74mm;
                    max-width: 74mm;
                    margin: 0 auto;
                    padding: 0;
                    background: #ffffff;
                    color: #000000;
                    font-family:
                        "Courier New",
                        Courier,
                        monospace;
                    font-size: 10px;
                    line-height: 1.4;
                }

                header,
                footer {
                    text-align: center;
                }

                img {
                    display: block;
                    width: 18mm;
                    height: 18mm;
                    margin: 0 auto 2mm;
                    border-radius: 50%;
                    object-fit: cover;
                    filter: grayscale(1);
                }

                h2,
                p {
                    margin: 0;
                }

                h2 {
                    font-size: 16px;
                    font-weight: 900;
                    line-height: 1.15;
                    text-transform: uppercase;
                }

                .receipt-rule {
                    border-top: 1px dashed #000000;
                    margin: 3mm 0;
                }

                .receipt-row {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 3mm;
                    width: 100%;
                }

                .receipt-row > :first-child {
                    min-width: 0;
                    overflow-wrap: anywhere;
                }

                .receipt-row > :last-child {
                    flex-shrink: 0;
                    max-width: 42mm;
                    text-align: right;
                    overflow-wrap: anywhere;
                }

                .receipt-label {
                    color: #000000;
                }

                section {
                    display: block;
                }

                .space-y-1 > * + * {
                    margin-top: 1mm;
                }

                .space-y-2 > * + * {
                    margin-top: 2mm;
                }

                .mb-1 {
                    margin-bottom: 1mm;
                }

                .mb-2 {
                    margin-bottom: 2mm;
                }

                .mt-0\.5 {
                    margin-top: .5mm;
                }

                .mt-1 {
                    margin-top: 1mm;
                }

                .mt-2 {
                    margin-top: 2mm;
                }

                .my-2 {
                    margin-top: 2mm;
                    margin-bottom: 2mm;
                }

                .pl-3 {
                    padding-left: 3mm;
                }

                .text-center {
                    text-align: center;
                }

                .text-right {
                    text-align: right;
                }

                .font-bold,
                .font-black,
                strong {
                    font-weight: 900;
                }

                .uppercase {
                    text-transform: uppercase;
                }

                .tracking-wide,
                .tracking-\[0\.18em\] {
                    letter-spacing: .08em;
                }

                .text-\[20px\] {
                    font-size: 16px;
                }

                .text-\[13px\] {
                    font-size: 11px;
                }

                .text-\[10px\] {
                    font-size: 9px;
                }

                .text-gray-500,
                .text-gray-700 {
                    color: #000000;
                }

                .border-t,
                .border-dotted {
                    border-top: 1px dotted #000000;
                }

                .break-words {
                    overflow-wrap: anywhere;
                }

                .max-w-\[210px\] {
                    max-width: 42mm;
                }

                .receipt-total-row {
                    font-size: 14px;
                    font-weight: 900;
                }

                .hidden {
                    display: none !important;
                }

                @media screen {
                    body {
                        padding: 12px 0;
                    }
                }

                @media print {
                    html,
                    body {
                        width: 74mm;
                        min-width: 74mm;
                        max-width: 74mm;
                    }

                    body {
                        margin: 0;
                        padding: 0;
                    }
                }
            </style>
        </head>

        <body>
            ${receiptClone.outerHTML}

            <script>
                window.addEventListener('load', function () {
                    const images = Array.from(document.images);

                    Promise.all(
                        images.map(function (image) {
                            if (image.complete) {
                                return Promise.resolve();
                            }

                            return new Promise(function (resolve) {
                                image.addEventListener('load', resolve, {
                                    once: true
                                });

                                image.addEventListener('error', resolve, {
                                    once: true
                                });
                            });
                        })
                    ).then(function () {
                        setTimeout(function () {
                            window.focus();
                            window.print();
                        }, 250);
                    });
                });

                window.addEventListener('afterprint', function () {
                    window.close();
                });
            <\/script>
        </body>
        </html>
    `;

    printWindow.document.open();
    printWindow.document.write(documentHtml);
    printWindow.document.close();
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

document.getElementById('receiptModal')?.addEventListener('click', function (event) {
    if (event.target === this) closeReceiptModal();
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeReceiptModal();
});

loadPayments();
</script>

@endsection