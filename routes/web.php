<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\AdminRestaurantSettingController;
use App\Http\Controllers\Customer\HomeController as CustomerHomeController;
use App\Http\Controllers\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Customer\ReservationController as CustomerReservationController;
use App\Http\Controllers\Customer\ChatbotController as CustomerChatbotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Side Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if (in_array($user->role, ['admin', 'staff', 'kitchen'])) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('customer.home');
});

Route::get('/home', [CustomerHomeController::class, 'index'])
    ->middleware(['auth'])
    ->name('customer.home');

Route::get('/menu', [CustomerMenuController::class, 'index'])
    ->middleware(['auth'])
    ->name('customer.menu');

Route::get('/reservations', [CustomerReservationController::class, 'index'])
    ->middleware(['auth'])
    ->name('customer.reservations.index');

Route::get('/reservations/create', [CustomerReservationController::class, 'create'])
    ->middleware(['auth'])
    ->name('customer.reservations.create');

Route::post('/reservations', [CustomerReservationController::class, 'store'])
    ->middleware(['auth'])
    ->name('customer.reservations.store');

Route::post('/chatbot/ask', [CustomerChatbotController::class, 'ask'])
    ->middleware(['auth'])
    ->name('customer.chatbot.ask');

/*
|--------------------------------------------------------------------------
| Default Dashboard Redirect
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    $user = auth()->user();

    if (in_array($user->role, ['admin', 'staff', 'kitchen'])) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('customer.home');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin Side Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/menu-items', function () {
        return view('admin.menu-items');
    })->name('menu-items');

    Route::get('/ingredients', function () {
        return view('admin.ingredients');
    })->name('ingredients');

    Route::get('/payments', function () {
        return view('admin.payments');
    })->name('payments');

    Route::get('/reservations', [AdminReservationController::class, 'index'])
        ->name('reservations');

    Route::patch('/reservations/{reservation}/status', [AdminReservationController::class, 'updateStatus'])
        ->name('reservations.update-status');

    Route::patch('/reservations/{reservation}/verify-payment', [AdminReservationController::class, 'verifyPayment'])
        ->name('reservations.verify-payment');

    Route::patch('/reservations/{reservation}/reject-payment', [AdminReservationController::class, 'rejectPayment'])
        ->name('reservations.reject-payment');

    Route::get('/settings', [AdminRestaurantSettingController::class, 'edit'])
        ->name('settings');

    Route::patch('/settings', [AdminRestaurantSettingController::class, 'update'])
        ->name('settings.update');

    Route::get('/reports', function () {
        return view('admin.reports');
    })->name('reports');

    Route::get('/users', function () {
        return view('admin.users');
    })->name('users');
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';