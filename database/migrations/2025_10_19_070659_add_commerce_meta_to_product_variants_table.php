<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // لو ماكانش options نضيفوه (MySQL 8 يسمح default JSON)
            if (! Schema::hasColumn('product_variants', 'options')) {
                $table->json('options')->default(DB::raw('(json_object())'));
            }

            // صور اختيارية
            if (! Schema::hasColumn('product_variants', 'images')) {
                $table->json('images')->nullable();
            }

            // Meta
            if (! Schema::hasColumn('product_variants', 'weight')) {
                $table->decimal('weight', 8, 3)->nullable()->after('price');
            }
            if (! Schema::hasColumn('product_variants', 'weight_unit')) {
                $table->string('weight_unit', 10)->nullable();
            }
            if (! Schema::hasColumn('product_variants', 'length')) {
                $table->decimal('length', 8, 2)->nullable();
            }
            if (! Schema::hasColumn('product_variants', 'width')) {
                $table->decimal('width', 8, 2)->nullable();
            }
            if (! Schema::hasColumn('product_variants', 'height')) {
                $table->decimal('height', 8, 2)->nullable();
            }
            if (! Schema::hasColumn('product_variants', 'dimension_unit')) {
                $table->string('dimension_unit', 10)->nullable();
            }
            if (! Schema::hasColumn('product_variants', 'material')) {
                $table->string('material', 120)->nullable()->index();
            }
            if (! Schema::hasColumn('product_variants', 'barcode')) {
                $table->string('barcode', 64)->nullable()->index();
            }
            if (! Schema::hasColumn('product_variants', 'mpn')) {
                $table->string('mpn', 64)->nullable()->index();
            }
            if (! Schema::hasColumn('product_variants', 'hs_code')) {
                $table->string('hs_code', 32)->nullable()->index();
            }
            if (! Schema::hasColumn('product_variants', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            foreach ([
                'options', 'images', 'weight', 'weight_unit', 'length', 'width', 'height',
                'dimension_unit', 'material', 'barcode', 'mpn', 'hs_code', 'cost',
            ] as $col) {
                if (Schema::hasColumn('product_variants', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
