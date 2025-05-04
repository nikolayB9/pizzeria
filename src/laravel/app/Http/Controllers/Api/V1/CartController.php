<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\CartVariantRequest;
use App\Http\Resources\Cart\CartProductResource;
use App\Models\Cart;
use App\Services\Api\V1\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(readonly CartService $cartService)
    {
    }

    public function index(): JsonResponse
    {
        $auth = $this->cartService->getAuthField();

        $cartProducts = CartProductResource::collection(
            Cart::where($auth['field'], $auth['value'])
                ->with([
                    'productVariant:id,name,product_id',
                    'productVariant.product:id,name',
                    'productVariant.product.previewImage:id,image_path,product_id',
                ])
                ->get()
        );

        return response()->json([
            'cartProducts' => $cartProducts,
            'totalPrice' => $this->cartService->getTotalPrice(),
        ]);
    }

    public function store(CartVariantRequest $request): JsonResponse
    {
        $wasAdded = $this->cartService->addProduct($request->validated()['variantId']);

        return response()->json([
            'status' => $wasAdded
                ? 'Продукт добавлен в корзину'
                : 'Увеличено количество товара в корзине',
            'totalPrice' => $this->cartService->getTotalPrice(),
        ]);
    }

    public function destroy(CartVariantRequest $request): JsonResponse
    {
        $wasDeleted = $this->cartService->deleteProduct($request->validated()['variantId']);

        return response()->json([
            'status' => $wasDeleted
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
