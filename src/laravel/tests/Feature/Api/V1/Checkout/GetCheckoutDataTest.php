<?php

namespace Api\V1\Checkout;

use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\AddressHelper;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Traits\HasAuthContext;

class GetCheckoutDataTest extends AbstractApiTestCase
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
        return '/api/v1/checkout';
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getRequestToSessionStart(): string
    {
        return '/api/v1';
    }

    protected function getResponse(bool $createCartItems = true,
                                   bool $isAuth = true,
                                   bool $withDefaultAddress = true): TestResponse
    {
        if ($isAuth) {
            $this->setUserId();
        } else {
            $this->setSessionId();
        }

        if ($createCartItems) {
            $auth = $this->getAuthField();
            CartHelper::createFromVariantByIdentifier($this->variants, $auth, true);
        }

        if ($withDefaultAddress && $isAuth) {
            AddressHelper::createAddresses($this->userId);
        }

        $route = $this->getRoute();
        $method = $this->getMethod();

        return $this->$method($route, ['Accept' => 'application/json']);
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $response = $this->getResponse();

        $this->checkSuccess($response);
    }

    public function testReturnsExpectedJsonStructure(): void
    {
        $response = $this->getResponse();

        $response->assertExactJsonStructure([
            'success',
            'data' => [
                'user' => [
                    'name',
                    'email',
                    'phone_number',
                    'address' => [
                        'id',
                        'city',
                        'street',
                        'house',
                        'is_default'
                    ],
                ],
                'cart' => [
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
                'cart_total',
                'delivery_cost',
                'total',
                'delivery_slots' => [
                    '*' => [
                        'from',
                        'slot',
                    ]
                ],
            ],
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getResponse();

        $data = $response->json('data');

        $user = $data['user'];
        $this->assertIsArray($user);
        $this->assertIsString($user['name']);
        $this->assertIsString($user['email']);
        $this->assertIsString($user['phone_number']);

        $address = $user['address'];
        $this->assertIsArray($address);
        $this->assertIsInt($address['id']);
        $this->assertIsString($address['city']);
        $this->assertIsString($address['street']);
        $this->assertIsString($address['house']);
        $this->assertTrue($address['is_default']);

        $cart = $data['cart'];
        $this->assertIsArray($cart);

        $cartItem = array_pop($cart);
        $this->assertIsString($cartItem['name']);
        $this->assertIsInt($cartItem['variant_id']);
        $this->assertIsString($cartItem['variant_name']);
        $this->assertIsNumeric($cartItem['price']);
        $this->assertIsInt($cartItem['qty']);
        $this->assertIsString($cartItem['preview_image_url']);
        $this->assertIsInt($cartItem['category_id']);

        $this->assertIsNumeric($data['cart_total']);
        $this->assertIsNumeric($data['delivery_cost']);
        $this->assertIsNumeric($data['total']);

        $deliverySlots = $data['delivery_slots'];
        $this->assertIsArray($deliverySlots);

        $slot = array_pop($deliverySlots);
        $this->assertIsString($slot['from']);
        $this->assertIsString($slot['slot']);
    }

    public function testReturnsOnlyExpectedCartProducts(): void
    {
        $response = $this->getResponse();

        $returnedIds = collect($response->json('data.cart'))->pluck('variant_id')->sort()->values();
        $expectedIds = $this->variants->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);
    }

    public function testDefaultAddressIsNullWhenUserHasNoAddresses(): void
    {
        $response = $this->getResponse(true, true, false);

        $this->checkSuccess($response);
        $this->assertNull($response->json('data.user.address'));
    }

    public function testFailsWhenUserNotAuthorized(): void
    {
        $response = $this->getResponse(true, false);

        $this->checkError($response, 401, 'Не авторизован.');
    }

    public function testFailsWhenCartIsEmpty(): void
    {
        $response = $this->getResponse(false);

        $this->checkError($response, 422, 'Корзина пуста, невозможно продолжить оформление заказа.');
    }
}
