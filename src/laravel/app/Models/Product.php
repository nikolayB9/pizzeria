<?php

namespace App\Models;

use App\Enums\ProductImage\ProductImageTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Product extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function parameters(): Collection
    {
        return $this->categories
            ->flatMap(fn($category) => $category->parameters)
            ->unique('id')          // оставляем только уникальные параметры
            ->values();             // values() сбрасывает ключи коллекции
    }

    public function previewImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->where('type', ProductImageTypeEnum::Preview);
    }

    /**
     * Возвращает url для превью продукта.
     *
     * Использует предварительно загруженное поле `previewImage`, если оно доступно,
     * иначе вызывает отношение с отдельным запросом к базе данных.
     */
    public function getPreviewImageUrl(): ?string
    {
        $previewImage = $this->previewImage;

        return $previewImage ? url($previewImage->image_path) : null;
    }

    /**
     * Определяет, имеет ли продукт более одного варианта.
     *
     * Использует предварительно загруженное поле `variants_count`, если оно доступно,
     * иначе выполняет отдельный запрос к базе данных.
     */
    public function hasMultipleVariants(): bool
    {
        return ($this->variants_count ?? $this->variants()->count()) > 1;
    }

    /**
     * Возвращает минимальную цену среди всех вариантов продукта.
     *
     * Использует предварительно загруженное поле `variants_min_price`, если оно доступно,
     * иначе выполняет отдельный запрос к базе данных.
     */
    public function getMinPrice(): float|int
    {
        return $this->variants_min_price ?? $this->variants()->min('price');
    }
}
