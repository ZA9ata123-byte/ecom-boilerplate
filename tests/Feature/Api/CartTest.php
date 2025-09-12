<?php

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_authenticated_user_can_add_a_product_to_their_cart(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product = Product::factory()->create();

        $this->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertStatus(201);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
        ]);
    }

    /** @test */
    public function an_authenticated_user_can_view_their_cart(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product = Product::factory()->create();
        $cart = $user->cart()->create();
        $cart->items()->create(['product_id' => $product->id]);

        $this->getJson('/api/cart')
            ->assertStatus(200)
            ->assertJsonFragment(['name' => $product->name]);
    }

    /** @test */
    public function an_authenticated_user_can_update_item_quantity_in_cart(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product = Product::factory()->create();
        $cart = $user->cart()->create();
        $cartItem = $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->putJson('/api/cart/items/' . $cartItem->id, ['quantity' => 5])
            ->assertStatus(200);
            
        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id, 'quantity' => 5]);
    }

    /** @test */
    public function an_authenticated_user_can_remove_an_item_from_their_cart(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $cart = $user->cart()->create();
        $cartItem = $cart->items()->create(['product_id' => Product::factory()->create()->id]);

        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);

        $this->deleteJson('/api/cart/items/' . $cartItem->id)
            ->assertStatus(204);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}