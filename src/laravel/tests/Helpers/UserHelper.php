<?php

namespace Tests\Helpers;

use App\Models\User;
use Database\Seeders\UserRoleSeeder;
use Illuminate\Support\Collection;

class UserHelper
{
    /**
     * Создает одного или нескольких пользователей.
     *
     * @param int $count Количество создаваемых пользователей.
     *
     * @return User|Collection Модель или коллекция созданных пользователей.
     */
    public static function createUser(int $count = 1, array $data = []): User|Collection
    {
        (new UserRoleSeeder())->run();

        $users = User::factory($count)->create($data);

        return $count === 1 ? $users->first() : $users;
    }

    /**
     * Получает пользователя из базы данных по переданному массиву для поиска.
     *
     * @param array<string, mixed> $searchData Массив с полями и значениями для поиска пользователя.
     * @param array<string> $with Массив с нужными отношениями модели User.
     *
     * @return User|null Модель пользователя или null, если не найден
     */
    public static function getUserByData(array $searchData, array $with = []): ?User
    {
        return User::where($searchData)
            ->with($with)
            ->first();
    }
}
