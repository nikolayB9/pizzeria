<?php

namespace Admin\Auth;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Admin\AbstractAdminTestCase;
use Tests\Helpers\UserHelper;

class AdminLoginTest extends AbstractAdminTestCase
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

        UserHelper::createUser(1, [
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/admin/login';
    }

    protected function getMethod(): string
    {
        return 'post';
    }

    protected function getResponse(mixed $data): TestResponse
    {
        $route = $this->getRoute();
        $method = $this->getMethod();

        return $this->$method($route, $data, ['referer' => route('login.create')]);
    }

    public function testGuestUserIsRedirectedToMainRouteAfterSuccessfulLogin(): void
    {
        $response = $this->getResponse(['email' => self::ADMIN_EMAIL, 'password' => self::ADMIN_PASSWORD]);

        $response->assertStatus(302);
        $response->assertRedirect(route('main'));

        $this->assertNotNull(session()->getId());
        $this->assertTrue(session()->has('_token'));

        $this->assertAuthenticated();
    }

    public function testAdminIsNotAuthenticatedAfterFailLogin(): void
    {
        $this->getResponse(['email' => self::ADMIN_EMAIL . 'wrong', 'password' => self::ADMIN_PASSWORD . 'wrong']);

        $this->assertFalse(auth()->check());
        $this->assertGuest();
    }

    public function testLoginFailsWithValidEmailAndInvalidPassword(): void
    {
        $response = $this->getResponse([
            'email' => self::ADMIN_EMAIL,
            'password' => self::ADMIN_PASSWORD . 'wrong',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $response->assertRedirect(route('login.create'));
        $this->assertGuest();
    }

    public function testLoginFailsWhenPasswordIsMissing(): void
    {
        $response = $this->getResponse(['email' => self::ADMIN_EMAIL]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
        $response->assertRedirect(route('login.create'));
        $this->assertGuest();
    }

    public function testLoginFailsWhenEmailIsMissing(): void
    {
        $response = $this->getResponse(['password' => self::ADMIN_PASSWORD]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $response->assertRedirect(route('login.create'));
        $this->assertGuest();
    }

    public function testNonAdminUserCannotLogInWithValidCredentials()
    {
        $response = $this->getResponse(['email' => self::USER_EMAIL, 'password' => self::USER_PASSWORD]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $response->assertRedirect(route('login.create'));
        $this->assertGuest();
    }

    public function testAuthenticatedUserIsRedirectedAwayFromLoginRoute(): void
    {
        $this->actingAs($this->admin);

        $response = $this->getResponse(['email' => self::ADMIN_EMAIL, 'password' => self::ADMIN_PASSWORD]);

        $response->assertStatus(302);
        $response->assertRedirect(route('main'));
    }
}
