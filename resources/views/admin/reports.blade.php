@extends('layouts.admin')

@section('content')

<h1 class="text-3xl font-bold mb-1">Reports & Forecast</h1>
<p class="text-gray-500 mb-6">View sales, ingredient usage, and AI forecast reports.</p>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-bold mb-3">Sales Report</h2>
        <p>Total sales and order data are shown in the dashboard.</p>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-bold mb-3">AI Forecast Report</h2>
        <p>Facebook Prophet forecast results are shown in the dashboard forecast section.</p>
    </div>
</div>

@endsection