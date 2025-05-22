<?php

namespace App\DTO\Api\V1\Cart;

use App\Models\Cart;
use Illuminate\Support\Collection;

class CartRawItemDto
{
    public function __construct(
        public int   $product_variant_id,
        public float $price,
        public int   $qty,
    )
    {
    }

    /**
     * Создаёт DTO из модели Cart.
     *
     * @param Cart $cart Экземпляр модели Cart.
     *
     * @return self
     */
    public static function fromModel(Cart $cart): self
    {
        return new self(
            product_variant_id: $cart->product_variant_id,
            price: $cart->price,
            qty: $cart->qty,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection $cart Коллекция моделей Cart.
     *
     * @return CartRawItemDto[] Массив DTO.
     */
    public static function collection(Collection $cart): array
    {
        return $cart->map(fn($cartItem) => self::fromModel($cartItem))->toArray();
    }

    /**
     * Преобразует массив DTO в массив для вставки в БД.
     *
     * @param CartRawItemDto[] $cart Массив DTO.
     *
     * @return array Массив для вставки в БД.
     */
    public static function toOrderProductInsertData(array $cart): array
    {
        return array_map(function ($cartItem) {
            return [
                'product_variant_id' => $cartItem->product_variant_id,
                'price' => $cartItem->price,
                'qty' => $cartItem->qty,
            ];
        }, $cart);
    }
}
