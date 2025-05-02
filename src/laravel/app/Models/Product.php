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

    public function detailImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->where('type', ProductImageTypeEnum::Detail);
    }
}
