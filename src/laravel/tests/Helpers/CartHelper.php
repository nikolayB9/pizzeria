<?php

namespace Tests\Helpers;

use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartHelper
{
    public static function createFromVariantByIdentifier(Collection $variants, string $identifierField, string $value)
    {
        foreach ($variants as $variant) {
            Cart::factory()->create([
                'user_id' => $identifierField === 'user_id' ? $value : null,
                'session_id' => $identifierField === 'session_id' ? $value : null,
                'product_variant_id' => $variant->id,
                'price' => $variant->price,
                'category_id' => $variant->product->productCategory->id,
            ]);
        }
    }
}
