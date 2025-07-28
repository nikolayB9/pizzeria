<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->alias([
            'remember.old.session.id' => \App\Http\Middleware\RememberOldSessionId::class,
            'guest.only.api' => \App\Http\Middleware\GuestOnlyApi::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guest.only.web' => \App\Http\Middleware\GuestOnlyWeb::class,
        ]);
    })
    ->withExceptions(function (Illuminate\Foundation\Configuration\Exceptions $exceptions) {
        // Обработка всех доменных исключений
        $exceptions->render(function (\App\Exceptions\Domain\DomainException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: $e->getCode(),
                errors: $e->getErrors(),
                meta: $e->getMeta(),
            );
        });

        // Фолбэк для всех прочих ошибок
        $exceptions->render(function (Throwable $e) {
            report($e); // логируем в sentry/лог
            return ApiResponse::fail(
                message: 'Internal Server Error',
                status: 500
            );
        });
    })->create();
