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

        if (!$this->orderCanAppearInKds($order)) {
            return back()->with('error', 'This order must be paid first before kitchen preparation.');
        }

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
                'buttonClass' => 'bg-yellow-500 hover:bg-yellow-600',
                'columnType' => 'pending',
                'emptyText' => 'No new orders',
            ],
            'preparing' => [
                'buttonText' => 'MARK READY',
                'nextStatus' => 'ready',
                'buttonClass' => 'bg-green-500 hover:bg-green-600',
                'columnType' => 'preparing',
                'emptyText' => 'No preparing orders',
            ],
            'ready' => [
                'buttonText' => 'COMPLETE',
                'nextStatus' => 'served',
                'buttonClass' => 'bg-gray-700 hover:bg-gray-800',
                'columnType' => 'ready',
                'emptyText' => 'No ready orders',
            ],
            'served' => [
                'buttonText' => null,
                'nextStatus' => null,
                'buttonClass' => '',
                'columnType' => 'served',
                'emptyText' => 'No completed orders today',
            ],
        ];

        $html = [];
        $counts = [];

        foreach ($statuses as $status => $config) {
            $statusOrders = $orders[$status] ?? collect();

            $counts[$status] = $statusOrders->count();

            if ($statusOrders->isEmpty()) {
                $html[$status] = '
                    <div class="h-40 flex items-center justify-center text-gray-400 font-bold">
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
        $today = now()->toDateString();

        $orders = Order::with(['items.menuItem'])
            ->where(function ($query) use ($today) {
                $query->whereRaw("LOWER(TRIM(status)) IN (?, ?, ?, ?, ?, ?)", [
                    'pending',
                    'new',
                    'placed',
                    'confirmed',
                    'preparing',
                    'ready',
                ])
                ->orWhere(function ($servedQuery) use ($today) {
                    $servedQuery->whereRaw("LOWER(TRIM(status)) = ?", ['served'])
                        ->whereDate('updated_at', $today);
                });
            })
            ->latest('updated_at')
            ->get()
            ->map(function ($order) {
                $status = strtolower(trim($order->status ?? 'pending'));

                if (in_array($status, ['new', 'placed', 'confirmed'], true)) {
                    $status = 'pending';
                }

                $order->status = $status;

                return $order;
            })
            ->filter(function ($order) {
                return $this->orderCanAppearInKds($order);
            })
            ->values();

        return $this->attachTableNumbers($orders)->groupBy('status');
    }

    private function orderCanAppearInKds(Order $order): bool
    {
        $status = strtolower(trim($order->status ?? 'pending'));

        if ($status === 'served') {
            return true;
        }

        $paymentMethod = strtolower(
            str_replace(
                ['_', '-'],
                ' ',
                trim($order->payment_method ?? '')
            )
        );

        $paymentStatus = strtolower(trim($order->payment_status ?? 'pending'));

        $isPaid = in_array($paymentStatus, [
            'paid',
            'verified',
            'settled',
            'completed',
        ], true);

        /*
        |--------------------------------------------------------------------------
        | Pay Later
        |--------------------------------------------------------------------------
        | Pay Later orders are allowed to go to kitchen even if payment is pending.
        */
        if (str_contains($paymentMethod, 'later')) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Pay at Counter / Cash
        |--------------------------------------------------------------------------
        | These must be paid first before appearing in KDS.
        */
        if (
            str_contains($paymentMethod, 'counter') ||
            str_contains($paymentMethod, 'cash')
        ) {
            return $isPaid;
        }

        /*
        |--------------------------------------------------------------------------
        | QR PH / Digital / Xendit
        |--------------------------------------------------------------------------
        | These must be paid first before appearing in KDS.
        */
        if (
            str_contains($paymentMethod, 'qr') ||
            str_contains($paymentMethod, 'digital') ||
            str_contains($paymentMethod, 'xendit')
        ) {
            return $isPaid;
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        | If old orders have no payment method, still allow them so KDS will not break.
        */
        if ($paymentMethod === '') {
            return true;
        }

        return $isPaid;
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