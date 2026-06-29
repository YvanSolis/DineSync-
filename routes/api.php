<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Models\MenuItem;
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
| Public / Tablet Menu API Route
|--------------------------------------------------------------------------
|
| Mobile/tablet source of truth:
| - response.data.data is always the menu item array.
| - no nested data object.
| - no old daily_limit/sold_today capacity logic.
| - availability is computed from linked ingredients and usable stock batches.
| - expired and used-up batches are ignored.
| - Chef Oppa Special/custom stays available.
|
*/

Route::get('/menu', function () {
    $today = now()->toDateString();

    /*
    |--------------------------------------------------------------------------
    | Clean expired / used-up batches
    |--------------------------------------------------------------------------
    */

    InventoryBatch::where('quantity_remaining', '>', 0)
        ->whereDate('expiry_date', '<', $today)
        ->where('status', '!=', 'expired')
        ->update([
            'status' => 'expired',
            'updated_at' => now(),
        ]);

    InventoryBatch::where('quantity_remaining', '<=', 0)
        ->where('status', '!=', 'used_up')
        ->update([
            'status' => 'used_up',
            'updated_at' => now(),
        ]);

    /*
    |--------------------------------------------------------------------------
    | Precompute stock maps once
    |--------------------------------------------------------------------------
    */

    $usableStockByIngredient = InventoryBatch::query()
        ->selectRaw('ingredient_id, COALESCE(SUM(quantity_remaining), 0) as usable_stock')
        ->where('status', 'active')
        ->where('quantity_remaining', '>', 0)
        ->whereDate('expiry_date', '>=', $today)
        ->groupBy('ingredient_id')
        ->pluck('usable_stock', 'ingredient_id')
        ->map(fn ($value) => (float) $value);

    $expiredBatchCountByIngredient = InventoryBatch::query()
        ->selectRaw('ingredient_id, COUNT(*) as expired_count')
        ->where('status', 'expired')
        ->groupBy('ingredient_id')
        ->pluck('expired_count', 'ingredient_id')
        ->map(fn ($value) => (int) $value);

    $usedUpBatchCountByIngredient = InventoryBatch::query()
        ->selectRaw('ingredient_id, COUNT(*) as used_up_count')
        ->where('status', 'used_up')
        ->groupBy('ingredient_id')
        ->pluck('used_up_count', 'ingredient_id')
        ->map(fn ($value) => (int) $value);

    /*
    |--------------------------------------------------------------------------
    | Load all menu-visible items
    |--------------------------------------------------------------------------
    | No where('is_available', true) here.
    | Mobile needs complete source of truth with computed fields.
    */

    $menuItems = MenuItem::query()
        ->select([
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
            'meal_type',
            'created_at',
            'updated_at',
        ])
        ->with([
            'ingredients' => function ($query) {
                $query->select([
                    'ingredients.id',
                    'ingredients.name',
                    'ingredients.current_stock',
                    'ingredients.unit',
                    'ingredients.threshold',
                ])->orderBy('ingredients.name');
            }
        ])
        ->orderBy('category')
        ->orderBy('name')
        ->get();

    $menuData = $menuItems->map(function ($item) use (
        $usableStockByIngredient,
        $expiredBatchCountByIngredient,
        $usedUpBatchCountByIngredient
    ) {
        $isCustom = $item->category === 'Chef Oppa Special' || $item->inventory_type === 'custom';
        $ingredients = $item->ingredients ?? collect();

        $maxOrderQuantity = 0;
        $isAvailable = false;
        $stockLabel = null;
        $unavailableReason = null;
        $ingredientDebug = [];

        /*
        |--------------------------------------------------------------------------
        | Chef Oppa Special / Custom
        |--------------------------------------------------------------------------
        */

        if ($isCustom) {
            $maxOrderQuantity = 1;
            $isAvailable = true;
            $stockLabel = 'Custom request available';
            $unavailableReason = null;
        }

        /*
        |--------------------------------------------------------------------------
        | No linked ingredients
        |--------------------------------------------------------------------------
        | Business-safe rule:
        | - No linked ingredients should not break the mobile response.
        | - It can still show as available if admin enabled it.
        */

        if (!$isCustom && $ingredients->isEmpty()) {
            $maxOrderQuantity = (bool) $item->is_available ? 99 : 0;
            $isAvailable = (bool) $item->is_available;
            $stockLabel = $isAvailable
                ? 'Available'
                : 'Unavailable based on ingredient stock.';
            $unavailableReason = $isAvailable
                ? null
                : 'No linked ingredients found or item is disabled.';
        }

        /*
        |--------------------------------------------------------------------------
        | Ingredient-based computation
        |--------------------------------------------------------------------------
        */

        if (!$isCustom && $ingredients->isNotEmpty()) {
            $servingCounts = [];
            $insufficientIngredients = [];
            $invalidIngredients = [];

            foreach ($ingredients as $ingredient) {
                $requiredQuantity = (float) ($ingredient->pivot->quantity_required ?? 0);
                $usableStock = (float) ($usableStockByIngredient[$ingredient->id] ?? 0);
                $expiredIgnored = (int) ($expiredBatchCountByIngredient[$ingredient->id] ?? 0);
                $usedUpIgnored = (int) ($usedUpBatchCountByIngredient[$ingredient->id] ?? 0);

                $computedServings = $requiredQuantity > 0
                    ? (int) floor($usableStock / $requiredQuantity)
                    : 0;

                if ($requiredQuantity <= 0) {
                    $invalidIngredients[] = $ingredient->name;
                } elseif ($computedServings <= 0) {
                    $insufficientIngredients[] = $ingredient->name;
                }

                $servingCounts[] = $computedServings;

                $ingredientDebug[] = [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'required_quantity' => $requiredQuantity,
                    'usable_stock' => $usableStock,
                    'unit' => $ingredient->unit,
                    'computed_servings' => $computedServings,
                    'expired_batches_ignored' => $expiredIgnored,
                    'used_up_batches_ignored' => $usedUpIgnored,
                ];
            }

            if (count($invalidIngredients)) {
                $maxOrderQuantity = 0;
                $isAvailable = false;
                $stockLabel = 'Unavailable based on ingredient stock.';
                $unavailableReason = 'Invalid ingredient quantity requirement for: ' . implode(', ', $invalidIngredients) . '.';
            } elseif (count($insufficientIngredients)) {
                $maxOrderQuantity = 0;
                $isAvailable = false;
                $stockLabel = 'Unavailable based on ingredient stock.';
                $unavailableReason = 'Insufficient stock for: ' . implode(', ', $insufficientIngredients) . '.';
            } else {
                $maxOrderQuantity = count($servingCounts)
                    ? max(0, min($servingCounts))
                    : 0;

                if ($maxOrderQuantity > 0) {
                    $isAvailable = true;
                    $stockLabel = 'Only ' . $maxOrderQuantity . ' order(s) available based on ingredient stock.';
                    $unavailableReason = null;
                } else {
                    $isAvailable = false;
                    $stockLabel = 'Unavailable based on ingredient stock.';
                    $unavailableReason = 'Maximum order quantity is 0 based on ingredient stock.';
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update stale DB availability quietly
        |--------------------------------------------------------------------------
        | This keeps web/admin status aligned with computed ingredient availability.
        */

        if ((bool) $item->is_available !== (bool) $isAvailable) {
            $item->forceFill([
                'is_available' => $isAvailable,
            ])->saveQuietly();
        }

        /*
        |--------------------------------------------------------------------------
        | Required debug log
        |--------------------------------------------------------------------------
        */

        Log::info('MENU ITEM INVENTORY DEBUG', [
            'id' => $item->id,
            'name' => $item->name,
            'ingredients_count' => $ingredients->count(),
            'ingredients' => $ingredientDebug,
            'max_order_quantity' => $maxOrderQuantity,
            'is_available' => $isAvailable,
            'stock_label' => $stockLabel,
            'unavailable_reason' => $unavailableReason,
        ]);

        return [
            'id' => $item->id,
            'name' => $item->name,
            'category' => $item->category,
            'price' => (float) $item->price,
            'description' => $item->description,
            'image_url' => $item->image_url,

            'is_available' => (bool) $isAvailable,
            'max_order_quantity' => (int) $maxOrderQuantity,
            'remaining_today' => (int) $maxOrderQuantity,

            'stock_label' => $stockLabel,
            'daily_inventory_label' => null,
            'unavailable_reason' => $unavailableReason,

            'ingredients' => $ingredients->map(function ($ingredient) use ($usableStockByIngredient) {
                $requiredQuantity = (float) ($ingredient->pivot->quantity_required ?? 0);
                $usableStock = (float) ($usableStockByIngredient[$ingredient->id] ?? 0);

                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'current_stock' => $usableStock,
                    'total_stock' => $usableStock,
                    'unit' => $ingredient->unit,
                    'threshold' => (float) ($ingredient->threshold ?? 0),
                    'quantity_required' => $requiredQuantity,
                    'pivot' => [
                        'quantity_required' => $requiredQuantity,
                    ],
                ];
            })->values(),

            'inventory_type' => $isCustom ? 'custom' : 'ingredient',
            'flavor_tags' => $item->flavor_tags ?? [],
            'meal_type' => $item->meal_type ?? 'main',
        ];
    })->values();

    return response()->json([
        'success' => true,
        'debug_source' => 'WEB_MENU_INGREDIENT_AVAILABILITY_FIXED_2026',
        'data' => $menuData,
    ]);
});