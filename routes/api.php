<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\MenuItem;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\TableStatusController;
use App\Http\Controllers\Api\XenditWebhookController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user(); 
});

/*
|--------------------------------------------------------------------------
| Xendit Webhook Route
|--------------------------------------------------------------------------
| This must be public because Xendit will call this endpoint after payment.
| Do not put this inside auth:sanctum middleware.
*/

Route::post('/xendit/webhook', [XenditWebhookController::class, 'handle'])
    ->name('xendit.webhook');

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    Route::apiResource('ingredients', IngredientController::class);
    Route::post('ingredients/{ingredient}/stock', [IngredientController::class, 'addStock']);
    Route::put('ingredients/{ingredient}/batches/{batch}', [IngredientController::class, 'updateBatch']);
    Route::delete('ingredients/{ingredient}/batches/{batch}', [IngredientController::class, 'deleteBatch']);

    Route::apiResource('menu-items', MenuItemController::class);
    Route::post('menu-items/{menuItem}/ingredients', [MenuItemController::class, 'attachIngredient']);
    Route::delete('menu-items/{menuItem}/ingredients/{ingredientId}', [MenuItemController::class, 'detachIngredient']);

    Route::apiResource('orders', OrderController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('users', UserController::class);

    Route::get('dashboard', [AdminReportController::class, 'dashboard']);
    Route::get('reports-forecast', [AdminReportController::class, 'reportsForecast']);
});

/*
|--------------------------------------------------------------------------
| Table Tablet Status API Routes
|--------------------------------------------------------------------------
| These are used by the mobile/tablet side.
| Example accounts:
| table1@dinesync.com hanggang table8@dinesync.com
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/table/online', [TableStatusController::class, 'online']);
    Route::post('/table/offline', [TableStatusController::class, 'offline']);
    Route::post('/table/heartbeat', [TableStatusController::class, 'heartbeat']);
    Route::get('/table/status', [TableStatusController::class, 'status']);
});

/*
|--------------------------------------------------------------------------
| Public / Tablet Menu API Routes
|--------------------------------------------------------------------------
*/

Route::get('/menu', function () {
    $today = now()->toDateString();

    $menuItems = MenuItem::query()
        ->where('is_available', true)
        ->whereNotNull('inventory_type')
        ->withSum([
            'orderItems as sold_today' => function ($query) use ($today) {
            $query->whereHas('order', function ($orderQuery) use ($today) {
                $orderQuery->whereRaw(
                    "DATE(created_at AT TIME ZONE 'Asia/Manila') = ?",
                    [$today]
                );
            });
        } ], 'quantity')
        ->select(
            'id',
            'name',
            'category',
            'description',
            'price',
            'image',
            'is_available',
            'inventory_type',
            'daily_limit',
            'flavor_tags',
            'meal_type'
        )
        ->orderBy('category')
        ->orderBy('name')
        ->get()
        ->map(function ($item) {
            $soldToday = (int) ($item->sold_today ?? 0);

            if ($item->inventory_type === 'custom') {
                $remainingToday = null;
                $maxOrderQuantity = 1;
                $dailyInventoryLabel = 'Staff confirms';
            } else {
                $remainingToday = max(0, (int) $item->daily_limit - $soldToday);
                $maxOrderQuantity = $remainingToday;

                $unit = $item->inventory_type === 'per_head' ? 'heads' : 'orders';
                $dailyInventoryLabel = "{$remainingToday} {$unit} left today";
            }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category,
                'description' => $item->description,
                'price' => (float) $item->price,
                'image' => $item->image,
                'image_url' => $item->image_url,
                'is_available' => (bool) $item->is_available,
                'inventory_type' => $item->inventory_type,
                'daily_limit' => $item->daily_limit,
                'flavor_tags' => $item->flavor_tags ?? [],
                'meal_type' => $item->meal_type,
                'sold_today' => $soldToday,
                'remaining_today' => $remainingToday,
                'max_order_quantity' => $maxOrderQuantity,
                'stock_label' => $dailyInventoryLabel,
                'daily_inventory_label' => $dailyInventoryLabel,
            ];
        })
        ->filter(function ($item) {
            if ($item['inventory_type'] === 'custom') {
                return true;
            }

            if (!in_array($item['inventory_type'], ['per_order', 'per_head'], true)) {
                return false;
            }

            if ($item['daily_limit'] === null) {
                return false;
            }

            return (int) $item['remaining_today'] > 0;
        })
        ->values();

    return response()->json([
        'success' => true,
        'debug_source' => 'HOSTINGER_MENU_ROUTE_UPDATED',
        'data' => $menuItems,
    ]);
});