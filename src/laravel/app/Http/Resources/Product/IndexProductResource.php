<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Traits\RequiresPreload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexProductResource extends JsonResource
{
    use RequiresPreload;

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
            'preview_image_url' => url($this->requireNotNullRelation('previewImage')->image_path),
            'has_multiple_variants' => $this->requirePreload('variants_count') > 1,
            'min_price' => $this->requirePreload('variants_min_price'),
        ];
    }
}
