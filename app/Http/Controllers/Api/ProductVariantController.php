<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * رجّع جميع المتغيّرات ديال منتج معيّن (public).
     */
    public function index(Product $product): JsonResponse
    {
        $variants = $product->variants()->get();

        return response()->json([
            'product_id' => $product->id,
            'variants'   => $variants,
        ]);
    }

    /**
     * إنشاء متغيّر جديد لمنتج (admin via Sanctum).
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'sku'        => ['nullable', 'string', 'max:255'],
            'name'       => ['nullable', 'string', 'max:255'],
            'options'    => ['nullable', 'array'],      // مثال: {"size": "L", "color": "Black"}
            'price'      => ['nullable', 'numeric', 'min:0'],
            'stock'      => ['nullable', 'integer', 'min:0'],
            'is_default' => ['sometimes', 'boolean'],
            'status'     => ['nullable', 'string', 'max:255'],
        ]);

        $variant = new ProductVariant();
        $variant->product_id = $product->id;
        $variant->sku        = $data['sku']        ?? null;
        $variant->name       = $data['name']       ?? null;
        $variant->options    = $data['options']    ?? null;
        $variant->price      = $data['price']      ?? null;
        $variant->stock      = $data['stock']      ?? null;
        $variant->is_default = $data['is_default'] ?? false;
        $variant->status     = $data['status']     ?? 'active';
        $variant->save();

        // إلا كان هاد المتغيّر هو default → نخليو غير هو
        if ($variant->is_default) {
            ProductVariant::where('product_id', $product->id)
                ->where('id', '!=', $variant->id)
                ->update(['is_default' => false]);
        }

        $product->load('variants');

        return response()->json([
            'message'  => 'Variant saved successfully',
            'variant'  => $variant,
            'variants' => $product->variants,
        ]);
    }

    /**
     * تحديث متغيّر موجود.
     */
    public function update(Request $request, Product $product, ProductVariant $variant): JsonResponse
    {
        if ($variant->product_id !== $product->id) {
            return response()->json(['message' => 'Variant does not belong to this product.'], 404);
        }

        $data = $request->validate([
            'sku'        => ['nullable', 'string', 'max:255'],
            'name'       => ['nullable', 'string', 'max:255'],
            'options'    => ['nullable', 'array'],
            'price'      => ['nullable', 'numeric', 'min:0'],
            'stock'      => ['nullable', 'integer', 'min:0'],
            'is_default' => ['sometimes', 'boolean'],
            'status'     => ['nullable', 'string', 'max:255'],
        ]);

        $variant->fill($data);
        $variant->save();

        if (array_key_exists('is_default', $data) && $variant->is_default) {
            ProductVariant::where('product_id', $product->id)
                ->where('id', '!=', $variant->id)
                ->update(['is_default' => false]);
        }

        $product->load('variants');

        return response()->json([
            'message'  => 'Variant updated successfully',
            'variant'  => $variant,
            'variants' => $product->variants,
        ]);
    }

    /**
     * حذف متغيّر.
     */
    public function destroy(Product $product, ProductVariant $variant): JsonResponse
    {
        if ($variant->product_id !== $product->id) {
            return response()->json(['message' => 'Variant does not belong to this product.'], 404);
        }

        $variant->delete();

        $product->load('variants');

        return response()->json([
            'message'  => 'Variant deleted successfully',
            'variants' => $product->variants,
        ]);
    }
}
