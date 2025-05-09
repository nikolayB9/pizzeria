<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use HasFactory;

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
}
