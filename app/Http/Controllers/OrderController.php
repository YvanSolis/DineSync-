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
            $order = new Order();

            $this->setIfColumn($order, 'status', $request->input('status', 'pending'));
            $this->setIfColumn($order, 'customer_name', $request->input('customer_name'));
            $this->setIfColumn($order, 'table_number', $request->input('table_number'));
            $this->setIfColumn($order, 'remarks', $request->input('remarks'));
            $this->setIfColumn($order, 'total_amount', 0);

            $order->save();

            $totalAmount = 0;

            foreach ($items as $itemData) {
                $menuItem = MenuItem::with('ingredients')->findOrFail($itemData['menu_item_id']);

                if (isset($menuItem->is_available) && !$menuItem->is_available) {
                    throw ValidationException::withMessages([
                        'menu_item_id' => "{$menuItem->name} is currently unavailable.",
                    ]);
                }

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

            /*
            |--------------------------------------------------------------------------
            | Important:
            |--------------------------------------------------------------------------
            | This is the part that deducts ingredients.
            | Order created from mobile/API should pass through this controller.
            */
            $inventoryDeductionService->deductForOrder($order);

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

    public function update(Request $request, Order $order, InventoryDeductionService $inventoryDeductionService)
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

        $status = strtolower((string) $request->input('status', ''));

        if (in_array($status, ['paid', 'completed', 'served', 'done'])) {
            $inventoryDeductionService->deductForOrder($order);
        }

        return response()->json(
            $order->fresh()->load('items.menuItem'),
        );
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }

    private function setIfColumn($model, string $column, $value): void
    {
        if (Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value;
        }
    }
}