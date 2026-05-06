<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ingredient;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminReportController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();
        $paidStatuses = ['paid', 'completed', 'success', 'successful'];

        $totalSalesToday = 0;

        if (Schema::hasTable('payments')) {
            $totalSalesToday = Payment::where(function ($query) use ($today) {
                    $query->whereDate('created_at', $today)
                        ->orWhereNull('created_at');
                })
                ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                ->sum('amount');
        }

        if ($totalSalesToday <= 0 && Schema::hasTable('orders')) {
            $totalSalesToday = Order::whereDate('created_at', $today)->sum('total_amount');
        }

        $totalOrdersToday = 0;
        $activeOrders = 0;

        if (Schema::hasTable('orders')) {
            $totalOrdersToday = Order::whereDate('created_at', $today)->count();

            if (Schema::hasColumn('orders', 'status')) {
                $activeOrders = Order::whereIn(DB::raw('LOWER(status)'), [
                    'pending',
                    'preparing',
                    'ongoing',
                    'active',
                    'processing'
                ])->count();
            }
        }

        $ingredients = Ingredient::orderBy('name')->get();

        $lowStockItems = $ingredients->filter(function ($ingredient) {
            $stock = $ingredient->total_stock ?? $ingredient->current_stock ?? 0;
            return (float) $stock <= (float) $ingredient->threshold;
        })->values();

        $lowStockAlerts = $lowStockItems->map(function ($ingredient) {
            $stock = $ingredient->total_stock ?? $ingredient->current_stock ?? 0;

            return [
                'name' => $ingredient->name,
                'ingredient_name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'current_stock' => (float) $stock,
                'stock' => (float) $stock,
                'threshold' => (float) $ingredient->threshold,
            ];
        })->values();

        $topSellingItems = collect();

        if (Schema::hasTable('order_items')) {
            $topSellingItems = OrderItem::select(
                    'menu_item_id',
                    DB::raw('SUM(quantity) as total_sold')
                )
                ->with('menuItem')
                ->groupBy('menu_item_id')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => optional($item->menuItem)->name ?? 'Unknown Item',
                        'item_name' => optional($item->menuItem)->name ?? 'Unknown Item',
                        'quantity' => (int) $item->total_sold,
                        'total_sold' => (int) $item->total_sold,
                    ];
                });
        }

        $ingredientUsageToday = collect();

        if (Schema::hasTable('ingredient_usages')) {
            $ingredientUsageToday = DB::table('ingredient_usages')
                ->join('ingredients', 'ingredient_usages.ingredient_id', '=', 'ingredients.id')
                ->select(
                    'ingredients.name as ingredient_name',
                    'ingredients.unit as unit',
                    DB::raw('SUM(ingredient_usages.quantity_used) as quantity_used')
                )
                ->whereDate('ingredient_usages.created_at', $today)
                ->groupBy('ingredients.name', 'ingredients.unit')
                ->orderByDesc('quantity_used')
                ->get()
                ->map(function ($usage) {
                    return [
                        'name' => $usage->ingredient_name,
                        'ingredient_name' => $usage->ingredient_name,
                        'unit' => $usage->unit,
                        'quantity_used' => (float) $usage->quantity_used,
                        'used' => (float) $usage->quantity_used,
                    ];
                });
        }

        $restockSuggestions = $lowStockItems->map(function ($ingredient) {
            $stock = $ingredient->total_stock ?? $ingredient->current_stock ?? 0;

            $suggested = max(
                (float) $ingredient->threshold * 2,
                (float) $ingredient->threshold - (float) $stock
            );

            return [
                'name' => $ingredient->name,
                'ingredient_name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'current_stock' => (float) $stock,
                'threshold' => (float) $ingredient->threshold,
                'suggested_quantity' => round($suggested, 2),
                'recommended_quantity' => round($suggested, 2),
                'reason' => 'Current stock is at or below alert level.',
            ];
        })->values();

        $simpleForecast = $restockSuggestions->map(function ($item) {
            return [
                'name' => $item['name'],
                'item_name' => $item['name'],
                'menu_item' => $item['name'],
                'unit' => $item['unit'],
                'current_stock' => $item['current_stock'],
                'predicted_demand' => $item['suggested_quantity'],
                'forecast_quantity' => $item['suggested_quantity'],
                'confidence' => 'Basic',
                'recommendation' => 'Restock suggested based on alert level.',
            ];
        });

        $salesThisWeek = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString();

            $paymentSales = 0;
            $orderSales = 0;

            if (Schema::hasTable('payments')) {
                $paymentSales = Payment::whereDate('created_at', $dateString)
                    ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                    ->sum('amount');

                if ($i === 0) {
                    $nullDatePaymentSales = Payment::whereNull('created_at')
                        ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                        ->sum('amount');

                    $paymentSales += $nullDatePaymentSales;
                }
            }

            if (Schema::hasTable('orders')) {
                $orderSales = Order::whereDate('created_at', $dateString)
                    ->sum('total_amount');
            }

            $total = $paymentSales > 0 ? $paymentSales : $orderSales;

            $salesThisWeek[] = [
                'label' => $date->format('D'),
                'date' => $dateString,
                'total' => (float) $total,
                'sales' => (float) $total,
            ];
        }

        return response()->json([
            'total_sales_today' => (float) $totalSalesToday,
            'sales_today' => (float) $totalSalesToday,
            'today_sales' => (float) $totalSalesToday,

            'total_orders_today' => (int) $totalOrdersToday,
            'orders_today' => (int) $totalOrdersToday,
            'today_orders' => (int) $totalOrdersToday,

            'active_orders' => (int) $activeOrders,
            'pending_orders' => (int) $activeOrders,

            'low_stock_count' => $lowStockItems->count(),
            'low_stock_items_count' => $lowStockItems->count(),

            'sales_this_week' => $salesThisWeek,
            'weekly_sales' => $salesThisWeek,
            'sales_chart' => $salesThisWeek,

            'top_selling_items' => $topSellingItems,
            'popular_menu_items' => $topSellingItems,
            'top_items' => $topSellingItems,

            'ingredient_usage_today' => $ingredientUsageToday,
            'ingredient_usage' => $ingredientUsageToday,

            'low_stock_items' => $lowStockAlerts,
            'low_stock_alerts' => $lowStockAlerts,

            'restock_suggestions' => $restockSuggestions,
            'restock' => $restockSuggestions,

            'simple_forecast' => $simpleForecast,
            'ai_demand_forecast' => $simpleForecast,
            'forecast' => $simpleForecast,
            'demand_forecast' => $simpleForecast,
        ]);
    }

    public function reportsForecast()
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();
        $paidStatuses = ['paid', 'completed', 'success', 'successful'];

        $totalRevenue7d = 0;

        if (Schema::hasTable('payments')) {
            $totalRevenue7d = Payment::whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                ->sum('amount');
        }

        if ($totalRevenue7d <= 0 && Schema::hasTable('orders')) {
            $totalRevenue7d = Order::whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount');
        }

        $totalOrders7d = 0;

        if (Schema::hasTable('payments')) {
            $totalOrders7d = Payment::whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                ->count();
        }

        if ($totalOrders7d <= 0 && Schema::hasTable('orders')) {
            $totalOrders7d = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        }

        $avgOrderValue = $totalOrders7d > 0 ? $totalRevenue7d / $totalOrders7d : 0;
        $dailyAverageRevenue = $totalRevenue7d / 7;
        $forecastedRevenue = round($dailyAverageRevenue * 1.10, 2);

        $salesOrderTrends = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString();

            $sales = 0;
            $ordersCount = 0;

            if (Schema::hasTable('payments')) {
                $sales = Payment::whereDate('created_at', $dateString)
                    ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                    ->sum('amount');

                if ($i === 0) {
                    $sales += Payment::whereNull('created_at')
                        ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                        ->sum('amount');
                }
            }

            if ($sales <= 0 && Schema::hasTable('orders')) {
                $sales = Order::whereDate('created_at', $dateString)->sum('total_amount');
            }

            if (Schema::hasTable('orders')) {
                $ordersCount = Order::whereDate('created_at', $dateString)->count();
            }

            if ($ordersCount <= 0 && Schema::hasTable('payments') && $i === 0) {
                $ordersCount = Payment::whereNull('created_at')
                    ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                    ->count();
            }

            $salesOrderTrends[] = [
                'label' => $date->format('M d'),
                'date' => $dateString,
                'sales' => (float) $sales,
                'orders' => (int) $ordersCount,
            ];
        }

        $revenueByCategory = collect();

        if (Schema::hasTable('order_items') && Schema::hasTable('menu_items')) {
            $revenueByCategory = DB::table('order_items')
                ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
                ->select(
                    'menu_items.category',
                    DB::raw('SUM(order_items.quantity * menu_items.price) as revenue')
                )
                ->groupBy('menu_items.category')
                ->orderByDesc('revenue')
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => $item->category ?: 'Uncategorized',
                        'revenue' => (float) $item->revenue,
                    ];
                });
        }

        $inventoryUsageForecast = collect();

        if (
            Schema::hasTable('order_items') &&
            Schema::hasTable('menu_item_ingredients') &&
            Schema::hasTable('ingredients')
        ) {
            $inventoryUsageForecast = DB::table('order_items')
                ->join('menu_item_ingredients', 'order_items.menu_item_id', '=', 'menu_item_ingredients.menu_item_id')
                ->join('ingredients', 'menu_item_ingredients.ingredient_id', '=', 'ingredients.id')
                ->select(
                    'ingredients.id',
                    'ingredients.name',
                    'ingredients.unit',
                    'ingredients.threshold',
                    'ingredients.current_stock',
                    DB::raw('SUM(order_items.quantity * menu_item_ingredients.quantity_required) as used_quantity')
                )
                ->groupBy('ingredients.id', 'ingredients.name', 'ingredients.unit', 'ingredients.threshold', 'ingredients.current_stock')
                ->orderByDesc('used_quantity')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $forecast = round(((float) $item->used_quantity / 7) * 1.15 * 7, 2);

                    return [
                        'ingredient' => $item->name,
                        'unit' => $item->unit,
                        'used_quantity' => round((float) $item->used_quantity, 2),
                        'forecast_quantity' => $forecast,
                        'current_stock' => round((float) $item->current_stock, 2),
                        'threshold' => round((float) $item->threshold, 2),
                    ];
                });
        }

        $forecastDetails = collect();

        if (Schema::hasTable('order_items') && Schema::hasTable('menu_items')) {
            $forecastDetails = DB::table('order_items')
                ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
                ->select(
                    'menu_items.name',
                    'menu_items.category',
                    DB::raw('SUM(order_items.quantity) as total_sold')
                )
                ->groupBy('menu_items.name', 'menu_items.category')
                ->orderByDesc('total_sold')
                ->limit(8)
                ->get()
                ->map(function ($item) {
                    $avgDaily = (float) $item->total_sold / 7;
                    $predicted = max(1, ceil($avgDaily * 1.15));

                    return [
                        'name' => $item->name,
                        'category' => $item->category ?: 'Uncategorized',
                        'predicted_demand' => $predicted,
                        'confidence' => 'Smart Estimate',
                        'recommendation' => $predicted >= 5
                            ? 'Prepare extra stock tomorrow'
                            : 'Maintain regular prep level',
                    ];
                });
        }

        return response()->json([
            'total_revenue_7d' => round((float) $totalRevenue7d, 2),
            'avg_order_value' => round((float) $avgOrderValue, 2),
            'total_orders_7d' => (int) $totalOrders7d,
            'forecasted_revenue' => round((float) $forecastedRevenue, 2),

            'sales_order_trends' => $salesOrderTrends,
            'revenue_by_category' => $revenueByCategory,
            'inventory_usage_forecast' => $inventoryUsageForecast,
            'forecast_details' => $forecastDetails,

            'forecast_mode' => 'Smart estimate based on recent activity',
        ]);
    }
}