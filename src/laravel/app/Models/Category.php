<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function parameters(): BelongsToMany
    {
        return $this->belongsToMany(Parameter::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
