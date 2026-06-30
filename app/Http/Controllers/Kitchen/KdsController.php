<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $oldStatus = strtolower(trim($order->status ?? 'pending'));

        DB::transaction(function () use ($order, $newStatus, $oldStatus) {
            if ($newStatus === 'preparing' && $oldStatus !== 'preparing') {
                $this->deductIngredientsForOrderIfNeeded($order);
            }

            $order->status = $newStatus;
            $order->updated_at = now();
            $order->save();
        });

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

    private function deductIngredientsForOrderIfNeeded(Order $order): void
    {
        if (
            Schema::hasTable('ingredient_usages') &&
            DB::table('ingredient_usages')
                ->where('order_id', $order->id)
                ->exists()
        ) {
            return;
        }

        $order->load(['items.menuItem.ingredients']);

        foreach ($order->items as $orderItem) {
            $menuItem = $orderItem->menuItem;

            if (!$menuItem) {
                continue;
            }

            if ($menuItem->category === 'Chef Oppa Special' || $menuItem->inventory_type === 'custom') {
                continue;
            }

            foreach ($menuItem->ingredients as $ingredient) {
                $requiredPerItem = (float) ($ingredient->pivot->quantity_required ?? 0);
                $orderQuantity = (int) $orderItem->quantity;
                $totalRequired = $requiredPerItem * $orderQuantity;

                if ($totalRequired <= 0) {
                    continue;
                }

                $this->deductIngredientStock($order, $orderItem, $ingredient, $totalRequired);
            }
        }
    }

    private function deductIngredientStock(Order $order, OrderItem $orderItem, Ingredient $ingredient, float $requiredQuantity): void
    {
        $remainingToDeduct = $requiredQuantity;

        $batches = InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $availableInBatch = (float) $batch->quantity_remaining;
            $deductedFromBatch = min($availableInBatch, $remainingToDeduct);

            $batch->quantity_remaining = $availableInBatch - $deductedFromBatch;

            if ($batch->quantity_remaining <= 0) {
                $batch->quantity_remaining = 0;
                $batch->status = 'used_up';
            }

            $batch->save();

            $this->recordInventoryTransaction(
                $ingredient,
                $batch->id,
                'stock_out',
                $deductedFromBatch,
                (float) ($batch->unit_cost ?? 0),
                "Used for Order #{$order->order_number}"
            );

            $this->recordIngredientUsage(
                $order,
                $orderItem,
                $ingredient,
                $deductedFromBatch,
                $batch->id
            );

            $remainingToDeduct -= $deductedFromBatch;
        }

        $this->syncIngredientStock($ingredient);
    }

    private function syncIngredientStock(Ingredient $ingredient): void
    {
        $today = now()->toDateString();

        InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('quantity_remaining', '<=', 0)
            ->update([
                'status' => 'used_up',
            ]);

        $totalUsableStock = InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', $today)
            ->sum('quantity_remaining');

        $ingredient->forceFill([
            'current_stock' => $totalUsableStock,
        ])->saveQuietly();
    }

    private function recordInventoryTransaction(
        Ingredient $ingredient,
        ?int $batchId,
        string $type,
        float $quantity,
        float $unitCost,
        string $remarks
    ): void {
        if (!Schema::hasTable('inventory_transactions')) {
            return;
        }

        InventoryTransaction::create([
            'ingredient_id' => $ingredient->id,
            'inventory_batch_id' => $batchId,
            'type' => $type,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'remarks' => $remarks,
        ]);
    }

    private function recordIngredientUsage(
    Order $order,
    OrderItem $orderItem,
    Ingredient $ingredient,
    float $quantityUsed,
    ?int $batchId
): void {
    if (!Schema::hasTable('ingredient_usages')) {
        return;
    }

    $data = [
        'created_at' => now(),
        'updated_at' => now(),
    ];

    if (Schema::hasColumn('ingredient_usages', 'order_id')) {
        $data['order_id'] = $order->id;
    }

    if (Schema::hasColumn('ingredient_usages', 'order_item_id')) {
        $data['order_item_id'] = $orderItem->id;
    }

    if (Schema::hasColumn('ingredient_usages', 'menu_item_id')) {
        $data['menu_item_id'] = $orderItem->menu_item_id;
    }

    if (Schema::hasColumn('ingredient_usages', 'ingredient_id')) {
        $data['ingredient_id'] = $ingredient->id;
    }

    if (Schema::hasColumn('ingredient_usages', 'inventory_batch_id')) {
        $data['inventory_batch_id'] = $batchId;
    }

    if (Schema::hasColumn('ingredient_usages', 'quantity_used')) {
        $data['quantity_used'] = $quantityUsed;
    }

    if (Schema::hasColumn('ingredient_usages', 'quantity')) {
        $data['quantity'] = $quantityUsed;
    }

    if (Schema::hasColumn('ingredient_usages', 'unit')) {
        $data['unit'] = $ingredient->unit;
    }

    if (Schema::hasColumn('ingredient_usages', 'remarks')) {
        $data['remarks'] = "Used for Order #{$order->order_number}";
    }

    DB::table('ingredient_usages')->insert($data);
    }
}