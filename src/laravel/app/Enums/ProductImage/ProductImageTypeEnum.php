<?php

namespace App\Enums\ProductImage;

enum ProductImageTypeEnum: int
{
    case Preview = 1;
    case Detail = 2;

    public function slug(): string
    {
        return match($this) {
            self::Preview => 'preview',
            self::Detail => 'detail',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Preview => 'Для превью',
            self::Detail => 'Для страницы продукта',
        };
    }
}
