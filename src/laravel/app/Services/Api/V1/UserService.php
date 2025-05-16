<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\User\UserPreviewDto;
use App\Exceptions\User\AuthenticatedUserExpectedException;
use App\Repositories\User\UserRepositoryInterface;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    /**
     * Получает DTO с минимальным набором данных для отображения информации о пользователе.
     *
     * @return UserPreviewDto Объект DTO с данными пользователя.
     * @throws AuthenticatedUserExpectedException Если пользователь не авторизован.
     *         (Это Runtime исключение, так как авторизация должна быть проверена middleware).
     */
    public function getPreviewData(): UserPreviewDto
    {
        if (!auth()->check()) {
            throw new AuthenticatedUserExpectedException('Пользователь должен быть авторизован.');
        }

        $user = $this->userRepository->getPreviewModelById(auth()->id());

        return UserPreviewDto::fromModel($user);
    }
}
