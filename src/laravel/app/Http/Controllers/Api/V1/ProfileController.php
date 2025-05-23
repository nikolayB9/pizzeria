<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController
{
    public function __construct(private readonly ProfileService $profileService)
    {
    }

    /**
     * Возвращает данные профиля для авторизованного пользователя.
     *
     * @return JsonResponse JSON-ответ с данными профиля.
     */
    public function show(): JsonResponse
    {
        return ApiResponse::success(
            data: $this->profileService->getProfileData(),
        );
    }

    /**
     * Возвращает минимальный набор данных для отображения информации об авторизованном пользователе.
     *
     * @return JsonResponse Json-ответ с данными пользователя.
     */
    public function preview(): JsonResponse
    {
        return ApiResponse::success(
            data: $this->profileService->getPreviewData()
        );
    }
}
