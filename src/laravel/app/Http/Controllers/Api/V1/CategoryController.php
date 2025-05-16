<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    /**
     * Возвращает список всех категорий продуктов.
     *
     * @return JsonResponse Json - ответ со списком категорий.
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            data: $this->categoryService->getAllCategories()
        );
    }
}
