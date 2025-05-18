<?php

namespace App\Repositories\Cart;

use App\DTO\Api\V1\Cart\AddToCartProductDto;
use App\Exceptions\Cart\CartMergeException;
use App\Exceptions\Cart\CartUpdateException;
use App\Exceptions\Cart\ProductVariantNotFoundInCartException;
use Illuminate\Database\Eloquent\Collection;

interface CartRepositoryInterface
{
    /**
     * Возвращает товары из корзины по заданному идентификатору пользователя.
     *
     * @param string $field
     * @param string $value
     *
     * @return Collection
     */
    public function getItemsByIdentifier(string $field, string $value): Collection;

    /**
     * Добавляет товар в корзину или увеличивает его количество, если он уже есть.
     *
     * @param AddToCartProductDto $productDto
     * @param string $field
     * @param string $value
     *
     * @return void
     * @throws CartUpdateException
     */
    public function addProductToCartByIdentifier(AddToCartProductDto $productDto, string $field, string $value): void;

    /**
     * Удаляет товар из корзины или уменьшает его количество, если количество больше 1.
     *
     * @param int $productVariantId
     * @param string $field
     * @param string $value
     *
     * @return void
     * @throws CartUpdateException
     * @throws ProductVariantNotFoundInCartException
     */
    public function deleteProductFromCartByIdentifier(int $productVariantId, string $field, string $value): void;

    /**
     * Очищает корзину пользователя по идентификатору.
     *
     * @param string $field
     * @param string $value
     *
     * @return void
     * @throws CartUpdateException
     */
    public function clearCartByIdentifier(string $field, string $value): void;

    /**
     * Возвращает общую стоимость товаров в корзине по заданному идентификатору пользователя.
     *
     * @param string $field
     * @param string $value
     *
     * @return float
     */
    public function getTotalPriceByIdentifier(string $field, string $value): float;

    /**
     * Возвращает общее количество товаров из корзины для указанной категории и идентификатора пользователя.
     *
     * @param string $categoryId
     * @param string $field
     * @param string $value
     *
     * @return int
     */
    public function getTotalQuantityByCategoryAndIdentifier(string $categoryId, string $field, string $value): int;

    /**
     * Проверяет, есть ли записи в корзине по указанному идентификатору.
     *
     * @param string $field
     * @param int|string $value
     *
     * @return bool
     */
    public function hasItemsByIdentifier(string $field, int|string $value): bool;

    /**
     * Обновляет записи в корзине по указанному идентификатору.
     *
     * @param array<string, mixed> $data
     * @param string $field
     * @param int|string $value
     *
     * @return void
     * @throws CartUpdateException
     */
    public function updateByIdentifier(array $data, string $field, int|string $value): void;

    /**
     * Переносит корзину с сессии на авторизованного пользователя.
     *
     * @param string $sessionId
     * @param int $userId
     *
     * @return void
     * @throws CartMergeException
     */
    public function transferCartFromSessionToUser(string $sessionId, int $userId): void;
}
