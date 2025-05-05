<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_index_returns_200_ok(): void
    {
        $response = $this->get('/api/v1/products');

        $response->assertStatus(200);
    }

    public function test_index_returns_expected_json_structure(): void
    {
        $response = $this->get('/api/v1/products');

        $response->assertExactJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'slug',
                    'preview_image_url',
                    'has_multiple_variants',
                    'min_price'
                ]
            ],
        ]);
    }

    public function test_index_returns_expected_types(): void
    {
        $response = $this->get('/api/v1/products');

        $data = $response->json('data');

        foreach ($data as $product) {
            $this->assertIsInt($product['id']);
            $this->assertIsString($product['name']);
            $this->assertTrue(is_string($product['description']) || is_null($product['description']));
            $this->assertIsString($product['slug']);
            $this->assertIsString($product['preview_image_url']);
            $this->assertIsBool($product['has_multiple_variants']);
            $this->assertTrue(is_int($product['min_price']) || is_float($product['min_price']) || is_string($product['min_price']));
        }
    }

    public function test_index_returns_created_products()
    {
        Product::factory()->create(['name' => 'Test Pizza', 'slug' => 'test-pizza']);

        $response = $this->get('/api/v1/products');

        $response->assertJsonFragment(['name' => 'Test Pizza', 'slug' => 'test-pizza']);
    }
}
