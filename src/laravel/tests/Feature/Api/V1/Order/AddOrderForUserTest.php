<?php

namespace Api\V1\Order;

use App\Enums\Error\ErrorMessageEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Models\Address;
use App\Models\User;
use Database\Seeders\OrderStatusSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\AddressHelper;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Helpers\UserHelper;

class AddOrderForUserTest extends AbstractApiTestCase
{
    protected const COUNT_CATEGORIES = 3;
    protected const COUNT_PRODUCTS_IN_CATEGORY = 1;
    protected const COUNT_VARIANTS_IN_PRODUCT = 3;
    protected const NOW = '2025-01-01 15:00:00';

    protected Collection $variants;
    protected User $user;
    protected ?Address $defaultAddress = null;

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

        $this->user = UserHelper::createUser();

        (new OrderStatusSeeder())->run();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Carbon::setTestNow();
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/orders';
    }

    protected function getMethod(): string
    {
        return 'post';
    }

    protected function getResponse(mixed $requestData = null,
                                   bool  $createRequestData = true,
                                   bool  $createDefaultAddress = true,
                                   bool  $createCart = true,
                                   bool  $isAuth = true): TestResponse
    {
        if (!$requestData && $createRequestData) {
            $minDeliveryLeadTime = config('order.min_delivery_lead_time');

            $time = Carbon::parse(self::NOW)->addMinutes($minDeliveryLeadTime);

            if ($time->minute > 0) {
                $time->addHour()->setTime($time->hour, 0);
            }

            $formatted = $time->format('H:i');

            $requestData = [
                'delivery_time' => $formatted,
                'comment' => null,
            ];
        }

        if ($isAuth && $createDefaultAddress) {
            $this->defaultAddress = AddressHelper::createAddresses($this->user->id);
        }

        if ($isAuth && $createCart) {
            CartHelper::createFromVariantByIdentifier(
                $this->variants,
                ['field' => 'user_id', 'value' => $this->user->id],
            );
        }

        if ($isAuth) {
            $this->actingAs($this->user);
        }

        Carbon::setTestNow(Carbon::parse(self::NOW));

        $route = $this->getRoute();
        $method = $this->getMethod();

        return $this->$method($route, $requestData, ['Accept' => 'application/json']);
    }

    protected function checkSuccess(TestResponse $response,
                                    mixed        $data = [],
                                    array        $meta = [],
                                    int          $status = 200): void
    {
        parent::checkSuccess($response, $data, $meta, 201);
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $response = $this->getResponse();

        $this->checkSuccess($response);
    }

    public function testOrderIsCreatedSuccessfully()
    {
        $this->getResponse();

        $orderData = [
            'user_id' => $this->user->id,
            'address_id' => $this->defaultAddress->id,
            'status' => OrderStatusEnum::CREATED->value,
        ];

        $this->assertDatabaseHas('orders', $orderData);

        $order = $this->user->orders->first();

        $product = $this->variants->random();
        $productData = [
            'order_id' => $order->id,
            'product_variant_id' => $product->id,
            'qty' => 1,
            'price' => $product->price,
        ];

        $this->assertDatabaseHas('order_product', $productData);

        $expectedCount = $this->variants->count();
        $this->assertDatabaseCount('order_product', $expectedCount);
    }

    public function testFailsIfUserHasNoDefaultAddress()
    {
        $response = $this->getResponse(null, true, false);

        $this->checkError(
            $response,
            404,
            'Не найден дефолтный адрес пользователя.',
        );
    }

    public function testFailsIfDeliveryTimeIsLessThanMinimumLeadTime()
    {
        $minDeliveryLeadTime = config('order.min_delivery_lead_time');
        $time = $minDeliveryLeadTime - 10;

        $lessThenMinTime = Carbon::parse(self::NOW)->addMinutes($time);

        $formatted = $lessThenMinTime->format('H:i');

        $requestData = [
            'delivery_time' => $formatted,
            'comment' => null,
        ];

        $response = $this->getResponse($requestData, false);

        $this->checkError(
            $response,
            422,
            "Время доставки должно быть не менее чем через $minDeliveryLeadTime минут.",
        );
    }

    public function testFailsIfCartIsEmpty()
    {
        $response = $this->getResponse(null, true, true, false);

        $this->checkError(
            $response,
            422,
            'Корзина пуста, невозможно создать заказ.',
        );
    }

    public function testFailsWhenRequestDataIsInvalid()
    {
        $invalidData = [
            'delivery_time' => '12-30',
            'comment' => ['array'],
        ];

        $response = $this->getResponse($invalidData, false);

        $this->checkError(
            $response,
            422,
            ErrorMessageEnum::VALIDATION->value,
            [
                "delivery_time" => ["The delivery time field must match the format H:i."],
                "comment" => ["The comment field must be a string."],
            ],
        );
    }

    public function testFailsWhenRequiredFieldIsMissing()
    {
        $invalidData = [];

        $response = $this->getResponse($invalidData, false);

        $this->checkError(
            $response,
            422,
            ErrorMessageEnum::VALIDATION->value,
            [
                "delivery_time" => ["The delivery time field is required."],
                "comment" => ["The comment field must be present."],
            ],
        );
    }

    public function testFailsWhenUserNotAuthorized(): void
    {
        $response = $this->getResponse(
            null,
            true,
            true,
            true,
            false,
        );

        $this->checkError($response, 401, 'Не авторизован.');
    }
}
