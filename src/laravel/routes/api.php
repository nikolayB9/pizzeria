<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/products', [\App\Http\Controllers\Api\V1\ProductController::class, 'index']);
    Route::get('/products/{productSlug}', [\App\Http\Controllers\Api\V1\ProductController::class, 'show']);

    Route::post('/cart', [\App\Http\Controllers\Api\V1\CartController::class, 'store']);
});
