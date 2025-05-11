<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Cart\CategoryLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\CartVariantRequest;
use App\Models\Cart;
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

        return response()->json([
            'data' => $cartProducts,
            'meta' => [
                'totalPrice' => $this->cartService->getTotalPrice(),
            ]
        ]);
    }

    public function store(CartVariantRequest $request): JsonResponse
    {
        try {
            $this->cartService->addProduct($request->validated()['variantId']);
        } catch (CategoryLimitExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => 'Продукт добавлен в корзину',
            'totalPrice' => $this->cartService->getTotalPrice(),
        ]);
    }

    public function destroy(CartVariantRequest $request): JsonResponse
    {
        $deleted = $this->cartService->deleteProduct($request->validated()['variantId']);

        return response()->json([
            'status' => $deleted
                ? 'Продукт удален из корзины'
                : 'Уменьшено количество товара в корзине',
            'totalPrice' => $this->cartService->getTotalPrice(),
        ]);
    }

    public function clear(): JsonResponse
    {
        $auth = $this->cartService->getAuthField();

        Cart::where($auth['field'], $auth['value'])
            ->delete();

        return response()->json([
            'status' => 'Корзина очищена',
        ]);
    }
}
