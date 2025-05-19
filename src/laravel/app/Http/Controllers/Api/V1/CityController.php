<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\City\CityNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\CityService;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    public function __construct(private readonly CityService $cityService)
    {
    }

    /**
     * Возвращает список всех городов.
     *
     * @return JsonResponse JSON-ответ со списком городов.
     */
    public function index(): JsonResponse
    {
        $cities = $this->cityService->getAllCities();

        return ApiResponse::success(
            data: $cities,
        );
    }

    /**
     * Возвращает список всех улиц для указанного города.
     *
     * @param int $id ID города.
     *
     * @return JsonResponse JSON-ответ со списком улиц города.
     */
    public function streets(int $id): JsonResponse
    {
        try {
            $streets = $this->cityService->getCityStreets($id);
        } catch (CityNotFoundException $e) {
            return ApiResponse::fail(
                $e->getMessage(),
                404,
            );
        }

        return ApiResponse::success(
            data: $streets,
        );
    }
}
