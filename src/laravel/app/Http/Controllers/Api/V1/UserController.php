<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\UserService;
use Illuminate\Http\JsonResponse;

class UserController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function show()
    {

    }

    /**
     * Возвращает минимальный набор данных для отображения информации об авторизованном пользователе.
     *
     * @return JsonResponse Json - ответ с данными пользователя.
     */
    public function preview(): JsonResponse
    {
        return ApiResponse::success(
            data: $this->userService->getPreviewData()
        );
    }
}
