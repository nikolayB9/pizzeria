<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\YooKassa\YooKassaWebhookController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Роуты api (/api/v1/...)
Route::prefix('v1')->group(function () {

    Route::middleware([
        // Нужен, чтобы Sanctum не требовал токен для каждого запроса и использовал куки.
        EnsureFrontendRequestsAreStateful::class,

        // Обеспечивает доступ к сессии, кукам, CSRF и другой функциональности, необходимой для авторизации через куки
        'web',
    ])->group(function () {
        // Публичные маршруты
        Route::get('/categories', [CategoryController::class, 'index']);

        Route::get('/cities', [CityController::class, 'index']);
        Route::get('/cities/{id}/streets', [CityController::class, 'streets']);

        Route::get('/products/category/{slug}', [ProductController::class, 'indexByCategory']);
        Route::get('/products/{slug}', [ProductController::class, 'show']);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::delete('/cart', [CartController::class, 'destroy']);
        Route::delete('/cart/clear', [CartController::class, 'clear']);

        // Защищенные маршруты
        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

            Route::get('/profile', [ProfileController::class, 'show']);
            Route::get('/profile/preview', [ProfileController::class, 'preview']);

            Route::get('/addresses', [AddressController::class, 'index']);
            Route::get('/addresses/{id}', [AddressController::class, 'show']);
            Route::post('/addresses', [AddressController::class, 'store']);
            Route::patch('/addresses/{id}', [AddressController::class, 'update']);
            Route::patch('/addresses/{id}/default', [AddressController::class, 'setDefault']);
            Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

            Route::get('/checkout', [CheckoutController::class, 'show']);

            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{id}', [OrderController::class, 'show']);
            Route::post('/orders', [OrderController::class, 'store']);

            Route::get('/pay/{orderId}', [PaymentController::class, 'pay']);
        });

        // Авторизация
        Route::middleware(['guest.only.api', 'remember.old.session.id'])->group(function () {
            Route::post('/login', [AuthenticatedSessionController::class, 'store']);
            Route::post('/register', [RegisteredUserController::class, 'store']);
        });
    });

    Route::post('/webhook/yookassa', [YooKassaWebhookController::class, 'handle']);
});
