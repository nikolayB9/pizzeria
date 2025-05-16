<?php

namespace App\DTO\Api\V1\User;

use App\Models\User;

class UserPreviewDto
{
    public function __construct(
        public string $name,
    )
    {
    }

    /**
     * Создаёт DTO из модели User.
     *
     * @param User $user Экземпляр модели User.
     * @return self
     */
    public static function fromModel(User $user): self
    {
        return new self(
            name: $user->name,
        );
    }
}
