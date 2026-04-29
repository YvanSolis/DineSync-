<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminReportController;

Route::prefix('admin')->group(function () {
    Route::apiResource('ingredients', IngredientController::class);
    Route::apiResource('menu-items', MenuItemController::class);
    Route::post('menu-items/{menuItem}/ingredients', [MenuItemController::class, 'attachIngredient']);

    Route::apiResource('orders', OrderController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::get('dashboard', [AdminReportController::class, 'dashboard']);
});