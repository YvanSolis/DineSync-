<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
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
|
| Mobile/tablet source of truth:
| - Returns menu-visible items.
| - Does not hide items just because ingredients are missing.
| - Computes max_order_quantity from usable, non-expired stock batches.
| - Expired batches are not counted.
| - Custom/Chef Oppa Special remains available when manually enabled.
|
*/

Route::get('/menu', function () {
    $today = now()->toDateString();

    /*
    |--------------------------------------------------------------------------
    | Lightweight batch status cleanup
    |--------------------------------------------------------------------------
    | Do not refresh all menu availability here. This endpoint should stay fast.
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
    | Compute usable stock once
    |--------------------------------------------------------------------------
    | This avoids repeated stock queries per ingredient/menu item.
    */
    $usableStockByIngredient = InventoryBatch::query()
        ->selectRaw('ingredient_id, COALESCE(SUM(quantity_remaining), 0) as usable_stock')
        ->where('status', 'active')
        ->where('quantity_remaining', '>', 0)
        ->whereDate('expiry_date', '>=', $today)
        ->groupBy('ingredient_id')
        ->pluck('usable_stock', 'ingredient_id')
        ->map(fn ($value) => (float) $value);

    /*
    |--------------------------------------------------------------------------
    | Load menu items with linked ingredients
    |--------------------------------------------------------------------------
    | Do not filter by is_available here. Mobile needs the full source of truth.
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
                ])->orderBy('name');
            }
        ])
        ->orderBy('category')
        ->orderBy('name')
        ->get();

    $data = $menuItems->map(function ($item) use ($usableStockByIngredient) {
        $isCustom = $item->category === 'Chef Oppa Special' || $item->inventory_type === 'custom';
        $manualEnabled = (bool) $item->is_available;
        $ingredients = $item->ingredients ?? collect();

        $maxOrderQuantity = 0;
        $computedAvailable = false;
        $stockLabel = '';
        $unavailableReason = null;
        $ingredientDebug = [];

        /*
        |--------------------------------------------------------------------------
        | Custom item rule
        |--------------------------------------------------------------------------
        */
        if ($isCustom) {
            $maxOrderQuantity = $manualEnabled ? 1 : 0;
            $computedAvailable = $manualEnabled;
            $stockLabel = $manualEnabled
                ? 'Staff confirms availability'
                : 'Manually disabled';
        }

        /*
        |--------------------------------------------------------------------------
        | No linked ingredients rule
        |--------------------------------------------------------------------------
        | Do not automatically make every no-ingredient item unavailable.
        | If admin enabled it, mobile can show it as available.
        */
        if (!$isCustom && $ingredients->isEmpty()) {
            $maxOrderQuantity = $manualEnabled ? 99 : 0;
            $computedAvailable = $manualEnabled;
            $stockLabel = $manualEnabled
                ? 'Available. No ingredients linked.'
                : 'Manually disabled';
            $unavailableReason = $manualEnabled ? null : 'Manually disabled';
        }

        /*
        |--------------------------------------------------------------------------
        | Ingredient-based rule
        |--------------------------------------------------------------------------
        */
        if (!$isCustom && $ingredients->isNotEmpty()) {
            $possibleQuantities = [];
            $invalidIngredient = null;
            $insufficientIngredient = null;

            foreach ($ingredients as $ingredient) {
                $required = (float) ($ingredient->pivot->quantity_required ?? 0);
                $usableStock = (float) ($usableStockByIngredient[$ingredient->id] ?? 0);

                $ingredientDebug[] = [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'required_per_order' => $required,
                    'usable_stock' => $usableStock,
                    'unit' => $ingredient->unit,
                    'possible_orders' => $required > 0 ? (int) floor($usableStock / $required) : 0,
                ];

                if ($required <= 0) {
                    $invalidIngredient = $ingredient->name;
                    break;
                }

                if ($usableStock < $required) {
                    $insufficientIngredient = $ingredient->name;
                }

                $possibleQuantities[] = (int) floor($usableStock / $required);
            }

            if ($invalidIngredient) {
                $maxOrderQuantity = 0;
                $computedAvailable = false;
                $stockLabel = 'Invalid ingredient usage';
                $unavailableReason = "Invalid ingredient usage for {$invalidIngredient}.";
            } elseif ($insufficientIngredient) {
                $maxOrderQuantity = 0;
                $computedAvailable = false;
                $stockLabel = 'Insufficient ingredients';
                $unavailableReason = "Insufficient stock of {$insufficientIngredient}.";
            } else {
                $maxOrderQuantity = count($possibleQuantities)
                    ? max(0, min($possibleQuantities))
                    : 0;

                $computedAvailable = $manualEnabled && $maxOrderQuantity > 0;

                if (!$manualEnabled) {
                    $stockLabel = 'Manually disabled';
                    $unavailableReason = 'Manually disabled in admin.';
                } elseif ($maxOrderQuantity <= 0) {
                    $stockLabel = 'Insufficient ingredients';
                    $unavailableReason = 'Maximum order quantity is 0 based on ingredient stock.';
                } else {
                    $stockLabel = $maxOrderQuantity . ' order(s) available based on ingredients';
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Keep DB status aligned for linked/custom items only
        |--------------------------------------------------------------------------
        | This avoids stale /api/menu values without doing heavy refreshAll.
        */
        if ($item->is_available !== $computedAvailable) {
            $item->forceFill([
                'is_available' => $computedAvailable,
            ])->saveQuietly();
        }

        Log::info('MOBILE_MENU_ITEM_AVAILABILITY_DEBUG', [
            'id' => $item->id,
            'name' => $item->name,
            'category' => $item->category,
            'manual_enabled_before_compute' => $manualEnabled,
            'final_is_available' => $computedAvailable,
            'max_order_quantity' => $maxOrderQuantity,
            'linked_ingredients_count' => $ingredients->count(),
            'ingredients' => $ingredientDebug,
            'stock_label' => $stockLabel,
            'unavailable_reason' => $unavailableReason,
        ]);

        return [
            'id' => $item->id,
            'name' => $item->name,
            'category' => $item->category,
            'description' => $item->description,
            'price' => (float) $item->price,
            'image' => $item->image,
            'image_url' => $item->image_url,
            'is_available' => $computedAvailable,

            'inventory_type' => $isCustom ? 'custom' : ($item->inventory_type ?: 'per_order'),
            'daily_limit' => null,

            'flavor_tags' => $item->flavor_tags ?? [],
            'meal_type' => $item->meal_type,

            'sold_today' => 0,
            'remaining_today' => $isCustom ? null : $maxOrderQuantity,
            'max_order_quantity' => $isCustom ? ($computedAvailable ? 1 : 0) : $maxOrderQuantity,

            'stock_label' => $stockLabel,
            'daily_inventory_label' => $stockLabel,
            'unavailable_reason' => $unavailableReason,

            'ingredients' => $ingredients->map(function ($ingredient) use ($usableStockByIngredient) {
                $required = (float) ($ingredient->pivot->quantity_required ?? 0);
                $usableStock = (float) ($usableStockByIngredient[$ingredient->id] ?? 0);

                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'current_stock' => $usableStock,
                    'total_stock' => $usableStock,
                    'unit' => $ingredient->unit,
                    'threshold' => (float) ($ingredient->threshold ?? 0),
                    'quantity_required' => $required,
                    'pivot' => [
                        'quantity_required' => $required,
                    ],
                ];
            })->values(),
        ];
    })->values();

    return response()->json([
        'success' => true,
        'debug_source' => 'INGREDIENT_BASED_PUBLIC_MENU_API_V2',
        'data' => $data,
    ]);
});