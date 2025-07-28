<?php

namespace App\Models;

use App\Enums\Order\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'status' => OrderStatusEnum::class,
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'order_product')
            ->withPivot(['qty', 'price']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
