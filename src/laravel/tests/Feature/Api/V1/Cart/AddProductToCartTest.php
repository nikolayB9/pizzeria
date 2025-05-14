<?php

namespace Api\V1\Cart;

use App\Enums\Error\ErrorMessageEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Traits\AssertsDatabaseWithAuth;
use Tests\Traits\HasAuthContext;

class AddProductToCartTest extends AbstractApiTestCase
{
    use HasAuthContext, AssertsDatabaseWithAuth;

    protected const CATEGORY_LIMIT = 10;
    protected const COUNT_CATEGORIES = 2;
    protected const COUNT_PRODUCTS_OF_CATEGORY = 2;
    protected const COUNT_VARIANTS_OF_PRODUCT = 3;

    protected Collection $variants;
    protected Collection $products;
    protected Collection $categories;

    protected function setUpTestContext(): void
    {
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

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/cart';
    }

    protected function getMethod(): string
    {
        return 'post';
    }

    protected function getResponse(string $authType, mixed $productData): TestResponse
    {
        if ($authType === 'session') {
            $this->setSessionId();
        } else {
            $this->setUserId();
        }

        if (is_int($productData)) {
            $productData = ['variantId' => $productData];
        }

        $route = $this->getRoute();
        $method = $this->getMethod();

        if ($authType === 'session') {
            return $this->withCookie('laravel_session', $this->sessionId)
                ->$method($route, $productData, ['Accept' => 'application/json']);
        } else {
            return $this->$method($route, $productData, ['Accept' => 'application/json']);
        }
    }

    protected function checkSuccess(TestResponse $response, mixed $data = [], mixed $meta = [], int $status = 200): void
    {
        $meta = $meta === [] ?: ['totalPrice' => $meta];

        parent::checkSuccess($response, $data, $meta, $status);

        $totalPrice = $response->json('meta.totalPrice');

        $this->assertTrue(is_float($totalPrice) || is_int($totalPrice));
    }

    protected function getRequestToSessionStart(): string
    {
        return '/api/v1/cart';
    }

    protected function getTableName(): string
    {
        return 'cart';
    }

    protected function getDatabaseFieldMapping(): array
    {
        return [
            'product_variant_id' => 'id',
            'qty' => 'qty',
        ];
    }

    public function testAddProductToEmptyCartUsingSessionId(): void
    {
        $variant = $this->variants->random();
        $response = $this->getResponse('session', $variant->id);

        $this->checkSuccess($response, meta: $variant->price);
        $this->assertDatabase(['id' => $variant->id, 'qty' => 1]);
    }

    public function testAddProductToEmptyCartUsingUserId(): void
    {
        $variant = $this->variants->random();
        $response = $this->getResponse('user', $variant->id);

        $this->checkSuccess($response, meta: $variant->price);
        $this->assertDatabase(['id' => $variant->id, 'qty' => 1]);
    }

    public function testAddNewProductToNonEmptyCartUsingSessionId(): void
    {
        $this->setSessionId();

        $variants = $this->variants->random(3);

        CartHelper::createFromVariantByIdentifier(
            $variants,
            $this->getAuthField(),
        );

        $filtered = $this->variants->whereNotIn('id', $variants->pluck('id'));
        $variant = $filtered->random();

        $response = $this->getResponse('session', $variant->id);

        $totalPrice = (float)$variant->price + $variants->sum('price');

        $this->checkSuccess($response, meta: $totalPrice);
        $this->assertDatabase(['id' => $variant->id, 'qty' => 1]);
    }

    public function testAddNewProductToNonEmptyCartUsingUserId(): void
    {
        $this->setUserId();

        $variants = $this->variants->random(3);

        CartHelper::createFromVariantByIdentifier(
            $variants,
            $this->getAuthField(),
        );

        $filtered = $this->variants->whereNotIn('id', $variants->pluck('id'));
        $variant = $filtered->random();

        $response = $this->getResponse('user', $variant->id);

        $totalPrice = (float)$variant->price + $variants->sum('price');

        $this->checkSuccess($response, meta: $totalPrice);
        $this->assertDatabase(['id' => $variant->id, 'qty' => 1]);
    }

    public function testIncreaseQuantityOfExistingProductUsingSessionId(): void
    {
        $this->setSessionId();

        $variants = $this->variants->random(3);

        CartHelper::createFromVariantByIdentifier(
            $variants,
            $this->getAuthField(),
        );

        $variant = $variants->random();

        $response = $this->getResponse('session', $variant->id);

        $totalPrice = (float)$variant->price + $variants->sum('price');

        $this->checkSuccess($response, meta: $totalPrice);
        $this->assertDatabase(['id' => $variant->id, 'qty' => 2]);
    }

    public function testIncreaseQuantityOfExistingProductUsingUserId(): void
    {
        $this->setUserId();

        $variants = $this->variants->random(3);

        CartHelper::createFromVariantByIdentifier(
            $variants,
            $this->getAuthField(),
        );

        $variant = $variants->random();

        $response = $this->getResponse('user', $variant->id);

        $totalPrice = (float)$variant->price + $variants->sum('price');

        $this->checkSuccess($response, meta: $totalPrice);
        $this->assertDatabase(['id' => $variant->id, 'qty' => 2]);
    }

