<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Refill;
use App\Models\RestaurantTable;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KdsController extends Controller
{
    public function index()
    {
        $orders = $this->getKdsOrders();

        $refills = Schema::hasTable('refills')
            ? $this->getKdsRefills()
            : collect();

        return view('kitchen.dashboard', compact('orders', 'refills'));
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
            /*
            |--------------------------------------------------------------------------
            | Existing order deduction protection
            |--------------------------------------------------------------------------
            | Your OrderController already deducts ingredients during order creation.
            | This method keeps the old protection so the same order is not deducted twice.
            */
            if ($newStatus === 'preparing' && $oldStatus !== 'preparing') {
                $this->deductIngredientsForOrderIfNeeded($order);
            }

            $order->status = $newStatus;
            $order->updated_at = now();
            $order->save();
        });

        AuditService::record(
            module: 'Kitchen',
            action: 'status_changed',
            description: "Changed order {$order->order_number} status from {$oldStatus} to {$newStatus}.",
            auditable: $order,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus],
            request: $request
        );

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

    /*
    |--------------------------------------------------------------------------
    | Refill status update
    |--------------------------------------------------------------------------
    | requested -> preparing -> ready -> served
    |
    | Inventory is deducted only once when the refill first becomes preparing.
    */
    public function updateRefillStatus(Request $request, Refill $refill)
    {
        if (!Schema::hasTable('refills') || !Schema::hasTable('refill_items')) {
            return $this->refillErrorResponse(
                $request,
                'Refill tables are not installed yet.',
                500
            );
        }

        $validated = $request->validate([
            'status' => 'required|in:requested,preparing,ready,served,cancelled',
        ]);

        $newStatus = strtolower(trim($validated['status']));
        $originalStatus = strtolower(trim($refill->status ?? 'requested'));

        try {
            DB::transaction(function () use ($refill, $newStatus) {
                $lockedRefill = Refill::query()
                    ->whereKey($refill->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldStatus = strtolower(trim($lockedRefill->status ?? 'requested'));

                if ($this->isInvalidRefillTransition($oldStatus, $newStatus)) {
                    throw new \RuntimeException(
                        "Invalid refill transition from {$oldStatus} to {$newStatus}."
                    );
                }

                if (
                    $newStatus === 'preparing'
                    && $oldStatus !== 'preparing'
                    && empty($lockedRefill->preparing_at)
                ) {
                    $this->deductIngredientsForRefill($lockedRefill);
                    $lockedRefill->preparing_at = now();
                }

                if ($newStatus === 'ready' && empty($lockedRefill->ready_at)) {
                    $lockedRefill->ready_at = now();
                }

                if ($newStatus === 'served' && empty($lockedRefill->served_at)) {
                    $lockedRefill->served_at = now();
                }

                if ($newStatus === 'cancelled' && empty($lockedRefill->cancelled_at)) {
                    $lockedRefill->cancelled_at = now();
                }

                $lockedRefill->status = $newStatus;
                $lockedRefill->save();
            });
        } catch (\Throwable $error) {
            report($error);

            return $this->refillErrorResponse(
                $request,
                $error->getMessage() ?: 'Failed to update refill status.',
                422
            );
        }

        $refill->refresh();

        AuditService::record(
            module: 'Refills',
            action: 'status_changed',
            description: "Changed refill #{$refill->id} status from {$originalStatus} to {$newStatus}.",
            auditable: $refill,
            oldValues: ['status' => $originalStatus],
            newValues: [
                'status' => $newStatus,
                'preparing_at' => $refill->preparing_at,
                'ready_at' => $refill->ready_at,
                'served_at' => $refill->served_at,
                'cancelled_at' => $refill->cancelled_at,
            ],
            request: $request
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Refill status updated successfully.',
                'refill_id' => $refill->id,
                'status' => $newStatus,
            ]);
        }

        return back()->with('success', 'Refill status updated successfully.');
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

    /*
    |--------------------------------------------------------------------------
    | Refill queue endpoint
    |--------------------------------------------------------------------------
    | The next Blade step will call this endpoint every few seconds.
    */
    public function fetchRefills()
    {
        if (!Schema::hasTable('refills')) {
            return response()->json([
                'success' => true,
                'html' => [],
                'counts' => [
                    'requested' => 0,
                    'preparing' => 0,
                    'ready' => 0,
                    'served' => 0,
                ],
            ]);
        }

        $refills = $this->getKdsRefills();

        $statuses = [
            'requested' => [
                'buttonText' => 'START PREPARING',
                'nextStatus' => 'preparing',
                'buttonClass' => 'bg-blue-600 hover:bg-blue-700',
                'emptyText' => 'No refill requests',
            ],
            'preparing' => [
                'buttonText' => 'MARK READY',
                'nextStatus' => 'ready',
                'buttonClass' => 'bg-green-600 hover:bg-green-700',
                'emptyText' => 'No refills preparing',
            ],
            'ready' => [
                'buttonText' => 'MARK SERVED',
                'nextStatus' => 'served',
                'buttonClass' => 'bg-gray-800 hover:bg-black',
                'emptyText' => 'No refills ready',
            ],
            'served' => [
                'buttonText' => null,
                'nextStatus' => null,
                'buttonClass' => '',
                'emptyText' => 'No served refills today',
            ],
        ];

        $html = [];
        $counts = [];

        foreach ($statuses as $status => $config) {
            $statusRefills = $refills[$status] ?? collect();
            $counts[$status] = $statusRefills->count();

            if ($statusRefills->isEmpty()) {
                $html[$status] = '
                    <div class="h-32 flex items-center justify-center text-gray-400 font-bold">
                        ' . $config['emptyText'] . '
                    </div>
                ';
                continue;
            }

            $html[$status] = $statusRefills->map(function ($refill) use ($config) {
                return view('kitchen.partials.refill-card', [
                    'refill' => $refill,
                    'buttonText' => $config['buttonText'],
                    'nextStatus' => $config['nextStatus'],
                    'buttonClass' => $config['buttonClass'],
                ])->render();
            })->implode('');
        }

        return response()->json([
            'success' => true,
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

    private function getKdsRefills()
    {
        $today = now()->toDateString();

        return Refill::query()
            ->with([
                'order:id,order_number,table_number,table_session_id',
                'menuItem:id,name,category',
                'items.ingredient:id,name,unit,current_stock',
            ])
            ->where(function ($query) use ($today) {
                $query->whereIn('status', [
                    'requested',
                    'preparing',
                    'ready',
                ])
                ->orWhere(function ($servedQuery) use ($today) {
                    $servedQuery
                        ->where('status', 'served')
                        ->whereDate('served_at', $today);
                });
            })
            ->latest('updated_at')
            ->get()
            ->map(function ($refill) {
                if (!$refill->table_number && $refill->order) {
                    $refill->table_number = $refill->order->table_number;
                }

                return $refill;
            })
            ->groupBy('status');
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

        if (str_contains($paymentMethod, 'later')) {
            return true;
        }

        if (
            str_contains($paymentMethod, 'counter')
            || str_contains($paymentMethod, 'cash')
        ) {
            return $isPaid;
        }

        if (
            str_contains($paymentMethod, 'qr')
            || str_contains($paymentMethod, 'digital')
            || str_contains($paymentMethod, 'xendit')
        ) {
            return $isPaid;
        }

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

    private function isInvalidRefillTransition(string $oldStatus, string $newStatus): bool
    {
        if ($oldStatus === $newStatus) {
            return false;
        }

        $allowed = [
            'requested' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['served', 'cancelled'],
            'served' => [],
            'cancelled' => [],
        ];

        return !in_array($newStatus, $allowed[$oldStatus] ?? [], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Refill inventory deduction
    |--------------------------------------------------------------------------
    | FEFO: nearest-expiry usable batch is deducted first.
    */
    private function deductIngredientsForRefill(Refill $refill): void
    {
        $refill->loadMissing(['items.ingredient', 'order', 'menuItem']);

        if ($refill->items->isEmpty()) {
            throw new \RuntimeException('This refill has no configured refill items.');
        }

        foreach ($refill->items as $refillItem) {
            $ingredient = $refillItem->ingredient;
            $quantity = (float) $refillItem->quantity;

            if (!$ingredient || $quantity <= 0) {
                continue;
            }

            $availableStock = $this->getUsableIngredientStock($ingredient);

            if ($availableStock < $quantity) {
                throw new \RuntimeException(
                    "Not enough stock for {$ingredient->name}. "
                    . "Available: {$availableStock} {$ingredient->unit}. "
                    . "Required: {$quantity} {$ingredient->unit}."
                );
            }

            $this->deductIngredientStockForRefill(
                $refill,
                $ingredient,
                $quantity
            );
        }
    }

    private function deductIngredientStockForRefill(
        Refill $refill,
        Ingredient $ingredient,
        float $requiredQuantity
    ): void {
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

            $orderNumber = $refill->order?->order_number ?? $refill->order_id;

            $this->recordInventoryTransaction(
                $ingredient,
                $batch->id,
                'stock_out',
                $deductedFromBatch,
                (float) ($batch->unit_cost ?? 0),
                "Used for Refill #{$refill->id} / Order #{$orderNumber}"
            );

            $this->recordRefillIngredientUsage(
                $refill,
                $ingredient,
                $deductedFromBatch,
                $batch->id
            );

            $remainingToDeduct -= $deductedFromBatch;
        }

        if ($remainingToDeduct > 0.00001) {
            throw new \RuntimeException(
                "Unable to fully deduct {$ingredient->name} for refill."
            );
        }

        $this->syncIngredientStock($ingredient);
    }

    private function getUsableIngredientStock(Ingredient $ingredient): float
    {
        $this->syncIngredientStock($ingredient);

        return (float) InventoryBatch::where('ingredient_id', $ingredient->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->sum('quantity_remaining');
    }

    private function recordRefillIngredientUsage(
        Refill $refill,
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

        $this->addUsageColumn($data, 'order_id', $refill->order_id);
        $this->addUsageColumn($data, 'menu_item_id', $refill->menu_item_id);
        $this->addUsageColumn($data, 'ingredient_id', $ingredient->id);
        $this->addUsageColumn($data, 'inventory_batch_id', $batchId);
        $this->addUsageColumn($data, 'quantity_used', $quantityUsed);
        $this->addUsageColumn($data, 'quantity', $quantityUsed);
        $this->addUsageColumn($data, 'unit', $ingredient->unit);
        $this->addUsageColumn(
            $data,
            'remarks',
            "Used for Refill #{$refill->id}"
        );

        if (Schema::hasColumn('ingredient_usages', 'refill_id')) {
            $data['refill_id'] = $refill->id;
        }

        DB::table('ingredient_usages')->insert($data);
    }

    private function addUsageColumn(array &$data, string $column, $value): void
    {
        if (Schema::hasColumn('ingredient_usages', $column)) {
            $data[$column] = $value;
        }
    }

    private function refillErrorResponse(
        Request $request,
        string $message,
        int $status
    ) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()->with('error', $message);
    }

    private function deductIngredientsForOrderIfNeeded(Order $order): void
    {
        if (
            Schema::hasTable('ingredient_usages')
            && DB::table('ingredient_usages')
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

            if (
                $menuItem->category === 'Chef Oppa Special'
                || $menuItem->inventory_type === 'custom'
            ) {
                continue;
            }

            foreach ($menuItem->ingredients as $ingredient) {
                $requiredPerItem = (float) ($ingredient->pivot->quantity_required ?? 0);
                $orderQuantity = (int) $orderItem->quantity;
                $totalRequired = $requiredPerItem * $orderQuantity;

                if ($totalRequired <= 0) {
                    continue;
                }

                $this->deductIngredientStock(
                    $order,
                    $orderItem,
                    $ingredient,
                    $totalRequired
                );
            }
        }
    }

    private function deductIngredientStock(
        Order $order,
        OrderItem $orderItem,
        Ingredient $ingredient,
        float $requiredQuantity
    ): void {
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
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '<', $today)
            ->update([
                'status' => 'expired',
            ]);

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
