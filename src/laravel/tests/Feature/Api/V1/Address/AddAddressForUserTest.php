<?php

namespace Api\V1\Address;

use App\Models\City;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CityHelper;
use Tests\Helpers\UserHelper;

class AddAddressForUserTest extends AbstractApiTestCase
{
    protected const COUNT_STREETS_FOR_CITY = 10;

    protected User $user;
    protected City $city;
    protected Collection $streets;
    protected array $validatedData;

    protected function setUpTestContext(): void
    {
        $this->user = UserHelper::createUser();

        $this->city = CityHelper::createCities();
        $this->streets = CityHelper::createStreetsForCity($this->city->id, self::COUNT_STREETS_FOR_CITY);

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
        return '/api/v1/addresses';
    }

    protected function getMethod(): string
    {
        return 'post';
    }

    protected function getResponse(mixed $data,
                                   bool  $isAuth = true): TestResponse
    {
        if ($isAuth) {
            $this->actingAs($this->user);
        }

        $route = $this->getRoute();
        $method = $this->getMethod();

        return $this->$method($route, $data, ['Accept' => 'application/json']);
    }

    protected function checkSuccess(TestResponse $response, mixed $data = [], array $meta = [], int $status = 200): void
    {
        parent::checkSuccess($response, $data, $meta, 201);
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $response = $this->getResponse($this->validatedData);

        $this->checkSuccess($response);
    }

    public function testAddressCreateSuccessfully(): void
    {
        $this->getResponse($this->validatedData);

        $this->assertDatabaseHas('addresses', [
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

    public function testFailsWhenStreetDoesNotBelongToCity()
    {
        $otherCity = CityHelper::createCities();
        $otherStreet = CityHelper::createStreetsForCity($otherCity->id, 1);

        $data = $this->validatedData;
        $data['street_id'] = $otherStreet->id;

        $response = $this->getResponse($data);

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

        $response = $this->getResponse($data);

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
        $response = $this->getResponse($this->validatedData, false);

        $this->checkError($response, 401, 'Не авторизован.');
    }
}
