<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // تأكد من المفاتيح الأساسية
            if (! Schema::hasColumn('product_variants', 'product_id')) {
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('product_variants', 'sku')) {
                $table->string('sku')->unique()->index();
            }

            if (! Schema::hasColumn('product_variants', 'price')) {
                $table->decimal('price', 10, 2)->default(0);
            }

            if (! Schema::hasColumn('product_variants', 'stock')) {
                $table->integer('stock')->default(0);
            }

            // هادو اللي ناقصين عندك حسب الخطأ
            if (! Schema::hasColumn('product_variants', 'attributes')) {
                $table->json('attributes')->nullable();
            }

            if (! Schema::hasColumn('product_variants', 'images')) {
                $table->json('images')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // رجّع فقط الأعمدة اللي زدنا هنا
            if (Schema::hasColumn('product_variants', 'images')) {
                $table->dropColumn('images');
            }
            if (Schema::hasColumn('product_variants', 'attributes')) {
                $table->dropColumn('attributes');
            }
            if (Schema::hasColumn('product_variants', 'stock')) {
                $table->dropColumn('stock');
            }
            if (Schema::hasColumn('product_variants', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('product_variants', 'sku')) {
                $table->dropColumn('sku');
            }

            // مكنحيدوش product_id إلا إلا بغينا حتى نفكّو الكونسترين
            // خليها كما هي باش ما نفقدوش العلاقة.
        });
    }
};
