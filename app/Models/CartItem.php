<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * ثمن الوحدة: إذا عندنا Variant بثمن → ناخدوه،
     * وإلا كنرجعو لثمن المنتج الأصلي.
     */
    public function unitPrice(): float
    {
        $variant = $this->variant;
        if ($variant && ! is_null($variant->price)) {
            return (float) $variant->price;
        }

        $product = $this->product;
        if ($product && ! is_null($product->price)) {
            return (float) $product->price;
        }

        return 0.0;
    }

    /**
     * مجموع السطر: unit_price × quantity
     */
    public function lineTotal(): float
    {
        return $this->unitPrice() * (int) $this->quantity;
    }
}
