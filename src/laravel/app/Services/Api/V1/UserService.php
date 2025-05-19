<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\User\UserPreviewDto;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Traits\AuthenticatedUserTrait;

class UserService
{
    use AuthenticatedUserTrait;

    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    /**
     * Получает DTO с минимальным набором данных для отображения информации о пользователе.
     *
     * @return UserPreviewDto Объект DTO с данными пользователя.
     */
    public function getPreviewData(): UserPreviewDto
    {
        $userId = $this->userIdOrFail();
        $user = $this->userRepository->getPreviewModelById($userId);

        return UserPreviewDto::fromModel($user);
    }
}
