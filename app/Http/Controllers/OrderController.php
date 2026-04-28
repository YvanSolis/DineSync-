<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Ingredient;
use App\Models\IngredientUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(
            Order::with('items.menuItem', 'ingredientUsages.ingredient')->get()
        );
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $totalAmount = 0;

            $order = Order::create([
                'order_number' => 'ORD-' . time(),
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            foreach ($request->items as $item) {
                $menuItem = MenuItem::with('ingredients')->findOrFail($item['menu_item_id']);
                $quantity = $item['quantity'];

                $totalAmount += $menuItem->price * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'price' => $menuItem->price,
                ]);

                foreach ($menuItem->ingredients as $ingredient) {
                    $usedQuantity = $ingredient->pivot->quantity_required * $quantity;

                    Ingredient::where('id', $ingredient->id)
                        ->decrement('current_stock', $usedQuantity);

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
                $order->load('items.menuItem', 'ingredientUsages.ingredient')
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
        $order->update($request->only(['status']));
        return response()->json($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Deleted']);
    }
}