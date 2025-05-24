<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GuestOnlyApi
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->guest() && $request->expectsJson()) {
            Log::debug('API guest route вызван авторизованным пользователем', [
                'user_id' => auth()->id(),
                'route' => $request->path(),
            ]);

            return ApiResponse::fail(
                'Доступ только для неавторизованных пользователей.',
                403
            );
        }

        return $next($request);
    }
}
