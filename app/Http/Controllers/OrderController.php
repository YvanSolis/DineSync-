<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Services\InventoryDeductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(
            Order::with(['items.menuItem', 'payment'])->latest()->get()
        );
    }

    public function store(Request $request, InventoryDeductionService $inventoryDeductionService)
    {
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

        return DB::transaction(function () use ($request, $items, $inventoryDeductionService) {
            $this->validateInventoryBeforeOrder($items);

            $table = $this->detectTableFromRequest($request);
            $tableNumber = $table?->table_number ?? $request->input('table_number');

            $order = new Order();

            $this->setIfColumn($order, 'order_number', $this->generateOrderNumber());
            $this->setIfColumn($order, 'status', 'pending');
            $this->setIfColumn($order, 'customer_name', $request->input('customer_name'));
            $this->setIfColumn($order, 'table_number', $tableNumber);
            $this->setIfColumn($order, 'table_id', $table?->id);
            $this->setIfColumn($order, 'remarks', $request->input('remarks'));
            $this->setIfColumn($order, 'notes', $request->input('notes'));
            $this->setIfColumn($order, 'total_amount', 0);

            $order->save();

            $totalAmount = 0;

            foreach ($items as $itemData) {
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

            if ($table) {
                $table->update([
                    'status' => 'occupied',
                    'current_order_id' => $order->id,
                    'occupied_at' => $table->occupied_at ?? now(),
                    'notes' => 'Tablet order',
                ]);
            }

            if (Schema::hasTable('payments')) {
                DB::table('payments')->insert([
                    'order_id' => $order->id,
                    'payment_method' => $request->input('payment_method', 'Cash'),
                    'amount' => $totalAmount,
                    'status' => $request->input('payment_status', 'paid'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $inventoryDeductionService->deductForOrder($order);

            $this->refreshAllMenuAvailability();

            return response()->json(
                $order->fresh()->load(['items.menuItem', 'payment']),
                201
            );
        }); Log::info('ORDER STORE HIT FROM MOBILE/WEB', [
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
            $this->setIfColumn($order, 'table_number', $table->table_number);
            $this->setIfColumn($order, 'table_id', $table->id);

            $table->update([
                'status' => 'occupied',
                'current_order_id' => $order->id,
                'occupied_at' => $table->occupied_at ?? now(),
                'notes' => 'Order assigned',
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

    private function detectTableFromRequest(Request $request): ?RestaurantTable
    {
        $tableNumber = null;

        if ($request->filled('table_number')) {
            $tableNumber = $request->input('table_number');
        }

        if (!$tableNumber && $request->filled('table_id')) {
            $tableById = RestaurantTable::find($request->input('table_id'));

            if ($tableById) {
                return $tableById;
            }
        }

        $authUser = $request->user();

        if (!$tableNumber && $authUser && $authUser->role === 'table_customer') {
            $tableNumber = $authUser->table_number;
        }

        $bearerToken = $request->bearerToken();

        if (!$tableNumber && $bearerToken && preg_match('/table-token-(\d+)/', $bearerToken, $matches)) {
            $tableNumber = $matches[1];
        }

        if (!$tableNumber) {
            $headerTableNumber = $request->header('X-Table-Number');

            if ($headerTableNumber) {
                $tableNumber = $headerTableNumber;
            }
        }

        if (!$tableNumber) {
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

    private function validateInventoryBeforeOrder(array $items): void
    {
        $requiredIngredients = [];

        foreach ($items as $itemData) {
            $menuItem = MenuItem::with('ingredients')->findOrFail($itemData['menu_item_id']);

            if (
                Schema::hasColumn('menu_items', 'is_available') &&
                !$menuItem->is_available
            ) {
                throw ValidationException::withMessages([
                    'inventory' => "{$menuItem->name} is currently unavailable.",
                ]);
            }

            $orderQuantity = (int) $itemData['quantity'];

            foreach ($menuItem->ingredients as $ingredient) {
                $requiredPerItem = (float) $ingredient->pivot->quantity_required;
                $totalRequired = $requiredPerItem * $orderQuantity;

                if (!isset($requiredIngredients[$ingredient->id])) {
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
            $ingredient = \App\Models\Ingredient::where('id', $data['ingredient_id'])
                ->lockForUpdate()
                ->first();

            if (!$ingredient) {
                throw ValidationException::withMessages([
                    'inventory' => 'Ingredient not found.',
                ]);
            }

            $availableStock = (float) $ingredient->total_stock;
            $requiredStock = (float) $data['required'];

            if ($availableStock < $requiredStock) {
                $menuNames = implode(', ', array_unique($data['menu_items']));

                throw ValidationException::withMessages([
                    'inventory' => "Cannot place order. Not enough stock for {$ingredient->name}. Required: {$requiredStock} {$ingredient->unit}, Available: {$availableStock} {$ingredient->unit}. Affected item/s: {$menuNames}.",
                ]);
            }
        }
    }

    private function refreshAllMenuAvailability(): void
    {
        if (!Schema::hasColumn('menu_items', 'is_available')) {
            return;
        }

        $menuItems = MenuItem::with('ingredients')->get();

        foreach ($menuItems as $menuItem) {
            $isAvailable = true;

            foreach ($menuItem->ingredients as $ingredient) {
                $requiredPerItem = (float) $ingredient->pivot->quantity_required;
                $availableStock = (float) $ingredient->total_stock;

                if ($availableStock < $requiredPerItem) {
                    $isAvailable = false;
                    break;
                }
            }

            $menuItem->update([
                'is_available' => $isAvailable,
            ]);
        }
    }

    private function setIfColumn($model, string $column, $value): void
    {
        if (Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value;
        }
    }
}