<?php

namespace Admin\Auth;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Admin\AbstractAdminTestCase;
use Tests\Helpers\UserHelper;

class AdminLogoutTest extends AbstractAdminTestCase
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
        return '/admin/logout';
    }

    protected function getMethod(): string
    {
        return 'post';
    }

    protected function getResponse(bool $isAuth = true): TestResponse
    {
        if ($isAuth) {
            $this->actingAs($this->admin);
        }

        $route = $this->getRoute();
        $method = $this->getMethod();

        return $this->$method($route);
    }

    public function testLogoutSuccessfully(): void
    {
        $response = $this->getResponse();

        $response->assertStatus(302);
        $response->assertRedirect(route('login.create'));

        $this->assertGuest();
    }

    public function testReturns403IfGuest()
    {
        $response = $this->getResponse(false);
        $response->assertStatus(403);
        $this->assertGuest();
    }

    public function testReturns403IfAuthenticatedUserIsNotAdmin()
    {
        $nonAdminUser = UserHelper::createUser(1, [
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        $this->actingAs($nonAdminUser);

        $response = $this->getResponse(false);

        $response->assertStatus(403);
        $this->assertAuthenticated();
        $this->assertFalse(auth()->user()->isAdmin());
    }
}
