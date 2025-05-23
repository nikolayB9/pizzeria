<?php

namespace App\DTO\Api\V1\Profile;

use App\Models\User;

class ProfilePreviewDto
{
    public function __construct(
        public int    $id,
        public string $name,
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
        return new self(
            id: $user->id,
            name: $user->name,
        );
    }
}
