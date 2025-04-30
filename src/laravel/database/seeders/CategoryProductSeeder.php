<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CategoryProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->syncCategoryProduct('pizza', ['vetcina-i-syr', 'karbonara']);
        $this->syncCategoryProduct('hits', ['vetcina-i-syr']);
        $this->syncCategoryProduct('drinks', ['kakao', 'sokoladnyi-molocnyi-kokteil']);
        $this->syncCategoryProduct('new', ['kakao']);
    }

    private function syncCategoryProduct(string $categorySlug, array $productSlugs): void
    {
        // Находим категорию по slug
        $category = Category::select('id')->where('slug', $categorySlug)->first();

        if (!$category) {
            throw new \RuntimeException("Category with slug '$categorySlug' not found.");
        }

        // Находим продукты по slug
        $productIds = Product::whereIn('slug', $productSlugs)->pluck('id');

        if (count($productSlugs) !== $productIds->count()) {
            throw new \RuntimeException("One or more products not found for category: $categorySlug.");
        }

        // Привязываем продукты к категории (без удаления существующих)
        $category->products()->syncWithoutDetaching($productIds);
    }
}
