<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // نزيدو الكولون غير إلا ماكانش من قبل
            if (! Schema::hasColumn('carts', 'cart_token')) {
                // UUID بدون default (Postgres كيدعم uuid)
                $table->uuid('cart_token')
                    ->nullable()
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'cart_token')) {
                $table->dropColumn('cart_token');
            }
        });
    }
};
