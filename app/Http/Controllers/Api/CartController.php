<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    /**
     * Display the authenticated user's cart contents.
     */
    public function index(Request $request): JsonResponse
    {
        // We will improve this later to handle guest carts too
        $cart = $request->user()->cart()->with('items.product')->first();

        return response()->json($cart);
    }

    /**
     * Store a new item in the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
        ]);

        // We will improve this later to handle guest carts too
        $cart = $request->user()->cart()->firstOrCreate();

        $cartItem = $cart->items()->updateOrCreate(
            ['product_id' => $request->product_id],
            ['quantity' => $request->quantity ?? 1]
        );

        return response()->json($cartItem, 201);
    }

    /**
     * Update the specified item in storage.
     */
    public function update(Request $request, CartItem $item): JsonResponse
    {
        // We should add authorization here to ensure the user owns this cart item
        $request->validate(['quantity' => 'required|integer|min:1']);

        $item->update(['quantity' => $request->quantity]);

        return response()->json($item);
    }
    public function destroy(CartItem $item): JsonResponse
    {
        // Add authorization check here later
        $item->delete();
        return response()->json(null, 204);
    }
}