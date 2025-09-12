<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_authenticated_user_can_create_an_order_from_their_cart(): void
    {
        // 1. Arrange: Create a user with a cart that has items
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product1 = Product::factory()->create(['price' => 100]);
        $product2 = Product::factory()->create(['price' => 50]);
        $cart = $user->cart()->create();
        $cart->items()->create(['product_id' => $product1->id, 'quantity' => 2]); // 2 x 100 = 200
        $cart->items()->create(['product_id' => $product2->id, 'quantity' => 1]); // 1 x 50 = 50

        // 2. Act: Make a POST request to the create order endpoint
        $response = $this->postJson('/api/orders');

        // 3. Assert: Check for a successful response and correct data
        $response->assertStatus(201); // 201 = Created

        // Assert a new order was created with the correct total price (250)
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => 250.00
        ]);
        
        // Assert the order items were created
        $this->assertDatabaseCount('order_items', 2);

        // Assert the cart is now empty
        $this->assertDatabaseCount('cart_items', 0);
    }
}