<?php

namespace App\Repositories\User;

use App\Exceptions\User\MissingUserException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Получает модель пользователя по ID с минимальным набором полей для формирования превью.
     *
     * @param int $userId ID пользователя.
     *
     * @return User Модель пользователя с ограниченным набором данных (для предпросмотра).
     * @throws MissingUserException Если пользователь не найден (Runtime: ожидается существующий $userId).
     */
    public function getPreviewModelById(int $userId): User
    {
        try {
            return User::where('id', $userId)
                ->select('id', 'name')
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new MissingUserException("Пользователь с ID [$userId] не найден.");
        }
    }
}
