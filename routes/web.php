<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

    Route::get('/reports', function () {
        return view('admin.reports');
    })->name('reports');

    Route::get('/users', function () {
        return view('admin.users');
    })->name('users');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';