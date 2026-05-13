@extends('layouts.service')

@section('page-title', 'Customer Assistance')
@section('page-subtitle', 'Manage customer requests and service assistance')

@section('content')
<div class="space-y-8">

    <!-- Assistance Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <p class="text-gray-400 text-sm font-semibold">Pending Requests</p>
            <h3 class="text-4xl font-extrabold text-orange-600 mt-2">6</h3>
            <p class="text-xs text-gray-400 mt-2">Needs attention</p>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <p class="text-gray-400 text-sm font-semibold">In Progress</p>
            <h3 class="text-4xl font-extrabold text-blue-600 mt-2">3</h3>
            <p class="text-xs text-gray-400 mt-2">Being handled</p>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <p class="text-gray-400 text-sm font-semibold">Resolved Today</p>
            <h3 class="text-4xl font-extrabold text-green-600 mt-2">18</h3>
            <p class="text-xs text-gray-400 mt-2">Completed requests</p>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <p class="text-gray-400 text-sm font-semibold">Avg Response</p>
            <h3 class="text-4xl font-extrabold text-purple-600 mt-2">3m</h3>
            <p class="text-xs text-gray-400 mt-2">Average response time</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div class="flex flex-wrap gap-3">
                <button class="px-5 py-3 rounded-2xl bg-orange-500 text-white text-sm font-bold shadow-md shadow-orange-100">All Requests</button>
                <button class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-500 text-sm font-bold hover:bg-orange-50 hover:text-orange-600">Pending</button>
                <button class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-500 text-sm font-bold hover:bg-blue-50 hover:text-blue-600">In Progress</button>
                <button class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-500 text-sm font-bold hover:bg-green-50 hover:text-green-600">Resolved</button>
            </div>

            <button class="px-5 py-3 rounded-2xl bg-orange-500 text-white text-sm font-bold hover:bg-orange-600 shadow-md shadow-orange-100">
                + Add Manual Request
            </button>
        </div>
    </div>

    <!-- Request Cards -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl shadow-sm border border-orange-100 overflow-hidden">
            <div class="p-6 bg-orange-50 border-b border-orange-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-900">Table 05</h3>
                        <p class="text-sm text-gray-400 mt-1">Requested 2 mins ago</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500 text-white">Pending</span>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl">🙋</div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Request Type</p>
                        <p class="font-extrabold text-gray-900">Call Waiter</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl p-4">
                    <p class="text-sm text-gray-500">Customer is requesting service staff assistance at the table.</p>
                </div>

                <div class="flex gap-3">
                    <button class="flex-1 py-3 rounded-2xl bg-blue-500 text-white text-sm font-bold hover:bg-blue-600">Start</button>
                    <button class="flex-1 py-3 rounded-2xl bg-green-500 text-white text-sm font-bold hover:bg-green-600">Resolve</button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-blue-100 overflow-hidden">
            <div class="p-6 bg-blue-50 border-b border-blue-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-900">Table 02</h3>
                        <p class="text-sm text-gray-400 mt-1">Requested 6 mins ago</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-500 text-white">In Progress</span>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl">🥢</div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Request Type</p>
                        <p class="font-extrabold text-gray-900">Extra Utensils</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl p-4">
                    <p class="text-sm text-gray-500">Customer asked for extra chopsticks and spoon.</p>
                </div>

                <div class="flex gap-3">
                    <button class="flex-1 py-3 rounded-2xl bg-gray-100 text-gray-500 text-sm font-bold">Handling</button>
                    <button class="flex-1 py-3 rounded-2xl bg-green-500 text-white text-sm font-bold hover:bg-green-600">Resolve</button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-green-100 overflow-hidden">
            <div class="p-6 bg-green-50 border-b border-green-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-900">Table 08</h3>
                        <p class="text-sm text-gray-400 mt-1">Resolved 4 mins ago</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-500 text-white">Resolved</span>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-green-100 text-green-600 flex items-center justify-center text-2xl">💧</div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Request Type</p>
                        <p class="font-extrabold text-gray-900">Need Water</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl p-4">
                    <p class="text-sm text-gray-500">Water request has been completed by service staff.</p>
                </div>

                <button class="w-full py-3 rounded-2xl bg-gray-100 text-gray-500 text-sm font-bold">
                    Completed
                </button>
            </div>
        </div>
    </div>

    <!-- Request Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-extrabold text-gray-900">All Assistance Requests</h3>
            <p class="text-sm text-gray-400 mt-1">Track all customer service requests</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-xs font-extrabold text-gray-400 uppercase tracking-wider px-6 py-4">Request</th>
                        <th class="text-left text-xs font-extrabold text-gray-400 uppercase tracking-wider px-6 py-4">Table</th>
                        <th class="text-left text-xs font-extrabold text-gray-400 uppercase tracking-wider px-6 py-4">Type</th>
                        <th class="text-left text-xs font-extrabold text-gray-400 uppercase tracking-wider px-6 py-4">Time</th>
                        <th class="text-left text-xs font-extrabold text-gray-400 uppercase tracking-wider px-6 py-4">Status</th>
                        <th class="text-right text-xs font-extrabold text-gray-400 uppercase tracking-wider px-6 py-4">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-5 font-extrabold text-gray-900">#REQ-3012</td>
                        <td class="px-6 py-5 font-bold text-gray-700">Table 05</td>
                        <td class="px-6 py-5 text-sm text-gray-500">Call Waiter</td>
                        <td class="px-6 py-5 text-sm text-gray-500">2 mins ago</td>
                        <td class="px-6 py-5"><span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-50 text-orange-600">Pending</span></td>
                        <td class="px-6 py-5 text-right">
                            <button class="px-4 py-2 rounded-xl bg-blue-500 text-white text-xs font-bold hover:bg-blue-600">Start</button>
                        </td>
                    </tr>

                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-5 font-extrabold text-gray-900">#REQ-3011</td>
                        <td class="px-6 py-5 font-bold text-gray-700">Table 02</td>
                        <td class="px-6 py-5 text-sm text-gray-500">Extra Utensils</td>
                        <td class="px-6 py-5 text-sm text-gray-500">6 mins ago</td>
                        <td class="px-6 py-5"><span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600">In Progress</span></td>
                        <td class="px-6 py-5 text-right">
                            <button class="px-4 py-2 rounded-xl bg-green-500 text-white text-xs font-bold hover:bg-green-600">Resolve</button>
                        </td>
                    </tr>

                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-5 font-extrabold text-gray-900">#REQ-3010</td>
                        <td class="px-6 py-5 font-bold text-gray-700">Table 08</td>
                        <td class="px-6 py-5 text-sm text-gray-500">Need Water</td>
                        <td class="px-6 py-5 text-sm text-gray-500">12 mins ago</td>
                        <td class="px-6 py-5"><span class="px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-600">Resolved</span></td>
                        <td class="px-6 py-5 text-right">
                            <button class="px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-xs font-bold hover:bg-gray-200">View</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection