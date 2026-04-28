<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ingredient;
use App\Models\OrderItem;
use App\Models\IngredientUsage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class AdminReportController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();

        $totalSalesToday = Order::whereDate('created_at', $today)->sum('total_amount');
        $totalOrdersToday = Order::whereDate('created_at', $today)->count();

        $lowStockItems = Ingredient::whereColumn('current_stock', '<=', 'threshold')->get();

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

        $restockSuggestions = $ingredientUsageToday->map(function ($item) {
            return [
                'ingredient' => $item->ingredient->name,
                'unit' => $item->ingredient->unit,
                'suggested_restock' => round($item->total_used * 1.2, 2),
            ];
        });

        $tomorrowForecast = IngredientUsage::select('ingredient_id', DB::raw('AVG(quantity_used) as average_used'))
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

        // Real Prophet forecast using Python
        $prophetInput = IngredientUsage::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(quantity_used) as quantity')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->values();

        $prophetForecast = null;

        if ($prophetInput->count() >= 2) {
            $process = new Process([
                'python',
                base_path('forecast.py')
            ]);

            $process->setInput(json_encode($prophetInput));
            $process->setTimeout(120);
            $process->run();

            if ($process->isSuccessful()) {
                $prophetForecast = json_decode($process->getOutput(), true);
            } else {
                $prophetForecast = [
                    'error' => $process->getErrorOutput()
                ];
            }
        }

        return response()->json([
            'total_sales_today' => $totalSalesToday,
            'total_orders_today' => $totalOrdersToday,
            'low_stock_items' => $lowStockItems,
            'top_selling_items' => $topSellingItems,
            'ingredient_usage_today' => $ingredientUsageToday,
            'restock_suggestions' => $restockSuggestions,
            'tomorrow_forecast' => $tomorrowForecast,
            'prophet_forecast' => $prophetForecast,
        ]);
    }
}