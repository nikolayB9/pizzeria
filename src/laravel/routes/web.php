<?php

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminMainController;
use App\Http\Controllers\Admin\AdminOrderController;
use Illuminate\Support\Facades\Route;

// Роуты админ - панели
Route::prefix('/admin')->group(function () {
    Route::middleware('guest.only.web')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login.create');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

        Route::get('/', [AdminMainController::class, 'index'])->name('main');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('order.index');
        Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    });

});

// Роуты сайта (Spa)
// Перехватывает все URL, кроме тех, что начинаются с api или admin
Route::get('/{any}', function () {
    return view('site.app');
})->where('any', '^(?!api|admin).*$');
