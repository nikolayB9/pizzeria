<?php

namespace Api\V1\Cart;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class AddProductToCartTest extends TestCase
{
    use RefreshDatabase;

    protected const CATEGORY_LIMIT = 10;
    protected const COUNT_CATEGORIES = 2;
    protected const COUNT_PRODUCTS_OF_CATEGORY = 2;
    protected const COUNT_VARIANTS_OF_PRODUCT = 3;

    protected Collection $variants;
    protected Collection $products;
    protected Collection $categories;
    protected string $sessionId;
    protected int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = CategoryHelper::createCategoryOfType(self::COUNT_CATEGORIES);

        foreach ($this->categories as $category) {
            Config::set("cart.limits_by_category_slug.{$category->slug}", self::CATEGORY_LIMIT);
        }

        $this->products = ProductHelper::createProductsWithVariantsForCategories(
            $this->categories,
            self::COUNT_PRODUCTS_OF_CATEGORY,
            self::COUNT_VARIANTS_OF_PRODUCT
        );

        $this->variants = $this->products->flatMap(fn($product) => $product->variants);
    }

    protected function addProductToCartResponseUsingSessionId(mixed $productData): TestResponse
    {
        $this->setSessionId();

        return $this->withCookie('laravel_session', $this->sessionId)
            ->post('/api/v1/cart', $productData, ['Accept' => 'application/json']);
    }

    protected function addProductToCartResponseUsingUserId(mixed $productData): TestResponse
    {
        $this->setUserId();

        return $this->post('/api/v1/cart', $productData, ['Accept' => 'application/json']);
    }

    protected function setSessionId(): void
    {
        if (empty($this->sessionId)) {
            $this->get('/api/v1/cart');
            $this->sessionId = session()->getId();
        }
    }

    protected function setUserId(): void
    {
        if (empty($this->userId)) {
            $user = UserHelper::createUser();

            $this->userId = $user->id;
            $this->actingAs($user);
        }
    }

    protected function checkSuccess(TestResponse $response, float|int $expectedTotalPrice): void
    {
        $response->assertStatus(200);
        $response->assertExactJsonStructure([
            'success',
            'totalPrice',
        ]);
        $this->assertTrue($response->json('success') === true);

        $totalPrice = $response->json('totalPrice');

        $this->assertTrue(is_float($totalPrice) || is_int($totalPrice));
        $this->assertEquals($expectedTotalPrice, $totalPrice);
    }

    protected function checkError(TestResponse $response, int $status, string $message = null): void
    {
        $response->assertStatus($status);

        if ($message) {
            $response->assertExactJsonStructure([
                'error',
            ]);

            $error = $response->json('error');

            $this->assertIsString($error);
            $this->assertEquals($message, $error);
        }
    }

    public function testAddProductToEmptyCartUsingSessionId(): void
    {
        $variant = $this->variants->random();
        $response = $this->addProductToCartResponseUsingSessionId(['variantId' => $variant->id]);

        $this->checkSuccess($response, $variant->price);
    }

    public function testAddProductToEmptyCartUsingUserId(): void
    {
        $variant = $this->variants->random();
        $response = $this->addProductToCartResponseUsingUserId(['variantId' => $variant->id]);

        $this->checkSuccess($response, $variant->price);
    }

    public function testAddNewProductToNonEmptyCartUsingSessionId(): void
    {
        $this->setSessionId();
        $variants = $this->variants->random(3);

        CartHelper::createFromVariantByIdentifier(
            $variants,
            'session_id',
            $this->sessionId,
            false
        );

        $filtered = $this->variants->whereNotIn('id', $variants->pluck('id'));
        $variant = $filtered->random();
        $response = $this->addProductToCartResponseUsingSessionId(['variantId' => $variant->id]);
        $totalPrice = (float)$variant->price + $variants->sum('price');

        $this->checkSuccess($response, $totalPrice);
    }

    public function testAddNewProductToNonEmptyCartUsingUserId(): void
    {
        $this->setUserId();
        $variants = $this->variants->random(3);

        CartHelper::createFromVariantByIdentifier(
            $variants,
            'user_id',
            $this->userId,
            false
        );

        $filtered = $this->variants->whereNotIn('id', $variants->pluck('id'));
        $variant = $filtered->random();
        $response = $this->addProductToCartResponseUsingSessionId(['variantId' => $variant->id]);
        $totalPrice = (float)$variant->price + $variants->sum('price');

        $this->checkSuccess($response, $totalPrice);
    }

    public function testIncreaseQuantityOfExistingProductUsingSessionId(): void
    {
        $this->setSessionId();
        $variants = $this->variants->random(3);

        CartHelper::createFromVariantByIdentifier(
            $variants,
            'session_id',
            $this->sessionId,
            false
        );

        $variant = $variants->random();
        $response = $this->addProductToCartResponseUsingSessionId(['variantId' => $variant->id]);
        $totalPrice = (float)$variant->price + $variants->sum('price');

        $this->checkSuccess($response, $totalPrice);
    }

    public function testIncreaseQuantityOfExistingProductUsingUserId(): void
    {
        $this->setUserId();
        $variants = $this->variants->random(3);

        CartHelper::createFromVariantByIdentifier(
            $variants,
            'user_id',
            $this->userId,
            false
        );

        $variant = $variants->random();
        $response = $this->addProductToCartResponseUsingSessionId(['variantId' => $variant->id]);
        $totalPrice = (float)$variant->price + $variants->sum('price');

        $this->checkSuccess($response, $totalPrice);
    }

    public function testCannotAddNewProductWhenCategoryLimitExceededUsingSessionId()
    {
        $this->setSessionId();

        $variantId = CartHelper::selectVariantAndFillCartToCategoryLimit(
            $this->categories,
            $this->products,
            $this->variants,
            'session_id',
            $this->sessionId,
            self::CATEGORY_LIMIT,
            true
        );

        $response = $this->addProductToCartResponseUsingSessionId(['variantId' => $variantId]);

        $this->checkError($response, 422, 'Достигнут лимит товаров данной категории.');
    }

    public function testCannotAddNewProductWhenCategoryLimitExceededUsingUserId()
    {
        $this->setUserId();

        $variantId = CartHelper::selectVariantAndFillCartToCategoryLimit(
            $this->categories,
            $this->products,
            $this->variants,
            'user_id',
            $this->userId,
            self::CATEGORY_LIMIT,
            true
        );

        $response = $this->addProductToCartResponseUsingUserId(['variantId' => $variantId]);

        $this->checkError($response, 422, 'Достигнут лимит товаров данной категории.');
    }

    public function testCannotIncreaseQtyOfProductWhenCategoryLimitExceededUsingSessionId()
    {
        $this->setSessionId();

        $variantId = CartHelper::selectVariantAndFillCartToCategoryLimit(
            $this->categories,
            $this->products,
            $this->variants,
            'session_id',
            $this->sessionId,
            self::CATEGORY_LIMIT,
            false
        );

        $response = $this->addProductToCartResponseUsingSessionId(['variantId' => $variantId]);

        $this->checkError($response, 422, 'Достигнут лимит товаров данной категории.');
    }

    public function testCannotIncreaseQtyOfProductWhenCategoryLimitExceededUsingUserId()
    {
        $this->setUserId();

        $variantId = CartHelper::selectVariantAndFillCartToCategoryLimit(
            $this->categories,
            $this->products,
            $this->variants,
            'user_id',
            $this->userId,
            self::CATEGORY_LIMIT,
            false
        );

        $response = $this->addProductToCartResponseUsingUserId(['variantId' => $variantId]);

        $this->checkError($response, 422, 'Достигнут лимит товаров данной категории.');
    }

    public function testAddNonExistentProductToCartUsingSessionId()
    {
        $nonExistentId = $this->variants->max('id') + 1;
        $response = $this->addProductToCartResponseUsingSessionId(['variantId' => $nonExistentId]);

        $this->checkError($response, 422);
    }

    public function testAddNonExistentProductToCartUsingUserId()
    {
        $nonExistentId = $this->variants->max('id') + 1;
        $response = $this->addProductToCartResponseUsingUserId(['variantId' => $nonExistentId]);

        $this->checkError($response, 422);
    }

    public function testAddUnpublishedProductToCartUsingSessionId()
    {
        $unpublishedProducts = ProductHelper::createProductsWithVariantsForCategories(
            $this->categories,
            self::COUNT_PRODUCTS_OF_CATEGORY,
            self::COUNT_VARIANTS_OF_PRODUCT,
            false,
        );
        $variants = $unpublishedProducts->flatMap(fn($product) => $product->variants);
        $unpublishedId = $variants->random()->id;

        $response = $this->addProductToCartResponseUsingSessionId(['variantId' => $unpublishedId]);

        $this->checkError($response, 403, 'Товар не опубликован и не может быть добавлен в корзину.');
    }

    public function testAddUnpublishedProductToCartUsingUserId()
    {
        $unpublishedProducts = ProductHelper::createProductsWithVariantsForCategories(
            $this->categories,
            self::COUNT_PRODUCTS_OF_CATEGORY,
            self::COUNT_VARIANTS_OF_PRODUCT,
            false,
        );
        $variants = $unpublishedProducts->flatMap(fn($product) => $product->variants);
        $unpublishedId = $variants->random()->id;

        $response = $this->addProductToCartResponseUsingUserId(['variantId' => $unpublishedId]);

        $this->checkError($response, 403, 'Товар не опубликован и не может быть добавлен в корзину.');
    }
}
