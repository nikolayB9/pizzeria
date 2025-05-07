<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Category\CategoryNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Services\Api\V1\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(readonly ProductService $productService)
    {
    }

    /**
     * Возвращает опубликованные продукты по slug категории.
     *
     * @param string $categorySlug Slug категории.
     * @return JsonResponse Список продуктов или 404, если категория не найдена.
     */
    public function getByCategory(string $categorySlug): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByCategorySlug($categorySlug);
        } catch (CategoryNotFoundException) {
            return response()->json([
                'message' => 'Категория не найдена',
            ], 404);
        }

        return response()->json($products);
    }

    public function show(string $productSlug)
    {
        return ProductResource::make(
            Product::where('slug', $productSlug)
                ->with(['detailImage', 'variants'])
                ->firstOrFail()
        );
    }
}
