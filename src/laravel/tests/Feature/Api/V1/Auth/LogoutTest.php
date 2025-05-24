<?php

namespace Api\V1\Auth;

use App\Enums\Error\ErrorMessageEnum;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\UserHelper;

class LogoutTest extends AbstractApiTestCase
{
    protected User $user;

    protected function setUpTestContext(): void
    {
        $this->user = UserHelper::createUser();
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/logout';
    }

    protected function getMethod(): string
    {
        return 'post';
    }

    protected function getResponse(): TestResponse
    {
        $route = $this->getRoute();
        $method = $this->getMethod();

        return $this->$method($route, [], ['Accept' => 'application/json']);
    }

    public function testLogoutReturnsSuccessfulResponse(): void
    {
        $this->actingAs($this->user);

        $response = $this->getResponse();

        $this->checkSuccess($response);
    }

    public function testUserIsNotAuthenticatedAfterSuccessfulLogout(): void
    {
        $this->actingAs($this->user);
        $this->assertAuthenticated();

        $this->getResponse();

        $this->assertFalse(auth()->check());
        $this->assertGuest();
    }

    public function testSessionIdIsRegeneratedAfterSuccessfulLogout(): void
    {
        $this->actingAs($this->user);
        $sessionIdBefore = session()->getId();

        $this->getResponse();

        $sessionIdAfter = session()->getId();
        $this->assertNotEquals($sessionIdBefore, $sessionIdAfter);
    }

    public function testLogoutFailsWhenUserIsNotAuthenticated(): void
    {
        $response = $this->getResponse();

        $this->checkError($response, 401, ErrorMessageEnum::UNAUTHORIZED->value);
    }
}
