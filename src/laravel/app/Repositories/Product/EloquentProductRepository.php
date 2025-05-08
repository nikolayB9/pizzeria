<?php

namespace App\Repositories\Product;

use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    /**
     * Получает опубликованный продукт по его slug или выбрасывает исключение, если не найден.
     *
     * @param string $slug Slug продукта.
     * @return Product Модель продукта с загруженными вариантами.
     * @throws ProductNotFoundException Если продукт не найден.
     */
    public function getBySlug(string $slug): Product
    {
        try {
            return Product::where('slug', $slug)
                ->published()
                ->with(['detailImage', 'variants.parameters'])
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new ProductNotFoundException("Продукт [$slug] не найден.");
        }
    }
}
