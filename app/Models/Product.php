<?php

namespace App\Models;

use App\Models\Metafield;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    /**
     * الحقول اللي نسمحو فيها بالـ mass assignment.
     */
    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'short_description',
        'image_url',
        'gallery',
        'attributes',
        'sku',
        'stock',
        'price',
        'status',

        'brand',
        'material',
        'origin_country',
        'barcode',
        'mpn',
        'hs_code',
        'cost',
        'tax_class',

        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'dimension_unit',

        'is_fragile',
        'is_perishable',
        'expiry_date',
        'composition',
        'care_instructions',
        'age_group',
        'gender',
        'condition',
    ];

    /**
     * الكاست ديال بعض الحقول.
     */
    protected $casts = [
        'gallery'        => 'array',
        'attributes'     => 'array',
        'is_fragile'     => 'boolean',
        'is_perishable'  => 'boolean',
        'weight'         => 'float',
        'length'         => 'float',
        'width'          => 'float',
        'height'         => 'float',
        'expiry_date'    => 'datetime',
    ];

    /**
     * الصور المرتبطة بهذا المنتج (جاليري).
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * الميتافيلدز المرتبطة بهدا المنتج.
     */
    public function metafields(): MorphMany
    {
        return $this->morphMany(Metafield::class, 'metafieldable');
    }

    /**
     * Setter مريح: يزيد/يحدّث ميتافيلد واحد.
     */
    public function setMetafield(string $namespace, string $key, $value, string $type = 'string'): Metafield
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            if ($type === 'string') {
                $type = 'json';
            }
        }

        return $this->metafields()->updateOrCreate(
            ['namespace' => $namespace, 'key' => $key],
            ['value' => $value, 'type' => $type],
        );
    }

    /**
     * Getter مريح: يجيب قيمة ميتافيلد ولا يرجّع default.
     */
    public function getMetafield(string $namespace, string $key, $default = null)
    {
        $mf = $this->metafields()
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->first();

        if (! $mf) {
            return $default;
        }

        return match ($mf->type) {
            'integer' => (int) $mf->value,
            'float'   => (float) $mf->value,
            'boolean' => (bool) $mf->value,
            'json'    => json_decode($mf->value, true),
            default   => $mf->value,
        };
    }

    /**
     * رجّع SEO meta من الميتافيلدز مع قيم افتراضية من نفس المنتج.
     *
     * namespace: seo
     *  - seo.title       => seo / title
     *  - seo.description => seo / description
     *  - seo.image_url   => seo / image_url
     *  - seo.keywords    => seo / keywords
     */
    public function getSeoMeta(): array
    {
        return [
            'title'       => $this->getMetafield('seo', 'title', $this->name),
            'description' => $this->getMetafield(
                'seo',
                'description',
                $this->short_description ?? $this->description
            ),
            'image_url'   => $this->getMetafield('seo', 'image_url', $this->image_url),
            'keywords'    => $this->getMetafield('seo', 'keywords', null),
        ];
    }

    /**
     * يرجع رابط الصورة الرئيسية:
     * 1) إلى كانت صورة is_primary = true → نرجعوها
     * 2) إلا ما كايناش → أول صورة فالجاليري
     * 3) إلا ما كاين والو → image_url القديم
     */
    public function primaryImageUrl(): ?string
    {
        $primary = $this->images()
            ->where('is_primary', true)
            ->first();

        if ($primary) {
            return $primary->url;
        }

        $first = $this->images()->first();

        if ($first) {
            return $first->url;
        }

        return $this->image_url;
    }

    /**
     * المتغيرات المرتبطة بهذا المنتج.
     */
    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->orderByDesc('is_default')
            ->orderBy('id');
    }
}
