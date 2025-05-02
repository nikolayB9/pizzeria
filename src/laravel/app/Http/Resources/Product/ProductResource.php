<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Traits\RequiresPreload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'detail_image_url' => url($this->requireNotNullRelation('detailImage')->image_path),
            'variants' => $this->requireRelation('variants'),
        ];
    }
}
