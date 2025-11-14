<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metafields', function (Blueprint $table) {
            $table->id();

            // صاحب الميتافيلد (Product, Variant, Order, User...)
            $table->unsignedBigInteger('metafieldable_id');
            $table->string('metafieldable_type');

            // نفس النظام ديال Shopify: namespace + key + value
            $table->string('namespace')->nullable();   // مثال: shipping, inventory, seo
            $table->string('key');                     // مثال: weight_grams, fragility, hs_code
            $table->text('value')->nullable();         // كنديرو فيها JSON ولا نص عادي
            $table->string('type')->default('string'); // string, integer, boolean, json...

            $table->timestamps();

            // باش البحث يكون سريع على صاحب الميتافيلد
            $table->index(
                ['metafieldable_type', 'metafieldable_id'],
                'metafields_owner_index'
            );

            // باش نقدرو نلقاو quickly metafields بنفس namespace+key
            $table->index(
                ['namespace', 'key'],
                'metafields_ns_key_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metafields');
    }
};
