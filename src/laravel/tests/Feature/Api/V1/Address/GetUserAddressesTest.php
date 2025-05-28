<?php

namespace Api\V1\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\AddressHelper;
use Tests\Helpers\UserHelper;

class GetUserAddressesTest extends AbstractApiTestCase
{
    protected const COUNT_USERS = 3;
    protected const COUNT_ADDRESSES_FOR_EACH_USER = 1;

    protected User $user;
    protected Address|Collection $userAddresses;

    protected function setUpTestContext(): void
    {
        $users = UserHelper::createUser(self::COUNT_USERS);

        foreach ($users as $user) {
            AddressHelper::createAddresses($user->id);
        }

        $this->user = UserHelper::createUser();
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/addresses';
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(bool $createUserOrders = true,
                                   int  $countOrders = 1,
                                   bool $existIsDefault = true,
                                   bool $isAuth = true): TestResponse
    {
        if ($isAuth) {
            $this->actingAs($this->user);
        }

        if ($createUserOrders && $countOrders) {
            if ($existIsDefault) {
                $this->userAddresses = AddressHelper::createAddresses($this->user->id, $countOrders);
            } else {
                $this->userAddresses = AddressHelper::createAddresses(
                    $this->user->id,
                    $countOrders,
                    1,
                    1,
                    false,
                );
            }
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
                '*' => [
                    'id',
                    'city',
                    'street',
                    'house',
                    'is_default'
                ],
            ],
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getResponse();

        $addresses = $response->json('data');

        $this->assertIsArray($addresses);

        $address = array_pop($addresses);
        $this->assertIsArray($address);
        $this->assertIsInt($address['id']);
        $this->assertIsString($address['city']);
        $this->assertIsString($address['street']);
        $this->assertIsString($address['house']);
        $this->assertIsBool($address['is_default']);
    }

    public function testReturnsOnlyExpectedAddressIds(): void
    {
        $response = $this->getResponse(true, 3);

        $addresses = $response->json('data');
        $returnedIds = collect($addresses)->pluck('id')->sort()->values();

        $expectedIds = $this->userAddresses->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);
    }

    public function testReturnsOneDefaultAddress()
    {
        $response = $this->getResponse(true, 3);

        $addresses = $response->json('data');
        $defaultCount = collect($addresses)
            ->filter(fn($address) => $address['is_default'] === true)
            ->count();

        $this->assertSame(1, $defaultCount);
    }

    public function testReturnsEmptyArrayIfAddressesNotExists(): void
    {
        $response = $this->getResponse(false);

        $this->assertEquals([], $response->json('data'));
    }

    public function testFailsWhenUserNotAuthorized(): void
    {
        $response = $this->getResponse(true, 1, true, false);

        $this->checkError($response, 401, 'Не авторизован.');
    }
}
