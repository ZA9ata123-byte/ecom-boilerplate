<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * عرض السلة الحالية للمستخدم.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            // حاليا: سلة فارغة لغير المسجّل (غادي نطوروها لاحقا للضيف)
            return response()->json([
                'id'    => null,
                'items' => [],
                'total' => 0,
            ]);
        }

        // نجيب أو ننشئ سلة للمستخدم
        $cart = Cart::with(['items.product'])
            ->firstOrCreate(['user_id' => $user->id]);

        // نحسب التوتال من ثمن المنتج مباشرة
        $total = $cart->items->sum(function (CartItem $item) {
            $price = $item->product->price ?? 0;
            return (float) $price * $item->quantity;
        });

        return response()->json([
            'id'    => $cart->id,
            'items' => $cart->items->map(function (CartItem $item) {
                return [
                    'id'         => $item->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price ?? null,
                    'product'    => $item->product ? [
                        'id'    => $item->product->id,
                        'name'  => $item->product->name,
                        'price' => $item->product->price,
                        'sku'   => $item->product->sku,
                        'type'  => $item->product->type,
                    ] : null,
                ];
            }),
            'total' => $total,
        ]);
    }

    /**
     * إضافة منتج للسلة أو تحديث الكمية ديالو.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        // نخلق/نجيب السلة ديال هاد المستخدم
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            []
        );

        // نشوف واش نفس المنتج راه كاين فالسلة
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            // نزيد الكمية
            $item->quantity += $data['quantity'];
            $item->save();
        } else {
            // نخلق عنصر جديد فالسلة
            $item = CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $data['quantity'],
            ]);
        }

        $cart->load('items.product');

        $total = $cart->items->sum(function (CartItem $item) {
            $price = $item->product->price ?? 0;
            return (float) $price * $item->quantity;
        });

        return response()->json([
            'message' => 'Item added to cart',
            'cart'    => [
                'id'    => $cart->id,
                'items' => $cart->items->map(function (CartItem $item) {
                    return [
                        'id'         => $item->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'price'      => $item->product->price ?? null,
                        'product'    => $item->product ? [
                            'id'    => $item->product->id,
                            'name'  => $item->product->name,
                            'price' => $item->product->price,
                            'sku'   => $item->product->sku,
                            'type'  => $item->product->type,
                        ] : null,
                    ];
                }),
                'total' => $total,
            ],
        ]);
    }

    /**
     * تحديث عنصر فالسلة (الكمية).
     */
    public function update(Request $request, CartItem $item): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($item->cart->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item->quantity = $data['quantity'];
        $item->save();

        $cart = $item->cart->load('items.product');

        $total = $cart->items->sum(function (CartItem $item) {
            $price = $item->product->price ?? 0;
            return (float) $price * $item->quantity;
        });

        return response()->json([
            'message' => 'Cart updated',
            'cart'    => [
                'id'    => $cart->id,
                'items' => $cart->items->map(function (CartItem $item) {
                    return [
                        'id'         => $item->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'price'      => $item->product->price ?? null,
                        'product'    => $item->product ? [
                            'id'    => $item->product->id,
                            'name'  => $item->product->name,
                            'price' => $item->product->price,
                            'sku'   => $item->product->sku,
                            'type'  => $item->product->type,
                        ] : null,
                    ];
                }),
                'total' => $total,
            ],
        ]);
    }

    /**
     * حذف عنصر من السلة.
     */
    public function destroy(Request $request, CartItem $item): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($item->cart->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $cart = $item->cart;

        $item->delete();

        $cart->load('items.product');

        $total = $cart->items->sum(function (CartItem $item) {
            $price = $item->product->price ?? 0;
            return (float) $price * $item->quantity;
        });

        return response()->json([
            'message' => 'Item removed from cart',
            'cart'    => [
                'id'    => $cart->id,
                'items' => $cart->items->map(function (CartItem $item) {
                    return [
                        'id'         => $item->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'price'      => $item->product->price ?? null,
                        'product'    => $item->product ? [
                            'id'    => $item->product->id,
                            'name'  => $item->product->name,
                            'price' => $item->product->price,
                            'sku'   => $item->product->sku,
                            'type'  => $item->product->type,
                        ] : null,
                    ];
                }),
                'total' => $total,
            ],
        ]);
    }
}
