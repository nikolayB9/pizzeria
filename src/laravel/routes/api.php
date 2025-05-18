<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Роуты api (/api/v1/...)
Route::middleware([
    // Нужен, чтобы Sanctum не требовал токен для каждого запроса и использовал куки.
    EnsureFrontendRequestsAreStateful::class,

    // Обеспечивает доступ к сессии, кукам, CSRF и другой функциональности, необходимой для авторизации через куки
    'web',
])->prefix('v1')->group(function () {

    // Публичные маршруты
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/products/category/{slug}', [ProductController::class, 'indexByCategory']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart', [CartController::class, 'destroy']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);

    // Защищенные маршруты
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

        Route::get('/user/preview', [UserController::class, 'preview']);
        Route::get('/user', [UserController::class, 'show']);

        Route::post('/addresses', [AddressController::class, 'store']);

        Route::get('/checkout/data', [CheckoutController::class, 'getData']);
    });

    // Авторизация
    Route::middleware(['guest', 'remember.old.session.id'])->group(function () {
        Route::post('/login', [AuthenticatedSessionController::class, 'store']);
        Route::post('/register', [RegisteredUserController::class, 'store']);
    });
});
