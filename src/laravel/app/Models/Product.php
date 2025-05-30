<?php

namespace App\Models;

use App\Enums\Category\CategoryTypeEnum;
use App\Enums\ProductImage\ProductImageTypeEnum;
use App\Exceptions\Product\MissingProductCategoryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Product extends Model
{
    use HasFactory;

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

    public function productCategoryRelation(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->where('type', CategoryTypeEnum::ProductType)
            ->limit(1);
    }

    /**
     * Возвращает обязательную категорию продукта с типом "product", используется как accessor: $product->productCategory.
     *
     * @return Category Модель категории с типом "product".
     * @throws MissingProductCategoryException Если категория не найдена.
     */
    public function getProductCategoryAttribute(): Category
    {
        $category = $this->productCategoryRelation->first();

        if (!$category) {
            throw new MissingProductCategoryException("Продукт [{$this->slug}] не имеет обязательную категорию с типом 'product'.");
        }

        return $category;
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

    public function scopePublished($query): Builder
    {
        return $query->where('is_published', true);
    }
}
