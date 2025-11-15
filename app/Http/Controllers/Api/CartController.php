<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * عرض السلة الحالية (مسجّل أو زائر).
     */
    public function index(Request $request): JsonResponse
    {
        [$cart, $token] = $this->resolveCart($request, createIfMissing: true);

        if (! $cart) {
            return response()->json([
                'id'    => null,
                'items' => [],
                'total' => 0,
            ]);
        }

        $cart->loadMissing(['items.product', 'items.variant']);
        $payload = $this->transformCart($cart);

        $response = response()->json($payload);

        if ($token !== null) {
            $response->cookie(
                'cart_token',
                $token,
                60 * 24 * 30, // 30 يوم
                '/',
                null,
                true,   // secure (فالبرو تكون https)
                true,   // httpOnly
                false,
                'Lax'
            );
        }

        return $response;
    }

    /**
     * إضافة منتج للسلة أو تحديث الكمية ديالو.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id'         => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity'           => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        [$cart, $token] = $this->resolveCart($request, createIfMissing: true);

        $variant = null;
        if (! empty($data['product_variant_id'])) {
            $variant = ProductVariant::where('id', $data['product_variant_id'])
                ->where('product_id', $product->id)
                ->firstOrFail();
        }

        $itemQuery = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id);

        if ($variant) {
            $itemQuery->where('product_variant_id', $variant->id);
        } else {
            $itemQuery->whereNull('product_variant_id');
        }

        $item = $itemQuery->first();

        if ($item) {
            $item->quantity += $data['quantity'];
            $item->save();
        } else {
            $item = CartItem::create([
                'cart_id'            => $cart->id,
                'product_id'         => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity'           => $data['quantity'],
            ]);
        }

        $cart->load(['items.product', 'items.variant']);
        $payload = $this->transformCart($cart);

        $response = response()->json([
            'message' => 'Item added to cart',
            'cart'    => $payload,
        ]);

        if ($token !== null) {
            $response->cookie(
                'cart_token',
                $token,
                60 * 24 * 30,
                '/',
                null,
                true,
                true,
                false,
                'Lax'
            );
        }

        return $response;
    }

    /**
     * تحديث عنصر فالسلة (الكمية).
     */
    public function update(Request $request, CartItem $item): JsonResponse
    {
        [$cart, $token] = $this->resolveCart($request, createIfMissing: false);

        if (! $cart || $item->cart_id !== $cart->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item->quantity = $data['quantity'];
        $item->save();

        $cart->load(['items.product', 'items.variant']);
        $payload = $this->transformCart($cart);

        $response = response()->json([
            'message' => 'Cart updated',
            'cart'    => $payload,
        ]);

        if ($token !== null) {
            $response->cookie(
                'cart_token',
                $token,
                60 * 24 * 30,
                '/',
                null,
                true,
                true,
                false,
                'Lax'
            );
        }

        return $response;
    }

    /**
     * حذف عنصر من السلة.
     */
    public function destroy(Request $request, CartItem $item): JsonResponse
    {
        [$cart, $token] = $this->resolveCart($request, createIfMissing: false);

        if (! $cart || $item->cart_id !== $cart->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $cart = $item->cart;
        $item->delete();

        $cart->load(['items.product', 'items.variant']);
        $payload = $this->transformCart($cart);

        $response = response()->json([
            'message' => 'Item removed',
            'cart'    => $payload,
        ]);

        if ($token !== null) {
            $response->cookie(
                'cart_token',
                $token,
                60 * 24 * 30,
                '/',
                null,
                true,
                true,
                false,
                'Lax'
            );
        }

        return $response;
    }

    /**
     * تحديد السلة الحالية (User أو Guest).
     *
     * @return array{0: ?Cart, 1: ?string}
     */
    private function resolveCart(Request $request, bool $createIfMissing = false): array
    {
        $user = $request->user('sanctum') ?? $request->user();

        // مسجّل ✅
        if ($user) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            return [$cart, null];
        }

        // زائر ✅
        $token = $request->cookie('cart_token');

        if (! $token) {
            if (! $createIfMissing) {
                return [null, null];
            }

            $token = (string) Str::uuid();

            $cart = Cart::create([
                'cart_token' => $token,
            ]);

            return [$cart, $token];
        }

        $cart = Cart::firstOrCreate(['cart_token' => $token]);

        return [$cart, $token];
    }

    /**
     * تحويل الكارت لفورما موحدة.
     */
    private function transformCart(Cart $cart): array
    {
        $cart->loadMissing(['items.product', 'items.variant']);

        $items = $cart->items->map(function (CartItem $item) {
            $unitPrice = $item->unitPrice();

            return [
                'id'                 => $item->id,
                'product_id'         => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity'           => $item->quantity,
                'unit_price'         => $unitPrice,
                'line_total'         => $item->lineTotal(),
                'product'            => $item->product ? [
                    'id'    => $item->product->id,
                    'name'  => $item->product->name,
                    'price' => $item->product->price,
                    'sku'   => $item->product->sku,
                    'type'  => $item->product->type,
                ] : null,
                'variant'            => $item->variant ? [
                    'id'         => $item->variant->id,
                    'sku'        => $item->variant->sku,
                    'name'       => $item->variant->name,
                    'options'    => $item->variant->options,
                    'price'      => $item->variant->price,
                    'is_default' => $item->variant->is_default,
                ] : null,
            ];
        });

        return [
            'id'    => $cart->id,
            'items' => $items,
            'total' => $items->sum(fn ($i) => $i['line_total']),
        ];
    }
}
