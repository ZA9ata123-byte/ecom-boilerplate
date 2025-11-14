<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    /**
     * رجّع جميع الصور ديال المنتج (public).
     */
    public function index(Product $product): JsonResponse
    {
        $images = $product->images()->get();

        return response()->json([
            'product_id' => $product->id,
            'images'     => $images,
        ]);
    }

    /**
     * إضافة صورة جديدة للجاليري أو تحديد صورة رئيسية (admin via Sanctum).
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'url'        => ['required', 'string', 'max:2048'],
            'alt'        => ['nullable', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        // إلا مبعثش sort_order نخليه 0
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);

        // إلا قال هادي رئيسية → نحيد is_primary من أي صورة سابقة
        if ($data['is_primary']) {
            ProductImage::where('product_id', $product->id)
                ->update(['is_primary' => false]);
        }

        $image = ProductImage::create([
            'product_id' => $product->id,
            'url'        => $data['url'],
            'alt'        => $data['alt'] ?? null,
            'is_primary' => $data['is_primary'],
            'sort_order' => $data['sort_order'],
        ]);

        $product->load('images');

        return response()->json([
            'message' => 'Image saved successfully',
            'image'   => $image,
            'images'  => $product->images,
        ]);
    }

    /**
     * حذف صورة من الجاليري (admin via Sanctum).
     */
    public function destroy(Product $product, ProductImage $image): JsonResponse
    {
        // تأكد أن الصورة تابعة لهذا المنتج
        if ($image->product_id !== $product->id) {
            return response()->json([
                'message' => 'Image does not belong to this product.',
            ], 422);
        }

        $image->delete();

        $product->load('images');

        return response()->json([
            'message' => 'Image deleted successfully',
            'images'  => $product->images,
        ]);
    }
}
