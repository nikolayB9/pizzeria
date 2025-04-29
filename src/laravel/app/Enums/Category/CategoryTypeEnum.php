<?php

namespace App\Enums\Category;

enum CategoryTypeEnum: int
{
    case ProductType = 1;
    case Marketing = 2;

    public function slug(): string
    {
        return match($this) {
            self::ProductType => 'product_type',
            self::Marketing => 'marketing',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::ProductType => 'Тип продукта',
            self::Marketing => 'Маркетинговая категория',
        };
    }
}
