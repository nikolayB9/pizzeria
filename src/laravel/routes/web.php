<?php

use Illuminate\Support\Facades\Route;

// Роуты админ - панели
Route::prefix('/admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\MainController::class, 'index']);
});

// Роуты сайта (Vue)
// Перехватывает все URL, кроме тех, что начинаются с api или admin
Route::get('/{any}', function () {
    return view('site.app');
})->where('any', '^(?!api|admin).*$');
