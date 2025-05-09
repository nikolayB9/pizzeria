<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imagePath = config('product.default_image_path');

        return [
            'image_path' => $imagePath,
        ];
    }
}
