<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GuestOnlyWeb
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        if (Auth::guard($guard)->check()) {
            Log::debug('WEB guest route вызван авторизованным пользователем', [
                'user_id' => Auth::id(),
                'route' => $request->path(),
                'guard' => $guard,
            ]);

            return redirect()->route('main');
        }

        return $next($request);
    }
}
