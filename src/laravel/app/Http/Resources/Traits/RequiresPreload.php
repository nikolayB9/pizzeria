<?php

namespace App\Http\Resources\Traits;

trait RequiresPreload
{
    /**
     * Гарантирует, что отношение было предварительно загружено.
     */
    private function requireRelation(string $relation): mixed
    {
        if (!$this->relationLoaded($relation)) {
            throw new \LogicException("Relation [$relation] must be preloaded on [" . static::class . "].");
        }

        return $this->$relation;
    }

    /**
     * Гарантирует, что отношение было предварительно загружено и не равно null.
     */
    private function requireNotNullRelation(string $relation): mixed
    {
        $relationValue = $this->requireRelation($relation);

        if (is_null($relationValue)) {
            throw new \LogicException("Expected not-null relation [$relation] on [" . static::class . "].");
        }

        return $relationValue;
    }

    /**
     * Гарантирует, что агрегатное поле (например, variants_count) было предварительно загружено.
     */
    private function requirePreload(string $preload): mixed
    {
        if (!array_key_exists($preload, $this->getAttributes())) {
            throw new \LogicException("Attribute [$preload] must be preloaded (using withCount(), withMin()) on [" . static::class . "].");
        }

        return $this->$preload;
    }
}
