<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Profile\ProfileDto;
use App\DTO\Api\V1\Profile\ProfilePreviewDto;
use App\Repositories\Api\V1\Profile\ProfileRepositoryInterface;
use App\Services\Traits\AuthenticatedUserTrait;

class ProfileService
{
    use AuthenticatedUserTrait;

    public function __construct(private readonly ProfileRepositoryInterface $profileRepository)
    {
    }

    /**
     * Возвращает профиль авторизованного пользователя.
     *
     * @return ProfileDto Данные профиля пользователя.
     */
    public function getProfileData(): ProfileDto
    {
        $userId = $this->userIdOrFail();

        return $this->profileRepository->getProfileById($userId);
    }

    /**
     * Возвращает DTO с минимальным набором данных для отображения информации о пользователе.
     *
     * @return ProfilePreviewDto Данные профиля пользователя для превью.
     */
    public function getPreviewData(): ProfilePreviewDto
    {
        $userId = $this->userIdOrFail();

        return $this->profileRepository->getPreviewModelById($userId);
    }
}
