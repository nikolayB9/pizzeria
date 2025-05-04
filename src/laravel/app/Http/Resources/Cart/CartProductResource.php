<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Traits\RequiresPreload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartProductResource extends JsonResource
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
            'variant_id' => $this->product_variant_id,
            'price' => $this->price,
            'qty' => $this->qty,
            'variant_name' => $this->requireNotNullRelation('productVariant')->name,
            'name' => $this->requireNotNullRelation('productVariant')->product->name,
            'preview_image_url' => url($this->requireNotNullRelation('productVariant')->product->previewImage->image_path),
        ];
    }
}
