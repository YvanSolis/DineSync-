<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\AdminRestaurantSettingController;
use App\Http\Controllers\ServiceStaffController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Kitchen\KdsController;
use App\Http\Controllers\Customer\HomeController as CustomerHomeController;
use App\Http\Controllers\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Customer\ReservationController as CustomerReservationController;
use App\Http\Controllers\Customer\ChatbotController as CustomerChatbotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'staff' || $user->role === 'service_staff') {
        return redirect()->route('service.dashboard');
    }

    if ($user->role === 'kitchen' || $user->role === 'kitchen_staff') {
        return redirect()->route('kitchen.dashboard');
    }

    return redirect()->route('customer.home');
});

/*
|--------------------------------------------------------------------------
| Customer Side Routes
|--------------------------------------------------------------------------
*/

Route::get('/home', [CustomerHomeController::class, 'index'])
    ->middleware(['auth', 'role:customer'])
    ->name('customer.home');

Route::get('/menu', [CustomerMenuController::class, 'index'])
    ->middleware(['auth', 'role:customer'])
    ->name('customer.menu');

Route::get('/reservations', [CustomerReservationController::class, 'index'])
    ->middleware(['auth', 'role:customer'])
    ->name('customer.reservations.index');

Route::get('/reservations/create', [CustomerReservationController::class, 'create'])
    ->middleware(['auth', 'role:customer'])
    ->name('customer.reservations.create');

Route::post('/reservations', [CustomerReservationController::class, 'store'])
    ->middleware(['auth', 'role:customer'])
    ->name('customer.reservations.store');

Route::post('/chatbot/ask', [CustomerChatbotController::class, 'ask'])
    ->middleware(['auth', 'role:customer'])
    ->name('customer.chatbot.ask');

/*
|--------------------------------------------------------------------------
| Default Dashboard Redirect
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'staff' || $user->role === 'service_staff') {
        return redirect()->route('service.dashboard');
    }

    if ($user->role === 'kitchen' || $user->role === 'kitchen_staff') {
        return redirect()->route('kitchen.dashboard');
    }

    return redirect()->route('customer.home');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin Side Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
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

    Route::get('/users/list', [UserController::class, 'index'])
        ->name('users.list');

    Route::post('/users', [UserController::class, 'store'])
        ->name('users.store');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->name('users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy');
});

/*
|--------------------------------------------------------------------------
| Service Staff Side Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:staff'])->prefix('service')->name('service.')->group(function () {
    Route::get('/dashboard', [ServiceStaffController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/active-orders', [ServiceStaffController::class, 'activeOrders'])
        ->name('active-orders');

    Route::patch('/active-orders/{order}/status', [ServiceStaffController::class, 'updateOrderStatus'])
        ->name('active-orders.update-status');

    Route::get('/table-monitoring', [ServiceStaffController::class, 'tableMonitoring'])
        ->name('table-monitoring');

    Route::patch('/table-monitoring/{table}/walk-in', [ServiceStaffController::class, 'assignWalkIn'])
        ->name('table-monitoring.walk-in');

    Route::patch('/table-monitoring/{table}/cleaning', [ServiceStaffController::class, 'markTableCleaning'])
        ->name('table-monitoring.cleaning');

    Route::patch('/table-monitoring/{table}/available', [ServiceStaffController::class, 'markTableAvailable'])
        ->name('table-monitoring.available');

    Route::get('/reservations', [ServiceStaffController::class, 'reservations'])
        ->name('reservations');

    Route::patch('/reservations/{reservation}/status', [ServiceStaffController::class, 'updateReservationStatus'])
        ->name('reservations.update-status');

    Route::patch('/reservations/{reservation}/verify-payment', [ServiceStaffController::class, 'verifyReservationPayment'])
        ->name('reservations.verify-payment');

    Route::patch('/reservations/{reservation}/reject-payment', [ServiceStaffController::class, 'rejectReservationPayment'])
        ->name('reservations.reject-payment');

    Route::get('/customer-assistance', [ServiceStaffController::class, 'customerAssistance'])
        ->name('customer-assistance');
});

/*
|--------------------------------------------------------------------------
| Kitchen Staff / KDS Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'kitchen.staff'])->prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('/dashboard', [KdsController::class, 'index'])
        ->name('dashboard');

    Route::get('/orders/fetch', [KdsController::class, 'fetchOrders'])
        ->name('orders.fetch');

    Route::patch('/orders/{order}/status', [KdsController::class, 'updateStatus'])
        ->name('orders.status');
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