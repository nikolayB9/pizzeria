<?php


use Illuminate\Support\Facades\Route;

require __DIR__ . '/admin.php';

Route::get('/test', function () {
    return 123;
});
