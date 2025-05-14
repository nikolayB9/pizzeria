<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Cart\CartUpdateException;
use App\Exceptions\Cart\CategoryLimitExceededException;
use App\Exceptions\Cart\ProductVariantNotFoundInCartException;
use App\Exceptions\Product\ProductNotPublishedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\AddToCartRequest;
use App\Http\Requests\Api\V1\Cart\DeleteFromCartRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * Возвращает список продуктов в корзине с их общей стоимостью.
     *
     * @return JsonResponse JSON-ответ со списком продуктов и общей стоимостью корзины.
     */
    public function index(): JsonResponse
    {
        $cartProducts = $this->cartService->getUserCartProducts();

        return ApiResponse::success(
            data: $cartProducts,
            meta: ['totalPrice' => $this->cartService->getTotalPrice($cartProducts, false)],
        );
    }

    /**
     * Добавляет продукт в корзину, если не превышен лимит по категории.
     *
     * @param AddToCartRequest $request Валидация переданного variantId.
     * @return JsonResponse JSON-ответ со статусом и общей стоимостью корзины.
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        try {
            $this->cartService->addProduct($request->validated()['variantId']);
        } catch (ProductNotPublishedException) {
            return ApiResponse::fail(
                'Товар не опубликован и не может быть добавлен в корзину.',
                403,
            );
        } catch (CategoryLimitExceededException) {
            return ApiResponse::fail(
                'Достигнут лимит товаров данной категории.',
                422,
            );
        } catch (CartUpdateException) {
            return ApiResponse::fail(
                'Не удалось добавить товар в корзину. Пожалуйста, попробуйте позже.',
                500,
            );
        }

        return ApiResponse::success(
            meta: ['totalPrice' => $this->cartService->getTotalPrice()],
        );
    }

    /**
     * Удаляет товар из корзины или уменьшает его количество.
     *
     * @param DeleteFromCartRequest $request Валидация переданного variantId.
     * @return JsonResponse JSON-ответ со статусом и общей стоимостью корзины.
     */
    public function destroy(DeleteFromCartRequest $request): JsonResponse
    {
        try {
            $this->cartService->deleteProduct($request->validated()['variantId']);
        } catch (ProductVariantNotFoundInCartException) {
            return ApiResponse::fail(
                'Товар не найден в корзине.',
                422,
            );
        } catch (CartUpdateException) {
            return ApiResponse::fail(
                'Не удалось удалить товар из корзины. Пожалуйста, попробуйте позже.',
                500,
            );
        }

        return ApiResponse::success(
            meta: ['totalPrice' => $this->cartService->getTotalPrice()],
        );
    }

    /**
     * Очищает корзину.
     *
     * @return JsonResponse JSON-ответ со статусом.
     */
    public function clear(): JsonResponse
    {
        try {
            $this->cartService->clearUserCart();
        } catch (CartUpdateException) {
            return ApiResponse::fail(
                'Не удалось очистить корзину. Пожалуйста, попробуйте позже.',
                500,
            );
        }

        return ApiResponse::success();
    }
}
