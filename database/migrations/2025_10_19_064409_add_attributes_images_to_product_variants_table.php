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
            if (! Schema::hasColumn('product_variants', 'attributes')) {
                $table->json('attributes')->nullable()->after('stock');
            }
            if (! Schema::hasColumn('product_variants', 'images')) {
                $table->json('images')->nullable()->after('attributes');
            }
        });

        // إلا كان عندك عمود options وقديم، ننسخو ل attributes باش مايضيع والو
        if (Schema::hasColumn('product_variants', 'options') && Schema::hasColumn('product_variants', 'attributes')) {
            DB::statement('UPDATE product_variants SET attributes = options WHERE attributes IS NULL AND options IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'images')) {
                $table->dropColumn('images');
            }
            if (Schema::hasColumn('product_variants', 'attributes')) {
                $table->dropColumn('attributes');
            }
        });
    }
};
