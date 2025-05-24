<?php

namespace Api\V1\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Helpers\UserHelper;
use Tests\Traits\HasAuthContext;

class LoginTest extends AbstractApiTestCase
{
    use HasAuthContext;

    protected const PASSWORD = 'uniquePassword!*_123';

    protected User $user;
    protected Collection $users;

    protected function setUpTestContext(): void
    {
        $this->users = UserHelper::createUser(3);

        $this->user = UserHelper::createUser(
            1,
            ['password' => self::PASSWORD]
        );
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/login';
    }

    protected function getMethod(): string
    {
        return 'post';
    }

    protected function getRequestToSessionStart(): string
    {
        return '/api/v1';
    }

    protected function getResponse(mixed $data): TestResponse
    {
        $route = $this->getRoute();
        $method = $this->getMethod();

        if ($this->sessionId) {
            return $this->withCookie('laravel_session', $this->sessionId)
                ->$method($route, $data, ['Accept' => 'application/json']);
        }

        return $this->$method($route, $data, ['Accept' => 'application/json']);
    }

    public function testLoginReturnsSuccessfulResponse(): void
    {
        $response = $this->getResponse(['email' => $this->user->email, 'password' => self::PASSWORD]);

        $this->checkSuccess($response);
    }

    public function testUserIsAuthenticatedAfterSuccessfulLogin(): void
    {
        $this->getResponse(['email' => $this->user->email, 'password' => self::PASSWORD]);

        $this->assertEquals(auth()->id(), $this->user->id);
    }

    public function testLoginReturnsExpectedJsonStructure(): void
    {
        $response = $this->getResponse(['email' => $this->user->email, 'password' => self::PASSWORD]);

        $response->assertExactJsonStructure([
            'success',
            'data' => [],
            'meta' => [
                'cart_merge',
            ],
        ]);
    }

    public function testLoginDispatchesLoginEvent(): void
    {
        Event::fake();

        $this->getResponse(['email' => $this->user->email, 'password' => self::PASSWORD]);

        Event::assertDispatched(Login::class);
    }

    public function testLoginReturnsCartMergeFalseWhenNoSessionCartExists(): void
    {
        $response = $this->getResponse(['email' => $this->user->email, 'password' => self::PASSWORD]);

        $this->assertFalse($response->json('meta.cart_merge'));
    }

    public function testLoginReturnsCartMergeTrueWhenSessionCartExists(): void
    {
        $category = CategoryHelper::createCategoryOfType();
        $product = ProductHelper::createProductsWithVariantsForCategories(
            $category,
            1,
            1
        );
        $variant = $product->variants()->first();

        $this->setSessionId();
        $auth = $this->getAuthField('session');

        CartHelper::createFromVariantByIdentifier($variant, $auth);

        $response = $this->getResponse(['email' => $this->user->email, 'password' => self::PASSWORD]);

        $this->checkSuccess($response);
        $this->assertTrue($response->json('meta.cart_merge'));
    }

    public function testLoginFailsWithValidEmailAndInvalidPassword(): void
    {
        $response = $this->getResponse(['email' => $this->user->email, 'password' => 'wrongPassword']);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["email" => [0 => "These credentials do not match our records."]],
        );
    }

    public function testLoginFailsWithValidEmailAndPasswordFromDifferentUser(): void
    {
        $otherExistEmail = $this->users->random()->email;

        $response = $this->getResponse(['email' => $otherExistEmail, 'password' => self::PASSWORD]);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["email" => [0 => "These credentials do not match our records."]],
        );
    }

    public function testLoginFailsWhenPasswordIsMissing(): void
    {
        $response = $this->getResponse(['email' => $this->user->email]);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["password" => [0 => "The password field is required."]],
        );
    }

    public function testLoginFailsWhenEmailIsMissing(): void
    {
        $response = $this->getResponse(['password' => self::PASSWORD]);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["email" => [0 => "The email field is required."]],
        );
    }

    public function testLoginFailsWithInvalidEmailFormat(): void
    {
        $response = $this->getResponse(['email' => 'not-an-email', 'password' => self::PASSWORD]);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["email" => [0 => "The email field must be a valid email address."]],
        );
    }
}
