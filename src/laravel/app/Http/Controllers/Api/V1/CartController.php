<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\AddToCartRequest;
use App\Services\Api\V1\CartService;

class CartController extends Controller
{
    public function __construct(readonly CartService $cartService)
    {
    }

    public function index()
    {

    }

    public function store(AddToCartRequest $request)
    {
        $this->cartService->addProduct($request->validated()['variantId']);

        return response()->json([
            'status' => 'Продукт добавлен в корзину',
            'totalPrice' => $this->cartService->getTotalPrice(),
        ]);
    }
}
