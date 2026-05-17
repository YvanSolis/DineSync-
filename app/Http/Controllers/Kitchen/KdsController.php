<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    public function index()
    {
        $orders = $this->getKdsOrders();

        return view('kitchen.dashboard', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,served',
        ]);

        $newStatus = strtolower(trim($request->status));

        $order->status = $newStatus;
        $order->updated_at = now();
        $order->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'order_id' => $order->id,
                'status' => $newStatus,
            ]);
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    public function fetchOrders()
    {
        $orders = $this->getKdsOrders();

        $statuses = [
            'pending' => [
                'buttonText' => 'START PREPARING',
                'nextStatus' => 'preparing',
                'buttonClass' => 'bg-orange-500 hover:bg-orange-600',
                'columnType' => 'pending',
                'emptyText' => 'No new orders',
            ],
            'preparing' => [
                'buttonText' => 'MARK READY',
                'nextStatus' => 'ready',
                'buttonClass' => 'bg-emerald-600 hover:bg-emerald-700',
                'columnType' => 'preparing',
                'emptyText' => 'No preparing orders',
            ],
            'ready' => [
                'buttonText' => 'COMPLETE',
                'nextStatus' => 'served',
                'buttonClass' => 'bg-slate-600 hover:bg-slate-700',
                'columnType' => 'ready',
                'emptyText' => 'No ready orders',
            ],
            'served' => [
                'buttonText' => null,
                'nextStatus' => null,
                'buttonClass' => '',
                'columnType' => 'served',
                'emptyText' => 'No completed orders',
            ],
        ];

        $html = [];
        $counts = [];

        foreach ($statuses as $status => $config) {
            $statusOrders = $orders[$status] ?? collect();

            $counts[$status] = $statusOrders->count();

            if ($statusOrders->isEmpty()) {
                $html[$status] = '
                    <div class="h-40 flex items-center justify-center text-slate-500 font-bold">
                        ' . $config['emptyText'] . '
                    </div>
                ';
                continue;
            }

            $html[$status] = $statusOrders->map(function ($order) use ($config) {
                return view('kitchen.partials.order-card', [
                    'order' => $order,
                    'buttonText' => $config['buttonText'],
                    'nextStatus' => $config['nextStatus'],
                    'buttonClass' => $config['buttonClass'],
                    'columnType' => $config['columnType'],
                ])->render();
            })->implode('');
        }

        return response()->json([
            'html' => $html,
            'counts' => $counts,
        ]);
    }

    private function getKdsOrders()
    {
        $orders = Order::with(['items.menuItem'])
            ->whereRaw("LOWER(TRIM(status)) IN (?, ?, ?, ?, ?, ?, ?)", [
                'pending',
                'new',
                'placed',
                'confirmed',
                'preparing',
                'ready',
                'served',
            ])
            ->latest()
            ->get()
            ->map(function ($order) {
                $status = strtolower(trim($order->status ?? 'pending'));

                // Only aliases for new orders.
                // Do NOT convert preparing to pending.
                if (in_array($status, ['new', 'placed', 'confirmed'])) {
                    $status = 'pending';
                }

                $order->status = $status;

                return $order;
            });

        return $this->attachTableNumbers($orders)->groupBy('status');
    }

    private function attachTableNumbers($orders)
    {
        $orderIds = $orders->pluck('id')->filter()->values();
        $tableIds = $orders->pluck('table_id')->filter()->values();

        $tablesByOrderId = RestaurantTable::whereIn('current_order_id', $orderIds)
            ->get()
            ->keyBy('current_order_id');

        $tablesById = RestaurantTable::whereIn('id', $tableIds)
            ->get()
            ->keyBy('id');

        return $orders->map(function ($order) use ($tablesByOrderId, $tablesById) {
            $tableNumber = $order->table_number ?? null;

            if (!$tableNumber && !empty($order->table_id)) {
                $tableNumber = optional($tablesById->get($order->table_id))->table_number;
            }

            if (!$tableNumber) {
                $tableNumber = optional($tablesByOrderId->get($order->id))->table_number;
            }

            $order->source_table_number = $tableNumber;

            return $order;
        });
    }
}