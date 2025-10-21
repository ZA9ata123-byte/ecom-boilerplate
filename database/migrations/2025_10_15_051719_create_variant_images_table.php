<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $t->string('path'); // مثال: storage/variants/images/abc.png
            $t->timestamps();

            $t->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_images');
    }
};
