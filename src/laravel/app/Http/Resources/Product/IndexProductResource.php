<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'preview_image_url' => $this->getPreviewImageUrl(),
            'has_multiple_variants' => $this->hasMultipleVariants(),
            'min_price' => $this->getMinPrice(),
        ];
    }
}
