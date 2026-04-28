@extends('layouts.admin')

@section('content')

<h1 class="text-3xl font-bold mb-1">Payment Management</h1>
<p class="text-gray-500 mb-6">View payment transactions and statuses.</p>

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
        html += `
            <div class="flex justify-between border-b py-3">
                <div>
                    <p class="font-semibold">Order #${payment.order_id}</p>
                    <p class="text-sm text-gray-500">${payment.payment_method}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold">₱${payment.amount}</p>
                    <p class="${payment.status === 'paid' ? 'text-green-600' : 'text-yellow-600'}">
                        ${payment.status}
                    </p>
                </div>
            </div>
        `;
    });

    document.getElementById('payments').innerHTML = html || '<p class="text-gray-500">No payments yet.</p>';
}

loadPayments();
</script>

@endsection