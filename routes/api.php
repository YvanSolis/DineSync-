<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\UserController;

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