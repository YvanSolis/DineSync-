<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/dashboard');
});

Route::get('/admin/dashboard', function () {
    return view('dashboard');
});

Route::get('/admin/ingredients', function () {
    return view('admin.ingredients');
});

Route::get('/admin/menu-items', function () {
    return view('admin.menu-items');
});

Route::get('/admin/payments', function () {
    return view('admin.payments');
});

Route::get('/admin/reports', function () {
    return view('admin.reports');
});

Route::get('/admin/users', function () {
    return view('admin.users');
});