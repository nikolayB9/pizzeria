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
            'preview_image_url' => url($this->requireNotNullRelation('previewImage')->image_path),
            'has_multiple_variants' => $this->requirePreload('variants_count') > 1,
            'min_price' => $this->requirePreload('variants_min_price'),
        ];
    }

    /**
     * Гарантирует, что отношение было предварительно загружено и не равно null.
     */
    private function requireNotNullRelation(string $relation): mixed
    {
        $relationValue = $this->requireRelation($relation);

        if (is_null($relationValue)) {
            throw new \LogicException("Expected not-null relation [$relation].");
        }

        return $relationValue;
    }

    /**
     * Гарантирует, что агрегатное поле (например, variants_count) было предварительно загружено.
     */
    private function requirePreload(string $preload): mixed
    {
        if (!array_key_exists($preload, $this->getAttributes())) {
            throw new \LogicException("Attribute [$preload] must be preloaded (using withCount(), withMin()).");
        }

        return $this->$preload;
    }

    /**
     * Гарантирует, что отношение было предварительно загружено.
     */
    private function requireRelation(string $relation): mixed
    {
        if (!$this->relationLoaded($relation)) {
            throw new \LogicException("Relation [$relation] must be preloaded.");
        }

        return $this->$relation;
    }
}
