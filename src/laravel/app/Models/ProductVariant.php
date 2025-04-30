<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function parameters(): BelongsToMany
    {
        return $this->belongsToMany(Parameter::class)
            ->withPivot(['value', 'is_shared']);
    }
}
