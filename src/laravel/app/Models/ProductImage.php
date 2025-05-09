<?php

namespace App\Models;

use App\Enums\ProductImage\ProductImageTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'type' => ProductImageTypeEnum::class,
    ];
}
