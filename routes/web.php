<?php

use Illuminate\Support\Facades\Route;

Route::get('/admin/ingredients', function () {
    return view('admin.ingredients');
});

Route::get('/admin/menu-items', function () {
    return view('admin.menu-items');
});

Route::get('/admin/dashboard', function () {
    return view('dashboard');
});