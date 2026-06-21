<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use App\Services\OpenAIForecastService;

class AdminReportController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();
        $startOfWeek = now()->subDays(6)->startOfDay();
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

        /*
        |--------------------------------------------------------------------------
        | Daily Menu Capacity / Low Stock Alerts
        |--------------------------------------------------------------------------
        | New inventory logic:
        | - per_order = ala carte, counted by order quantity
        | - per_head = unlimited meals, counted by heads/persons
        | - custom = Chef Oppa Special, staff confirms
        |
        | Low stock now means low remaining menu capacity for today.
        */

        $menuItems = collect();

        if (Schema::hasTable('menu_items')) {
            $menuItems = MenuItem::query()
                ->orderBy('category')
                ->orderBy('name')
                ->get();
        }

        $menuCapacityUsage = $menuItems->map(function ($item) {
            $type = $item->inventory_type ?? 'per_order';
            $unit = $type === 'per_head'
                ? 'heads'
                : ($type === 'custom' ? 'requests' : 'orders');

            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_name' => $item->name,
                'menu_item' => $item->name,
                'category' => $item->category ?: 'Uncategorized',
                'inventory_type' => $type,
                'unit' => $unit,
                'daily_limit' => $item->daily_limit,
                'sold_today' => $item->sold_today,
                'remaining_today' => $item->remaining_today,
                'is_available' => (bool) $item->is_available,
                'stock_label' => $item->stock_label,
                'daily_inventory_label' => $item->daily_inventory_label,
            ];
        })->values();

        $lowStockItems = $menuCapacityUsage->filter(function ($item) {
            if (($item['inventory_type'] ?? 'per_order') === 'custom') {
                return false;
            }

            if ($item['daily_limit'] === null) {
                return false;
            }

            $remaining = (int) ($item['remaining_today'] ?? 0);

            return $remaining > 0 && $remaining <= 5;
        })->values();

        $soldOutItems = $menuCapacityUsage->filter(function ($item) {
            if (($item['inventory_type'] ?? 'per_order') === 'custom') {
                return false;
            }

            if ($item['daily_limit'] === null) {
                return false;
            }

            return (int) ($item['remaining_today'] ?? 0) <= 0;
        })->values();

        $lowStockAlerts = $lowStockItems->map(function ($item) {
            return [
                'name' => $item['name'],
                'item_name' => $item['name'],
                'menu_item' => $item['name'],
                'category' => $item['category'],
                'inventory_type' => $item['inventory_type'],
                'unit' => $item['unit'],
                'current_stock' => (int) $item['remaining_today'],
                'stock' => (int) $item['remaining_today'],
                'remaining' => (int) $item['remaining_today'],
                'remaining_today' => (int) $item['remaining_today'],
                'daily_limit' => $item['daily_limit'],
                'sold_today' => (int) $item['sold_today'],
                'threshold' => 5,
                'reason' => "Only {$item['remaining_today']} {$item['unit']} left today.",
            ];
        })->values();

        $preparationSuggestions = $lowStockItems
            ->merge($soldOutItems)
            ->map(function ($item) {
                $remaining = (int) ($item['remaining_today'] ?? 0);
                $unit = $item['unit'];

                if ($remaining <= 0) {
                    $reason = "Sold out for today. Consider preparing more {$unit} if service is still ongoing.";
                    $recommendation = "Prepare additional {$unit} or mark as unavailable.";
                } else {
                    $reason = "Low daily capacity. Only {$remaining} {$unit} left today.";
                    $recommendation = "Prepare additional {$unit} if demand is expected to continue.";
                }

                return [
                    'name' => $item['name'],
                    'item_name' => $item['name'],
                    'menu_item' => $item['name'],
                    'category' => $item['category'],
                    'inventory_type' => $item['inventory_type'],
                    'unit' => $unit,
                    'current_stock' => $remaining,
                    'remaining_today' => $remaining,
                    'daily_limit' => $item['daily_limit'],
                    'sold_today' => (int) $item['sold_today'],
                    'suggested_quantity' => max(5, (int) ceil(((int) $item['daily_limit']) * 0.25)),
                    'recommended_quantity' => max(5, (int) ceil(((int) $item['daily_limit']) * 0.25)),
                    'reason' => $reason,
                    'recommendation' => $recommendation,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Top Selling Items
        |--------------------------------------------------------------------------
        */

        $topSellingItems = collect();

        if (Schema::hasTable('order_items')) {
            $topSellingItems = OrderItem::select(
                    'menu_item_id',
                    DB::raw('SUM(quantity) as total_sold')
                )
                ->with('menuItem')
                ->whereDate('created_at', $today)
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

        /*
        |--------------------------------------------------------------------------
        | Menu Usage Today
        |--------------------------------------------------------------------------
        */

        $menuUsageToday = collect();

        if (Schema::hasTable('order_items') && Schema::hasTable('menu_items')) {
            $menuUsageToday = DB::table('order_items')
                ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
                ->select(
                    'menu_items.name',
                    'menu_items.category',
                    'menu_items.inventory_type',
                    DB::raw('SUM(order_items.quantity) as quantity_used')
                )
                ->whereDate('order_items.created_at', $today)
                ->groupBy('menu_items.name', 'menu_items.category', 'menu_items.inventory_type')
                ->orderByDesc('quantity_used')
                ->limit(8)
                ->get()
                ->map(function ($usage) {
                    $type = $usage->inventory_type ?? 'per_order';

                    return [
                        'name' => $usage->name,
                        'item_name' => $usage->name,
                        'menu_item' => $usage->name,
                        'category' => $usage->category ?: 'Uncategorized',
                        'inventory_type' => $type,
                        'unit' => $type === 'per_head' ? 'heads' : ($type === 'custom' ? 'requests' : 'orders'),
                        'quantity_used' => (int) $usage->quantity_used,
                        'used' => (int) $usage->quantity_used,
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Simple Demand Forecast
        |--------------------------------------------------------------------------
        */

        $simpleForecast = collect();

        if (Schema::hasTable('order_items') && Schema::hasTable('menu_items')) {
            $simpleForecast = DB::table('order_items')
                ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
                ->select(
                    'menu_items.name',
                    'menu_items.category',
                    'menu_items.inventory_type',
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('COUNT(DISTINCT DATE(order_items.created_at)) as active_days')
                )
                ->whereBetween('order_items.created_at', [$startOfWeek, now()->endOfDay()])
                ->groupBy('menu_items.name', 'menu_items.category', 'menu_items.inventory_type')
                ->orderByDesc('total_sold')
                ->limit(8)
                ->get()
                ->map(function ($item) {
                    $totalSold = (int) $item->total_sold;
                    $activeDays = max(1, (int) $item->active_days);
                    $avgDaily = $totalSold / 7;
                    $predicted = (int) max(1, ceil($avgDaily * 1.15));

                    if ($totalSold >= 20 && $activeDays >= 3) {
                        $confidence = 'High';
                    } elseif ($totalSold >= 8 && $activeDays >= 2) {
                        $confidence = 'Medium';
                    } else {
                        $confidence = 'Low';
                    }

                    $type = $item->inventory_type ?? 'per_order';
                    $unit = $type === 'per_head' ? 'heads' : ($type === 'custom' ? 'requests' : 'orders');

                    return [
                        'name' => $item->name,
                        'item_name' => $item->name,
                        'menu_item' => $item->name,
                        'category' => $item->category ?: 'Uncategorized',
                        'unit' => $unit,
                        'predicted_demand' => $predicted,
                        'forecast_quantity' => $predicted,
                        'confidence' => $confidence,
                        'recommendation' => "Prepare for approximately {$predicted} {$unit} tomorrow.",
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Sales This Week
        |--------------------------------------------------------------------------
        */

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

            /*
            * Keep old keys for compatibility with existing dashboard JS.
            * The values are now menu-capacity based, not ingredient based.
            */
            'ingredient_usage_today' => $menuUsageToday,
            'ingredient_usage' => $menuUsageToday,
            'menu_capacity_usage' => $menuCapacityUsage,
            'menu_usage_today' => $menuUsageToday,

            'low_stock_items' => $lowStockAlerts,
            'low_stock_alerts' => $lowStockAlerts,

            'restock_suggestions' => $preparationSuggestions,
            'restock' => $preparationSuggestions,
            'preparation_suggestions' => $preparationSuggestions,

            'simple_forecast' => $simpleForecast,
            'ai_demand_forecast' => $simpleForecast,
            'forecast' => $simpleForecast,
            'demand_forecast' => $simpleForecast,
        ]);
    }

    public function reportsForecast(OpenAIForecastService $openAIForecastService)
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();
        $paidStatuses = ['paid', 'completed', 'success', 'successful'];

        /*
        |--------------------------------------------------------------------------
        | 7-Day Revenue
        |--------------------------------------------------------------------------
        | Reports page is for the last 7 days.
        | Dashboard is for today only.
        */

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

        /*
        |--------------------------------------------------------------------------
        | 7-Day Orders
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Sales & Orders Trend — Last 7 Days
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Revenue by Category — Last 7 Days
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Menu Demand Forecast — Last 7 Days
        |--------------------------------------------------------------------------
        | Uses order_items from the last 7 days.
        */

        $forecastDetails = collect();

        if (Schema::hasTable('order_items') && Schema::hasTable('menu_items')) {
            $forecastDetails = DB::table('order_items')
                ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
                ->select(
                    'menu_items.id',
                    'menu_items.name',
                    'menu_items.category',
                    'menu_items.inventory_type',
                    'menu_items.daily_limit',
                    DB::raw('SUM(order_items.quantity) as total_sold_7d'),
                    DB::raw('COUNT(DISTINCT DATE(order_items.created_at)) as active_sales_days')
                )
                ->whereBetween('order_items.created_at', [$startDate, $endDate])
                ->groupBy(
                    'menu_items.id',
                    'menu_items.name',
                    'menu_items.category',
                    'menu_items.inventory_type',
                    'menu_items.daily_limit'
                )
                ->orderByDesc('total_sold_7d')
                ->limit(12)
                ->get()
                ->map(function ($item) {
                    $sold7d = (int) $item->total_sold_7d;
                    $activeDays = max(1, (int) $item->active_sales_days);

                    $avgDaily = $sold7d / 7;
                    $predictedDemand = (int) max(1, ceil($avgDaily * 1.15));

                    $inventoryType = $item->inventory_type ?? 'per_order';

                    $unit = match ($inventoryType) {
                        'per_head' => 'heads',
                        'custom' => 'requests',
                        default => 'orders',
                    };

                    if ($sold7d >= 20 && $activeDays >= 3) {
                        $confidence = 'High';
                    } elseif ($sold7d >= 8 && $activeDays >= 2) {
                        $confidence = 'Medium';
                    } else {
                        $confidence = 'Low';
                    }

                    if ($inventoryType === 'custom') {
                        $recommendation = 'Treat as custom request. Staff should confirm price and availability before accepting.';
                    } elseif ($predictedDemand >= 15) {
                        $recommendation = "Prepare for high demand. Estimated {$predictedDemand} {$unit} next operating day.";
                    } elseif ($predictedDemand >= 8) {
                        $recommendation = "Prepare a moderate amount. Estimated {$predictedDemand} {$unit} next operating day.";
                    } else {
                        $recommendation = "Normal preparation is enough. Estimated {$predictedDemand} {$unit} next operating day.";
                    }

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'menu_item' => $item->name,
                        'category' => $item->category ?: 'Uncategorized',
                        'inventory_type' => $inventoryType,
                        'daily_limit' => $item->daily_limit,
                        'unit' => $unit,

                        // Important: this makes it clear reports are 7-day based
                        'recent_sold_7d' => $sold7d,
                        'sold_7d' => $sold7d,
                        'total_sold' => $sold7d,

                        'active_sales_days' => $activeDays,
                        'predicted_demand' => $predictedDemand,
                        'forecast_quantity' => $predictedDemand,
                        'confidence' => $confidence,
                        'recommendation' => $recommendation,
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Menu Capacity Forecast Chart Data
        |--------------------------------------------------------------------------
        */

        $menuCapacityForecast = $forecastDetails->map(function ($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'menu_item' => $item['menu_item'],
                'category' => $item['category'],
                'inventory_type' => $item['inventory_type'],
                'daily_limit' => $item['daily_limit'],
                'unit' => $item['unit'],
                'recent_sold_7d' => $item['recent_sold_7d'],
                'sold_7d' => $item['sold_7d'],
                'total_sold' => $item['total_sold'],
                'predicted_demand' => $item['predicted_demand'],
                'forecast_quantity' => $item['forecast_quantity'],
                'confidence' => $item['confidence'],
                'recommendation' => $item['recommendation'],
            ];
        })->values();

        /*
        |--------------------------------------------------------------------------
        | Daily Capacity Alerts
        |--------------------------------------------------------------------------
        | This is still daily because capacity is reset daily.
        | But it is shown as a separate alert, not the main 7-day report metric.
        */

        $capacityAlerts = collect();

        if (Schema::hasTable('menu_items')) {
            $capacityAlerts = MenuItem::query()
                ->where('inventory_type', '!=', 'custom')
                ->whereNotNull('daily_limit')
                ->get()
                ->map(function ($item) {
                    $remaining = (int) ($item->remaining_today ?? 0);
                    $dailyLimit = (int) ($item->daily_limit ?? 0);

                    if ($dailyLimit <= 0) {
                        return null;
                    }

                    if ($remaining <= 0) {
                        $risk = 'High';
                    } elseif ($remaining <= 5) {
                        $risk = 'Low';
                    } else {
                        $risk = 'Normal';
                    }

                    if ($risk === 'Normal') {
                        return null;
                    }

                    $unit = $item->inventory_type === 'per_head' ? 'heads' : 'orders';

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'menu_item' => $item->name,
                        'category' => $item->category ?: 'Uncategorized',
                        'inventory_type' => $item->inventory_type,
                        'unit' => $unit,
                        'daily_limit' => $dailyLimit,
                        'sold_today' => (int) ($item->sold_today ?? 0),
                        'remaining_today' => $remaining,
                        'risk_level' => $risk,
                    ];
                })
                ->filter()
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | System Summary
        |--------------------------------------------------------------------------
        */

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
            'Review top-selling menu items from the last 7 days before preparing tomorrow’s menu capacity.',
        ];

        if ($capacityAlerts->count() > 0) {
            $recommendations[] = 'Some menu items have low daily capacity today. Review Daily Capacity Alerts before service continues.';
        }

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

            'menu_capacity_forecast' => $menuCapacityForecast,
            'menu_demand_forecast' => $menuCapacityForecast,
            'forecast_details' => $forecastDetails,

            'capacity_alerts' => $capacityAlerts,

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
