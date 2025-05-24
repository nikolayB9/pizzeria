<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Авторизация пользователя.
     *
     * @param LoginRequest $request Данные запроса с валидацией логина и пароля.
     *
     * @return JsonResponse Json-ответ с информацией о статусе слияния корзины пользователя в метаданных.
     * @throws ValidationException Если логин или пароль не прошли валидацию.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return ApiResponse::success(
            meta: ['cart_merge' => session()->pull('cart_merge', false)],
        );
    }

    /**
     * Выход из учетной записи пользователя.
     *
     * @param Request $request Объект HTTP-запроса.
     *
     * @return JsonResponse Json-ответ с подтверждением успешного выхода.
     */
    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return ApiResponse::success();
    }
}