    public function testCannotAddNewProductWhenCategoryLimitExceededUsingSessionId(): void
    {
        $this->setSessionId();

        $variantId = CartHelper::selectVariantAndFillCartToCategoryLimit(
            $this->categories,
            $this->products,
            $this->variants,
            $this->getAuthField(),
            self::CATEGORY_LIMIT,
            true,
        );

        $response = $this->getResponse('session', $variantId);

        $this->checkError($response, 422, 'Достигнут лимит товаров данной категории.');
        $this->assertDatabase([], ['id' => $variantId]);
    }

    public function testCannotAddNewProductWhenCategoryLimitExceededUsingUserId(): void
    {
        $this->setUserId();

        $variantId = CartHelper::selectVariantAndFillCartToCategoryLimit(
            $this->categories,
            $this->products,
            $this->variants,
            $this->getAuthField(),
            self::CATEGORY_LIMIT,
            true,
        );

        $response = $this->getResponse('user', $variantId);

        $this->checkError($response, 422, 'Достигнут лимит товаров данной категории.');
        $this->assertDatabase([], ['id' => $variantId]);
    }

    public function testCannotIncreaseQtyOfProductWhenCategoryLimitExceededUsingSessionId(): void
    {
        $this->setSessionId();

        $variantId = CartHelper::selectVariantAndFillCartToCategoryLimit(
            $this->categories,
            $this->products,
            $this->variants,
            $this->getAuthField(),
            self::CATEGORY_LIMIT,
            false,
        );

        $response = $this->getResponse('session', $variantId);

        $this->checkError($response, 422, 'Достигнут лимит товаров данной категории.');
    }

    public function testCannotIncreaseQtyOfProductWhenCategoryLimitExceededUsingUserId(): void
    {
        $this->setUserId();

        $variantId = CartHelper::selectVariantAndFillCartToCategoryLimit(
            $this->categories,
            $this->products,
            $this->variants,
            $this->getAuthField(),
            self::CATEGORY_LIMIT,
            false,
        );

        $response = $this->getResponse('user', $variantId);

        $this->checkError($response, 422, 'Достигнут лимит товаров данной категории.');
    }

    public function testAddUnpublishedProductToCartUsingSessionId(): void
    {
        $unpublishedProducts = ProductHelper::createProductsWithVariantsForCategories(
            $this->categories,
            self::COUNT_PRODUCTS_OF_CATEGORY,
            self::COUNT_VARIANTS_OF_PRODUCT,
            false,
        );
        $variants = $unpublishedProducts->flatMap(fn($product) => $product->variants);
        $unpublishedId = $variants->random()->id;

        $response = $this->getResponse('session', ['variantId' => $unpublishedId]);

        $this->checkError($response, 403, 'Товар не опубликован и не может быть добавлен в корзину.');
        $this->assertDatabase([], ['id' => $unpublishedId]);
    }

    public function testAddUnpublishedProductToCartUsingUserId(): void
    {
        $unpublishedProducts = ProductHelper::createProductsWithVariantsForCategories(
            $this->categories,
            self::COUNT_PRODUCTS_OF_CATEGORY,
            self::COUNT_VARIANTS_OF_PRODUCT,
            false,
        );
        $variants = $unpublishedProducts->flatMap(fn($product) => $product->variants);
        $unpublishedId = $variants->random()->id;

        $response = $this->getResponse('user', ['variantId' => $unpublishedId]);

        $this->checkError($response, 403, 'Товар не опубликован и не может быть добавлен в корзину.');
        $this->assertDatabase([], ['id' => $unpublishedId]);
    }

    public function testAddNonExistentProductIdToCartUsingSessionId(): void
    {
        $nonExistentId = $this->variants->max('id') + 1;

        $response = $this->getResponse('session', ['variantId' => $nonExistentId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testAddNonExistentProductIdToCartUsingUserId(): void
    {
        $nonExistentId = $this->variants->max('id') + 1;

        $response = $this->getResponse('user', ['variantId' => $nonExistentId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testAddProductWithInvalidVariantIdTypeUsingSessionId(): void
    {
        $invalidVariantId = 'string instead of int';

        $response = $this->getResponse('session', ['variantId' => $invalidVariantId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testAddProductWithInvalidVariantIdTypeUsingUserId(): void
    {
        $invalidVariantId = 'string instead of int';

        $response = $this->getResponse('user', ['variantId' => $invalidVariantId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testAddProductWithWrongVariantIdKeyUsingSessionId(): void
    {
        $existedId = $this->variants->random()->id;

        $wrongKey = 'variant_id';

        $response = $this->getResponse('session', [$wrongKey => $existedId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testAddProductWithWrongVariantIdKeyUsingUserId(): void
    {
        $existedId = $this->variants->random()->id;

        $wrongKey = 'variant_id';

        $response = $this->getResponse('user', [$wrongKey => $existedId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }
}
