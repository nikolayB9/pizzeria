<?php

namespace Api\V1\City;

use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CityHelper;

class GetCitiesTest extends AbstractApiTestCase
{
    protected const COUNT_CITIES = 3;

    protected Collection $cities;

    protected function setUpTestContext(): void
    {
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/cities';
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(bool $createCities = true): TestResponse
    {
        if ($createCities) {
            $this->cities = CityHelper::createCities(self::COUNT_CITIES);
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
                    'name',
                ],
            ],
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getResponse();

        $cities = $response->json('data');
        $city = array_pop($cities);

        $this->assertIsInt($city['id']);
        $this->assertIsString($city['name']);
    }

    public function testResponseIncludesOnlyExpectedCities(): void
    {
        $response = $this->getResponse();

        $returnedIds = collect($response->json('data'))->pluck('id')->sort()->values();
        $expectedIds = $this->cities->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);
    }

    public function testReturnsEmptyArrayIfCitiesNotExist(): void
    {
        $response = $this->getResponse(false);

        $this->checkSuccess($response);
        $this->assertEquals([], $response->json('data'));
    }
}
