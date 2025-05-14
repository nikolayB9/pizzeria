<?php

namespace Api\V1\Product;

use App\Models\Product;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\ProductHelper;

class GetProductBySlugTest extends AbstractApiTestCase
{
    protected const COUNT_PRODUCTS = 1;

    protected Product $product;

    protected function setUpTestContext(): void
    {
        $this->product = ProductHelper::createProduct(self::COUNT_PRODUCTS, true);
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return "/api/v1/products/$routeParameter";
    }

    protected function getMethod(): string
    {
        return 'get';
    }

    protected function getResponse(?string $productSlug = null): TestResponse
    {
        if ($productSlug === null) {
            $productSlug = $this->product->slug;
        }

        $route = $this->getRoute($productSlug);
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
            'meta',
        ]);
    }

    public function testReturnedFieldsHaveExpectedTypes(): void
    {
        $response = $this->getResponse();

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
        $this->assertTrue(is_int($variant['price']) || is_float($variant['price']));
        $this->assertTrue(is_null($variant['old_price']) || is_int($variant['old_price']) || is_float($variant['old_price']));
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

    public function testReturnsCorrectProductBySlug(): void
    {
        ProductHelper::createProduct(3);

        $response = $this->getResponse();

        $product = $response->json('data');

        $this->assertEquals($this->product->id, $product['id']);
        $this->assertEquals($this->product->name, $product['name']);
    }

    public function testReturns404ForNonExistentSlug(): void
    {
        $nonExistentSlug = 'non-existent-slug';

        $response = $this->getResponse($nonExistentSlug);

        $this->checkError($response, 404, 'Продукт не найден.');
    }
}
