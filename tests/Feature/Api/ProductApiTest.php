<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_all_products(): void
    {
        Product::factory()->count(3)->create();
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    /** @test */
    public function it_can_fetch_a_single_product(): void
    {
        $product = Product::factory()->create();
        $response = $this->getJson('/api/products/'.$product->id);
        $response->assertStatus(200);
        $response->assertJson(['id' => $product->id]);
    }

    /** @test */
    public function a_guest_cannot_create_a_product(): void
    {
        $productData = ['name' => 'Guest Product', 'price' => 100];
        $response = $this->postJson('/api/products', $productData);
        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function a_normal_user_cannot_create_a_product(): void
    {
        // NEW TEST: Ensure a regular user is forbidden
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);
        $productData = ['name' => 'User Product', 'price' => 100];
        $response = $this->postJson('/api/products', $productData);
        $response->assertStatus(403); // 403 = Forbidden
    }

    /** @test */
    public function an_admin_can_create_a_product(): void
    {
        // UPDATED: We create an 'admin' user
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $productData = ['name' => 'New Product', 'price' => 199.99];
        $response = $this->postJson('/api/products', $productData);
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }

    /** @test */
    public function an_admin_can_update_a_product(): void
    {
        // UPDATED: We use an 'admin' user
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $product = Product::factory()->create();
        $updateData = ['name' => 'Updated Product Name'];
        $response = $this->putJson('/api/products/'.$product->id, $updateData);
        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Product Name']);
    }

    /** @test */
    public function an_admin_can_delete_a_product(): void
    {
        // UPDATED: We use an 'admin' user
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $product = Product::factory()->create();
        $response = $this->deleteJson('/api/products/'.$product->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
