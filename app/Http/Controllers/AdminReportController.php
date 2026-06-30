<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OpenAIForecastService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    private string $timezone = 'Asia/Manila';

    private array $paidStatuses = [
        'paid',
        'completed',
        'success',
        'successful',
        'verified',
        'settled',
    ];

    private array $activeStatuses = [
        'awaiting_payment',
        'pending',
        'preparing',
        'ready',
        'ongoing',
        'active',
        'processing',
    ];

    public function dashboard()
    {
        [$startOfToday, $endOfToday] = $this->manilaDayUtcRange(now($this->timezone)->toDateString());

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        | Dashboard should only READ data.
        | Do not update inventory_batches, ingredients, or menu availability here.
        | Those writes make the dashboard slow on every refresh.
        */

        $ordersTodayQuery = Order::query()
            ->whereBetween('created_at', [$startOfToday, $endOfToday]);

        $totalOrdersToday = (clone $ordersTodayQuery)->count();

        $activeOrders = (clone $ordersTodayQuery)
            ->whereIn(DB::raw('LOWER(TRIM(status))'), $this->activeStatuses)
            ->count();

        $totalSalesToday = Order::query()
            ->whereIn(DB::raw('LOWER(TRIM(payment_status))'), $this->paidStatuses)
            ->where(function ($query) use ($startOfToday, $endOfToday) {
                $query->whereBetween('paid_at', [$startOfToday, $endOfToday])
                    ->orWhere(function ($fallbackQuery) use ($startOfToday, $endOfToday) {
                        $fallbackQuery->whereNull('paid_at')
                            ->whereBetween('created_at', [$startOfToday, $endOfToday]);
                    });
            })
            ->sum('total_amount');

        if ((float) $totalSalesToday <= 0) {
            $totalSalesToday = Payment::query()
                ->whereBetween('created_at', [$startOfToday, $endOfToday])
                ->whereIn(DB::raw('LOWER(TRIM(status))'), $this->paidStatuses)
                ->sum('amount');
        }

        $topSellingItems = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->select(
                'order_items.menu_item_id',
                DB::raw("COALESCE(menu_items.name, 'Unknown Item') as name"),
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->whereBetween('orders.created_at', [$startOfToday, $endOfToday])
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

        $recentOrdersToday = Order::query()
            ->select([
                'id',
                'order_number',
                'status',
                'payment_status',
                'payment_method',
                'table_number',
                'total_amount',
                'created_at',
            ])
            ->with(['items.menuItem'])
            ->whereBetween('created_at', [$startOfToday, $endOfToday])
            ->whereIn(DB::raw('LOWER(TRIM(status))'), [
                'awaiting_payment',
                'pending',
                'preparing',
                'ready',
            ])
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(status)) = 'awaiting_payment' THEN 1
                    WHEN LOWER(TRIM(status)) = 'pending' THEN 2
                    WHEN LOWER(TRIM(status)) = 'preparing' THEN 3
                    WHEN LOWER(TRIM(status)) = 'ready' THEN 4
                    ELSE 5
                END
            ")
            ->latest()
            ->limit(8)
            ->get()
            ->map(function ($order) {
                $itemsSummary = $order->items
                    ->map(function ($item) {
                        $name = $item->menuItem->name
                            ?? $item->name
                            ?? 'Menu Item';

                        return ((int) ($item->quantity ?? 1)) . 'x ' . $name;
                    })
                    ->filter()
                    ->values()
                    ->join(', ');

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number ?? ('Order #' . $order->id),
                    'status' => $order->status ?? 'pending',
                    'payment_status' => $order->payment_status ?? null,
                    'payment_method' => $order->payment_method ?? null,
                    'table_number' => $order->table_number ?? '—',
                    'total_amount' => (float) ($order->total_amount ?? 0),
                    'items_summary' => $itemsSummary ?: 'No items listed',
                    'time' => optional($order->created_at)->timezone($this->timezone)->format('h:i A'),
                    'created_time' => optional($order->created_at)->timezone($this->timezone)->format('h:i A'),
                ];
            });

        $inventoryAlerts = Ingredient::query()
            ->orderBy('name')
            ->limit(200)
            ->get()
            ->filter(function ($ingredient) {
                return in_array($ingredient->stock_status, [
                    'out_of_stock',
                    'low_stock',
                    'reorder_soon',
                    'near_expiry',
                ], true);
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

        $unavailableMenuItems = MenuItem::query()
            ->select(['id', 'name', 'category', 'inventory_type', 'is_available'])
            ->where(function ($query) {
                $query->where('is_available', false)
                    ->orWhere('is_available', 0);
            })
            ->where(function ($query) {
                $query->whereNull('inventory_type')
                    ->orWhere('inventory_type', '!=', 'custom');
            })
            ->where('category', '!=', 'Chef Oppa Special')
            ->orderBy('category')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_name' => $item->name,
                    'menu_item' => $item->name,
                    'category' => $item->category ?: 'Uncategorized',
                    'stock_label' => 'Insufficient linked ingredients.',
                    'reason' => 'Insufficient linked ingredients.',
                ];
            });

        $ingredientUsageToday = DB::table('ingredient_usages')
            ->join('ingredients', 'ingredient_usages.ingredient_id', '=', 'ingredients.id')
            ->select(
                'ingredients.id',
                'ingredients.name',
                'ingredients.unit',
                DB::raw('SUM(ingredient_usages.quantity_used) as quantity_used')
            )
            ->whereBetween('ingredient_usages.created_at', [$startOfToday, $endOfToday])
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
        $endManila = now($this->timezone)->endOfDay();
        $startManila = now($this->timezone)->subDays(6)->startOfDay();
        $startDate = $startManila->copy()->timezone('UTC');
        $endDate = $endManila->copy()->timezone('UTC');

        $paidOrderDateFilter = function ($query) use ($startDate, $endDate) {
            $query->whereBetween('orders.paid_at', [$startDate, $endDate])
                ->orWhere(function ($fallbackQuery) use ($startDate, $endDate) {
                    $fallbackQuery->whereNull('orders.paid_at')
                        ->whereBetween('orders.created_at', [$startDate, $endDate]);
                });
        };

        $orderDateFilter = function ($query) use ($startDate, $endDate) {
            $query->whereBetween('orders.created_at', [$startDate, $endDate]);
        };

        $totalRevenue7d = Order::query()
            ->whereIn(DB::raw('LOWER(TRIM(payment_status))'), $this->paidStatuses)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('paid_at', [$startDate, $endDate])
                    ->orWhere(function ($fallbackQuery) use ($startDate, $endDate) {
                        $fallbackQuery->whereNull('paid_at')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->sum('total_amount');

        if ((float) $totalRevenue7d <= 0) {
            $totalRevenue7d = Payment::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn(DB::raw('LOWER(TRIM(status))'), $this->paidStatuses)
                ->sum('amount');
        }

        $totalOrders7d = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn(DB::raw('LOWER(TRIM(status))'), ['cancelled', 'canceled', 'voided'])
            ->count();

        $avgOrderValue = $totalOrders7d > 0
            ? round($totalRevenue7d / $totalOrders7d, 2)
            : 0;

        $dailyAverageRevenue = $totalRevenue7d / 7;
        $forecastedRevenue = round($dailyAverageRevenue * 1.10, 2);

        $salesOrderTrends = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now($this->timezone)->subDays($i);
            $dayStart = $date->copy()->startOfDay()->timezone('UTC');
            $dayEnd = $date->copy()->endOfDay()->timezone('UTC');

            $sales = Order::query()
                ->whereIn(DB::raw('LOWER(TRIM(payment_status))'), $this->paidStatuses)
                ->where(function ($query) use ($dayStart, $dayEnd) {
                    $query->whereBetween('paid_at', [$dayStart, $dayEnd])
                        ->orWhere(function ($fallbackQuery) use ($dayStart, $dayEnd) {
                            $fallbackQuery->whereNull('paid_at')
                                ->whereBetween('created_at', [$dayStart, $dayEnd]);
                        });
                })
                ->sum('total_amount');

            $ordersCount = Order::query()
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->whereNotIn(DB::raw('LOWER(TRIM(status))'), ['cancelled', 'canceled', 'voided'])
                ->count();

            $salesOrderTrends[] = [
                'label' => $date->format('M d'),
                'date' => $date->toDateString(),
                'sales' => (float) $sales,
                'orders' => (int) $ordersCount,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Revenue by Category
        |--------------------------------------------------------------------------
        | Important fix:
        | Use the parent orders table for the 7-day date filter. Some production
        | order_items rows do not reliably carry the same created_at timing, which
        | caused the category chart to show no data even when orders/revenue existed.
        */
        $revenueByCategory = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->select(
                DB::raw("COALESCE(menu_items.category, 'Uncategorized') as category"),
                DB::raw('SUM(order_items.quantity * COALESCE(order_items.price, menu_items.price, 0)) as revenue')
            )
            ->whereIn(DB::raw('LOWER(TRIM(orders.payment_status))'), $this->paidStatuses)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('orders.paid_at', [$startDate, $endDate])
                    ->orWhere(function ($fallbackQuery) use ($startDate, $endDate) {
                        $fallbackQuery->whereNull('orders.paid_at')
                            ->whereBetween('orders.created_at', [$startDate, $endDate]);
                    });
            })
            ->groupBy(DB::raw("COALESCE(menu_items.category, 'Uncategorized')"))
            ->orderByDesc('revenue')
            ->limit(8)
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category ?: 'Uncategorized',
                    'revenue' => (float) $item->revenue,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Menu Demand Forecast
        |--------------------------------------------------------------------------
        | Uses last 7 days of actual order_items joined to orders, then produces
        | simple rule-based AI-style recommendations. This keeps your existing
        | Reports & Forecast page and OpenAI integration untouched while ensuring
        | the forecast chart/table always has usable data when order history exists.
        */
        $forecastDetails = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->select(
                'menu_items.id',
                DB::raw("COALESCE(menu_items.name, 'Unknown Item') as name"),
                DB::raw("COALESCE(menu_items.category, 'Uncategorized') as category"),
                DB::raw('SUM(order_items.quantity) as total_sold_7d'),
                DB::raw('COUNT(DISTINCT DATE(orders.created_at)) as active_sales_days')
            )
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn(DB::raw('LOWER(TRIM(orders.status))'), ['cancelled', 'canceled', 'voided'])
            ->groupBy(
                'menu_items.id',
                DB::raw("COALESCE(menu_items.name, 'Unknown Item')"),
                DB::raw("COALESCE(menu_items.category, 'Uncategorized')")
            )
            ->orderByDesc('total_sold_7d')
            ->limit(12)
            ->get()
            ->map(function ($item) {
                $sold7d = (int) $item->total_sold_7d;
                $activeDays = max(1, (int) $item->active_sales_days);
                $avgDaily = $sold7d / 7;

                // Simple demand forecast: average daily demand + 15% buffer.
                $predictedDemand = (int) max(1, ceil($avgDaily * 1.15));

                if ($sold7d >= 20 && $activeDays >= 3) {
                    $confidence = 'High';
                    $recommendation = "High demand item. Prepare around {$predictedDemand} order(s) for the next operating day.";
                } elseif ($sold7d >= 8 && $activeDays >= 2) {
                    $confidence = 'Medium';
                    $recommendation = "Moderate demand. Prepare around {$predictedDemand} order(s) and monitor stock.";
                } else {
                    $confidence = 'Low';
                    $recommendation = "Low recent demand. Prepare only around {$predictedDemand} order(s) to avoid overstocking.";
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
                    'total_sold_7d' => $sold7d,
                    'active_sales_days' => $activeDays,
                    'predicted_demand' => $predictedDemand,
                    'forecast_quantity' => $predictedDemand,
                    'confidence' => $confidence,
                    'recommendation' => $recommendation,
                ];
            });

        $forecastConfidence = $totalOrders7d >= 20
            ? 'High'
            : ($totalOrders7d >= 8 ? 'Medium' : 'Low');

        $topForecastItem = $forecastDetails->first();
        $topCategory = $revenueByCategory->first();

        $summary = 'This report summarizes the last 7 days of sales and order activity. Total revenue for the period is ₱'
            . number_format((float) $totalRevenue7d, 2)
            . " from {$totalOrders7d} order(s). Forecasted next-day revenue is ₱"
            . number_format((float) $forecastedRevenue, 2)
            . ' based on recent 7-day performance.';

        $recommendations = [
            'Use this Reports & Forecast page for 7-day sales, revenue, and demand review.',
            'Use the Dashboard for today’s daily monitoring only.',
        ];

        if ($topForecastItem) {
            $recommendations[] = "Top demand item: {$topForecastItem['name']} with {$topForecastItem['sold_7d']} order(s) sold in the last 7 days. Recommended prep: {$topForecastItem['predicted_demand']} order(s).";
        } else {
            $recommendations[] = 'No menu demand forecast yet. Forecast will appear after order item history is available.';
        }

        if ($topCategory) {
            $recommendations[] = "Highest revenue category: {$topCategory['category']} with ₱" . number_format((float) $topCategory['revenue'], 2) . ' revenue.';
        }

        return response()->json([
            'period_label' => 'Last 7 Days',
            'period_start' => $startManila->toDateString(),
            'period_end' => $endManila->toDateString(),

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

    private function manilaDayUtcRange(string $date): array
    {
        $start = Carbon::parse($date, $this->timezone)
            ->startOfDay()
            ->timezone('UTC');

        $end = Carbon::parse($date, $this->timezone)
            ->endOfDay()
            ->timezone('UTC');

        return [$start, $end];
    }
}
