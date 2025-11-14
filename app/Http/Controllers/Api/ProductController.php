<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct()
    {
        // التوكن ضروري للإضافة/التعديل/الحذف
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        return Product::with('variants')->latest()->paginate(15);
    }

    public function show(Product $product)
    {
        return $product->load('variants');
    }

    public function store(Request $request)
    {
        // سجّل كلشي اللي جاي من الفرونت باش نعرفو واش payload صحيح
        Log::info('📦 Incoming product payload', $request->all());

        // قواعد عامة
        $baseRules = [
            'type'        => ['required', Rule::in(['simple', 'variable'])],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url'   => ['nullable', 'url', 'required_if:type,variable'],
            'gallery'     => ['nullable', 'array'],
            'gallery.*'   => ['url'],
            'status'      => ['nullable', Rule::in(['active', 'draft'])],

            // حقول اختيارية إضافية
            'short_description' => ['nullable','string','max:1000'],
            'barcode'           => ['nullable','string','max:64'],
            'mpn'               => ['nullable','string','max:64'],
            'hs_code'           => ['nullable','string','max:32'],
            'brand'             => ['nullable','string','max:120'],
            'material'          => ['nullable','string','max:120'],
            'origin_country'    => ['nullable','string','size:2'],
            'cost'              => ['nullable','numeric','min:0'],
            'tax_class'         => ['nullable','string','max:50'],

            'weight'            => ['nullable','numeric','min:0'],
            'weight_unit'       => ['nullable','string','max:10'],
            'length'            => ['nullable','numeric','min:0'],
            'width'             => ['nullable','numeric','min:0'],
            'height'            => ['nullable','numeric','min:0'],
            'dimension_unit'    => ['nullable','string','max:10'],

            'is_fragile'        => ['nullable','boolean'],
            'is_perishable'     => ['nullable','boolean'],
            'expiry_date'       => ['nullable','date'],

            'composition'       => ['nullable','string'],
            'care_instructions' => ['nullable','string'],

            'age_group'         => ['nullable','string','max:30'],
            'gender'            => ['nullable','string','max:20'],
            'condition'         => ['nullable','string','max:30'],
        ];

        // قواعد المنتج البسيط
        $simpleRules = [
            'price' => ['required_if:type,simple','numeric','min:0'],
            'sku'   => ['required_if:type,simple','string','max:100','unique:products,sku'],
            'stock' => ['nullable','integer','min:0'],
        ];

        // قواعد المنتج المتغير
        $variableRules = [
            'attributes'             => ['required_if:type,variable','array','min:1'],
            'attributes.*.name'      => ['required','string','max:50'],
            'attributes.*.options'   => ['required','array','min:1'],
            'attributes.*.options.*' => ['string','max:50'],

            'variants'               => ['required_if:type,variable','array','min:1'],
            'variants.*.sku'         => ['required','string','max:100','distinct','unique:product_variants,sku'],
            'variants.*.price'       => ['required','numeric','min:0'],
            'variants.*.stock'       => ['nullable','integer','min:0'],
            'variants.*.attributes'  => ['required','array'], // مثال: {"size":"S","color":"black"}
            'variants.*.images'      => ['nullable','array','max:3'],
            'variants.*.images.*'    => ['url'],
        ];

        $data = $request->validate(array_merge($baseRules, $simpleRules, $variableRules));

        $product = DB::transaction(function () use ($data) {
            // إنشاء المنتج
            $product = Product::create([
                'type'        => $data['type'],
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'image_url'   => $data['image_url'] ?? null,
                'gallery'     => isset($data['gallery']) ? array_values($data['gallery']) : [],
                'status'      => $data['status'] ?? 'active',

                // simple فقط
                'price'       => $data['type'] === 'simple' ? $data['price'] : null,
                'sku'         => $data['type'] === 'simple' ? $data['sku']   : null,
                'stock'       => $data['type'] === 'simple' ? ($data['stock'] ?? 0) : 0, // للـ variable دايماً 0

                // variable فقط
                'attributes'  => $data['type'] === 'variable' ? $data['attributes'] : null,

                // الحقول الاختيارية المشتركة
                'short_description' => $data['short_description'] ?? null,
                'barcode'           => $data['barcode'] ?? null,
                'mpn'               => $data['mpn'] ?? null,
                'hs_code'           => $data['hs_code'] ?? null,
                'brand'             => $data['brand'] ?? null,
                'material'          => $data['material'] ?? null,
                'origin_country'    => $data['origin_country'] ?? null,
                'cost'              => $data['cost'] ?? null,
                'tax_class'         => $data['tax_class'] ?? 'standard',

                'weight'            => $data['weight'] ?? null,
                'weight_unit'       => $data['weight_unit'] ?? 'kg',
                'length'            => $data['length'] ?? null,
                'width'             => $data['width'] ?? null,
                'height'            => $data['height'] ?? null,
                'dimension_unit'    => $data['dimension_unit'] ?? 'cm',

                'is_fragile'        => (bool)($data['is_fragile'] ?? false),
                'is_perishable'     => (bool)($data['is_perishable'] ?? false),
                'expiry_date'       => $data['expiry_date'] ?? null,

                'composition'       => $data['composition'] ?? null,
                'care_instructions' => $data['care_instructions'] ?? null,

                'age_group'         => $data['age_group'] ?? null,
                'gender'            => $data['gender'] ?? null,
                'condition'         => $data['condition'] ?? null,
            ]);

            // إنشاء المتغيرات (للـ variable)
            if ($data['type'] === 'variable') {
                $allowedAttrNames = collect($data['attributes'])->pluck('name')->values();

                foreach ($data['variants'] as $v) {
                    // تأكد أن مفاتيح attributes ضمن المصرّح بها
                    $variantAttrs = collect($v['attributes']);
                    if ($variantAttrs->keys()->diff($allowedAttrNames)->isNotEmpty()) {
                        abort(422, 'Variant attributes keys must match declared attributes.');
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku'        => $v['sku'],
                        'price'      => $v['price'],
                        'stock'      => $v['stock'] ?? 0,
                        // جدولك فيه عمود "options" (JSON) بدل "attributes"
                        'options'    => $v['attributes'],
                        'images'     => isset($v['images'])
                            ? array_slice(array_values($v['images']), 0, 3)
                            : [],
                    ]);
                }
            }

            return $product->load('variants');
        });

        return response()->json([
            'message' => 'Product created',
            'data'    => $product,
        ], 201);
    }
}
