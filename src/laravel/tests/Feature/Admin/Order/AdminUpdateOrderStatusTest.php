<?php

namespace Admin\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Enums\User\UserRoleEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Admin\AbstractAdminTestCase;
use Tests\Helpers\OrderHelper;
use Tests\Helpers\UserHelper;

class AdminUpdateOrderStatusTest extends AbstractAdminTestCase
{
    protected const COUNT_ORDERS = 5;
    protected const ADMIN_PASSWORD = 'admin_password';
    protected const ADMIN_EMAIL = 'admin@example.com';
    protected const USER_PASSWORD = 'user_password';
    protected const USER_EMAIL = 'user@example.com';

    protected User $admin;
    protected Order $order;
    protected Collection $orders;
    protected OrderStatusEnum $randomStatus;

    protected function setUpTestContext(): void
    {
        $this->orders = OrderHelper::createOrders(self::COUNT_ORDERS);

        $this->order = $this->orders->random();

        $this->admin = UserHelper::createUser(1, [
            'email' => self::ADMIN_EMAIL,
            'password' => self::ADMIN_PASSWORD,
            'role' => UserRoleEnum::Admin,
        ]);

        $this->randomStatus = Arr::random(OrderStatusEnum::cases());
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return "/admin/orders/$routeParameter/status";
    }

    protected function getMethod(): string
    {
        return 'patch';
    }

    protected function getResponse(mixed $orderId,
                                   mixed $data,
                                   bool  $isAuth = true): TestResponse
    {
        if ($isAuth) {
            $this->actingAs($this->admin);
        }

        $route = $this->getRoute($orderId);
        $method = $this->getMethod();

        return $this->$method($route, $data);
    }

    public function testUpdateStatusSuccessfully(): void
    {
        $currentStatus = $this->order->status;

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => $currentStatus->value,
        ]);

        $otherStatus = collect(OrderStatusEnum::cases())
            ->reject(fn($case) => $case === $currentStatus)
            ->random();

        $response = $this->getResponse($this->order->id, ['status' => $otherStatus->value]);

        $response->assertStatus(200);
        $this->assertEquals('Статус успешно обновлен', $response->json('message'));
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => $otherStatus->value,
        ]);
    }

    public function testFailsIfOrderNotFound()
    {
        $notExistId = $this->orders->max('id') + 1;

        $response = $this->getResponse($notExistId, ['status' => $this->randomStatus->value]);

        $response->assertStatus(404);
        $this->assertEquals("Заказ с ID [$notExistId] не найден.", $response->json('error'));
    }

    public function testFailsIfStatusIsInvalid()
    {
        $currentStatus = $this->order->status;

        $max = max(array_map(fn($case) => $case->value, OrderStatusEnum::cases()));
        $invalidStatus = $max + 1;

        $response = $this->getResponse($this->order->id, ['status' => $invalidStatus]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => $currentStatus->value,
        ]);
    }

    public function testReturns403IfGuest(): void
    {
        $response = $this->getResponse(
            $this->order->id,
            ['status' => $this->randomStatus->value],
            false
        );

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

        $response = $this->getResponse(
            $this->order->id,
            ['status' => $this->randomStatus->value],
            false
        );

        $response->assertStatus(403);
        $this->assertAuthenticated();
        $this->assertFalse(auth()->user()->isAdmin());
    }
}
