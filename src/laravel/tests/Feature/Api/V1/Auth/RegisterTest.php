<?php

namespace Api\V1\Auth;

use Database\Seeders\UserRoleSeeder;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Helpers\UserHelper;
use Tests\Traits\HasAuthContext;

class RegisterTest extends AbstractApiTestCase
{
    use HasAuthContext;

    protected const EMAIL = 'unique.for.test@mail.ru';
    protected const PHONE_NUMBER = '+79998887766';
    protected const PASSWORD = 'uniquePassword!*_123';

    protected array $regData = [
        'name' => 'TestName',
        'phone_number' => self::PHONE_NUMBER,
        'email' => self::EMAIL,
        'password' => self::PASSWORD,
        'password_confirmation' => self::PASSWORD,
        'birth_date' => '1999-01-01',
    ];

    protected function setUpTestContext(): void
    {
        (new UserRoleSeeder())->run();
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/register';
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

    public function testRegisterReturnsSuccessfulResponse(): void
    {
        $response = $this->getResponse($this->regData);

        $this->checkSuccess($response);
    }

    public function testUserIsAuthenticatedAfterSuccessfulRegister(): void
    {
        $this->getResponse($this->regData);

        $user = UserHelper::getUserByData(['email' => self::EMAIL]);

        $this->assertEquals(auth()->id(), $user->id);
    }

    public function testUserIsNotAuthenticatedAfterFailRegister(): void
    {
        $data = $this->regData;
        $data['email'] = 'wrong.email';

        $this->getResponse($data);

        $this->assertFalse(auth()->check());
    }

    public function testRegisterReturnsExpectedJsonStructure(): void
    {
        $response = $this->getResponse($this->regData);

        $response->assertExactJsonStructure([
            'success',
            'data' => [],
            'meta' => [
                'cart_merge',
            ],
        ]);
    }

    public function testRegisterDispatchesLoginEvent(): void
    {
        Event::fake();

        $this->getResponse($this->regData);

        Event::assertDispatched(Login::class);
    }

    public function testRegisterReturnsCartMergeFalseWhenNoSessionCartExists(): void
    {
        $response = $this->getResponse($this->regData);

        $this->assertFalse($response->json('meta.cart_merge'));
    }

    public function testRegisterReturnsCartMergeTrueWhenSessionCartExists(): void
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

        $response = $this->getResponse($this->regData);

        $this->checkSuccess($response);
        $this->assertTrue($response->json('meta.cart_merge'));
    }

    public function testCreateUserAfterSuccessfulRegister(): void
    {
        UserHelper::createUser(3);

        $this->assertDatabaseMissing(
            'users',
            [
                'email' => self::EMAIL,
                'phone_number' => self::PHONE_NUMBER,
            ],
        );

        $this->getResponse($this->regData);

        $this->assertDatabaseHas(
            'users',
            [
                'email' => self::EMAIL,
                'phone_number' => self::PHONE_NUMBER,
            ],
        );
    }

    public function testRegisterFailsWithInvalidPassword(): void
    {
        $data = $this->regData;

        $data['password'] = 0;

        $response = $this->getResponse($data);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            [
                "password" => [
                    "The password field confirmation does not match.",
                    "The password field must be a string.",
                    "The password field must be at least 8 characters.",
                ],
            ]
        );
    }

    public function testRegisterFailsWithExistEmail(): void
    {
        $user = UserHelper::createUser();

        $existEmail = $user->email;
        $data = $this->regData;
        $data['email'] = $existEmail;

        $response = $this->getResponse($data);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["email" => [0 => "The email has already been taken."]],
        );
    }

    public function testRegisterFailsWithExistPhoneNumber(): void
    {
        $user = UserHelper::createUser();

        $existPhoneNumber = $user->phone_number;
        $data = $this->regData;
        $data['phone_number'] = $existPhoneNumber;

        $response = $this->getResponse($data);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["phone_number" => [0 => "The phone number has already been taken."]],
        );
    }

    public function testRegisterFailsWhenPasswordIsMissing(): void
    {
        $data = $this->regData;
        unset($data['password']);

        $response = $this->getResponse($data);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["password" => [0 => "The password field is required."]],
        );
    }

    public function testRegisterFailsWhenEmailIsMissing(): void
    {
        $data = $this->regData;
        unset($data['email']);

        $response = $this->getResponse($data);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["email" => [0 => "The email field is required."]],
        );
    }

    public function testRegisterFailsWithInvalidEmailFormat(): void
    {
        $data = $this->regData;
        $data['email'] = 'wrong.email';

        $response = $this->getResponse($data);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ["email" => [0 => "The email field must be a valid email address."]],
        );
    }
}
