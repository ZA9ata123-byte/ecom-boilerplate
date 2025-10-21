<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'type','name','slug','short_description','description',
        'image','image_url','gallery','main_image_path','gallery_paths',
        'status','price','sku','compare_at_price','discount_percent',
        'attributes', 'stock',

        // Commerce/Logistics meta
        'weight','weight_unit','length','width','height','dimension_unit',
        'material','origin_country','brand',
        'barcode','mpn','hs_code',
        'cost','tax_class',
        'is_fragile','is_perishable','expiry_date',
        'composition','care_instructions',
        'age_group','gender','condition',
    ];

    protected $casts = [
        'gallery'          => 'array',
        'gallery_paths'    => 'array',
        'attributes'       => 'array',

        // meta
        'weight'           => 'decimal:3',
        'length'           => 'decimal:2',
        'width'            => 'decimal:2',
        'height'           => 'decimal:2',
        'cost'             => 'decimal:2',
        'is_fragile'       => 'boolean',
        'is_perishable'    => 'boolean',
        'expiry_date'      => 'date',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
