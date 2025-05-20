<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Address\FailedSetDefaultAddressException;
use App\Exceptions\Address\UserAddressNotAddException;
use App\Exceptions\Address\UserAddressNotDeletedException;
use App\Exceptions\Address\UserAddressNotFoundException;
use App\Exceptions\Address\UserAddressNotUpdatedException;
use App\Http\Requests\Api\V1\Address\StoreUserAddressRequest;
use App\Http\Requests\Api\V1\Address\UpdateUserAddressRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\AddressService;
use Illuminate\Http\JsonResponse;

class AddressController
{
    public function __construct(private readonly AddressService $addressService)
    {
    }

    /**
     * Возвращает список адресов авторизованного пользователя.
     *
     * @return JsonResponse JSON-ответ с массивом адресов в сокращённом формате (город, улица, дом).
     */
    public function index(): JsonResponse
    {
        $addresses = $this->addressService->getUserAddresses();

        return ApiResponse::success(
            data: $addresses,
        );
    }

    /**
     * Возвращает полный адрес по его ID для авторизованного пользователя.
     *
     * @param int $id ID адреса пользователя.
     *
     * @return JsonResponse JSON-ответ, содержащий DTO с полными данными адреса.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $address = $this->addressService->getUserAddress($id);
        } catch (UserAddressNotFoundException $e) {
            return ApiResponse::fail(
                $e->getMessage(),
                404,
            );
        }

        return ApiResponse::success(
            data: $address,
        );
    }

    /**
     * Добавляет новый адрес для авторизованного пользователя.
     *
     * @param StoreUserAddressRequest $request Валидированные данные запроса.
     *
     * @return JsonResponse JSON-ответ: success = true в случае успеха.
     */
    public function store(StoreUserAddressRequest $request): JsonResponse
    {
        try {
            $this->addressService->createUserAddress($request->toDto());
        } catch (UserAddressNotAddException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 500,
            );
        }

        return ApiResponse::success();
    }

    /**
     * Редактирует адрес авторизованного пользователя.
     *
     * @param int $id ID редактируемого адреса.
     * @param UpdateUserAddressRequest $request Валидированные данные запроса.
     *
     * @return JsonResponse JSON-ответ: success = true в случае успеха.
     */
    public function update(int $id, UpdateUserAddressRequest $request): JsonResponse
    {
        try {
            $this->addressService->updateUserAddress($id, $request->toDto());
        } catch (UserAddressNotFoundException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 404,
            );
        } catch (UserAddressNotUpdatedException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 500,
            );
        }

        return ApiResponse::success();
    }

    /**
     * Устанавливает адрес по умолчанию для авторизованного пользователя.
     *
     * @param int $id ID адреса, который нужно сделать основным.
     *
     * @return JsonResponse JSON-ответ: success = true в случае успеха.
     */
    public function setDefault(int $id): JsonResponse
    {
        try {
            $this->addressService->setDefaultUserAddress($id);
        } catch (FailedSetDefaultAddressException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 500,
            );
        }

        return ApiResponse::success();
    }

    /**
     * Удаляет адрес авторизованного пользователя.
     *
     * @param int $id ID удаляемого адреса.
     *
     * @return JsonResponse JSON-ответ: success = true в случае успеха.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->addressService->deleteUserAddress($id);
        } catch (UserAddressNotFoundException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 404,
            );
        } catch (UserAddressNotDeletedException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 500,
            );
        }

        return ApiResponse::success();
    }
}
