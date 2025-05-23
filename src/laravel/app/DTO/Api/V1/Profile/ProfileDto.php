<?php

namespace App\DTO\Api\V1\Profile;

use App\Models\User;

class ProfileDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $phone_number,
        public string $email,
        public string $birth_date,
    )
    {
    }

    /**
     * Создаёт DTO из модели User.
     *
     * @param User $user Экземпляр модели User.
     *
     * @return self
     */
    public static function fromModel(User $user): self
    {
        $birthDate = $user->birth_date
            ? \Carbon\Carbon::parse($user->birth_date)->translatedFormat('j F Y г.')
            : null;

        return new self(
            id: $user->id,
            name: $user->name,
            phone_number: $user->phone_number,
            email: $user->email,
            birth_date: $birthDate,
        );
    }
}
