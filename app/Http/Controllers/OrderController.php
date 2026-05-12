<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Services\InventoryDeductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(
            Order::with('items.menuItem')->latest()->get()
        );
    }

    public function store(Request $request, InventoryDeductionService $inventoryDeductionService)
    {
        $validated = $request->validate([
            'items' => 'required_without:order_items|array',
            'items.*.menu_item_id' => 'required_with:items|exists:menu_items,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',

            'order_items' => 'required_without:items|array',
            'order_items.*.menu_item_id' => 'required_with:order_items|exists:menu_items,id',
            'order_items.*.quantity' => 'required_with:order_items|integer|min:1',

            'status' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'table_number' => 'nullable|string|max:50',
            'remarks' => 'nullable|string',
        ]);

        $items = $request->input('items', $request->input('order_items', []));

        return DB::transaction(function () use ($request, $items, $inventoryDeductionService) {

            // 1. Check inventory first. Kapag kulang, hindi gagawa ng order.
            $this->validateInventoryBeforeOrder($items);

            // 2. Create order only if enough inventory.
            $order = new Order();

            $this->setIfColumn($order, 'status', $request->input('status', 'pending'));
            $this->setIfColumn($order, 'customer_name', $request->input('customer_name'));
            $this->setIfColumn($order, 'table_number', $request->input('table_number'));
            $this->setIfColumn($order, 'remarks', $request->input('remarks'));
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

                $orderItem->save();

                $totalAmount += $subtotal;
            }

            $this->setIfColumn($order, 'total_amount', $totalAmount);
            $order->save();

            // 3. Deduct inventory only after successful validation and order creation.
            $inventoryDeductionService->deductForOrder($order);

            // 4. Update menu availability after deduction.
            $this->refreshAllMenuAvailability();

            return response()->json(
                $order->fresh()->load('items.menuItem'),
                201
            );
        });
    }

    public function show(Order $order)
    {
        return response()->json(
            $order->load('items.menuItem')
        );
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'table_number' => 'nullable|string|max:50',
            'remarks' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            $this->setIfColumn($order, $key, $value);
        }

        $order->save();

        return response()->json(
            $order->fresh()->load('items.menuItem')
        );
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
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
                    'inventory' => "Ingredient not found.",
                ]);
            }

            // Important: total_stock comes from usable batches only.
            // It excludes expired, inactive, and zero-quantity batches.
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