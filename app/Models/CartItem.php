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
     * ثمن الوحدة: يا إما من الـ variant، وإلا من المنتج الأصلي.
     */
    public function unitPrice(): float
    {
        if ($this->variant && $this->variant->price !== null) {
            return (float) $this->variant->price;
        }

        if ($this->product && $this->product->price !== null) {
            return (float) $this->product->price;
        }

        return 0.0;
    }

    /**
     * المجموع ديال السطر = الثمن × الكمية.
     */
    public function lineTotal(): float
    {
        return $this->unitPrice() * (int) $this->quantity;
    }
}
