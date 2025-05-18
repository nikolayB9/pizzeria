<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Address\UserAddressNotAddException;
use App\Http\Requests\Api\V1\Address\StoreUserAddressRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\AddressService;
use Illuminate\Http\JsonResponse;

class AddressController
{
    public function __construct(private readonly AddressService $addressService)
    {
    }

    /**
     * Добавляет новый адрес для авторизованного пользователя.
     *
     * @param StoreUserAddressRequest $request Валидированные данные запроса.
     *
     * @return JsonResponse JSON-ответ с DTO нового адреса для отображения в списке.
     */
    public function store(StoreUserAddressRequest $request): JsonResponse
    {
        try {
            $address = $this->addressService->createUserAddress($request->toDto());
        } catch (UserAddressNotAddException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 500,
            );
        }

        return ApiResponse::success(
            data: $address,
        );
    }
}
