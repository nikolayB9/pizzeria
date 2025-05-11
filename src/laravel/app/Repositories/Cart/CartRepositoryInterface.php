<?php

namespace App\Repositories\Cart;

use Illuminate\Database\Eloquent\Collection;

interface CartRepositoryInterface
{
    /**
     * Возвращает товары из корзины по заданному идентификатору пользователя.
     *
     * @param string $identifierField
     * @param string $value
     * @return Collection
     */
    public function getItemsByIdentifier(string $identifierField, string $value): Collection;
}
