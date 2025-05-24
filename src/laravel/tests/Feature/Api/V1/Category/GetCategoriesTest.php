<?php

namespace Api\V1\Category;

use App\Enums\Category\CategoryTypeEnum;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CategoryHelper;

class GetCategoriesTest extends AbstractApiTestCase
{
    protected const COUNT_PRODUCT_CATEGORIES = 2;
    protected const COUNT_MARKETING_CATEGORIES = 2;

    protected Collection $categories;

    protected function setUpTestContext(): void
    {
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/categories';
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(bool $createCategories = true): TestResponse
    {
        if ($createCategories) {
            $productCategories = $this->category = CategoryHelper::createCategoryOfType(
                self::COUNT_PRODUCT_CATEGORIES,
            );

            $marketingCategories = $this->category = CategoryHelper::createCategoryOfType(
                self::COUNT_MARKETING_CATEGORIES,
                CategoryTypeEnum::Marketing,
            );

            $this->categories = $productCategories->merge($marketingCategories);
        }

        $route = $this->getRoute();
        $method = $this->getMethod();

        return $this->$method($route);
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
                    'slug',
                    'type_slug',
                ]
            ],
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getResponse();

        $categories = $response->json('data');
        $category = array_pop($categories);

        $this->assertIsInt($category['id']);
        $this->assertIsString($category['name']);
        $this->assertIsString($category['slug']);
        $this->assertIsString($category['type_slug']);
    }

    public function testResponseIncludesOnlyExpectedCategories(): void
    {
        $response = $this->getResponse();

        $returnedIds = collect($response->json('data'))->pluck('id')->sort()->values();
        $expectedIds = $this->categories->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);
    }

    public function testReturnsEmptyArrayIfCategoriesNotExist(): void
    {
        $response = $this->getResponse(false);

        $this->checkSuccess($response);
        $this->assertEquals([], $response->json('data'));
    }
}
