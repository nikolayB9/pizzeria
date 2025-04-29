<?php

namespace App\Enums\Parameter;

enum ParameterGroupEnum: int
{
    case Features = 1;
    case Nutrition = 2;
    case Ingredients = 3;
    case Allergens = 4;

    public function slug(): string
    {
        return match($this) {
            self::Features => 'features',
            self::Nutrition => 'nutrition',
            self::Ingredients => 'ingredients',
            self::Allergens => 'allergens',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Features => 'Характеристики',
            self::Nutrition => 'Пищевая ценность',
            self::Ingredients => 'Состав',
            self::Allergens => 'Аллергены',
        };
    }
}
