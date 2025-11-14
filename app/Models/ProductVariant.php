<?php

namespace App\Models;

use App\Models\Metafield;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductVariant extends Model
{
    use HasFactory;

    /**
     * الحقول اللي نسمحو فيها بالـ mass assignment.
     * ماشي ضروري كلهم يكونو أعمدة، المهم غير إلى عطيناهم قيم فـ create/update تكون الأعمدة موجودة.
     */
    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'options',     // JSON: مثلا { "color": "أحمر", "size": "M" }
        'price',
        'stock',
        'is_default',
        'status',

        // أعمدة إضافية اللي كانوا فالمایغريشنات القديمة (meta, images, الخ)
        'attributes',  // JSON
        'images',      // JSON: لائحة ديال URLs ولا objects
        'weight',
        'length',
        'width',
        'height',
    ];

    /**
     * Casting باش نخدمو بسهولة مع JSON و Boolean و integer.
     */
    protected $casts = [
        'options'    => 'array',
        'attributes' => 'array',
        'images'     => 'array',
        'is_default' => 'boolean',
        'price'      => 'decimal:2',
        'stock'      => 'integer',
    ];

    /**
     * المنتج الأب ديال هاد المتغير.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * الميتافيلدز ديال المتغير (نفس system ديال المنتج، polymorphic).
     * مثال: inventory / warehouse_bin, shipping / extra_weight, seo / title_per_variant ...
     */
    public function metafields(): MorphMany
    {
        return $this->morphMany(Metafield::class, 'metafieldable');
    }

    /**
     * Helper بسيط: يرجع الثمن الفعال ديال المتغير
     * (ممكن فالمستقبل نضيفو خصومات خاصة بالمتغير هنا).
     */
    public function effectivePrice(): string
    {
        return $this->price ?? $this->product?->price ?? '0.00';
    }
}
