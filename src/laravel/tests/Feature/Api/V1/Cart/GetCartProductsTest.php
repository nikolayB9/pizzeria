<?php

namespace Api\V1\Cart;

use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Traits\HasAuthContext;

class GetCartProductsTest extends AbstractApiTestCase
{
    use HasAuthContext;

    protected const COUNT_CATEGORIES = 3;
    protected const COUNT_PRODUCTS_IN_CATEGORY = 3;
    protected const COUNT_VARIANTS_IN_PRODUCT = 3;

    protected Collection $variants;

    protected function setUpTestContext(): void
    {
        $categories = CategoryHelper::createCategoryOfType(self::COUNT_CATEGORIES);
        $products = ProductHelper::createProductsWithVariantsForCategories(
            $categories,
            self::COUNT_PRODUCTS_IN_CATEGORY,
            self::COUNT_VARIANTS_IN_PRODUCT,
        );

        $this->variants = $products->map(function ($product) {
            return $product->variants->random();
        });
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/cart';
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getRequestToSessionStart(): string
    {
        return '/api/v1/cart';
    }

    protected function getResponse(string $authType, bool $createCartItems = true): TestResponse
    {
        if ($authType === 'session') {
            $this->setSessionId();
        } else {
            $this->setUserId();
        }

        $auth = $this->getAuthField();

        if ($createCartItems) {
            CartHelper::createFromVariantByIdentifier($this->variants, $auth, true);
        }

        $route = $this->getRoute();
        $method = $this->getMethod();

        if ($authType === 'session') {
            return $this->withCookie('laravel_session', $this->sessionId)
                ->$method($route);
        } else {
            return $this->$method($route);
        }
    }

    public function testReturnsSuccessfulResponseUsingSessionId(): void
    {
        $response = $this->getResponse('session');
        $response->assertStatus(200);
    }

    public function testReturnsSuccessfulResponseUsingUserId(): void
    {
        $response = $this->getResponse('user');
        $response->assertStatus(200);
    }

    public function testReturnsExpectedJsonStructureUsingSessionId(): void
    {
        $response = $this->getResponse('session');

        $response->assertExactJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'name',
                    'variant_id',
                    'variant_name',
                    'price',
                    'qty',
                    'preview_image_url',
                    'category_id',
                ],
            ],
            'meta' => [
                'totalPrice',
            ],
        ]);
    }

    public function testReturnsExpectedJsonStructureUsingUserId(): void
    {
        $response = $this->getResponse('user');

        $response->assertExactJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'name',
                    'variant_id',
                    'variant_name',
                    'price',
                    'qty',
                    'preview_image_url',
                    'category_id',
                ],
            ],
            'meta' => [
                'totalPrice',
            ],
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypesUsingSessionId(): void
    {
        $response = $this->getResponse('session');
        $cartProducts = $response->json('data');

        $this->assertNotEmpty($cartProducts);
        $this->assertIsArray($cartProducts);

        $product = array_pop($cartProducts);

        $this->assertIsString($product['name']);
        $this->assertIsInt($product['variant_id']);
        $this->assertIsString($product['variant_name']);
        $this->assertTrue(is_int($product['price']) || is_float($product['price']) || is_string($product['price']));
        $this->assertIsInt($product['qty']);
        $this->assertIsString($product['preview_image_url']);
        $this->assertIsInt($product['category_id']);

        $meta = $response->json('meta');

        $this->assertTrue(is_int($meta['totalPrice']) || is_float($meta['totalPrice']) || is_string($meta['totalPrice']));
    }

    public function testReturnedFieldsHaveExpectedTypesUsingUserId(): void
    {
        $response = $this->getResponse('user');
        $cartProducts = $response->json('data');

        $this->assertNotEmpty($cartProducts);
        $this->assertIsArray($cartProducts);

        $product = array_pop($cartProducts);

        $this->assertIsString($product['name']);
        $this->assertIsInt($product['variant_id']);
        $this->assertIsString($product['variant_name']);
        $this->assertTrue(is_int($product['price']) || is_float($product['price']) || is_string($product['price']));
        $this->assertIsInt($product['qty']);
        $this->assertIsString($product['preview_image_url']);
        $this->assertIsInt($product['category_id']);

        $meta = $response->json('meta');

        $this->assertNotEmpty($meta);
        $this->assertTrue(is_int($meta['totalPrice']) || is_float($meta['totalPrice']) || is_string($meta['totalPrice']));
    }

    public function testResponseIncludesExpectedProductsAndTotalPriceUsingSessionId(): void
    {
        $categories = CategoryHelper::createCategoryOfType(3);
        ProductHelper::createProductsWithVariantsForCategories($categories);

        $response = $this->getResponse('session');
        $cartProducts = $response->json('data');
        $returnedIds = collect($cartProducts)->pluck('variant_id')->sort()->values();
        $expectedIds = $this->variants->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);

        $totalPrice = 0;
        foreach ($cartProducts as $product) {
            $totalPrice += $product['price'] * $product['qty'];
        }

        $this->assertEquals($totalPrice, $response->json('meta.totalPrice'));
    }

    public function testResponseIncludesExpectedProductsAndTotalPriceUsingUserId(): void
    {
        $categories = CategoryHelper::createCategoryOfType(3);
        ProductHelper::createProductsWithVariantsForCategories($categories);

        $response = $this->getResponse('user');
        $cartProducts = $response->json('data');
        $returnedIds = collect($cartProducts)->pluck('variant_id')->sort()->values();
        $expectedIds = $this->variants->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);

        $totalPrice = 0;
        foreach ($cartProducts as $product) {
            $totalPrice += $product['price'] * $product['qty'];
        }

        $this->assertEquals($totalPrice, $response->json('meta.totalPrice'));
    }

    public function testReturnsEmptyArrayZeroTotalPriceIfCartIsEmptyUsingSessionId(): void
    {
        $response = $this->getResponse('session', false);

        $this->assertEquals([], $response->json('data'));
        $this->assertEquals(0.0, $response->json('meta.totalPrice'));
    }

    public function testReturnsEmptyArrayAndZeroTotalPriceIfCartIsEmptyUsingUserId(): void
    {
        $response = $this->getResponse('user', false);

        $this->assertEquals([], $response->json('data'));
        $this->assertEquals(0.0, $response->json('meta.totalPrice'));
    }
}
