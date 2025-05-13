<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Cart\AddToCartProductDto;
use App\DTO\Api\V1\Cart\CartProductListItemDto;
use App\Exceptions\Cart\CartUpdateException;
use App\Exceptions\Cart\CategoryForLimitCheckNotFoundException;
use App\Exceptions\Cart\CategoryLimitExceededException;
use App\Exceptions\Cart\CategoryLimitNotSetInConfigException;
use App\Exceptions\Cart\ProductVariantNotFoundInCartException;
use App\Exceptions\Category\CategoryNotFoundException;
use App\Exceptions\Product\ProductNotPublishedException;
use App\Models\Cart;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;

class CartService
{
    public function __construct(private readonly CartRepositoryInterface $cartRepository)
    {
    }

    /**
     * Возвращает продукты из корзины текущего пользователя или сессии.
     *
     * @return CartProductListItemDto[] Массив DTO продуктов в корзине или пустой массив, если корзина пуста.
     */
    public function getUserCartProducts(): array
    {
        $auth = $this->getAuthField();

        $cartItems = $this->cartRepository->getItemsByIdentifier($auth['field'], $auth['value']);

        if ($cartItems->isEmpty()) {
            return [];
        }

        return CartProductListItemDto::collection($cartItems);
    }

    /**
     * Добавляет вариант продукта в корзину или увеличивает его количество, если он уже есть.
     *
     * @param int $variantId ID варианта продукта, добавляемого в корзину.
     * @return void
     * @throws ProductNotPublishedException Если связанный продукт не опубликован.
     * @throws CategoryLimitExceededException Если лимит категории превышен.
     * @throws CartUpdateException Если произошла ошибка при добавлении в корзину.
     */
    public function addProduct(int $variantId): void
    {
        $cartProduct = AddToCartProductDto::fromModel(
            app(ProductRepositoryInterface::class)->getProductVariantWithCategoryById($variantId)
        );

        $auth = $this->getAuthField();
        $identifierField = $auth['field'];
        $identifierValue = $auth['value'];

        $this->throwIfCategoryLimitExceeded($cartProduct->category_id, $identifierField, $identifierValue);

        $this->cartRepository->addProductToCartByIdentifier(
            $cartProduct,
            $identifierField,
            $identifierValue,
        );
    }

    /**
     * Удаляет вариант продукта из корзины или уменьшает его количество.
     *
     * @param int $variantId ID варианта продукта.
     * @return void
     * @throws CartUpdateException Если произошла ошибка при удалении или уменьшении количества.
     * @throws ProductVariantNotFoundInCartException Если вариант продукта с таким ID не найден в корзине пользователя.
     */
    public function deleteProduct(int $variantId): void
    {
        $auth = $this->getAuthField();

        $this->cartRepository->deleteProductFromCartByIdentifier($variantId, $auth['field'], $auth['value']);
    }

    /**
     * Привязывает корзину, созданную до авторизации, к авторизованному пользователю.
     *
     * Актуально, если пользователь добавил товары в корзину до входа в аккаунт.
     */
    public function mergeCartFromSessionToUser(string $oldSessionId, int $userId): void
    {
        Cart::where('session_id', $oldSessionId)
            ->update([
                'user_id' => $userId,
                'session_id' => null,
            ]);
    }

    /**
     * Возвращает массив с типом идентификатора пользователя и его значением.
     *
     * @return array{field: 'user_id'|'session_id', value: string} Массив с типом идентификации пользователя.
     */
    public function getAuthField(): array
    {
        return auth()->check()
            ? ['field' => 'user_id', 'value' => auth()->id()]
            : ['field' => 'session_id', 'value' => session()->getId()];
    }

    /**
     * Возвращает общую стоимость товаров из корзины текущего пользователя или сессии.
     *
     * Если массив продуктов передан — используется он.
     * Если пуст и $calculateIfEmpty = true — стоимость рассчитывается из базы.
     * Иначе возвращается 0.
     *
     * @param CartProductListItemDto[] $cartProducts Массив DTO продуктов в корзине.
     * @param bool $calculateIfEmpty Делать ли запрос к базе, если массив пуст.
     * @return float Общая стоимость товаров в корзине.
     */
    public function getTotalPrice(array $cartProducts = [], bool $calculateIfEmpty = true): float
    {
        if (empty($cartProducts) && !$calculateIfEmpty) {
            return 0.0;
        }

        if (!empty($cartProducts)) {
            $totalPrice = 0.0;

            foreach ($cartProducts as $product) {
                $totalPrice += $product->price * $product->qty;
            }

            return round((float)$totalPrice, 2);
        }

        $auth = $this->getAuthField();
        $totalPrice = $this->cartRepository->getTotalPriceByIdentifier($auth['field'], $auth['value']);

        return round($totalPrice, 2);
    }

    /**
     * Проверяет лимит товаров по категории и выбрасывает исключение, если он превышен.
     *
     * @param int $categoryId ID категории, для которой проверяется лимит.
     * @param string $identifierField Поле-идентификатор пользователя (например, 'user_id' или 'session_id').
     * @param string $identifierValue Значение идентификатора.
     * @return void
     * @throws CategoryForLimitCheckNotFoundException Если категория с заданным ID не найдена.
     * @throws CategoryLimitNotSetInConfigException Если лимит для категории не задан в конфигурации.
     * @throws CategoryLimitExceededException Если лимит товаров для категории превышен.
     */
    protected function throwIfCategoryLimitExceeded(int $categoryId, string $identifierField, string $identifierValue): void
    {
        $totalQty = $this->cartRepository->getTotalQuantityByCategoryAndIdentifier(
            $categoryId,
            $identifierField,
            $identifierValue
        );

        if ($totalQty === 0) {
            return;
        }

        try {
            $categorySlug = app(CategoryRepositoryInterface::class)->getSlugById($categoryId);
        } catch (CategoryNotFoundException) {
            throw new CategoryForLimitCheckNotFoundException("Категория с ID [$categoryId], проверяемая на лимит товаров в корзине, не найдена.");
        }

        $limit = config("cart.limits_by_category_slug.$categorySlug");

        if (is_null($limit)) {
            throw new CategoryLimitNotSetInConfigException("Лимит для категории [$categorySlug] не задан.");
        }

        if ($totalQty >= $limit) {
            throw new CategoryLimitExceededException("Нельзя добавить больше [$limit] товаров категории [$categorySlug] в корзину.");
        }
    }
}
