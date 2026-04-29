@extends('layouts.admin')

@section('content')

<h1 class="text-3xl font-bold mb-1">Payment Management</h1>
<p class="text-gray-500 mb-6">View PayMongo payment transactions and statuses.</p>

<div class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-bold mb-3">Payment Transactions</h2>
    <div id="payments"></div>
</div>

<script>
async function loadPayments() {
    const res = await fetch('/api/admin/payments');
    const data = await res.json();

    let html = '';

    data.forEach(payment => {
        const statusClass = payment.status === 'paid'
            ? 'text-green-600 bg-green-50'
            : payment.status === 'failed'
                ? 'text-red-600 bg-red-50'
                : 'text-yellow-600 bg-yellow-50';

        html += `
            <div class="flex justify-between items-center border-b py-3">
                <div>
                    <p class="font-semibold">
                        Order #${payment.order ? payment.order.order_number : payment.order_id}
                    </p>
                    <p class="text-sm text-gray-500">
                        Method: ${payment.payment_method || 'PayMongo'}
                    </p>
                    <p class="text-xs text-gray-400">
                        Reference: ${payment.reference_number || 'No reference yet'}
                    </p>
                </div>

                <div class="text-right">
                    <p class="font-bold">₱${Number(payment.amount).toLocaleString()}</p>
                    <span class="${statusClass} px-2 py-1 rounded text-sm font-bold">
                        ${payment.status}
                    </span>
                </div>
            </div>
        `;
    });

    document.getElementById('payments').innerHTML = html || '<p class="text-gray-500">No payments yet.</p>';
}

loadPayments();
</script>

@endsection