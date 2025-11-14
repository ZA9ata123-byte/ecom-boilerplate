<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metafield;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductMetafieldController extends Controller
{
    /**
     * رجّع جميع الميتافيلدز ديال منتج معيّن.
     */
    public function index(Product $product): JsonResponse
    {
        $metafields = $product->metafields()
            ->orderBy('namespace')
            ->orderBy('key')
            ->get()
            ->map(function (Metafield $mf) {
                return [
                    'id'         => $mf->id,
                    'namespace'  => $mf->namespace,
                    'key'        => $mf->key,
                    'value'      => $mf->value,
                    'type'       => $mf->type,
                    'created_at' => $mf->created_at,
                    'updated_at' => $mf->updated_at,
                ];
            });

        return response()->json([
            'product_id' => $product->id,
            'metafields' => $metafields,
        ]);
    }

    /**
     * إضافة / تحديث ميتافيلد واحد لمنتج معيّن.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'namespace' => ['nullable', 'string', 'max:255'],
            'key'       => ['required', 'string', 'max:255'],
            'value'     => ['nullable'],
            'type'      => ['nullable', 'string', 'max:50'],
        ]);

        $mf = $product->setMetafield(
            $data['namespace'] ?? null,
            $data['key'],
            $data['value'] ?? null,
            $data['type'] ?? 'string',
        );

        return response()->json([
            'message'   => 'Metafield saved successfully',
            'metafield' => [
                'id'         => $mf->id,
                'namespace'  => $mf->namespace,
                'key'        => $mf->key,
                'value'      => $mf->value,
                'type'       => $mf->type,
                'product_id' => $product->id,
            ],
        ]);
    }
}
