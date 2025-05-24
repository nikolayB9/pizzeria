<?php

namespace Api\V1\City;

use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CityHelper;

class GetStreetsByCityIdTest extends AbstractApiTestCase
{
    protected const COUNT_CITIES = 3;
    protected const COUNT_SREETS = 30;

    protected Collection $cities;

    protected function setUpTestContext(): void
    {
        $this->cities = CityHelper::createCities(self::COUNT_CITIES);
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return "/api/v1/cities/$routeParameter/streets";
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(mixed $cityId, bool $createStreets = true): TestResponse
    {
        if ($createStreets) {
            foreach ($this->cities as $city) {
                CityHelper::createStreetsForCity($city->id, self::COUNT_SREETS);
            }
        }

        $route = $this->getRoute($cityId);
        $method = $this->getMethod();

        return $this->$method($route, ['Accept' => 'application/json']);
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $city = $this->cities->random();

        $response = $this->getResponse($city->id);

        $this->checkSuccess($response);
    }

    public function testReturnsExpectedJsonStructure(): void
    {
        $city = $this->cities->random();
        $response = $this->getResponse($city->id);

        $response->assertExactJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                ],
            ],
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $city = $this->cities->random();
        $response = $this->getResponse($city->id);

        $streets = $response->json('data');
        $street = array_pop($streets);

        $this->assertIsInt($street['id']);
        $this->assertIsString($street['name']);
    }

    public function testResponseIncludesOnlyExpectedStreets(): void
    {
        $city = $this->cities->random();
        $response = $this->getResponse($city->id);

        $returnedIds = collect($response->json('data'))->pluck('id')->sort()->values();
        $expectedIds = $city->streets->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);
    }

    public function testReturnsEmptyArrayIfStreetsNotExist(): void
    {
        $city = $this->cities->random();
        $response = $this->getResponse($city->id, false);

        $this->checkSuccess($response);
        $this->assertEquals([], $response->json('data'));
    }

    public function testFailsWhenCityDoesNotExist(): void
    {
        $maxId = $this->cities->max('id') + 1;
        $response = $this->getResponse($maxId);

        $this->checkError($response, 404, 'Город не найден.');
    }

    public function testFailsWhenCityIdIsNotAnInteger(): void
    {
        $response = $this->getResponse('stringInsteadofInt');

        $this->checkError($response, 422, 'Неверный тип параметра.');
    }
}
