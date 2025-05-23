<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Profile\ProfileDto;
use App\DTO\Api\V1\Profile\ProfilePreviewDto;
use App\Exceptions\User\UserNotFoundException;
use App\Repositories\Profile\ProfileRepositoryInterface;
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
     * @throws UserNotFoundException Если пользователь не найден.
     */
    public function getProfileData(): ProfileDto
    {
        $userId = $this->userIdOrFail();

        return $this->profileRepository->getProfileById($userId);
    }

    /**
     * Получает DTO с минимальным набором данных для отображения информации о пользователе.
     *
     * @return ProfilePreviewDto Объект DTO с данными пользователя.
     */
    public function getPreviewData(): ProfilePreviewDto
    {
        $userId = $this->userIdOrFail();
        $user = $this->profileRepository->getPreviewModelById($userId);

        return ProfilePreviewDto::fromModel($user);
    }
}
