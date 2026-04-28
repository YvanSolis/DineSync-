<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\IngredientUsage;
use Illuminate\Database\Seeder;

class DemoOrderHistorySeeder extends Seeder
{
    public function run(): void
    {
        $menuItems = MenuItem::with('ingredients')->get();

        if ($menuItems->isEmpty()) {
            return;
        }

        for ($day = 7; $day >= 1; $day--) {
            $date = now()->subDays($day);

            $ordersToday = rand(3, 8);

            for ($i = 1; $i <= $ordersToday; $i++) {
                $menuItem = $menuItems->random();
                $quantity = rand(1, 4);

                $order = Order::create([
                    'order_number' => 'DEMO-' . $day . '-' . $i . '-' . time(),
                    'status' => 'completed',
                    'total_amount' => $menuItem->price * $quantity,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'price' => $menuItem->price,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                foreach ($menuItem->ingredients as $ingredient) {
                    $usedQuantity = $ingredient->pivot->quantity_required * $quantity;

                    IngredientUsage::create([
                        'ingredient_id' => $ingredient->id,
                        'order_id' => $order->id,
                        'quantity_used' => $usedQuantity,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }
        }
    }
}