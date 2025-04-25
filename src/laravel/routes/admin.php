<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/admin')->group(function () {
   Route::get('/', [\App\Http\Controllers\MainController::class, 'index']);
});
