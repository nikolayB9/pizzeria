<?php

namespace Api\V1\Address;

use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\AddressHelper;
use Tests\Helpers\UserHelper;

class GetUserAddressByIdTest extends AbstractApiTestCase
{
    protected const COUNT_USERS = 3;
    protected const COUNT_ADDRESSES_FOR_EACH_USER = 1;

    protected User $user;

    protected function setUpTestContext(): void
    {
        $users = UserHelper::createUser(self::COUNT_USERS);

        foreach ($users as $user) {
            AddressHelper::createAddresses($user->id, self::COUNT_ADDRESSES_FOR_EACH_USER);
        }

        $this->user = UserHelper::createUser();
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return "/api/v1/addresses/$routeParameter";
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(mixed $addressId,
                                   bool  $isAuth = true): TestResponse
    {
        if ($isAuth) {
            $this->actingAs($this->user);
        }

        $route = $this->getRoute($addressId);
        $method = $this->getMethod();

        return $this->$method($route, ['Accept' => 'application/json']);
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $address = AddressHelper::createAddresses($this->user->id);

        $response = $this->getResponse($address->id);

        $this->checkSuccess($response);
    }

    public function testReturnsExpectedJsonStructure(): void
    {
        $address = AddressHelper::createAddresses($this->user->id);

        $response = $this->getResponse($address->id);

        $response->assertExactJsonStructure([
            'success',
            'data' => [
                'id',
                'city_id',
                'street_id',
                'house',
                'entrance',
                'floor',
                'flat',
                'intercom_code',
                'is_default'
            ],
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $address = AddressHelper::createAddresses($this->user->id);

        $response = $this->getResponse($address->id);

        $address = $response->json('data');

        $this->assertIsArray($address);

        $this->assertIsInt($address['id']);
        $this->assertIsInt($address['city_id']);
        $this->assertIsInt($address['street_id']);
        $this->assertIsString($address['house']);
        $this->assertTrue(is_string($address['entrance']) || is_null($address['entrance']));
        $this->assertTrue(is_string($address['floor']) || is_null($address['floor']));
        $this->assertTrue(is_string($address['flat']) || is_null($address['flat']));
        $this->assertTrue(is_string($address['intercom_code']) || is_null($address['intercom_code']));
        $this->assertIsBool($address['is_default']);
    }

    public function testReturnsExpectedAddress(): void
    {
        $addresses = AddressHelper::createAddresses($this->user->id, 3);
        $address = $addresses->random();

        $response = $this->getResponse($address->id);

        $returnedAddress = $response->json('data');

        $this->assertEquals($address->city_id, $returnedAddress['city_id']);
        $this->assertEquals($address->street_id, $returnedAddress['street_id']);
        $this->assertEquals($address->house, $returnedAddress['house']);
        $this->assertEquals($address->flat, $returnedAddress['flat']);
    }

    public function testReturns404IfRequestedAddressNotFoundForUser(): void
    {
        $response = $this->getResponse(1);

        $this->checkError($response, 404, 'Адрес не найден.');
    }

    public function testFailsWhenUserNotAuthorized(): void
    {
        $address = AddressHelper::createAddresses($this->user->id);

        $response = $this->getResponse($address->id, false);

        $this->checkError($response, 401, 'Не авторизован.');
    }
}
