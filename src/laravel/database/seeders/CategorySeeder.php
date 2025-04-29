<?php

namespace Database\Seeders;

use App\Enums\Category\CategoryTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $this->createCategoriesWithProductType();
        $this->createMarketingCategories();
    }

    private function createCategoriesWithProductType(): void
    {
        $categories = [
            [
                'slug' => 'pizza',
                'name' => 'Пицца',
            ],
            [
                'slug' => 'drinks',
                'name' => 'Напитки',
            ],
        ];

        $this->updateOrInsertCategories($categories, CategoryTypeEnum::ProductType->value);
    }

    private function createMarketingCategories(): void
    {
        $categories = [
            [
                'slug' => 'hits',
                'name' => 'Хиты',
            ],
            [
                'slug' => 'new',
                'name' => 'Новинки',
            ],
        ];

        $this->updateOrInsertCategories($categories, CategoryTypeEnum::Marketing->value);
    }

    private function updateOrInsertCategories(array $categories, int $categoryTypeId): void
    {
        foreach ($categories as $category) {
            DB::table('categories')
                ->updateOrInsert(
                    ['slug' => $category['slug']],
                    [
                        'name' => $category['name'],
                        'type' => $categoryTypeId,
                    ],
                );
        }
    }
}
