<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\User\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Обрабатывает регистрацию нового пользователя.
     *
     * Создаёт нового пользователя, авторизует его и возвращает успешный ответ с
     * информацией о результате слияния корзины в блоке meta.
     *
     * @param RegisterRequest $request Валидированный запрос с данными пользователя.
     *
     * @return JsonResponse JSON-ответ с результатом и статусом слияния корзины.
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'birth_date' => $request->birth_date,
            'role' => UserRoleEnum::User->value,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return ApiResponse::success(
            meta: ['cart_merge' => session()->pull('cart_merge')],
        );
    }
}
