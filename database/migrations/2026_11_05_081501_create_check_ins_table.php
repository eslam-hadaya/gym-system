<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
                $table->id();

                // ربط حركة الدخول بالعضو
                $table->foreignId('member_id')
                      ->constrained('members')
                      ->cascadeOnDelete();

                // ربط حركة الدخول بعقد الاشتراك الذي تم الدخول بناءً عليه (للتدقيق لاحقاً)
                $table->foreignId('subscription_id')
                      ->nullable()
                      ->constrained('subscriptions')
                      ->nullOnDelete();

                // التوقيت الزمني الدقيق للدخول (مفهرس للتقارير اليومية والشهرية)
                $table->timestamp('checked_in_at')->index();

                // نتيجة عملية الفحص على الباب
                $table->enum('status', [
                    'granted',           // دخول نظامي واشتراك ساري
                    'denied_expired',    // اشتراك منتهي
                    'denied_unpaid',     // اشتراك معلق غير مدفوع
                    'denied_no_sub'      // لا يملك أي اشتراك
                ])->default('granted');

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
