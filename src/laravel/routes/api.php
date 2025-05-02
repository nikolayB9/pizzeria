<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

// Роуты api (/api/v1/...)
Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{productSlug}', [ProductController::class, 'show']);

    Route::post('/cart', [CartController::class, 'store']);

    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('guest');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth');
});
