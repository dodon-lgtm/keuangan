<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MarketingSpendController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Redirect halaman utama '/' ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes (Khusus Tamu / Belum Login)
Route::get('login', [LoginController::class, 'show'])
    ->name('login')
    ->middleware('guest');

Route::post('login', [LoginController::class, 'store'])
    ->middleware('guest');

// Fitur Terproteksi (Wajib Login)
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])
        ->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Master Data & Transaksi
    Route::resource('products', ProductController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('marketing-spends', MarketingSpendController::class);

    // Laporan
    Route::get('/reports/mer-roi', [ReportController::class, 'merRoi'])
        ->name('reports.mer-roi');
    Route::get('/reports/hpp-profit', [ReportController::class, 'hppProfit'])
        ->name('reports.hpp-profit');
});