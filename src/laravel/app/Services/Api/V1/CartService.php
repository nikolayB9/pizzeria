<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Cart\AddToCartProductDto;
use App\DTO\Api\V1\Cart\CartDetailedItemDto;
use App\DTO\Api\V1\Cart\CartRawItemDto;
use App\Exceptions\Cart\CartMergeException;
use App\Exceptions\Cart\CartUpdateException;
use App\Exceptions\Cart\CategoryForLimitCheckNotFoundException;
use App\Exceptions\Cart\CategoryLimitExceededException;
use App\Exceptions\Cart\CategoryLimitNotSetInConfigException;
use App\Exceptions\Cart\InvalidCartProductDataException;
use App\Exceptions\Cart\ProductVariantNotFoundInCartException;
use App\Exceptions\Category\CategoryNotFoundException;
use App\Exceptions\Product\ProductNotPublishedException;
use App\Repositories\Api\V1\Cart\CartRepositoryInterface;
use App\Repositories\Api\V1\Category\CategoryRepositoryInterface;
use App\Repositories\Api\V1\Product\ProductRepositoryInterface;
use Illuminate\Support\Facades\Log;

class CartService
{
    public function __construct(private readonly CartRepositoryInterface $cartRepository)
    {
    }

    /**
     * Возвращает продукты из корзины текущего пользователя или сессии.
     *
     * @return CartDetailedItemDto[] Массив DTO с данными товаров в корзине, или пустой массив, если корзина пуста.
     */
    public function getUserCartProducts(): array
    {
        $auth = $this->getAuthField();

        return $this->cartRepository->getDetailedCartItemsByIdentifier($auth['field'], $auth['value']);
    }

    /**
     * Добавляет вариант продукта в корзину или увеличивает его количество, если он уже есть.
     *
     * @param int $variantId ID варианта продукта, добавляемого в корзину.
     *
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
     *
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
     * Очищает корзину пользователя.
     *
     * @return void
     * @throws CartUpdateException Если произошла ошибка при попытке очистить корзину.
     */
    public function clearUserCart(): void
    {
        $auth = $this->getAuthField();

        $this->cartRepository->clearCartByIdentifier($auth['field'], $auth['value']);
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
     * @param CartDetailedItemDto[]|CartRawItemDto[] $cartProducts Массив DTO с товарами в корзине.
     * @param bool $calculateIfEmpty Делать ли запрос к базе, если массив пуст.
     *
     * @return float Общая стоимость товаров в корзине.
     * @throws InvalidCartProductDataException Если одно из значений price или qty некорректно.
     */
    public function getTotalPrice(array $cartProducts = [], bool $calculateIfEmpty = true): float
    {
        if (empty($cartProducts) && !$calculateIfEmpty) {
            Log::info('Расчет общей стоимости пропущен, корзина пуста и флаг расчета выключен', [
                'method' => __METHOD__,
            ]);
            return 0.0;
        }

        if (!empty($cartProducts)) {
            $totalPrice = 0.0;

            foreach ($cartProducts as $product) {
                if (!isset($product->price, $product->qty)
                    || !is_numeric($product->price) || !is_numeric($product->qty)
                    || $product->price < 0 || $product->qty < 1
                ) {
                    Log::error('Ошибка в цене или количестве товара при расчете общей стоимости корзины', [
                        'cart_product' => $product,
                        'method' => __METHOD__,
                    ]);

                    throw new InvalidCartProductDataException(
                        'Некорректные данные товара. Расчет стоимости невозможен.'
                    );
                }

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
     *
     * @return void
     * @throws CategoryForLimitCheckNotFoundException Если категория с заданным ID не найдена.
     * @throws CategoryLimitNotSetInConfigException Если лимит для категории не задан в конфигурации.
     * @throws CategoryLimitExceededException Если лимит товаров для категории превышен.
     */
    protected function throwIfCategoryLimitExceeded(
        int    $categoryId,
        string $identifierField,
        string $identifierValue
    ): void
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
            throw new CategoryForLimitCheckNotFoundException(
                "Категория с ID [$categoryId], проверяемая на лимит товаров в корзине, не найдена."
            );
        }

        $limit = config("cart.limits_by_category_slug.$categorySlug");

        if (is_null($limit)) {
            throw new CategoryLimitNotSetInConfigException("Лимит для категории [$categorySlug] не задан.");
        }

        if ($totalQty >= $limit) {
            throw new CategoryLimitExceededException(
                "Нельзя добавить больше [$limit] товаров категории [$categorySlug] в корзину."
            );
        }
    }

    /**
     * Выполняет перенос записей корзины от сессии к авторизованному пользователю.
     *
     * Если пользователь добавлял товары в корзину будучи неавторизованным,
     * эти записи переназначаются на его user_id после авторизации.
     * При этом предварительно очищаются старые записи, оставшиеся от предыдущих сессий.
     *
     * @param string $oldSessionId ID сессии неавторизованного пользователя.
     * @param int $userId ID авторизованного пользователя.
     *
     * @return bool True, если перенос выполнился. False, если корзина до авторизации была пуста.
     * @throws CartMergeException В случае ошибки при обновлении данных корзины.
     */
    public function mergeCartFromSessionToUser(string $oldSessionId, int $userId): bool
    {
        $existItemsForSessionId = $this->cartRepository->hasItemsByIdentifier(
            'session_id',
            $oldSessionId
        );

        if (!$existItemsForSessionId) {
            return false;
        }

        $this->cartRepository->transferCartFromSessionToUser($oldSessionId, $userId);

        return true;
    }
}
