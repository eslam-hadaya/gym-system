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
        Schema::create('members', function (Blueprint $table) {
                $table->id();

                // ربط العضو بحسابه في جدول المستخدمين لتسجيل الدخول
                $table->foreignId('user_id')
                      ->constrained('users')
                      ->cascadeOnDelete();

                // كود العضوية الفريد (مثال: GYM-1001) لتوليد رمز الـ QR
                $table->string('member_code')->unique();

                // رقم الهاتف مفهرس للبحث السريع في شاشة الاستقبال عند انقطاع النت
                $table->string('phone')->unique()->index();

                // رقم هاتف للطوارئ (اختياري)
                $table->string('emergency_contact')->nullable();

                // الجنس لتنظيم التقارير أو فترات تدريب الصالة
                $table->enum('gender', ['male', 'female']);

                // حالة العضو العامة في النادي
                $table->enum('status', ['active', 'inactive'])->default('active');

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
