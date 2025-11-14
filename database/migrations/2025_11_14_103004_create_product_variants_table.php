<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // جدول product_variants راه ديجا متواجد فالداتا بيز،
        // وكاينين مایغريشنز أخرى كتضيف عليه أعمدة.
        // هنا ما ندير حتى حاجة، غير نخلي المايغريشن تدوز بنجاح.
    }

    public function down(): void
    {
        // ما غاديش نحيدو الطابلة هنا،
        // حيث كاينين مایغريشنز أخرى مرتبطة بيه.
        // إلا فالمستقبل احتجنا نحيدو، خاص خطة rollback كاملة.
    }
};
