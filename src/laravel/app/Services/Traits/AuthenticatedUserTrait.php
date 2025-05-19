<?php

namespace App\Services\Traits;

use App\Exceptions\User\AuthenticatedUserExpectedException;

trait AuthenticatedUserTrait
{
    /**
     * Проверяет авторизацию текущего пользователя.
     *
     * @return int ID авторизованного пользователя.
     * @throws AuthenticatedUserExpectedException Если пользователь не авторизован.
     *        (Runtime-исключение: метод предполагает только авторизованного пользователя).
     */
    protected function userIdOrFail(): int
    {
        if (!auth()->check()) {
            throw new AuthenticatedUserExpectedException('Пользователь должен быть авторизован.');
        }

        return auth()->id();
    }
}
