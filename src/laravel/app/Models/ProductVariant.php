<?php

namespace App\Models;

use App\Enums\Category\CategoryTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function parameters(): BelongsToMany
    {
        return $this->belongsToMany(Parameter::class)
            ->withPivot(['value', 'is_shared']);
    }

    /**
     * Возвращает обязательную категорию продукта с типом 'product_type'.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getProductCategoryAttribute()
    {
        return $this->product
            ->categories()
            ->where('type', CategoryTypeEnum::ProductType->value)
            ->firstOrFail();
    }
}
