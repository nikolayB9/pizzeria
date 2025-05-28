<?php

namespace Admin\Order;

use App\DTO\Admin\Order\OrderListItemDto;
use App\DTO\Admin\Order\PaginationMetaDto;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Admin\AbstractAdminTestCase;
use Tests\Helpers\OrderHelper;
use Tests\Helpers\UserHelper;

class AdminOrdersTest extends AbstractAdminTestCase
{
    protected const ADMIN_PASSWORD = 'admin_password';
    protected const ADMIN_EMAIL = 'admin@example.com';
    protected const USER_PASSWORD = 'user_password';
    protected const USER_EMAIL = 'user@example.com';

    protected User $admin;

    protected function setUpTestContext(): void
    {
        $this->admin = UserHelper::createUser(1, [
            'email' => self::ADMIN_EMAIL,
            'password' => self::ADMIN_PASSWORD,
            'role' => UserRoleEnum::Admin,
        ]);
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        if ($routeParameter !== false) {
            return "/admin/orders?page=$routeParameter";
        }

        return '/admin/orders';
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(mixed $page = false,
                                   bool  $isAuth = true,
                                   int   $countOrders = 1,
                                   bool  $createOrders = true): TestResponse
    {
        if ($isAuth) {
            $this->actingAs($this->admin);
        }

        if ($createOrders && $countOrders) {
            OrderHelper::createOrders($countOrders);
        }

        $route = $this->getRoute($page);
        $method = $this->getMethod();

        return $this->$method($route);
    }

    public function testOrdersPageLoadsSuccessfully(): void
    {
        $response = $this->getResponse();

        $response->assertStatus(200);
        $response->assertViewIs('admin.order.index');

        $response->assertViewHas('orders');
        $response->assertViewHas('meta');
        $response->assertViewHas('statuses');

        $this->assertAuthenticated();
    }

    public function testIndexViewReturnsExpectedDataTypes(): void
    {
        $response = $this->getResponse(false, true, 10);

        $orders = $response->viewData('orders');
        $this->assertIsArray($orders);
        $this->assertNotEmpty($orders);
        $this->assertContainsOnlyInstancesOf(OrderListItemDto::class, $orders);

        $order = $orders[0];
        $this->assertIsInt($order->id);
        $this->assertIsString($order->created_at);
        $this->assertIsString($order->delivery);
        $this->assertIsNumeric($order->total);
        $this->assertIsString($order->user);
        $this->assertInstanceOf(OrderStatusEnum::class, $order->status);

        $meta = $response->viewData('meta');
        $this->assertInstanceOf(PaginationMetaDto::class, $meta);

        $statuses = $response->viewData('statuses');
        $this->assertContainsOnlyInstancesOf(OrderStatusEnum::class, $statuses);
    }

    public function testIndexViewReturnsExpectedCountOrders(): void
    {
        $ordersPerPage = config('admin.orders_per_page');
        $countOrders = $ordersPerPage * 3;
        $page = 2;

        $response = $this->getResponse($page, true, $countOrders);

        $orders = $response->viewData('orders');

        $this->assertCount($ordersPerPage, $orders);
    }

    public function testReturns403IfGuest(): void
    {
        $response = $this->getResponse(false, false);
        $response->assertStatus(403);
        $this->assertGuest();
    }

    public function testReturns403IfAuthenticatedUserIsNotAdmin(): void
    {
        $nonAdminUser = UserHelper::createUser(1, [
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        $this->actingAs($nonAdminUser);

        $response = $this->getResponse(false, false);

        $response->assertStatus(403);
        $this->assertAuthenticated();
        $this->assertFalse(auth()->user()->isAdmin());
    }
}
