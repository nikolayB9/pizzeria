<?php

namespace Api\V1\Product;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\TestCase;

class GetProductsByCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Collection $products;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = CategoryHelper::createCategoryOfType();
        $this->products = ProductHelper::createProductsOfCategory($this->category);
    }

    protected function getProductsByCategoryResponse(): TestResponse
    {
        return $this->get("/api/v1/products/category/{$this->category->slug}");
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $response = $this->getProductsByCategoryResponse();
        $response->assertStatus(200);
    }

    public function testReturnsExpectedJsonStructure(): void
    {
        $response = $this->getProductsByCategoryResponse();

        $response->assertExactJsonStructure([
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
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getProductsByCategoryResponse();
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

    public function testResponseIncludesOnlyExpectedCategoryProducts()
    {
        $otherCategory = Category::factory()->create();

        Product::factory(3)
            ->hasAttached($otherCategory)
            ->withVariants()
            ->withImages()
            ->create();

        $response = $this->getProductsByCategoryResponse();
        $returnedIds = collect($response->json('data'))->pluck('id')->sort()->values();
        $expectedIds = $this->products->pluck('id')->sort()->values();

        $this->assertEquals($expectedIds, $returnedIds);
    }

    public function testReturns404ForNonExistentCategorySlug()
    {
        $nonExistentSlug = 'non-existent-slug';
        $response = $this->get("/api/v1/products/category/$nonExistentSlug");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Категория не найдена'
        ]);
    }
}
