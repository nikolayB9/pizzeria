<?php

namespace Api\V1\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\AddressHelper;
use Tests\Helpers\OrderHelper;
use Tests\Helpers\UserHelper;

class DeleteUserAddressTest extends AbstractApiTestCase
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
        return "/api/v1/addresses/$routeParameter";
    }

    protected function getMethod(): string
    {
        return 'delete';
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

    public function testAddressDeleteSuccessfully(): void
    {
        $address = $this->addresses->random();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'user_id' => $this->user->id,
        ]);

        $this->getResponse($address->id);

        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function testDeletesDefaultAddressAndSetsNewDefaultIfExists(): void
    {
        $defaultAddress = $this->user->defaultAddress;

        $this->assertDatabaseHas('addresses', [
            'id' => $defaultAddress->id,
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $this->assertEquals(
            1,
            Address::where('user_id', $this->user->id)
                ->where('is_default', true)
                ->count()
        );

        $this->getResponse($defaultAddress->id);

        $this->assertDatabaseMissing('addresses', [
            'id' => $defaultAddress->id,
            'user_id' => $this->user->id,
        ]);

        $newDefaultAddress = $this->user->latestAddress;

        $this->assertDatabaseHas('addresses', [
            'id' => $newDefaultAddress->id,
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $this->assertNotEquals($defaultAddress->id, $newDefaultAddress->id);
    }

    public function testAddressNotDeletedIfLinkedToOrderAndUserIdIsSetToNull(): void
    {
        $defaultAddress = $this->user->defaultAddress;

        $this->assertDatabaseHas('addresses', [
            'id' => $defaultAddress->id,
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        OrderHelper::createOrdersForUsers($this->user,
            null,
            1,
            false,
            false,
        );

        $this->getResponse($defaultAddress->id);

        $this->assertDatabaseHas('addresses', [
            'id' => $defaultAddress->id,
            'user_id' => null,
            'is_default' => false,
        ]);

        $newDefaultAddress = $this->user->latestAddress;

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
