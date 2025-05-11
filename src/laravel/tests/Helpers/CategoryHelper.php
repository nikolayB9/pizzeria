<?php

namespace Tests\Helpers;

use App\Enums\Category\CategoryTypeEnum;
use App\Models\Category;
use Database\Seeders\CategoryTypeSeeder;
use Illuminate\Support\Collection;

class CategoryHelper
{
    /**
     * Создает категорию (категории) с указанным типом.
     *
     * @param int $countCategories Количество создаваемых категорий.
     * @param CategoryTypeEnum $typeEnum Тип категории.
     * @return Category|Collection Одна категория или коллекция категорий.
     */
    public static function createCategoryOfType(int $countCategories = 1, CategoryTypeEnum $typeEnum = CategoryTypeEnum::ProductType): Category|Collection
    {
        (new CategoryTypeSeeder())->run();

        $categories = Category::factory($countCategories)->create([
            'type' => $typeEnum->value,
        ]);

        return $countCategories === 1 ? $categories->first() : $categories;
    }
}
