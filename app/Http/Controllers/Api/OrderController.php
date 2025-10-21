<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $cart = $user->cart()->with('items.product')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 400);
        }

        // Use a database transaction to ensure all operations succeed or none do
        $order = DB::transaction(function () use ($user, $cart) {
            // Calculate total price
            $totalPrice = $cart->items->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            // Create the order
            $order = $user->orders()->create([
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // Move cart items to order items
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);
            }

            // Clear the cart
            $cart->items()->delete();

            return $order;
        });

        return response()->json($order->load('items.product'), 201);
    }
}
