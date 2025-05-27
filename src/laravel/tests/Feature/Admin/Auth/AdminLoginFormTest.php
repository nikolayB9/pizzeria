<?php

namespace Admin\Auth;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Admin\AbstractAdminTestCase;
use Tests\Helpers\UserHelper;

class AdminLoginFormTest extends AbstractAdminTestCase
{
    protected const ADMIN_PASSWORD = 'admin_password';
    protected const ADMIN_EMAIL = 'admin@example.com';

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
        return '/admin/login';
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(): TestResponse
    {
        $route = $this->getRoute();
        $method = $this->getMethod();

        return $this->$method($route);
    }

    public function testLoginPageLoadsSuccessfully(): void
    {
        $response = $this->getResponse();

        $response->assertStatus(200);
        $response->assertViewIs('admin.auth.login');

        $this->assertGuest();
    }

    public function testAuthenticatedUserIsRedirectedFromLoginPage(): void
    {
        $this->actingAs($this->admin);

        $response = $this->getResponse();

        $response->assertRedirect(route('main'));
    }
}
