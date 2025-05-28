<?php

namespace Tests\Helpers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\ParameterGroupSeeder;
use Database\Seeders\ProductImageTypeSeeder;
use Illuminate\Support\Collection;

class ProductHelper
{
    /**
     * Создает продукт или коллекцию продуктов для категории или для нескольких категорий.
     *
     * @param Category|Collection $categories Категория или категории, к которым будут привязаны продукты.
     * @param int $countProducts Количество создаваемых продуктов для каждой категории.
     * @param int $countVariants Количество создаваемых вариантов для каждого продукта.
     * @param bool $productsIsPublished Определяет, будут ли создаваемые продукты помечены как опубликованные.
     *
     * @return Product|Collection Продукт или коллекция созданных продуктов.
     */
    public static function createProductsWithVariantsForCategories(
        Category|Collection $categories,
        int                 $countProducts = 3,
        int                 $countVariants = 3,
        bool                $productsIsPublished = true,
    ): Product|Collection
    {
        (new ProductImageTypeSeeder())->run();

        $collection = collect()->wrap($categories);
        $products = collect();

        foreach ($collection as $category) {
            $products = $products->merge(
                Product::factory($countProducts)
                    ->hasAttached($category)
                    ->withVariants($countVariants)
                    ->withImages()
                    ->create([
                        'is_published' => $productsIsPublished,
                    ])
            );
        }

        return $products->count() === 1 ? $products->first() : $products;
    }

    /**
     * Создает один или несколько вариантов продуктов, предварительно создавая продукты и категории.
     *
     * @param int $countProducts Количество создаваемых продуктов для каждой категории.
     * @param int $countProductVariants Количество создаваемых вариантов для каждого продукта.
     * @param int $countCategories Количество категорий.
     *
     * @return ProductVariant|Collection Вариант продукта или коллекция вариантов продуктов.
     */
    public static function createProductVariantsAndCategories(int $countProducts = 1,
                                                              int $countProductVariants = 1,
                                                              int $countCategories = 1): ProductVariant|Collection
    {
        $categories = CategoryHelper::createCategoryOfType($countCategories);
        $products = self::createProductsWithVariantsForCategories(
            $categories,
            $countProducts,
            $countProductVariants);

        $collectionProducts = collect()->wrap($products);
        $variants = $collectionProducts->flatMap(fn($product) => $product->variants);

        return $variants->count() === 1 ? $variants->first() : $variants;
    }

    /**
     * Создает продукт(ы) с вариантами. Опционально добавляет параметры.
     *
     * @param int $countProducts Количество создаваемых продуктов.
     * @param bool $withParameters Добавить параметры к вариантам.
     *
     * @return Product|Collection Один продукт или коллекция продуктов.
     */
    public static function createProduct(int $countProducts = 1, bool $withParameters = false): Product|Collection
    {
        (new ProductImageTypeSeeder())->run();

        if ($withParameters) {
            (new ParameterGroupSeeder())->run();

            $products = Product::factory($countProducts)
                ->withVariantsAndParameters()
                ->withImages()
                ->create();
        } else {
            $products = Product::factory($countProducts)
                ->withVariants()
                ->withImages()
                ->create();
        }

        return $countProducts === 1 ? $products->first() : $products;
    }
}
