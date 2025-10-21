<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // type: simple | variable
            if (! Schema::hasColumn('products', 'type')) {
                $table->string('type')->default('simple')->index();
            }
            if (! Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->unique();
            }
            if (! Schema::hasColumn('products', 'stock')) {
                $table->integer('stock')->nullable();
            }
            if (! Schema::hasColumn('products', 'status')) {
                $table->string('status')->default('active')->index();
            }
            if (! Schema::hasColumn('products', 'attributes')) {
                $table->json('attributes')->nullable();
            } // خصائص عامة للمنتج المتغيّر
            // price موجود عندك، خليه nullable للمتغيّر
            if (Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // بإيجاز: ما نرجعوش تغييرات رجعية هنا لتبسيط الشرح
    }
};
