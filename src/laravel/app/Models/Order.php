<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Получить варианты продуктов, относящиеся к заказу, с указанием количества и цены,
     * а также минимальной информацией о связанных продуктах (id и name).
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'order_product')
            ->withPivot(['qty', 'price'])
            ->with(['product' => fn($q) => $q->select('id', 'name')])
            ->select('id', 'name', 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
