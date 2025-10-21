<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'options',           // JSON: {"size":"S","color":"black"}
        'price',
        'stock',
        'compare_at_price',
        'discount_percent',
    ];

    protected $casts = [
        'options' => 'array',
        'price'   => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
