<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\IngredientUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(
            Order::with('items.menuItem', 'ingredientUsages.ingredient')
                ->latest()
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;

            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('YmdHis'),
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            foreach ($request->items as $item) {
                $menuItem = MenuItem::with('ingredients')->findOrFail($item['menu_item_id']);
                $quantity = $item['quantity'];

                if ($menuItem->ingredients->isEmpty()) {
                    throw ValidationException::withMessages([
                        'ingredients' => "{$menuItem->name} has no linked ingredients yet."
                    ]);
                }

                foreach ($menuItem->ingredients as $ingredient) {
                    $requiredQty = $ingredient->pivot->quantity_required * $quantity;

                    if ($ingredient->current_stock < $requiredQty) {
                        throw ValidationException::withMessages([
                            'stock' => "{$ingredient->name} is not enough for {$menuItem->name}."
                        ]);
                    }
                }

                $totalAmount += $menuItem->price * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'price' => $menuItem->price,
                ]);

                foreach ($menuItem->ingredients as $ingredient) {
                    $usedQuantity = $ingredient->pivot->quantity_required * $quantity;

                    $ingredient->decrement('current_stock', $usedQuantity);

                    IngredientUsage::create([
                        'ingredient_id' => $ingredient->id,
                        'order_id' => $order->id,
                        'quantity_used' => $usedQuantity,
                    ]);
                }
            }

            $order->update([
                'total_amount' => $totalAmount,
                'status' => 'completed',
            ]);

            return response()->json(
                $order->load('items.menuItem', 'ingredientUsages.ingredient'),
                201
            );
        });
    }

    public function show(Order $order)
    {
        return response()->json(
            $order->load('items.menuItem', 'ingredientUsages.ingredient')
        );
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return response()->json(
            $order->load('items.menuItem', 'ingredientUsages.ingredient')
        );
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'message' => 'Deleted'
        ]);
    }
}