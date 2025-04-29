<?php

namespace App\Enums\User;

enum UserRoleEnum: int
{
    case User = 1;
    case Admin = 2;

    public function slug(): string
    {
        return match($this) {
            self::User => 'user',
            self::Admin => 'admin',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::User => 'Пользователь',
            self::Admin => 'Админ',
        };
    }
}
