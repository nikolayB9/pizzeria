<?php

namespace Api\V1\Profile;

use App\Enums\Error\ErrorMessageEnum;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\UserHelper;

class GetProfilePreviewTest extends AbstractApiTestCase
{
    protected const COUNT_USERS = 3;

    protected Collection $users;
    protected User $authUser;

    protected function setUpTestContext(): void
    {
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/profile/preview';
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(bool $isAuthorized = true): TestResponse
    {
        $this->users = UserHelper::createUser(self::COUNT_USERS);

        if ($isAuthorized) {
            $this->authUser = $this->users->random();
            $this->actingAs($this->authUser);
        }

        $route = $this->getRoute();
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
                'id',
                'name',
            ],
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getResponse();

        $user = $response->json('data');

        $this->assertIsInt($user['id']);
        $this->assertIsString($user['name']);
    }

    public function testReturnsExpectedProfile(): void
    {
        $response = $this->getResponse();

        $user = $response->json('data');

        $this->assertEquals($this->authUser->id, $user['id']);
        $this->assertEquals($this->authUser->name, $user['name']);
    }

    public function testReturns401IfUserNotAuthorized(): void
    {
        $response = $this->getResponse(false);
        $this->checkError($response, 401, ErrorMessageEnum::UNAUTHORIZED->value);
    }
}
