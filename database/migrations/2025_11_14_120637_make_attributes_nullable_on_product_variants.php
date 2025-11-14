<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Postgres: نخلي attributes تقبل NULL
        DB::statement('ALTER TABLE product_variants ALTER COLUMN attributes DROP NOT NULL');
    }

    public function down(): void
    {
        // لو بغينا نرجعو constraint (اختياري)
        DB::statement('ALTER TABLE product_variants ALTER COLUMN attributes SET NOT NULL');
    }
};
