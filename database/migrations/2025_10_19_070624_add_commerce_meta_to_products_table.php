<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // الوزن والأبعاد
            if (!Schema::hasColumn('products','weight')) {
                $table->decimal('weight', 8, 3)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products','weight_unit')) {
                $table->string('weight_unit', 10)->default('kg');
            }
            if (!Schema::hasColumn('products','length')) {
                $table->decimal('length', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products','width')) {
                $table->decimal('width', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products','height')) {
                $table->decimal('height', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products','dimension_unit')) {
                $table->string('dimension_unit', 10)->default('cm');
            }

            // معلومات مادية/صناعية
            if (!Schema::hasColumn('products','material')) {
                $table->string('material', 120)->nullable()->index();
            }
            if (!Schema::hasColumn('products','origin_country')) {
                $table->string('origin_country', 2)->nullable()->index(); // ISO-2
            }
            if (!Schema::hasColumn('products','brand')) {
                $table->string('brand', 120)->nullable()->index();
            }

            // أكواد تجارية
            if (!Schema::hasColumn('products','barcode')) {
                $table->string('barcode', 64)->nullable()->index(); // EAN/UPC
            }
            if (!Schema::hasColumn('products','mpn')) {
                $table->string('mpn', 64)->nullable()->index();
            }
            if (!Schema::hasColumn('products','hs_code')) {
                $table->string('hs_code', 32)->nullable()->index();
            }

            // تكاليف/ضرائب
            if (!Schema::hasColumn('products','cost')) {
                $table->decimal('cost', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('products','tax_class')) {
                $table->string('tax_class', 50)->default('standard');
            }

            // خصائص لوجستية
            if (!Schema::hasColumn('products','is_fragile')) {
                $table->boolean('is_fragile')->default(false);
            }
            if (!Schema::hasColumn('products','is_perishable')) {
                $table->boolean('is_perishable')->default(false);
            }
            if (!Schema::hasColumn('products','expiry_date')) {
                $table->date('expiry_date')->nullable();
            }

            // نصائح/تركيب
            if (!Schema::hasColumn('products','composition')) {
                $table->text('composition')->nullable();
            }
            if (!Schema::hasColumn('products','care_instructions')) {
                $table->text('care_instructions')->nullable();
            }

            // جمهور/حالة
            if (!Schema::hasColumn('products','age_group')) {
                $table->string('age_group', 30)->nullable();
            }
            if (!Schema::hasColumn('products','gender')) {
                $table->string('gender', 20)->nullable();
            }
            if (!Schema::hasColumn('products','condition')) {
                $table->string('condition', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach ([
                'weight','weight_unit','length','width','height','dimension_unit',
                'material','origin_country','brand','barcode','mpn','hs_code',
                'cost','tax_class','is_fragile','is_perishable','expiry_date',
                'composition','care_instructions','age_group','gender','condition'
            ] as $col) {
                if (Schema::hasColumn('products',$col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
