<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\MenuItem;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
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

    /*
    |--------------------------------------------------------------------------
    | AI Inventory Insight
    |--------------------------------------------------------------------------
    | This is system-generated AI-style decision support.
    | It does not modify inventory data.
    */
    Route::get('inventory-insights', [IngredientController::class, 'inventoryInsights']);
});

/*
|--------------------------------------------------------------------------
| Table Tablet Status API Routes
|--------------------------------------------------------------------------
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
| Ingredient-based inventory logic:
| - Expired batches are not counted.
| - Current stock is recomputed from usable active batches.
| - Menu item is available only if all linked ingredients have enough stock.
| - If one linked ingredient is insufficient, the menu item becomes unavailable.
| - Chef Oppa Special/custom remains available.
*/

Route::get('/menu', function () {
    $today = now()->toDateString();

    InventoryBatch::where('quantity_remaining', '>', 0)
        ->whereDate('expiry_date', '<', $today)
        ->update([
            'status' => 'expired',
        ]);

    InventoryBatch::where('quantity_remaining', '<=', 0)
        ->update([
            'status' => 'used_up',
        ]);

    Ingredient::query()->chunk(100, function ($ingredients) use ($today) {
        foreach ($ingredients as $ingredient) {
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

    MenuItem::refreshAllAvailability();

    $menuItems = MenuItem::query()
        ->with(['ingredients' => function ($query) {
            $query->orderBy('name');
        }])
        ->where('is_available', true)
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
        ->filter(function ($item) {
            if ($item->category === 'Chef Oppa Special' || $item->inventory_type === 'custom') {
                return true;
            }

            if ($item->ingredients->isEmpty()) {
                return false;
            }

            foreach ($item->ingredients as $ingredient) {
                $required = (float) ($ingredient->pivot->quantity_required ?? 0);
                $stock = (float) ($ingredient->current_stock ?? 0);

                if ($required <= 0) {
                    return false;
                }

                if ($stock < $required) {
                    return false;
                }
            }

            return $item->max_order_quantity > 0;
        })
        ->map(function ($item) {
            $isCustom = $item->category === 'Chef Oppa Special' || $item->inventory_type === 'custom';

            return [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category,
                'description' => $item->description,
                'price' => (float) $item->price,
                'image' => $item->image,
                'image_url' => $item->image_url,
                'is_available' => (bool) $item->is_available,

                'inventory_type' => $isCustom ? 'custom' : 'per_order',
                'daily_limit' => null,

                'flavor_tags' => $item->flavor_tags ?? [],
                'meal_type' => $item->meal_type,

                'sold_today' => 0,
                'remaining_today' => $isCustom ? null : $item->max_order_quantity,
                'max_order_quantity' => $isCustom ? 1 : $item->max_order_quantity,

                'stock_label' => $item->stock_label,
                'daily_inventory_label' => $item->stock_label,

                'ingredients' => $item->ingredients->map(function ($ingredient) {
                    return [
                        'id' => $ingredient->id,
                        'name' => $ingredient->name,
                        'current_stock' => (float) ($ingredient->current_stock ?? 0),
                        'unit' => $ingredient->unit,
                        'threshold' => (float) ($ingredient->threshold ?? 0),
                        'quantity_required' => (float) ($ingredient->pivot->quantity_required ?? 0),
                    ];
                })->values(),
            ];
        })
        ->values();

    return response()->json([
        'success' => true,
        'debug_source' => 'PERFECT_INGREDIENT_INVENTORY_MENU_ROUTE',
        'data' => $menuItems,
    ]);
});