<?php

namespace App\Repositories\Product;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * Возвращает коллекцию опубликованных продуктов для заданной категории.
     *
     * @param Category $category Модель категории, для которой загружаются продукты.
     * @return Collection Коллекция продуктов с отношением previewImage и агрегатами variants_min_price, variants_count.
     */
    public function getPublishedForCategory(Category $category): Collection
    {
        return $category->products()
            ->published()
            ->with(['previewImage'])
            ->withMin('variants', 'price')
            ->withCount('variants')
            ->get();
    }
}
