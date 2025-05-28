<?php

namespace Api\V1\Address;

use App\Models\Address;
use App\Models\City;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\AddressHelper;
use Tests\Helpers\CityHelper;
use Tests\Helpers\UserHelper;

class UpdateUserAddressTest extends AbstractApiTestCase
{
    protected const COUNT_STREETS_FOR_CITY = 10;

    protected User $user;
    protected Address $address;
    protected City $city;
    protected Collection $streets;
    protected array $validatedData;

    protected function setUpTestContext(): void
    {
        $this->user = UserHelper::createUser();

        $this->city = CityHelper::createCities();
        $this->streets = CityHelper::createStreetsForCity($this->city->id, self::COUNT_STREETS_FOR_CITY);

        $this->address = AddressHelper::createAddresses($this->user->id);

        $this->validatedData = [
            'city_id' => $this->city->id,
            'street_id' => $this->streets->random()->id,
            'house' => (string)rand(1, 100),
            'entrance' => (string)rand(1, 20),
            'floor' => (string)rand(1, 25),
            'flat' => (string)rand(1, 500),
            'intercom_code' => (string)rand(1, 500),
        ];
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return "/api/v1/addresses/$routeParameter";
    }

    protected function getMethod(): string
    {
        return 'patch';
    }

    protected function getResponse(mixed $orderId,
                                   mixed $data,
                                   bool  $isAuth = true): TestResponse
    {
        if ($isAuth) {
            $this->actingAs($this->user);
        }

        $route = $this->getRoute($orderId);
        $method = $this->getMethod();

        return $this->$method($route, $data, ['Accept' => 'application/json']);
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $response = $this->getResponse($this->address->id, $this->validatedData);

        $this->checkSuccess($response);
    }

    public function testAddressUpdateSuccessfully(): void
    {
        $this->assertDatabaseHas('addresses', [
            'id' => $this->address->id,
            'user_id' => $this->user->id,
        ]);

        $this->getResponse($this->address->id, $this->validatedData);

        $this->assertDatabaseHas('addresses', [
            'id' => $this->address->id,
            'user_id' => $this->user->id,
            'city_id' => $this->validatedData['city_id'],
            'street_id' => $this->validatedData['street_id'],
            'house' => $this->validatedData['house'],
            'entrance' => $this->validatedData['entrance'],
            'floor' => $this->validatedData['floor'],
            'flat' => $this->validatedData['flat'],
            'intercom_code' => $this->validatedData['intercom_code'],
        ]);
    }

    public function testReturns404IfRequestedAddressNotFoundForUser(): void
    {
        $otherUser = UserHelper::createUser();
        $otherAddress = AddressHelper::createAddresses($otherUser->id);

        $response = $this->getResponse($otherAddress->id, $this->validatedData);

        $this->checkError($response, 404, 'Адрес не найден.');
    }

    public function testFailsWhenStreetDoesNotBelongToCity()
    {
        $otherCity = CityHelper::createCities();
        $otherStreet = CityHelper::createStreetsForCity($otherCity->id, 1);

        $data = $this->validatedData;
        $data['street_id'] = $otherStreet->id;

        $response = $this->getResponse($this->address->id, $data);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            ['street_id' => ['The selected street id is invalid.']],
        );
    }

    public function testFailsWhenCityNotExists(): void
    {
        $notExistCityId = City::max('id') + 1;
        $data = $this->validatedData;
        $data['city_id'] = $notExistCityId;

        $response = $this->getResponse($this->address->id, $data);

        $this->checkError($response,
            422,
            'Ошибка валидации.',
            [
                'city_id' => ['The selected city id is invalid.'],
                'street_id' => ['The selected street id is invalid.'],
            ],
        );
    }

    public function testFailsWhenUserNotAuthorized(): void
    {
        $response = $this->getResponse($this->address->id, $this->validatedData, false);

        $this->checkError($response, 401, 'Не авторизован.');
    }
}
