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
        Schema::create('plans', function (Blueprint $table) {
                $table->id();

                // اسم الباقة (مثلاً: شهري عادي، 3 أشهر، VIP)
                $table->string('name');

                // وصف مختصر للباقة والمميزات المشمولة (اختياري)
                $table->text('description')->nullable();

                // السعر الحالي للباقة بالليرة السورية
                $table->decimal('price', 12, 2);

                // مدة الباقة بالأيام (مثلاً: 30، 90، 180، 365)
                $table->unsignedInteger('duration_days');

                // لتفعيل أو إيقاف ظهور الباقة للمشتركين الجدد دون حذفها
                $table->boolean('is_active')->default(true);

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
