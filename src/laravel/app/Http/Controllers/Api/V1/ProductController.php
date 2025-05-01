<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\IndexProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return IndexProductResource::collection(
            Product::where('is_published', true)
                ->with(['previewImage'])
                ->withMin('variants', 'price')
                ->withCount('variants')
                ->get()
        );
    }
}
