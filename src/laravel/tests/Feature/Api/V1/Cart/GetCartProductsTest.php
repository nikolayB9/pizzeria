<?php

namespace Api\V1\Cart;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class GetCartProductsTest extends TestCase
{
    use RefreshDatabase;

    protected Collection $variants;

    protected function setUp(): void
    {
        parent::setUp();

        $categories = CategoryHelper::createCategoryOfType(3);
        $products = ProductHelper::createProductsWithVariantsForCategories($categories);

        $this->variants = $products->map(function ($product) {
            return $product->variants->random();
        });
    }

    protected function getCartProductsResponseUsingSessionId(): TestResponse
    {
        $this->get('/api/v1/cart');
        $sessionId = session()->getId();

        CartHelper::createFromVariantByIdentifier($this->variants, 'session_id', $sessionId);

        return $this->withCookie('laravel_session', $sessionId)->get('/api/v1/cart');
    }

    protected function getCartProductsResponseUsingUserId(): TestResponse
    {
        $user = UserHelper::createUser();
        $this->actingAs($user);

        CartHelper::createFromVariantByIdentifier($this->variants, 'user_id', $user->id);

        return $this->get('/api/v1/cart');
    }

    public function testReturnsSuccessfulResponseUsingSessionId(): void
    {
        $response = $this->getCartProductsResponseUsingSessionId();
        $response->assertStatus(200);
    }

    public function testReturnsSuccessfulResponseUsingUserId(): void
    {
        $response = $this->getCartProductsResponseUsingUserId();
        $response->assertStatus(200);
    }

    public function testReturnsExpectedJsonStructureUsingSessionId(): void
    {
        $response = $this->getCartProductsResponseUsingSessionId();

        $response->assertExactJsonStructure([
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
        $response = $this->getCartProductsResponseUsingUserId();

        $response->assertExactJsonStructure([
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
        $response = $this->getCartProductsResponseUsingSessionId();
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

    public function testReturnedFieldsHaveExpectedTypesUsingUserId(): void
    {
        $response = $this->getCartProductsResponseUsingUserId();
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

    public function testResponseIncludesExpectedProductsAndTotalPriceUsingSessionId()
    {
        $categories = CategoryHelper::createCategoryOfType(3);
        ProductHelper::createProductsWithVariantsForCategories($categories);

        $response = $this->getCartProductsResponseUsingSessionId();
        $cartProducts = $response->json('data');
        $returnedIds = collect($cartProducts)->pluck('variant_id')->sort()->values();
        $expectedIds = $this->variants->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);

        $totalPrice = 0;
        foreach ($cartProducts as $product) {
            $totalPrice += $product['price'] * $product['qty'];
        }

        $this->assertEquals($response->json('meta.totalPrice'), $totalPrice);
    }

    public function testResponseIncludesExpectedProductsAndTotalPriceUsingUserId()
    {
        $categories = CategoryHelper::createCategoryOfType(3);
        ProductHelper::createProductsWithVariantsForCategories($categories);

        $response = $this->getCartProductsResponseUsingUserId();
        $cartProducts = $response->json('data');
        $returnedIds = collect($cartProducts)->pluck('variant_id')->sort()->values();
        $expectedIds = $this->variants->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);

        $totalPrice = 0;
        foreach ($cartProducts as $product) {
            $totalPrice += $product['price'] * $product['qty'];
        }

        $this->assertEquals($response->json('meta.totalPrice'), $totalPrice);
    }
}
