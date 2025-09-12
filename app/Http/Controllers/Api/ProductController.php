<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * لعرض لائحة بجميع المنتجات
     */
    public function index(): JsonResponse
    {
        return response()->json(Product::all());
    }

    /**
     * لتخزين منتج جديد تم إنشاؤه
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        return response()->json($product, 201);
    }

    /**
     * لعرض منتج محدد
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    /**
     * لتحديث منتج محدد في قاعدة البيانات
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());
        return response()->json($product);
    }

    /**
     * لحذف منتج محدد
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(null, 204); // 204 No Content
    }
}