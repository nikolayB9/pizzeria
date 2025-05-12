<?php

namespace Tests\Helpers;

use App\Models\Cart;
use Illuminate\Support\Collection;

class CartHelper
{
    /**
     * Создает записи в таблице корзины для переданных вариантов продуктов.
     *
     * @param Collection $variants Коллекция вариантов продуктов.
     * @param string $identifierField Поле-идентификатор пользователя (session_id или user_id).
     * @param string $identifierValue Значение идентификатора.
     * @param bool $randomQty
     * @return void
     */
    public static function createFromVariantByIdentifier(Collection $variants,
                                                         string     $identifierField,
                                                         string     $identifierValue,
                                                         bool       $randomQty = true): void
    {
        foreach ($variants as $variant) {
            Cart::factory()->create([
                'user_id' => $identifierField === 'user_id' ? $identifierValue : null,
                'session_id' => $identifierField === 'session_id' ? $identifierValue : null,
                'product_variant_id' => $variant->id,
                'price' => $variant->price,
                'category_id' => $variant->product->productCategory->id,
                'qty' => $randomQty ? rand(1, 3) : 1,
            ]);
        }
    }

    /**
     * Заполняет корзину товарами одной категории до достижения указанного лимита.
     *
     * @param Collection $variants Коллекция вариантов продуктов одной категории.
     * @param string $identifierField Поле-идентификатор пользователя (session_id или user_id).
     * @param string $identifierValue Значение идентификатора.
     * @param int $categoryLimit Максимальное количество товаров этой категории в корзине.
     * @param int|null $categoryId Явно заданный ID категории (чтобы не делать запрос через отношения).
     * @return void
     */
    public static function fillCartToCategoryLimit(Collection $variants,
                                                   string     $identifierField,
                                                   string     $identifierValue,
                                                   int        $categoryLimit,
                                                   ?int       $categoryId = null): void
    {
        $usedVariants = collect();

        while ($categoryLimit > 0) {
            if ($variants->isNotEmpty()) {
                $variant = $variants->random();
                $variants = $variants->reject(fn($var) => $var->is($variant));
                $usedVariants->push($variant);
            } else {
                $variant = $usedVariants->random();
            }

            $cartItem = Cart::where($identifierField, $identifierValue)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($cartItem) {
                $cartItem->increment('qty');
            } else {
                Cart::create([
                    'user_id' => $identifierField === 'user_id' ? $identifierValue : null,
                    'session_id' => $identifierField === 'session_id' ? $identifierValue : null,
                    'product_variant_id' => $variant->id,
                    'price' => $variant->price,
                    'category_id' => $categoryId ?? $variant->product->productCategory->id,
                    'qty' => 1,
                ]);
            }
            $categoryLimit--;
        }
    }

    /**
     * Выбирает случайный вариант товара из одной категории и заполняет корзину другими вариантами этой категории до достижения лимита.
     *
     * @param Collection $categories Коллекция категорий, из которой выбирается случайная.
     * @param Collection $products Коллекция продуктов всех категорий.
     * @param Collection $variants Коллекция всех вариантов продуктов.
     * @param string $identifierField Поле-идентификатор пользователя ('session_id' или 'user_id').
     * @param string $identifierValue Значение идентификатора.
     * @param int $categoryLimit Максимальное количество товаров одной категории в корзине.
     * @param bool $rejectVariant Удалять ли выбранный вариант из добавляемых в корзину (true — не будет добавлен).
     * @return int ID случайно выбранного варианта из выбранной категории.
     */
    public static function selectVariantAndFillCartToCategoryLimit(
        Collection $categories,
        Collection $products,
        Collection $variants,
        string     $identifierField,
        string     $identifierValue,
        int        $categoryLimit,
        bool       $rejectVariant
    ): int
    {
        $category = $categories->random();
        $categoryId = $category->id;

        // Отбираем продукты только из выбранной категории
        $categoryProducts = $products->filter(
            fn($product) => $product->productCategory->id === $categoryId
        );

        // Отбираем варианты только из отобранных продуктов
        $categoryVariants = $variants->filter(
            fn($variant) => $categoryProducts->contains('id', $variant->product_id)
        );

        $selectedVariant = $categoryVariants->random();

        if ($rejectVariant) {
            $categoryVariants = $categoryVariants->reject(
                fn($variant) => $variant->is($selectedVariant)
            );
        }

        self::fillCartToCategoryLimit(
            $categoryVariants,
            $identifierField,
            $identifierValue,
            $categoryLimit,
            $categoryId
        );

        return $selectedVariant->id;
    }
}
