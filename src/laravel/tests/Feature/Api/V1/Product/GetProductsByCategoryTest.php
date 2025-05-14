<?php

namespace Api\V1\Product;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;

class GetProductsByCategoryTest extends AbstractApiTestCase
{
    protected const COUNT_CATEGORIES = 1;
    protected const COUNT_PRODUCTS = 3;

    protected Category $category;
    protected Collection $products;

    protected function setUpTestContext(): void
    {
        $this->category = CategoryHelper::createCategoryOfType(self::COUNT_CATEGORIES);
        $this->products = ProductHelper::createProductsWithVariantsForCategories(
            $this->category,
            self::COUNT_PRODUCTS,
        );
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return "/api/v1/products/category/$routeParameter";
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(?string $categorySlug = null): TestResponse
    {
        if ($categorySlug === null) {
            $categorySlug = $this->category->slug;
        }

        $route = $this->getRoute($categorySlug);
        $method = $this->getMethod();

        return $this->$method($route);
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $response = $this->getResponse();
        $response->assertStatus(200);
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
                    'description',
                    'slug',
                    'preview_image_url',
                    'has_multiple_variants',
                    'min_price',
                ]
            ],
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getResponse();
        $products = $response->json('data');
        $product = array_pop($products);

        $this->assertIsInt($product['id']);
        $this->assertIsString($product['name']);
        $this->assertTrue(is_string($product['description']) || is_null($product['description']));
        $this->assertIsString($product['slug']);
        $this->assertIsString($product['preview_image_url']);
        $this->assertIsBool($product['has_multiple_variants']);
        $this->assertTrue(is_int($product['min_price']) || is_float($product['min_price']) || is_string($product['min_price']));
    }

    public function testResponseIncludesOnlyExpectedCategoryProducts(): void
    {
        $otherCategory = CategoryHelper::createCategoryOfType();
        ProductHelper::createProductsWithVariantsForCategories($otherCategory);

        $response = $this->getResponse();
        $returnedIds = collect($response->json('data'))->pluck('id')->sort()->values();
        $expectedIds = $this->products->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);
    }

    public function testReturnsEmptyArrayIfCategoryHasNoProducts(): void
    {
        $categoryWithoutProducts = CategoryHelper::createCategoryOfType();

        $response = $this->getResponse($categoryWithoutProducts->slug);

        $response->assertStatus(200);
        $this->assertEquals([], $response->json('data'));
    }

    public function testReturns404ForNonExistentCategorySlug(): void
    {
        $nonExistentSlug = 'non-existent-slug';
        $response = $this->getResponse($nonExistentSlug);

        $response->assertStatus(404);
        $this->assertEquals('Категория не найдена.', $response->json('message'));
    }
}
