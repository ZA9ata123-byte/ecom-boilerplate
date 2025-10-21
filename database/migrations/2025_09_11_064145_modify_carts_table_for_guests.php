<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change(); // نرجعوه قابل للقيمة الفارغة
            $table->uuid('session_id')->nullable()->after('id'); // نزيدو عمود لتتبع الزوار
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropColumn('session_id');
        });
    }
};
