<?php

namespace App\Repositories\Cart;

use App\DTO\Api\V1\Cart\AddToCartProductDto;
use App\Exceptions\Cart\CartUpdateException;
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

    /**
     * Возвращает общую стоимость товаров в корзине по заданному идентификатору пользователя.
     *
     * @param string $identifierField
     * @param string $value
     * @return float
     */
    public function getTotalPriceByIdentifier(string $identifierField, string $value): float;

    /**
     * Возвращает общее количество товаров из корзины для указанной категории и идентификатора пользователя.
     *
     * @param string $categoryId
     * @param string $identifierField
     * @param string $identifierValue
     * @return int
     */
    public function getTotalQuantityByCategoryAndIdentifier(string $categoryId, string $identifierField, string $identifierValue): int;

    /**
     * Добавляет товар в корзину или увеличивает его количество, если он уже есть.
     *
     * @param AddToCartProductDto $productDto
     * @param string $identifierField
     * @param string $identifierValue
     * @return void
     * @throws CartUpdateException
     */
    public function addProductToCartByIdentifier(AddToCartProductDto $productDto, string $identifierField, string $identifierValue): void;
}
