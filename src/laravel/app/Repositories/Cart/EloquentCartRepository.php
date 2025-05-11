<?php

namespace App\Repositories\Cart;

use App\DTO\Api\V1\Cart\AddToCartProductDto;
use App\Exceptions\Cart\CartUpdateException;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class EloquentCartRepository implements CartRepositoryInterface
{
    /**
     * Возвращает товары из корзины по заданному идентификатору пользователя.
     *
     * @param string $identifierField Поле, по которому будет производиться фильтрация ('user_id' или 'session_id').
     * @param string $value Значение идентификатора.
     * @return Collection Коллекция моделей Cart с необходимыми отношениями.
     */
    public function getItemsByIdentifier(string $identifierField, string $value): Collection
    {
        return Cart::where($identifierField, $value)
            ->with([
                'productVariant:id,name,product_id',
                'productVariant.product:id,name',
                'productVariant.product.previewImage:id,image_path,product_id',
                'productVariant.product.productCategoryRelation:id,type',
            ])
            ->get();
    }

    /**
     * Возвращает общую стоимость товаров в корзине по заданному идентификатору пользователя.
     *
     * @param string $identifierField Поле, по которому будет производиться фильтрация ('user_id' или 'session_id').
     * @param string $value Значение идентификатора.
     * @return float Общая стоимость всех товаров в корзине.
     */
    public function getTotalPriceByIdentifier(string $identifierField, string $value): float
    {
        return (float)Cart::where($identifierField, $value)
            ->selectRaw('SUM(price * qty) as total')
            ->value('total') ?? 0.0;
    }

    /**
     * Возвращает общее количество товаров из корзины для указанной категории и идентификатора пользователя.
     *
     * @param string $categoryId ID категории.
     * @param string $identifierField Поле фильтрации ('user_id' или 'session_id').
     * @param string $identifierValue Значение идентификатора пользователя.
     * @return int Общее количество единиц товаров (qty) в корзине для заданной категории.
     */
    public function getTotalQuantityByCategoryAndIdentifier(string $categoryId, string $identifierField, string $identifierValue): int
    {
        return (int)Cart::where($identifierField, $identifierValue)
            ->where('category_id', $categoryId)
            ->sum('qty');
    }

    /**
     * Добавляет товар в корзину или увеличивает его количество, если он уже есть.
     *
     * @param AddToCartProductDto $productDto Данные добавляемого товара.
     * @param string $identifierField Поле-идентификатор пользователя.
     * @param string $identifierValue Значение идентификатора.
     * @return void
     * @throws CartUpdateException Если произошла ошибка при обращении к базе данных.
     */
    public function addProductToCartByIdentifier(AddToCartProductDto $productDto, string $identifierField, string $identifierValue): void
    {
        $cartData = [
            $identifierField => $identifierValue,
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
        } catch (\Exception $e) {
            Log::error('Ошибка при добавлении товара в корзину', [
                'exception' => $e,
                'identifier_field' => $identifierField,
                'identifier_value' => $identifierValue,
                'product_variant_id' => $productDto->product_variant_id,
                'category_id' => $productDto->category_id,
                'price' => $productDto->price,
            ]);

            throw new CartUpdateException("Ошибка при добавлении товара в корзину: {$e->getMessage()}");
        }
    }
}
