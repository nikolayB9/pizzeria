<?php

namespace Database\Seeders;

use App\Enums\ProductImage\ProductImageTypeEnum;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    private string $imagePath = '/assets/img/default.jpg';

    public function run(): void
    {
        $productIds = Product::pluck('id');

        foreach ($productIds as $productId) {
            foreach (ProductImageTypeEnum::cases() as $imageType) {
                ProductImage::updateOrCreate(
                    [
                        'product_id' => $productId,
                        'type' => $imageType->value,
                    ],
                    ['image_path' => $this->imagePath]
                );
            }
        }
    }
}
