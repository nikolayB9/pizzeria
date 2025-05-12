<?php

namespace Api\V1\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Helpers\ProductHelper;
use Tests\TestCase;

class GetProductBySlugTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = ProductHelper::createProduct(1, true);
    }

    protected function getProductBySlugResponse(): TestResponse
    {
        return $this->get("/api/v1/products/{$this->product->slug}");
    }

    public function testReturnsSuccessfulResponse(): void
    {
        $response = $this->getProductBySlugResponse();
        $response->assertStatus(200);
    }

    public function testReturnsExpectedJsonStructure(): void
    {
        $response = $this->getProductBySlugResponse();

        $response->assertExactJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'detail_image_url',
                'variants' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'old_price',
                        'parameters' => [
                            '*' => [
                                'id',
                                'name',
                                'value',
                                'unit',
                                'is_shared',
                                'group',
                            ]
                        ],
                    ]
                ],
            ],
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getProductBySlugResponse();
        $product = $response->json('data');

        $this->assertIsInt($product['id']);
        $this->assertIsString($product['name']);
        $this->assertTrue(is_string($product['description']) || is_null($product['description']));
        $this->assertIsString($product['detail_image_url']);
        $this->assertIsArray($product['variants']);
        $this->assertNotEmpty($product['variants']);

        $variant = $product['variants'][0];
        $this->assertIsInt($variant['id']);
        $this->assertIsString($variant['name']);
        $this->assertTrue(is_int($variant['price']) || is_float($variant['price']) || is_string($variant['price']));
        $this->assertTrue(is_null($variant['old_price']) || is_int($variant['old_price']) || is_float($variant['old_price']) || is_string($variant['old_price']));
        $this->assertIsArray($variant['parameters']);
        $this->assertNotEmpty($variant['parameters']);

        $parameter = $variant['parameters'][0];
        $this->assertIsInt($parameter['id']);
        $this->assertIsString($parameter['name']);
        $this->assertIsString($parameter['value']);
        $this->assertTrue(is_string($parameter['unit']) || is_null($parameter['unit']));
        $this->assertIsBool($parameter['is_shared']);
        $this->assertIsInt($parameter['group']);
    }

    public function testReturnsCorrectProductBySlug()
    {
        ProductHelper::createProduct(3);

        $response = $this->getProductBySlugResponse();
        $responseProduct = $response->json('data');

        $this->assertEquals($this->product->id, $responseProduct['id']);
        $this->assertEquals($this->product->name, $responseProduct['name']);
    }

    public function testReturns404ForNonExistentSlug()
    {
        $nonExistentSlug = 'non-existent-slug';
        $response = $this->get("/api/v1/products/$nonExistentSlug");

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Продукт не найден'
        ]);
    }
}
