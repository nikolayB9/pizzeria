<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Category\CategoryNotFoundException;
use App\Exceptions\Product\ProductNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
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
            return ApiResponse::fail('Категория не найдена.', 404);
        }

        return ApiResponse::success(
            data: $products,
        );
    }

    /**
     * Возвращает продукт по slug или 404, если не найден.
     *
     * @param string $productSlug Slug продукта.
     * @return JsonResponse Продукт или 404, если не найден.
     */
    public function show(string $productSlug): JsonResponse
    {
        try {
            $product = $this->productService->getProductBySlug($productSlug);
        } catch (ProductNotFoundException) {
            return ApiResponse::fail('Продукт не найден.', 404);
        }

        return ApiResponse::success(
            data: $product,
        );
    }
}
