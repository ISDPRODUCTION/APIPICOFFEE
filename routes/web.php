<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Api\UnifiedApiController;
Route::get('/test-product-image', function() {
    $product = \App\Models\Product::first();
    return [
        'image_path' => $product->image,
        'image_url' => $product->image_url,
    ];
})->middleware('cache.headers:public;max_age=3600');

// ── Auth ───────────────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/forgot-password', [PasswordResetController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password/check', [PasswordResetController::class, 'checkEmail'])->name('password.check-email');
Route::post('/forgot-password/reset', [PasswordResetController::class, 'resetDirect'])->name('password.reset-direct');

// ── Authenticated ──────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/api/unified-data', [UnifiedApiController::class, 'index'])->name('api.unified');

    Route::prefix('categories')->name('categories.')->middleware('role:cashier,admin,manager,supervisor')->group(function () {
        Route::get('/api', [\App\Http\Controllers\CategoryController::class, 'getPaginated'])->name('api');
        Route::post('/', [\App\Http\Controllers\CategoryController::class, 'store'])->name('store');
    });

    // ── Dashboard POS ─────────────────────────────────────────────────────────
    Route::get('/', [PosController::class, 'index'])->name('pos.index');

    // ── Orders ────────────────────────────────────────────────────────────────
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/receipt/{orderNumber}', [OrderController::class, 'receipt'])->name('receipt.show');

    // ── Menu Management ───────────────────────────────────────────────────────
    Route::prefix('menu')->name('menu.')->middleware('role:cashier,admin,manager,supervisor')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::post('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // ── Sales Report ──────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->middleware('role:admin,manager,supervisor')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/chart-data', [ReportController::class, 'chartData'])->name('chart-data');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
        Route::get('/filter', [ReportController::class, 'filter'])->name('filter');
    });

    // ── Settings ──────────────────────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {

        // Halaman utama & profile (semua role)
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::post('/password', [SettingsController::class, 'updatePassword'])->name('password.update');
        
        // Theme color
        Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');


        // Business Identity/Roll
        Route::post('/identity', [SettingsController::class, 'updateIdentity'])->name('identity.update');

        // Manajemen karyawan 
        Route::post('/employees', [SettingsController::class, 'storeEmployee'])
            ->name('employees.store')
            ->middleware('role:manager');

        Route::match(['PUT', 'POST'], '/employees/{id}', [SettingsController::class, 'updateEmployee'])
            ->name('employees.update')
            ->middleware('role:manager');

        Route::delete('/employees/{id}', [SettingsController::class, 'destroyEmployee'])
            ->name('employees.destroy')
            ->middleware('role:manager');
    });
});