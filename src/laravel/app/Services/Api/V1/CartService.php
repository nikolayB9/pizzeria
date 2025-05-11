<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Cart\CartProductListItemDto;
use App\Exceptions\Cart\CategoryLimitExceededException;
use App\Models\Cart;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Repositories\Cart\CartRepositoryInterface;

class CartService
{
    public function __construct(private readonly CartRepositoryInterface $cartRepository)
    {
    }

    /**
     * Возвращает продукты из корзины текущего пользователя или сессии.
     *
     * @return CartProductListItemDto[] Массив DTO продуктов в корзине или пустой массив, если корзина пуста.
     */
    public function getUserCartProducts(): array
    {
        $auth = $this->getAuthField();

        $cartItems = $this->cartRepository->getItemsByIdentifier($auth['field'], $auth['value']);

        if ($cartItems->isEmpty()) {
            return [];
        }

        return CartProductListItemDto::collection($cartItems);
    }


    /**
     * @throws CategoryLimitExceededException
     */
    public function addProduct(int $variantId): void
    {
        $productVariant = ProductVariant::find($variantId);
        $category = $productVariant->productCategory;
        $auth = $this->getAuthField();

        $this->throwIfCategoryLimitExceeded($category, $auth);

        $cartData = [
            $auth['field'] => $auth['value'],
            'product_variant_id' => $variantId,
            'price' => $productVariant->price,  // фиксируем цену!
            'category_id' => $category->id,
        ];

        $cartItem = Cart::where($cartData)->first();

        if ($cartItem) {
            $cartItem->increment('qty');
        } else {
            Cart::create($cartData + ['qty' => 1]);
        }
    }

    public function deleteProduct(int $variantId): bool
    {
        $auth = $this->getAuthField();

        $cartItem = Cart::where($auth['field'], $auth['value'])
            ->where('product_variant_id', $variantId)
            ->select('id', 'qty')
            ->firstOrFail();

        if ($cartItem->qty > 1) {
            $cartItem->decrement('qty');
            return false;
        } else {
            $cartItem->delete();
            return true;
        }
    }

    /**
     * Привязывает корзину, созданную до авторизации, к авторизованному пользователю.
     *
     * Актуально, если пользователь добавил товары в корзину до входа в аккаунт.
     */
    public function mergeCartFromSessionToUser(string $oldSessionId, int $userId): void
    {
        Cart::where('session_id', $oldSessionId)
            ->update([
                'user_id' => $userId,
                'session_id' => null,
            ]);
    }

    /**
     * Возвращает массив с типом идентификатора пользователя и его значением.
     *
     * @return array{field: 'user_id'|'session_id', value: string} Массив с типом идентификации пользователя.
     */
    public function getAuthField(): array
    {
        return auth()->check()
            ? ['field' => 'user_id', 'value' => auth()->id()]
            : ['field' => 'session_id', 'value' => session()->getId()];
    }

    //TODO обработка коллекции
    public function getTotalPrice()
    {
        $auth = $this->getAuthField();
        return Cart::where($auth['field'], $auth['value'])
            ->selectRaw('SUM(price * qty) as total')
            ->value('total') ?? 0;
    }

    /**
     * Бросает исключение, если превышен лимит товаров по категории.
     *
     * @param Category $category Сущность категории.
     *
     * @throws CategoryLimitExceededException
     */
    protected function throwIfCategoryLimitExceeded(Category $category, array $auth): void
    {
        $cartItemsInCategory = Cart::where($auth['field'], $auth['value'])
            ->where('category_id', $category->id)
            ->select('id', 'qty')
            ->get();

        if (!$cartItemsInCategory->isEmpty()) {
            $totalQty = $cartItemsInCategory->sum('qty');

            $limit = $this->getLimitByCategorySlug($category->slug);

            if ($totalQty >= $limit) {
                throw new CategoryLimitExceededException("Нельзя добавить больше {$limit} товаров категории '{$category->slug}' в корзину.");
            }
        }
    }

    /**
     * Возвращает лимит количества товаров для указанной категории.
     * Источник лимитов — конфигурация config/cart.php.
     *
     * @throws \RuntimeException Если лимит для категории не задан.
     */
    private function getLimitByCategorySlug(string $categorySlug): int
    {
        $limits = config('cart.limits_by_category_slug', []);
        $limit = $limits[$categorySlug] ?? null;

        //TODO свое исключение, century

        if (is_null($limit)) {
            throw new \RuntimeException("Лимит для категории '{$categorySlug}' не задан.");
        }

        return $limit;
    }
}
