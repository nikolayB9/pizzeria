<?php

namespace Api\V1\Order;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\OrderHelper;
use Tests\Helpers\ProductHelper;
use Tests\Helpers\UserHelper;
use Tests\Traits\HasAuthContext;

class GetUserOrdersTest extends AbstractApiTestCase
{
    use HasAuthContext;

    protected const COUNT_CATEGORIES = 3;
    protected const COUNT_PRODUCTS_IN_CATEGORY = 3;
    protected const COUNT_VARIANTS_IN_PRODUCT = 3;
    protected const COUNT_RANDOM_VARIANTS = 3;
    protected const COUNT_USERS = 3;
    protected const COUNT_ORDERS_FOR_EACH_USER = 1;

    protected Collection $randomVariants;
    protected Order|Collection $userOrders;

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

        OrderHelper::createOrdersForUsers(
            $users,
            $allVariants,
            self::COUNT_ORDERS_FOR_EACH_USER,
            true,
        );
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        if ($routeParameter) {
            return "/api/v1/orders?page=$routeParameter";
        }

        return '/api/v1/orders';
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getRequestToSessionStart(): string
    {
        return '/api/v1';
    }

    protected function getResponse(bool $createUserOrders = true,
                                   int  $countOrders = 1,
                                   bool $randomCountVariants = false,
                                   bool $isAuth = true,
                                   int  $page = null): TestResponse
    {
        if ($isAuth) {
            $this->setUserId();
        } else {
            $this->setSessionId();
        }

        if ($createUserOrders && $isAuth) {
            $user = UserHelper::getUserByData(['id' => $this->userId]);
            $this->userOrders = OrderHelper::createOrdersForUsers(
                $user,
                $this->randomVariants,
                $countOrders,
                $randomCountVariants,
            );
        }

        $route = $this->getRoute($page);
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
                '*' => [
                    'id',
                    'created_at',
                    'address' => [
                        'id',
                        'city',
                        'street',
                        'house',
                        'is_default'
                    ],
                    'total',
                    'status',
                    'product_previews' => [
                        '*' => [
                            'url',
                        ]
                    ],
                ],
            ],
            'meta' => [
                'current_page',
                'next_page_url',
                'prev_page_url',
            ],
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getResponse();

        $data = $response->json('data');

        $order = array_pop($data);
        $this->assertIsArray($order);
        $this->assertIsString($order['created_at']);

        $address = $order['address'];
        $this->assertIsArray($address);
        $this->assertIsInt($address['id']);
        $this->assertIsString($address['city']);
        $this->assertIsString($address['street']);
        $this->assertIsString($address['house']);
        $this->assertTrue($address['is_default']);

        $this->assertIsNumeric($order['total']);
        $this->assertIsString($order['status']);

        $previews = $order['product_previews'];
        $this->assertIsArray($previews);

        $preview = array_pop($previews);
        $this->assertIsArray($preview);
        $this->assertIsString($preview['url']);

        $meta = $response->json('meta');
        $this->assertIsArray($meta);
        $this->assertIsInt($meta['current_page']);
        $this->assertTrue(is_string($meta['next_page_url']) || is_null($meta['next_page_url']));
        $this->assertTrue(is_string($meta['prev_page_url']) || is_null($meta['prev_page_url']));
    }

    public function testReturnsOnlyExpectedOrderIdsOnSecondPage(): void
    {
        $ordersPerPage = config('user.orders_per_page');
        $countOrders = $ordersPerPage * 3;
        $page = 2;

        $response = $this->getResponse(true, $countOrders, true, true, $page);

        $data = $response->json('data');
        $returnedIds = collect($data)->pluck('id')->values();

        $expectedIds = $this->userOrders->sortByDesc('created_at')
            ->pluck('id')
            ->slice((($page - 1) * $ordersPerPage), $ordersPerPage)
            ->values();

        $this->assertEquals($expectedIds, $returnedIds);
        $this->assertEquals($page, $response->json('meta.current_page'));
    }

    public function testReturnsEmptyArrayIfOrdersNotExists(): void
    {
        $response = $this->getResponse(false);

        $this->assertEquals([], $response->json('data'));
    }

    public function testReturnsExpectedAddressInOrder(): void
    {
        $response = $this->getResponse();
        $data = $response->json('data');
        $order = array_pop($data);
        $returnedAddress = $order['address'];

        $userWithAddress = UserHelper::getUserByData(['id' => $this->userId], ['defaultAddress']);
        $expectedAddress = $userWithAddress->defaultAddress;

        $this->assertEquals($expectedAddress->id, $returnedAddress['id']);
        $this->assertEquals($expectedAddress->city->name, $returnedAddress['city']);
        $this->assertEquals($expectedAddress->street->name, $returnedAddress['street']);
        $this->assertEquals($expectedAddress->house, $returnedAddress['house']);
    }

    public function testFailsWhenUserNotAuthorized(): void
    {
        $response = $this->getResponse(false, 1, false, false);

        $this->checkError($response, 401, 'Не авторизован.');
    }
}
