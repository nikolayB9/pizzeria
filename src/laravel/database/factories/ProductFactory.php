<?php

namespace Database\Factories;

use App\Enums\ProductImage\ProductImageTypeEnum;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productName = ucfirst(fake()->unique()->words(rand(1, 4), true));

        return [
            'name' => $productName,
            'description' => fake()->sentence(),
            'is_published' => true,
            'slug' => Str::slug($productName),
        ];
    }

    public function withVariants(int $count = 3): ProductFactory|Factory
    {
        return $this->has(
            ProductVariant::factory()->count($count),
            'variants'
        );
    }

    public function withImages(): ProductFactory|Factory
    {
        return $this->has(
            ProductImage::factory(['type' => ProductImageTypeEnum::Preview->value]),
            'previewImage'
        )
            ->has(
                ProductImage::factory(['type' => ProductImageTypeEnum::Detail->value]),
                'detailImage'
            );
    }
}
