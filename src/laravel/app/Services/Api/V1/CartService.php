<?php

namespace App\Services\Api\V1;

use App\Models\Cart;
use App\Models\ProductVariant;

class CartService
{
    public function addProduct(int $variantId)
    {
        $price = ProductVariant::where('id', $variantId)->value('price');

        $auth = $this->getAuthField();

        $cartData = [
            $auth['field'] => $auth['value'],
            'product_variant_id' => $variantId,
            'price' => $price, //фиксируем цену
        ];

        if (Cart::where($cartData)->exists()) {
            Cart::where($cartData)->increment('qty');
        } else {
            Cart::create($cartData + ['qty' => 1]);
        }
    }

    public function getAuthField(): array
    {
        return auth()->check()
            ? ['field' => 'user_id', 'value' => auth()->id()]
            : ['field' => 'session_id', 'value' => session()->id()];
    }

    public function getTotalPrice()
    {
        $auth = $this->getAuthField();
        return Cart::where($auth['field'], $auth['value'])
            ->selectRaw('SUM(price * qty) as total')
            ->value('total') ?? 0;
    }
}
