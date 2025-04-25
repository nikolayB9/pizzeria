<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserRequestsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_get_users_list(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertJsonIsArray();
        $response->assertExactJsonStructure([
           '*' => ['id', 'name', 'email']
        ]);
    }
}
