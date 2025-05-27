<?php

use App\Enums\Error\ErrorMessageEnum;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::fail(ErrorMessageEnum::VALIDATION->value, $e->status, $e->errors());
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return ApiResponse::fail(ErrorMessageEnum::UNAUTHORIZED->value, 401);
            }
        });

        $exceptions->render(function (TypeError $e, Request $request) {
            Log::warning('Type error', ['message' => $e->getMessage()]);
            if ($request->expectsJson()) {
                return ApiResponse::fail(ErrorMessageEnum::TYPE_ERROR->value, 422);
            }
        });

        $exceptions->render(function (ErrorException $e, Request $request) {
            if ($request->expectsJson()) {
                Log::error('PHP error', ['message' => $e->getMessage()]);
                return ApiResponse::fail(ErrorMessageEnum::ERROR->value, 500);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                Log::critical('Unhandled exception', [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return ApiResponse::fail(
                    ErrorMessageEnum::ERROR->value,
                    500
                );
            }
        });
    })->create();
