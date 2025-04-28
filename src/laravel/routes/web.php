<?php


use Illuminate\Support\Facades\Route;

require __DIR__ . '/admin.php';

Route::get('/{any}', function () {
    return view('site.app');
})->where('any', '^(?!api).*$');
