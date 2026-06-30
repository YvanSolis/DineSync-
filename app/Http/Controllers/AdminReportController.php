<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\OpenAIForecastService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminReportController extends Controller
{
    public function dashboard()
    {
        $today = now()->setTimezone('Asia/Manila')->format('Y-m-d');
        $paidStatuses = ['paid', 'completed', 'success', 'successful'];

        /*
        |--------------------------------------------------------------------------
        | Sync Ingredient-Based Inventory
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('inventory_batches')) {
            InventoryBatch::where('quantity_remaining', '>', 0)
                ->whereDate('expiry_date', '<', $today)
                ->update([
                    'status' => 'expired',
                ]);

            InventoryBatch::where('quantity_remaining', '<=', 0)
                ->update([
                    'status' => 'used_up',
                ]);
        }

        if (Schema::hasTable('ingredients')) {
            Ingredient::query()->chunk(100, function ($ingredients) use ($today) {
                foreach ($ingredients as $ingredient) {
                    if (! Schema::hasTable('inventory_batches')) {
                        continue;
                    }

                    $totalUsableStock = InventoryBatch::where('ingredient_id', $ingredient->id)
                        ->where('status', 'active')
                        ->where('quantity_remaining', '>', 0)
                        ->whereDate('expiry_date', '>=', $today)
                        ->sum('quantity_remaining');

                    $ingredient->forceFill([
                        'current_stock' => $totalUsableStock,
                    ])->saveQuietly();
                }
            });
        }

        if (Schema::hasTable('menu_items')) {
            MenuItem::refreshAllAvailability();
        }

        /*
        |--------------------------------------------------------------------------
        | Daily Orders / Sales
        |--------------------------------------------------------------------------
        */

        $totalOrdersToday = 0;
        $activeOrders = 0;
        $totalSalesToday = 0;

        if (Schema::hasTable('orders')) {
            $totalOrdersToday = Order::whereDate('created_at', $today)->count();

            if (Schema::hasColumn('orders', 'status')) {
                $activeOrders = Order::whereDate('created_at', $today)
                    ->whereIn(DB::raw('LOWER(status)'), [
                        'pending',
                        'preparing',
                        'ready',
                        'ongoing',
                        'active',
                        'processing',
                    ])
                    ->count();
            }
        }

        if (Schema::hasTable('payments')) {
            $totalSalesToday = Order::whereDate('created_at', $today)
             ->sum('total_amount');
        }

        if ($totalSalesToday <= 0 && Schema::hasTable('orders')) {
            $totalSalesToday = Order::whereDate('created_at', $today)
                ->where(function ($query) {
                    if (Schema::hasColumn('orders', 'payment_status')) {
                        $query->whereIn(DB::raw('LOWER(payment_status)'), [
                            'paid',
                            'completed',
                            'success',
                            'successful',
                        ]);
                    }

                    if (Schema::hasColumn('orders', 'status')) {
                        $query->orWhereIn(DB::raw('LOWER(status)'), [
                            'served',
                            'completed',
                        ]);
                    }
                })
                ->sum('total_amount');
        }

        /*
        |--------------------------------------------------------------------------
        | Top Selling Items Today
        |--------------------------------------------------------------------------
        */

        $topSellingItems = collect();

        if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
            $topSellingItems = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->leftJoin('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
                ->select(
                    'order_items.menu_item_id',
                    DB::raw('COALESCE(menu_items.name, "Unknown Item") as name'),
                    DB::raw('SUM(order_items.quantity) as total_sold')
                )
                ->whereDate('orders.created_at', $today)
                ->groupBy('order_items.menu_item_id', 'menu_items.name')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->menu_item_id,
                        'name' => $item->name,
                        'item_name' => $item->name,
                        'menu_item' => $item->name,
                        'quantity' => (int) $item->total_sold,
                        'total_sold' => (int) $item->total_sold,
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Recent Orders Today
        |--------------------------------------------------------------------------
        */

        $recentOrdersToday = collect();

        if (Schema::hasTable('orders')) {
            $recentOrdersToday = Order::query()
                ->whereDate('created_at', $today)
                ->latest()
                ->limit(8)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number ?? ('Order #' . $order->id),
                        'status' => $order->status ?? 'pending',
                        'payment_status' => $order->payment_status ?? null,
                        'payment_method' => $order->payment_method ?? null,
                        'table_number' => $order->table_number ?? '—',
                        'total_amount' => (float) ($order->total_amount ?? 0),
                        'time' => optional($order->created_at)->format('h:i A'),
                        'created_time' => optional($order->created_at)->format('h:i A'),
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Ingredient Inventory Alerts
        |--------------------------------------------------------------------------
        */

        $inventoryAlerts = collect();

        if (Schema::hasTable('ingredients')) {
            $inventoryAlerts = Ingredient::query()
                ->orderBy('name')
                ->get()
                ->filter(function ($ingredient) {
                    return in_array($ingredient->stock_status, [
                        'out_of_stock',
                        'low_stock',
                        'reorder_soon',
                        'near_expiry',
                    ]);
                })
                ->map(function ($ingredient) {
                    return [
                        'id' => $ingredient->id,
                        'name' => $ingredient->name,
                        'ingredient_name' => $ingredient->name,
                        'current_stock' => (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0),
                        'total_stock' => (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0),
                        'stock' => (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0),
                        'unit' => $ingredient->unit ?? 'unit',
                        'threshold' => (float) ($ingredient->threshold ?? 0),
                        'nearest_expiry_date' => $ingredient->nearest_expiry_date,
                        'stock_status' => $ingredient->stock_status,
                        'status' => $ingredient->stock_status,
                    ];
                })
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Unavailable Menu Items Based on Ingredients
        |--------------------------------------------------------------------------
        */

        $unavailableMenuItems = collect();

        if (Schema::hasTable('menu_items')) {
            $unavailableMenuItems = MenuItem::with('ingredients')
                ->where(function ($query) {
                    $query->where('is_available', false)
                        ->orWhere('is_available', 0);
                })
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->filter(function ($item) {
                    return $item->category !== 'Chef Oppa Special'
                        && $item->inventory_type !== 'custom';
                })
                ->map(function ($item) {
                    $missingIngredients = [];

                    foreach ($item->ingredients as $ingredient) {
                        $required = (float) ($ingredient->pivot->quantity_required ?? 0);
                        $stock = (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0);

                        if ($required <= 0) {
                            $missingIngredients[] = $ingredient->name . ' has invalid usage.';
                            continue;
                        }

                        if ($stock < $required) {
                            $missingIngredients[] = $ingredient->name . " is insufficient.";
                        }
                    }

                    $reason = count($missingIngredients)
                        ? implode(' ', $missingIngredients)
                        : ($item->stock_label ?? 'Insufficient linked ingredients.');

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'item_name' => $item->name,
                        'menu_item' => $item->name,
                        'category' => $item->category ?: 'Uncategorized',
                        'stock_label' => $item->stock_label,
                        'reason' => $reason,
                    ];
                })
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Ingredient Usage Today
        |--------------------------------------------------------------------------
        */

        $ingredientUsageToday = collect();

        if (Schema::hasTable('ingredient_usages') && Schema::hasTable('ingredients')) {
            $quantityColumn = Schema::hasColumn('ingredient_usages', 'quantity_used')
                ? 'ingredient_usages.quantity_used'
                : 'ingredient_usages.quantity';

            $ingredientUsageToday = DB::table('ingredient_usages')
                ->join('ingredients', 'ingredient_usages.ingredient_id', '=', 'ingredients.id')
                ->select(
                    'ingredients.id',
                    'ingredients.name',
                    'ingredients.unit',
                    DB::raw("SUM({$quantityColumn}) as quantity_used")
                )
                ->whereDate('ingredient_usages.created_at', $today)
                ->groupBy('ingredients.id', 'ingredients.name', 'ingredients.unit')
                ->orderByDesc('quantity_used')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'ingredient_name' => $item->name,
                        'unit' => $item->unit ?? 'unit',
                        'quantity_used' => (float) $item->quantity_used,
                        'used' => (float) $item->quantity_used,
                        'quantity' => (float) $item->quantity_used,
                    ];
                });
        } elseif (Schema::hasTable('inventory_transactions') && Schema::hasTable('ingredients')) {
            $ingredientUsageToday = DB::table('inventory_transactions')
                ->join('ingredients', 'inventory_transactions.ingredient_id', '=', 'ingredients.id')
                ->select(
                    'ingredients.id',
                    'ingredients.name',
                    'ingredients.unit',
                    DB::raw('SUM(inventory_transactions.quantity) as quantity_used')
                )
                ->whereDate('inventory_transactions.created_at', $today)
                ->whereIn(DB::raw('LOWER(inventory_transactions.type)'), [
                    'stock_out',
                    'out',
                    'deduct',
                    'deduction',
                ])
                ->groupBy('ingredients.id', 'ingredients.name', 'ingredients.unit')
                ->orderByDesc('quantity_used')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'ingredient_name' => $item->name,
                        'unit' => $item->unit ?? 'unit',
                        'quantity_used' => (float) $item->quantity_used,
                        'used' => (float) $item->quantity_used,
                        'quantity' => (float) $item->quantity_used,
                    ];
                });
        }

        return response()->json([
            'scope' => 'today',
            'scope_label' => 'Today / Daily Monitoring',

            'total_sales_today' => (float) $totalSalesToday,
            'sales_today' => (float) $totalSalesToday,
            'today_sales' => (float) $totalSalesToday,

            'total_orders_today' => (int) $totalOrdersToday,
            'orders_today' => (int) $totalOrdersToday,
            'today_orders' => (int) $totalOrdersToday,

            'active_orders' => (int) $activeOrders,
            'pending_orders' => (int) $activeOrders,

            'inventory_alert_count' => $inventoryAlerts->count(),
            'low_stock_count' => $inventoryAlerts->count(),

            'top_selling_items' => $topSellingItems,
            'popular_menu_items' => $topSellingItems,
            'top_items' => $topSellingItems,

            'recent_orders_today' => $recentOrdersToday,
            'recent_orders' => $recentOrdersToday,

            'inventory_alerts' => $inventoryAlerts,
            'low_stock_alerts' => $inventoryAlerts,
            'low_stock_items' => $inventoryAlerts,

            'unavailable_menu_items' => $unavailableMenuItems,
            'affected_menu_items' => $unavailableMenuItems,

            'ingredient_usage_today' => $ingredientUsageToday,
            'ingredient_usage' => $ingredientUsageToday,
        ]);
    }

    public function reportsForecast(OpenAIForecastService $openAIForecastService)
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();
        $paidStatuses = ['paid', 'completed', 'success', 'successful'];

        $totalRevenue7d = 0;

        if (Schema::hasTable('payments')) {
            $totalRevenue7d = Payment::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                ->sum('amount');
        }

        if ($totalRevenue7d <= 0 && Schema::hasTable('orders')) {
            $totalRevenue7d = Order::whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount');
        }

        $totalOrders7d = 0;

        if (Schema::hasTable('orders')) {
            $totalOrders7d = Order::whereBetween('created_at', [$startDate, $endDate])
                ->count();
        }

        if ($totalOrders7d <= 0 && Schema::hasTable('payments')) {
            $totalOrders7d = Payment::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                ->count();
        }

        $avgOrderValue = $totalOrders7d > 0
            ? round($totalRevenue7d / $totalOrders7d, 2)
            : 0;

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
            }

            if ($sales <= 0 && Schema::hasTable('orders')) {
                $sales = Order::whereDate('created_at', $dateString)
                    ->sum('total_amount');
            }

            if (Schema::hasTable('orders')) {
                $ordersCount = Order::whereDate('created_at', $dateString)
                    ->count();
            }

            if ($ordersCount <= 0 && Schema::hasTable('payments')) {
                $ordersCount = Payment::whereDate('created_at', $dateString)
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
                    DB::raw('SUM(order_items.quantity * order_items.price) as revenue')
                )
                ->whereBetween('order_items.created_at', [$startDate, $endDate])
                ->groupBy('menu_items.category')
                ->orderByDesc('revenue')
                ->limit(8)
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => $item->category ?: 'Uncategorized',
                        'revenue' => (float) $item->revenue,
                    ];
                });
        }

        $forecastDetails = collect();

        if (Schema::hasTable('order_items') && Schema::hasTable('menu_items')) {
            $forecastDetails = DB::table('order_items')
                ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
                ->select(
                    'menu_items.id',
                    'menu_items.name',
                    'menu_items.category',
                    DB::raw('SUM(order_items.quantity) as total_sold_7d'),
                    DB::raw('COUNT(DISTINCT DATE(order_items.created_at)) as active_sales_days')
                )
                ->whereBetween('order_items.created_at', [$startDate, $endDate])
                ->groupBy(
                    'menu_items.id',
                    'menu_items.name',
                    'menu_items.category'
                )
                ->orderByDesc('total_sold_7d')
                ->limit(12)
                ->get()
                ->map(function ($item) {
                    $sold7d = (int) $item->total_sold_7d;
                    $activeDays = max(1, (int) $item->active_sales_days);

                    $avgDaily = $sold7d / 7;
                    $predictedDemand = (int) max(1, ceil($avgDaily * 1.15));

                    if ($sold7d >= 20 && $activeDays >= 3) {
                        $confidence = 'High';
                    } elseif ($sold7d >= 8 && $activeDays >= 2) {
                        $confidence = 'Medium';
                    } else {
                        $confidence = 'Low';
                    }

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'menu_item' => $item->name,
                        'category' => $item->category ?: 'Uncategorized',
                        'unit' => 'orders',
                        'recent_sold_7d' => $sold7d,
                        'sold_7d' => $sold7d,
                        'total_sold' => $sold7d,
                        'active_sales_days' => $activeDays,
                        'predicted_demand' => $predictedDemand,
                        'forecast_quantity' => $predictedDemand,
                        'confidence' => $confidence,
                        'recommendation' => "Prepare for approximately {$predictedDemand} order(s) next operating day.",
                    ];
                });
        }

        $forecastConfidence = $totalOrders7d >= 20
            ? 'High'
            : ($totalOrders7d >= 8 ? 'Medium' : 'Low');

        $summary = "This report summarizes the last 7 days of sales and order activity. Total revenue for the period is ₱"
            . number_format((float) $totalRevenue7d, 2)
            . " from {$totalOrders7d} order(s). Forecasted next-day revenue is ₱"
            . number_format((float) $forecastedRevenue, 2)
            . " based on recent 7-day performance.";

        $recommendations = [
            'Use this Reports & Forecast page for 7-day sales, revenue, and demand review.',
            'Use the Dashboard for today’s daily monitoring only.',
            'Review top-selling menu items from the last 7 days before preparing tomorrow’s operations.',
        ];

        return response()->json([
            'period_label' => 'Last 7 Days',
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),

            'total_revenue_7d' => round((float) $totalRevenue7d, 2),
            'avg_order_value' => round((float) $avgOrderValue, 2),
            'total_orders_7d' => (int) $totalOrders7d,
            'forecasted_revenue' => round((float) $forecastedRevenue, 2),

            'sales_order_trends' => $salesOrderTrends,
            'revenue_by_category' => $revenueByCategory,

            'menu_demand_forecast' => $forecastDetails,
            'forecast_details' => $forecastDetails,

            'summary' => $summary,
            'ai_summary' => $summary,
            'recommendations' => $recommendations,
            'ai_recommendations' => $recommendations,
            'forecast_confidence' => $forecastConfidence,
            'ai_forecast_confidence' => $forecastConfidence,

            'forecast_mode' => '7-day system forecast based on sales, orders, and menu demand',
        ]);
    }
}