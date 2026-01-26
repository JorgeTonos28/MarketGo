<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_api_products_index()
    {
        $user = User::factory()->create();

        $category = ProductCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'icon' => 'test',
        ]);

        Product::create([
            'product_category_id' => $category->id,
            'name' => 'Product 1',
            'slug' => 'product-1',
            'unit' => 'kg'
        ]);

        $response = $this->actingAs($user)->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links']);
    }

    public function test_api_shopping_list_create_and_retrieve()
    {
        $user = User::factory()->create();

        // Test Create
        $response = $this->actingAs($user)->postJson('/api/shopping-lists', [
            'name' => 'My List',
            'status' => 'active',
            'items' => [
                [
                    'type' => 'manual',
                    'name' => 'Milk',
                    'unit' => 'L',
                    'quantity' => 1
                ]
            ]
        ]);

        $response->assertStatus(201);
        $listId = $response->json('id');

        // Test Retrieve
        $response = $this->actingAs($user)->getJson("/api/shopping-lists/$listId");

        $response->assertStatus(200)
            ->assertJson(['name' => 'My List']);
    }
}
