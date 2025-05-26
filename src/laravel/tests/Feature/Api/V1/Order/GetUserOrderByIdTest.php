<?php

namespace Api\V1\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\OrderHelper;
use Tests\Helpers\ProductHelper;
use Tests\Helpers\UserHelper;

class GetUserOrderByIdTest extends AbstractApiTestCase
{
    protected const COUNT_CATEGORIES = 3;
    protected const COUNT_PRODUCTS_IN_CATEGORY = 3;
    protected const COUNT_VARIANTS_IN_PRODUCT = 3;
    protected const COUNT_RANDOM_VARIANTS = 3;
    protected const COUNT_USERS = 3;
    protected const COUNT_ORDERS_FOR_EACH_USER = 1;

    protected Collection $randomVariants;
    protected Order|Collection $userOrder;
    protected User $user;

    protected function setUpTestContext(): void
    {
        $categories = CategoryHelper::createCategoryOfType(self::COUNT_CATEGORIES);
        $products = ProductHelper::createProductsWithVariantsForCategories(
            $categories,
            self::COUNT_PRODUCTS_IN_CATEGORY,
            self::COUNT_VARIANTS_IN_PRODUCT,
        );

        $allVariants = $products->map(function ($product) {
            return $product->variants->random();
        });

        $this->randomVariants = $allVariants->random(self::COUNT_RANDOM_VARIANTS);

        $users = UserHelper::createUser(self::COUNT_USERS);
        $this->user = $users->random();

        OrderHelper::createOrdersForUsers(
            $users,
            $allVariants,
            self::COUNT_ORDERS_FOR_EACH_USER,
            true,
        );

        $this->userOrder = OrderHelper::createOrdersForUsers(
            $this->user,
            $this->randomVariants,
        );
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return "/api/v1/orders/$routeParameter";
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(mixed $orderId,
                                   bool  $isAuth = true): TestResponse
    {
        if ($isAuth) {
            $this->actingAs($this->user);
        }

        $route = $this->getRoute($orderId);
        $method = $this->getMethod();

        return $this->$method($route, ['Accept' => 'application/json']);
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $response = $this->getResponse($this->userOrder->id);

        $this->checkSuccess($response);
    }

    public function testReturnsExpectedJsonStructure(): void
    {
        $response = $this->getResponse($this->userOrder->id);

        $response->assertExactJsonStructure([
            'success',
            'data' => [
                'id',
                'products' => [
                    '*' => [
                        'name',
                        'variant_name',
                        'price',
                        'qty',
                        'preview_image_url',
                    ],
                ],
                'total',
                'delivery_cost',
                'address' => [
                    'id',
                    'city',
                    'street',
                    'house',
                    'is_default',
                ],
                'created_at',
                'status',
            ],
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getResponse($this->userOrder->id);

        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertIsInt($data['id']);

        $products = $data['products'];
        $this->assertIsArray($products);

        $product = array_pop($products);
        $this->assertIsArray($product);
        $this->assertIsString($product['name']);
        $this->assertIsString($product['variant_name']);
        $this->assertIsNumeric($product['price']);
        $this->assertIsInt($product['qty']);
        $this->assertIsString($product['preview_image_url']);

        $this->assertIsNumeric($data['total']);
        $this->assertIsNumeric($data['delivery_cost']);

        $address = $data['address'];
        $this->assertIsArray($address);
        $this->assertIsInt($address['id']);
        $this->assertIsString($address['city']);
        $this->assertIsString($address['street']);
        $this->assertIsString($address['house']);
        $this->assertTrue($address['is_default']);

        $this->assertIsString($data['created_at']);
        $this->assertIsString($data['status']);
    }

    public function testReturnsExpectedOrderData(): void
    {
        $response = $this->getResponse($this->userOrder->id);

        $data = $response->json('data');

        $this->assertEquals($this->userOrder->id, $data['id']);

        $expectedVariantNames = $this->randomVariants->pluck('name')->sort()->values();
        $returnedVariantNames = collect($data['products'])->pluck('variant_name')->sort()->values();
        $this->assertEquals($expectedVariantNames, $returnedVariantNames);

        $expectedTotal = 0.0;
        foreach ($this->randomVariants as $variant) {
            $expectedTotal += $variant->price;
        }

        $this->assertEquals(round($expectedTotal, 2), $data['total']);

        $this->assertEquals($this->userOrder->address->id, $data['address']['id']);

        $this->assertEquals($this->userOrder->created_at->translatedFormat('d F Yг. H:i'), $data['created_at']);
    }

    public function testFailsIfOrderDoesNotBelongToUser(): void
    {
        $newUser = UserHelper::createUser();
        $newOrder = OrderHelper::createOrdersForUsers($newUser, $this->randomVariants);

        $response = $this->getResponse($newOrder->id);

        $this->checkError($response, 404, 'Заказ не найден.');
    }

    public function testReturns404IfOrderNotFound(): void
    {
        $response = $this->getResponse(999999);

        $this->checkError($response, 404, 'Заказ не найден.');
    }

    public function testFailsWhenUserNotAuthorized(): void
    {
        $response = $this->getResponse($this->userOrder->id, false);

        $this->checkError($response, 401, 'Не авторизован.');
    }

    public function testFailsWhenOrderIdIsNotAnInteger()
    {
        $response = $this->getResponse('stringInsteadOfInt');

        $this->checkError($response, 422, 'Неверный тип параметра.');
    }
}
