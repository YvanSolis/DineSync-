<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ingredient;
use App\Models\OrderItem;
use App\Models\IngredientUsage;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();

        $totalSalesToday = Order::whereDate('created_at', $today)->sum('total_amount');
        $totalOrdersToday = Order::whereDate('created_at', $today)->count();

        $lowStockItems = Ingredient::whereColumn('current_stock', '<=', 'threshold')
            ->orderBy('name')
            ->get();

        $topSellingItems = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as total_sold'))
            ->with('menuItem')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $ingredientUsageToday = IngredientUsage::select('ingredient_id', DB::raw('SUM(quantity_used) as total_used'))
            ->with('ingredient')
            ->whereDate('created_at', $today)
            ->groupBy('ingredient_id')
            ->get();

        $restockSuggestions = $lowStockItems->map(function ($ingredient) {
            $suggested = max(
                $ingredient->threshold * 2,
                $ingredient->threshold - $ingredient->current_stock
            );

            return [
                'ingredient' => $ingredient->name,
                'unit' => $ingredient->unit,
                'current_stock' => $ingredient->current_stock,
                'threshold' => $ingredient->threshold,
                'suggested_restock' => round($suggested, 2),
            ];
        });

        $simpleForecast = IngredientUsage::select('ingredient_id', DB::raw('AVG(quantity_used) as average_used'))
            ->with('ingredient')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('ingredient_id')
            ->get()
            ->map(function ($item) {
                $forecast = round($item->average_used * 1.2, 2);

                return [
                    'ingredient' => $item->ingredient->name,
                    'unit' => $item->ingredient->unit,
                    'current_stock' => $item->ingredient->current_stock,
                    'forecasted_need_tomorrow' => $forecast,
                    'status' => $item->ingredient->current_stock < $forecast ? 'Restock Needed' : 'Enough Stock',
                ];
            });

        return response()->json([
            'total_sales_today' => $totalSalesToday,
            'total_orders_today' => $totalOrdersToday,
            'low_stock_items' => $lowStockItems,
            'top_selling_items' => $topSellingItems,
            'ingredient_usage_today' => $ingredientUsageToday,
            'restock_suggestions' => $restockSuggestions,
            'simple_forecast' => $simpleForecast,
        ]);
    }
}