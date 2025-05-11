<?php

namespace Tests\Helpers;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\ParameterGroupSeeder;
use Database\Seeders\ProductImageTypeSeeder;
use Illuminate\Support\Collection;

class ProductHelper
{
    /**
     * Создает коллекцию продуктов для указанной категории.
     *
     * @param Category $category Категория, к которой будут привязаны продукты.
     * @param int $countProducts Количество создаваемых продуктов.
     * @return Collection Коллекция созданных продуктов.
     */
    public static function createProductsOfCategory(Category $category, int $countProducts = 3): Collection
    {
        (new ProductImageTypeSeeder())->run();

        return Product::factory($countProducts)
            ->hasAttached($category)
            ->withVariants()
            ->withImages()
            ->create();
    }

    /**
     * Создает коллекцию продуктов для рандомной категории из переданной коллекции.
     *
     * @param Collection $categories Коллекция категорий.
     * @param int $countProducts Количество создаваемых продуктов.
     * @return Collection Коллекция созданных продуктов.
     */
    public static function createProductsOfRandomCategory(Collection $categories, int $countProducts = 3): Collection
    {
        (new ProductImageTypeSeeder())->run();

        $products = collect();

        while ($countProducts > 0) {
            $products->add(
                Product::factory()
                    ->hasAttached($categories->random())
                    ->withVariants()
                    ->withImages()
                    ->create()
            );
            $countProducts--;
        }

        return $products;
    }

    /**
     * Создает продукт(ы) с вариантами. Опционально добавляет параметры.
     *
     * @param int $countProducts Количество создаваемых продуктов.
     * @param bool $withParameters Добавить параметры к вариантам.
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
