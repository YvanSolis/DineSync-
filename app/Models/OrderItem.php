<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(
            Order::with(['items.menuItem', 'payment'])->latest()->get()
        );
    }

    public function store(Request $request)
    {
        Log::info('ORDER STORE HIT FROM MOBILE/WEB', [
            'full_url' => $request->fullUrl(),
            'method' => $request->method(),
            'bearer_token' => $request->bearerToken(),
            'x_table_number' => $request->header('X-Table-Number'),
            'auth_user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'table_number' => $request->user()->table_number ?? null,
            ] : null,
            'body' => $request->all(),
        ]);

        $request->validate([
            'items' => 'required_without:order_items|array',
            'items.*.menu_item_id' => 'required_with:items|exists:menu_items,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',

            'order_items' => 'required_without:items|array',
            'order_items.*.menu_item_id' => 'required_with:order_items|exists:menu_items,id',
            'order_items.*.quantity' => 'required_with:order_items|integer|min:1',

            'customer_name' => 'nullable|string|max:255',
            'table_number' => 'nullable|string|max:50',
            'table_id' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'nullable|string|max:50',
        ]);

        $items = $request->input('items', $request->input('order_items', []));

        return DB::transaction(function () use ($request, $items) {
            $normalizedItems = $this->normalizeOrderItems($items);

            $this->validateInventoryBeforeOrder($normalizedItems);

            $table = $this->detectTableFromRequest($request);
            $tableNumber = $table?->table_number ?? $request->input('table_number');

            $tableSession = null;

            if ($table) {
                if ($table->status !== 'occupied') {
                    return response()->json([
                        'message' => 'Please wait for service staff to assign your table before placing an order.',
                    ], 403);
                }

                if (Schema::hasTable('table_sessions')) {
                    $tableSession = TableSession::where('restaurant_table_id', $table->id)
                        ->where('status', 'active')
                        ->latest()
                        ->first();

                    if (! $tableSession) {
                        return response()->json([
                            'message' => 'No active table session found. Please ask service staff to assign your table first.',
                        ], 403);
                    }
                }
            }

            $order = new Order();

            $this->setIfColumn($order, 'order_number', $this->generateOrderNumber());
            $this->setIfColumn($order, 'status', 'pending');
            $this->setIfColumn($order, 'customer_name', $request->input('customer_name'));
            $this->setIfColumn($order, 'table_number', $tableNumber);
            $this->setIfColumn($order, 'table_id', $table?->id);
            $this->setIfColumn($order, 'table_session_id', $tableSession?->id);
            $this->setIfColumn($order, 'remarks', $request->input('remarks'));
            $this->setIfColumn($order, 'notes', $request->input('notes'));
            $this->setIfColumn($order, 'payment_method', $request->input('payment_method', 'Cash'));
            $this->setIfColumn($order, 'payment_status', $request->input('payment_status', 'pending'));
            $this->setIfColumn($order, 'total_amount', 0);

            $order->save();

            $totalAmount = 0;

            foreach ($normalizedItems as $itemData) {
                $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);
                $quantity = (int) $itemData['quantity'];
                $price = (float) $menuItem->price;
                $subtotal = $price * $quantity;

                $orderItem = new OrderItem();

                $this->setIfColumn($orderItem, 'order_id', $order->id);
                $this->setIfColumn($orderItem, 'menu_item_id', $menuItem->id);
                $this->setIfColumn($orderItem, 'quantity', $quantity);
                $this->setIfColumn($orderItem, 'price', $price);
                $this->setIfColumn($orderItem, 'subtotal', $subtotal);
                $this->setIfColumn($orderItem, 'total_price', $subtotal);
                $this->setIfColumn($orderItem, 'notes', $itemData['notes'] ?? null);

                $orderItem->save();

                $totalAmount += $subtotal;
            }

            $this->setIfColumn($order, 'total_amount', $totalAmount);
            $order->save();

            if ($table && $table->status === 'occupied') {
                $table->update([
                    'current_order_id' => $order->id,
                    'notes' => $table->notes ?? 'Active table order',
                ]);
            }

            if (Schema::hasTable('payments')) {
                DB::table('payments')->insert([
                    'order_id' => $order->id,
                    'payment_method' => $request->input('payment_method', 'Cash'),
                    'amount' => $totalAmount,
                    'status' => $request->input('payment_status', 'pending'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->deductIngredientsForOrder($order);

            $this->refreshAllMenuAvailability();

            return response()->json(
                $order->fresh()->load(['items.menuItem', 'payment']),
                201
            );
        });
    }

    public function tableOrderHistory(Request $request)
    {
        $table = $this->detectTableFromRequest($request);

        if (! $table) {
            return response()->json([
                'message' => 'Table not found.',
                'orders' => [],
            ], 404);
        }

        if (! Schema::hasTable('table_sessions')) {
            return response()->json([
                'message' => 'Table sessions table does not exist yet.',
                'table_number' => $table->table_number,
                'session' => null,
                'orders' => [],
            ], 500);
        }

        $activeSession = TableSession::where('restaurant_table_id', $table->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $activeSession) {
            return response()->json([
                'message' => 'No active table session.',
                'table_number' => $table->table_number,
                'session' => null,
                'orders' => [],
            ]);
        }

        $orders = Order::with(['items.menuItem', 'payment'])
            ->where('table_session_id', $activeSession->id)
            ->oldest()
            ->get();

        return response()->json([
            'message' => 'Order history loaded successfully.',
            'table_number' => $table->table_number,
            'session' => $activeSession,
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        return response()->json(
            $order->load(['items.menuItem', 'payment'])
        );
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'table_number' => 'nullable|string|max:50',
            'table_id' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $table = $this->detectTableFromRequest($request);

        foreach (['status', 'customer_name', 'remarks', 'notes'] as $field) {
            if ($request->filled($field)) {
                $this->setIfColumn($order, $field, $request->input($field));
            }
        }

        if ($table) {
            $activeSession = null;

            if (Schema::hasTable('table_sessions')) {
                $activeSession = TableSession::where('restaurant_table_id', $table->id)
                    ->where('status', 'active')
                    ->latest()
                    ->first();
            }

            $this->setIfColumn($order, 'table_number', $table->table_number);
            $this->setIfColumn($order, 'table_id', $table->id);
            $this->setIfColumn($order, 'table_session_id', $activeSession?->id);

            $table->update([
                'notes' => $table->notes ?? 'Order linked to table',
            ]);
        }

        $order->save();

        return response()->json(
            $order->fresh()->load(['items.menuItem', 'payment'])
        );
    }

    public function destroy(Order $order)
    {
        $table = RestaurantTable::where('current_order_id', $order->id)->first();

        if ($table) {
            $table->update([
                'current_order_id' => null,
                'status' => 'cleaning',
                'notes' => 'Needs cleaning',
            ]);
        }

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }

    private function normalizeOrderItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $itemData) {
            $menuItemId = (int) $itemData['menu_item_id'];
            $quantity = (int) $itemData['quantity'];

            if (! isset($normalized[$menuItemId])) {
                $normalized[$menuItemId] = [
                    'menu_item_id' => $menuItemId,
                    'quantity' => 0,
                    'notes' => $itemData['notes'] ?? null,
                ];
            }

            $normalized[$menuItemId]['quantity'] += $quantity;
        }

        return array_values($normalized);
    }

    private function validateInventoryBeforeOrder(array $items): void
    {
        $requiredIngredients = [];

        foreach ($items as $itemData) {
            $menuItemId = (int) $itemData['menu_item_id'];
            $orderQuantity = (int) $itemData['quantity'];

            $menuItem = MenuItem::with('ingredients')->findOrFail($menuItemId);

            if ($menuItem->category === 'Chef Oppa Special' || $menuItem->inventory_type === 'custom') {
                continue;
            }

            if (Schema::hasColumn('menu_items', 'is_available') && ! $menuItem->is_available) {
                throw ValidationException::withMessages([
                    'inventory' => "{$menuItem->name} is currently unavailable.",
                ]);
            }

            if ($menuItem->ingredients->isEmpty()) {
                throw ValidationException::withMessages([
                    'inventory' => "{$menuItem->name} has no linked ingredients yet.",
                ]);
            }

            foreach ($menuItem->ingredients as $ingredient) {
                $requiredPerItem = (float) ($ingredient->pivot->quantity_required ?? 0);

                if ($requiredPerItem <= 0) {
                    throw ValidationException::withMessages([
                        'inventory' => "{$menuItem->name} has invalid ingredient usage for {$ingredient->name}.",
                    ]);
                }

                $totalRequired = $requiredPerItem * $orderQuantity;

                if (! isset($requiredIngredients[$ingredient->id])) {
                    $requiredIngredients[$ingredient->id] = [
                        'ingredient_id' => $ingredient->id,
                        'ingredient_name' => $ingredient->name,
                        'ingredient_unit' => $ingredient->unit,
                        'required' => 0,
                        'menu_items' => [],
                    ];
                }

                $requiredIngredients[$ingredient->id]['required'] += $totalRequired;
                $requiredIngredients[$ingredient->id]['menu_items'][] = $menuItem->name;
            }
        }

        foreach ($requiredIngredients as $data) {
            $ingredient = Ingredient::where('id', $data['ingredient_id'])
                ->lockForUpdate()
                ->first();

            if (! $ingredient) {
                throw ValidationException::withMessages([
                    'inventory' => 'Ingredient not found.',
                ]);
            }

            $availableStock = $this->getAvailableIngredientStock($ingredient);
            $requiredStock = (float) $data['required'];

            if ($availableStock < $requiredStock) {
                $menuNames = implode(', ', array_unique($data['menu_items']));

                throw ValidationException::withMessages([
                    'inventory' => "Cannot place order. Not enough stock for {$ingredient->name}. Available: {$availableStock} {$ingredient->unit}. Required: {$requiredStock} {$ingredient->unit}. Affected item/s: {$menuNames}.",
                ]);
            }
        }
    }

    private function deductIngredientsForOrder(Order $order): void
    {
        $order->load(['items.menuItem.ingredients']);

        foreach ($order->items as $orderItem) {
            $menuItem = $orderItem->menuItem;

            if (! $menuItem) {
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

        if (class_exists(InventoryBatch::class) && Schema::hasTable('inventory_batches')) {
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
        }

        if ($remainingToDeduct > 0) {
            $currentStock = (float) ($ingredient->current_stock ?? 0);
            $ingredient->current_stock = max(0, $currentStock - $remainingToDeduct);
            $ingredient->save();

            $this->recordInventoryTransaction(
                $ingredient,
                null,
                'stock_out',
                $remainingToDeduct,
                0,
                "Used for Order #{$order->order_number}"
            );

            $this->recordIngredientUsage(
                $order,
                $orderItem,
                $ingredient,
                $remainingToDeduct,
                null
            );
        }

        $this->syncIngredientCurrentStock($ingredient);
    }

    private function syncIngredientCurrentStock(Ingredient $ingredient): void
    {
        if (Schema::hasTable('inventory_batches')) {
            InventoryBatch::where('ingredient_id', $ingredient->id)
                ->where('quantity_remaining', '>', 0)
                ->whereDate('expiry_date', '<', now()->toDateString())
                ->update([
                    'status' => 'expired',
                ]);

            $totalUsableStock = InventoryBatch::where('ingredient_id', $ingredient->id)
                ->where('status', 'active')
                ->where('quantity_remaining', '>', 0)
                ->whereDate('expiry_date', '>=', now()->toDateString())
                ->sum('quantity_remaining');

            $ingredient->update([
                'current_stock' => $totalUsableStock,
            ]);

            return;
        }

        $ingredient->refresh();
    }

    private function getAvailableIngredientStock(Ingredient $ingredient): float
    {
        if (Schema::hasTable('inventory_batches')) {
            InventoryBatch::where('ingredient_id', $ingredient->id)
                ->where('quantity_remaining', '>', 0)
                ->whereDate('expiry_date', '<', now()->toDateString())
                ->update([
                    'status' => 'expired',
                ]);

            return (float) InventoryBatch::where('ingredient_id', $ingredient->id)
                ->where('status', 'active')
                ->where('quantity_remaining', '>', 0)
                ->whereDate('expiry_date', '>=', now()->toDateString())
                ->sum('quantity_remaining');
        }

        return (float) ($ingredient->current_stock ?? 0);
    }

    private function recordInventoryTransaction(
        Ingredient $ingredient,
        ?int $batchId,
        string $type,
        float $quantity,
        float $unitCost,
        string $remarks
    ): void {
        if (! Schema::hasTable('inventory_transactions')) {
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
        if (! Schema::hasTable('ingredient_usages')) {
            return;
        }

        $data = [
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $this->addColumnValue($data, 'order_id', $order->id, 'ingredient_usages');
        $this->addColumnValue($data, 'order_item_id', $orderItem->id, 'ingredient_usages');
        $this->addColumnValue($data, 'menu_item_id', $orderItem->menu_item_id, 'ingredient_usages');
        $this->addColumnValue($data, 'ingredient_id', $ingredient->id, 'ingredient_usages');
        $this->addColumnValue($data, 'inventory_batch_id', $batchId, 'ingredient_usages');
        $this->addColumnValue($data, 'quantity_used', $quantityUsed, 'ingredient_usages');
        $this->addColumnValue($data, 'quantity', $quantityUsed, 'ingredient_usages');
        $this->addColumnValue($data, 'unit', $ingredient->unit, 'ingredient_usages');
        $this->addColumnValue($data, 'remarks', "Used for Order #{$order->order_number}", 'ingredient_usages');

        DB::table('ingredient_usages')->insert($data);
    }

    private function refreshAllMenuAvailability(): void
    {
        if (! Schema::hasColumn('menu_items', 'is_available')) {
            return;
        }

        MenuItem::with('ingredients')->chunk(100, function ($menuItems) {
            foreach ($menuItems as $menuItem) {
                if ($menuItem->category === 'Chef Oppa Special' || $menuItem->inventory_type === 'custom') {
                    $menuItem->forceFill([
                        'inventory_type' => 'custom',
                        'is_available' => true,
                    ])->saveQuietly();

                    continue;
                }

                $menuItem->forceFill([
                    'inventory_type' => $menuItem->inventory_type ?: 'per_order',
                    'daily_limit' => null,
                    'is_available' => $menuItem->computeAvailability(),
                ])->saveQuietly();
            }
        });
    }

    private function detectTableFromRequest(Request $request): ?RestaurantTable
    {
        $tableNumber = null;

        if ($request->filled('table_number')) {
            $tableNumber = $request->input('table_number');
        }

        if (! $tableNumber && $request->filled('table_id')) {
            $tableById = RestaurantTable::find($request->input('table_id'));

            if ($tableById) {
                return $tableById;
            }
        }

        $authUser = $request->user();

        if (! $tableNumber && $authUser && $authUser->role === 'table_customer') {
            $tableNumber = $authUser->table_number;
        }

        $bearerToken = $request->bearerToken();

        if (! $tableNumber && $bearerToken && preg_match('/table-token-(\d+)/', $bearerToken, $matches)) {
            $tableNumber = $matches[1];
        }

        if (! $tableNumber) {
            $headerTableNumber = $request->header('X-Table-Number');

            if ($headerTableNumber) {
                $tableNumber = $headerTableNumber;
            }
        }

        if (! $tableNumber) {
            return null;
        }

        return RestaurantTable::where('table_number', $tableNumber)->first();
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    private function addColumnValue(array &$data, string $column, $value, string $table): void
    {
        if (Schema::hasColumn($table, $column)) {
            $data[$column] = $value;
        }
    }

    private function setIfColumn($model, string $column, $value): void
    {
        if (Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value;
        }
    }
}