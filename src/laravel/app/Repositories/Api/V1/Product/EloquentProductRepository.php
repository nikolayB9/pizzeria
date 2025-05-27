<?php

namespace App\Repositories\Api\V1\Product;

use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Product\ProductNotPublishedException;
use App\Exceptions\Product\ProductVariantMustExistException;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * Возвращает коллекцию опубликованных продуктов для заданной категории.
     *
     * @param Category $category Модель категории, для которой загружаются продукты.
     *
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
     *
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

    /**
     * Возвращает вариант опубликованного продукта с категорией типа "product".
     *
     * @param int $id ID варианта продукта.
     *
     * @return ProductVariant Модель варианта продукта с загруженными связями.
     * @throws ProductVariantMustExistException Если вариант продукта с указанным ID не найден.
     * @throws ProductNotPublishedException Если связанный продукт не опубликован.
     */
    public function getProductVariantWithCategoryById(int $id): ProductVariant
    {
        try {
            $variant = ProductVariant::where('id', $id)
                ->select('id', 'product_id', 'price')
                ->with([
                    'product:id,is_published,slug',
                    'product.productCategoryRelation:id,type',
                ])
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new ProductVariantMustExistException("Вариант продукта с id [$id] должен был быть найден в базе данных (проверка уже проведена в FormRequest).");
        }

        if (!$variant->product->is_published) {
            throw new ProductNotPublishedException("Продукт [{$variant->product->slug}], содержащий вариант с id [$id] не опубликован.");
        }

        return $variant;
    }
}
