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
});

/*
|--------------------------------------------------------------------------
| Public / Tablet Menu API Routes
|--------------------------------------------------------------------------
*/

Route::get('/menu', function () {
    $menuItems = MenuItem::query()
        ->where('is_available', true)
        ->select('id', 'name', 'category', 'price', 'image', 'is_available')
        ->orderBy('category')
        ->orderBy('name')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $menuItems,
    ]);
});