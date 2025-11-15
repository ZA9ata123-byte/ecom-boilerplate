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
        [$cart, $response] = $this->resolveCart($request);

        $data = $this->transformCart($cart);

        if ($response) {
            return $response->setData($data);
        }

        return response()->json($data);
    }

    /**
     * إضافة منتج للسلة أو تحديث الكمية ديالو (مسجّل أو زائر).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id'         => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity'           => ['required', 'integer', 'min:1'],
        ]);

        [$cart, $response] = $this->resolveCart($request);

        $product = Product::findOrFail($data['product_id']);

        $variant = null;
        if (! empty($data['product_variant_id'])) {
            $variant = ProductVariant::where('id', $data['product_variant_id'])
                ->where('product_id', $product->id)
                ->firstOrFail();
        }

        // نشوف واش نفس السطر (نفس المنتج ونفس الفاريانت) موجود
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

        $cartData = $this->transformCart($cart);

        $payload = [
            'message' => 'Item added to cart',
            'cart'    => $cartData,
        ];

        if ($response) {
            return $response->setData($payload);
        }

        return response()->json($payload);
    }

    /**
     * تحديث عنصر فالسلة (الكمية).
     */
    public function update(Request $request, CartItem $item): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if ($user && $item->cart->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item->quantity = $data['quantity'];
        $item->save();

        $cart = $item->cart->load(['items.product', 'items.variant']);

        $cartData = $this->transformCart($cart);

        return response()->json([
            'message' => 'Cart updated',
            'cart'    => $cartData,
        ]);
    }

    /**
     * حذف عنصر من السلة.
     */
    public function destroy(Request $request, CartItem $item): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if ($user && $item->cart->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $cart = $item->cart;

        $item->delete();

        $cart->load(['items.product', 'items.variant']);

        $cartData = $this->transformCart($cart);

        return response()->json([
            'message' => 'Item removed from cart',
            'cart'    => $cartData,
        ]);
    }

    /**
     * helper: يرد لينا الكارت (user أو guest) + response فيه الكوكي إلا احتجناه.
     *
     * @return array{0: Cart, 1: ?JsonResponse}
     */
    private function resolveCart(Request $request): array
    {
        $user = $request->user('sanctum') ?? $request->user();
        $response = null;

        // مسجّل
        if ($user) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['cart_token' => null],
            );

            return [$cart, $response];
        }

        // زائر (guest)
        $token = $request->cookie('cart_token');

        if ($token) {
            $cart = Cart::whereNull('user_id')
                ->where('cart_token', $token)
                ->first();
        }

        if (empty($cart)) {
            $newToken = (string) Str::uuid();

            $cart = Cart::create([
                'user_id'    => null,
                'cart_token' => $newToken,
            ]);

            $response = response()->json();
            $this->attachCartCookie($response, $newToken);
        }

        return [$cart, $response];
    }

    /**
     * يركب الكوكي ديال cart_token فـ response.
     */
    private function attachCartCookie(JsonResponse $response, string $token): void
    {
        if ($token === '') {
            return;
        }

        $response->cookie(
            'cart_token',
            $token,
            60 * 24 * 30, // 30 يوم (بالدقايق)
            '/',
            null,
            false, // https only -> نخليوها false دابا، نفعلوها ملي ندوزو https
            false, // httpOnly
            false,
            'Lax',
        );
    }

    /**
     * توحيد فورما السلة فالرد.
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
