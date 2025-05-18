<?php

namespace App\Repositories\Cart;

use App\DTO\Api\V1\Cart\AddToCartProductDto;
use App\Exceptions\Cart\CartMergeException;
use App\Exceptions\Cart\CartUpdateException;
use App\Exceptions\Cart\ProductVariantNotFoundInCartException;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentCartRepository implements CartRepositoryInterface
{
    /**
     * Возвращает товары из корзины по заданному идентификатору пользователя.
     *
     * @param string $field Поле-идентификатор пользователя (user_id или session_id).
     * @param int|string $value Значение идентификатора.
     *
     * @return Collection Коллекция моделей Cart с необходимыми отношениями.
     */
    public function getItemsByIdentifier(string $field, int|string $value): Collection
    {
        return Cart::where($field, $value)
            ->with([
                'productVariant:id,name,product_id',
                'productVariant.product:id,name',
                'productVariant.product.previewImage:id,image_path,product_id',
                'productVariant.product.productCategoryRelation:id,type',
            ])
            ->get();
    }

    /**
     * Добавляет товар в корзину или увеличивает его количество, если он уже есть.
     *
     * @param AddToCartProductDto $productDto Данные добавляемого товара.
     * @param string $field Поле-идентификатор пользователя (user_id или session_id).
     * @param int|string $value Значение идентификатора.
     *
     * @return void
     * @throws CartUpdateException Если произошла ошибка при обращении к базе данных.
     */
    public function addProductToCartByIdentifier(AddToCartProductDto $productDto, string $field, int|string $value): void
    {
        $cartData = [
            $field => $value,
            'product_variant_id' => $productDto->product_variant_id,
            'price' => $productDto->price,
            'category_id' => $productDto->category_id,
        ];

        try {
            $cartItem = Cart::where($cartData)->first();

            if ($cartItem) {
                $cartItem->increment('qty');
            } else {
                Cart::create($cartData + ['qty' => 1]);
            }
        } catch (\Throwable $e) {
            Log::error('Ошибка при добавлении товара в корзину', [
                'exception' => $e,
                'identifier_field' => $field,
                'identifier_value' => $value,
                'product_variant_id' => $productDto->product_variant_id,
                'category_id' => $productDto->category_id,
                'price' => $productDto->price,
                'method' => __METHOD__,
            ]);

            throw new CartUpdateException("Непредвиденная ошибка при добавлении товара в корзину: {$e->getMessage()}");
        }
    }

    /**
     * Удаляет товар из корзины или уменьшает его количество, если количество больше 1.
     *
     * @param int $productVariantId ID варианта продукта.
     * @param string $field Поле-идентификатор пользователя (user_id или session_id).
     * @param int|string $value Значение идентификатора.
     *
     * @return void
     * @throws CartUpdateException Если произошла ошибка при обращении к базе данных.
     * @throws ProductVariantNotFoundInCartException Если продукт не найден в корзине пользователя.
     */
    public function deleteProductFromCartByIdentifier(int $productVariantId, string $field, int|string $value): void
    {
        $cartItem = Cart::where($field, $value)
            ->where('product_variant_id', $productVariantId)
            ->first();

        if (!$cartItem) {
            throw new ProductVariantNotFoundInCartException(
                "Продукт с вариантом ID [$productVariantId] не найден в корзине. [$field: $value]"
            );
        }

        if ($cartItem->qty <= 0) {
            Log::warning('Попытка удалить товар с нулевым количеством в корзине', [
                'identifier_field' => $field,
                'identifier_value' => $value,
                'product_variant_id' => $productVariantId,
                'method' => __METHOD__,
            ]);
            $cartItem->delete();
            return;
        }

        try {
            if ($cartItem->qty > 1) {
                $cartItem->decrement('qty');
            } else {
                $cartItem->delete();
            }
        } catch (\Throwable $e) {
            Log::error('Ошибка при удалении товара из корзины', [
                'exception' => $e,
                'identifier_field' => $field,
                'identifier_value' => $value,
                'product_variant_id' => $productVariantId,
                'method' => __METHOD__,
            ]);

            throw new CartUpdateException("Непредвиденная ошибка при удалении товара из корзины: {$e->getMessage()}");
        }
    }

    /**
     * Очищает корзину пользователя по идентификатору.
     *
     * @param string $field Поле-идентификатор пользователя (user_id или session_id).
     * @param int|string $value Значение идентификатора.
     *
     * @return void
     * @throws CartUpdateException Если произошла ошибка при обращении к базе данных.
     */
    public function clearCartByIdentifier(string $field, int|string $value): void
    {
        try {
            Cart::where($field, $value)
                ->delete();
        } catch (\Throwable $e) {
            Log::error('Ошибка при попытке очистить корзину', [
                'exception' => $e,
                'identifier_field' => $field,
                'identifier_value' => $value,
                'method' => __METHOD__,
            ]);

            throw new CartUpdateException("Непредвиденная ошибка при попытке очистить корзину: {$e->getMessage()}");
        }
    }

    /**
     * Возвращает общую стоимость товаров в корзине по заданному идентификатору пользователя.
     *
     * @param string $field Поле-идентификатор пользователя (user_id или session_id).
     * @param int|string $value Значение идентификатора.
     *
     * @return float Общая стоимость всех товаров в корзине.
     */
    public function getTotalPriceByIdentifier(string $field, int|string $value): float
    {
        return (float)Cart::where($field, $value)
            ->selectRaw('SUM(price * qty) as total')
            ->value('total') ?? 0.0;
    }

    /**
     * Возвращает общее количество товаров из корзины для указанной категории и идентификатора пользователя.
     *
     * @param string $categoryId ID категории.
     * @param string $field Поле-идентификатор пользователя (user_id или session_id).
     * @param int|string $value Значение идентификатора пользователя.
     *
     * @return int Общее количество единиц товаров (qty) в корзине для заданной категории.
     */
    public function getTotalQuantityByCategoryAndIdentifier(string $categoryId, string $field, int|string $value): int
    {
        return (int)Cart::where($field, $value)
            ->where('category_id', $categoryId)
            ->sum('qty');
    }

    /**
     * Проверяет, есть ли записи в корзине по указанному идентификатору.
     *
     * @param string $field Поле-идентификатор пользователя (user_id или session_id).
     * @param int|string $value Значение идентификатора.
     *
     * @return bool True, если записи существуют, иначе false.
     */
    public function hasItemsByIdentifier(string $field, int|string $value): bool
    {
        return Cart::where($field, $value)
            ->exists();
    }

    /**
     * Обновляет записи в корзине по указанному идентификатору.
     *
     * @param array<string, mixed> $data Массив данных для обновления (в формате поле => значение).
     * @param string $field Поле-идентификатор пользователя (user_id или session_id).
     * @param int|string $value Значение идентификатора.
     *
     * @return void
     * @throws CartUpdateException В случае ошибки при обновлении корзины.
     */
    public function updateByIdentifier(array $data, string $field, int|string $value): void
    {
        try {
            Cart::where($field, $value)
                ->update(
                    $data
                );
        } catch (\Throwable $e) {
            Log::error('Ошибка при попытке обновить данные корзины', [
                'exception' => $e,
                'update_data' => $data,
                'identifier_field' => $field,
                'identifier_value' => $value,
                'method' => __METHOD__,
            ]);

            throw new CartUpdateException("Непредвиденная ошибка при попытке обновить данные корзины: {$e->getMessage()}");
        }
    }

    /**
     * Переносит корзину с сессии на авторизованного пользователя.
     *
     * Если у авторизованного пользователя уже есть записи в корзине (оставшиеся с прошлых сессий),
     * они удаляются. Затем все позиции, добавленные в корзину в текущей сессии (неавторизованной),
     * привязываются к пользователю.
     *
     * @param string $sessionId ID сессии неавторизованного пользователя.
     * @param int $userId ID авторизованного пользователя.
     *
     * @return void
     * @throws CartMergeException В случае ошибки при обновлении данных корзины.
     */
    public function transferCartFromSessionToUser(string $sessionId, int $userId): void
    {
        try {
            DB::transaction(function () use ($sessionId, $userId) {
                $this->clearCartByIdentifier('user_id', $userId);
                $this->updateByIdentifier(
                    [
                        'user_id' => $userId,
                        'session_id' => null,
                    ],
                    'session_id',
                    $sessionId,
                );
            });
        } catch (\Throwable $e) {
            Log::error('Ошибка при переносе корзины после авторизации.', [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'exception' => $e,
            ]);

            throw new CartMergeException();
        }
    }

}
