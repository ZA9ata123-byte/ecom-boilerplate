<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'type')) {
                $table->string('type')->default('simple')->index();
            }
            if (! Schema::hasColumn('products', 'status')) {
                // حيدنا after('stock') باش مايتعلقش بوجود العمود
                $table->string('status')->default('active')->index();
            }
            if (! Schema::hasColumn('products', 'image_url')) {
                $table->string('image_url')->nullable();
            }
            if (! Schema::hasColumn('products', 'gallery')) {
                $table->json('gallery')->nullable();
            }
            if (! Schema::hasColumn('products', 'attributes')) {
                $table->json('attributes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'attributes')) {
                $table->dropColumn('attributes');
            }
            if (Schema::hasColumn('products', 'gallery')) {
                $table->dropColumn('gallery');
            }
            if (Schema::hasColumn('products', 'image_url')) {
                $table->dropColumn('image_url');
            }
            if (Schema::hasColumn('products', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('products', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
