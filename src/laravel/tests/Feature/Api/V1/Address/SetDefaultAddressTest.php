<?php

namespace Api\V1\Address;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\AddressHelper;
use Tests\Helpers\UserHelper;

class SetDefaultAddressTest extends AbstractApiTestCase
{
    protected const COUNT_USER_ADDRESSES = 5;

    protected User $user;
    protected Collection $addresses;

    protected function setUpTestContext(): void
    {
        $this->user = UserHelper::createUser();

        $this->addresses = AddressHelper::createAddresses($this->user->id, self::COUNT_USER_ADDRESSES);
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return "/api/v1/addresses/$routeParameter/default";
    }

    protected function getMethod(): string
    {
        return 'patch';
    }

    protected function getResponse(mixed $addressId,
                                   bool  $isAuth = true): TestResponse
    {
        if ($isAuth) {
            $this->actingAs($this->user);
        }

        $route = $this->getRoute($addressId);
        $method = $this->getMethod();

        return $this->$method($route, [], ['Accept' => 'application/json']);
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $response = $this->getResponse($this->addresses->random()->id);

        $this->checkSuccess($response);
    }

    public function testAddressSetDefaultSuccessfully(): void
    {
        $oldDefaultAddress = $this->addresses->filter(fn($address) => $address->is_default === true)->first();

        $notDefaultAddresses = $this->addresses->filter(fn($address) => $address->id !== $oldDefaultAddress->id);

        $newDefaultAddress = $notDefaultAddresses->random();

        $this->assertDatabaseHas('addresses', [
            'id' => $oldDefaultAddress->id,
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $newDefaultAddress->id,
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $this->getResponse($newDefaultAddress->id);

        $this->assertDatabaseHas('addresses', [
            'id' => $oldDefaultAddress->id,
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $newDefaultAddress->id,
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
    }

    public function testReturns404IfRequestedAddressNotFoundForUser(): void
    {
        $otherUser = UserHelper::createUser();
        $otherAddress = AddressHelper::createAddresses($otherUser->id);

        $response = $this->getResponse($otherAddress->id);

        $this->checkError($response, 404, 'Адрес не найден.');
    }

    public function testFailsWhenUserNotAuthorized(): void
    {
        $response = $this->getResponse($this->addresses->random()->id, false);

        $this->checkError($response, 401, 'Не авторизован.');
    }
}
