<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;

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
}
