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
        Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();

                // ربط الاشتراك باللاعب
                $table->foreignId('member_id')
                      ->constrained('members')
                      ->cascadeOnDelete();

                // ربط الاشتراك بالباقة المختارة
                // restrictOnDelete: يمنع حذف الباقة من جدول plans إذا كان هناك لاعبون اشتركوا بها مسبقاً
                $table->foreignId('plan_id')
                      ->constrained('plans')
                      ->restrictOnDelete();

                // التواريخ الزمنية للعقد
                $table->date('start_date');
                $table->date('end_date')->index(); // مفهرس لسرعة فحص الحضور اليومي

                // السعر الفعلي المدفوع لحظة الاشتراك (Financial Snapshot)
                $table->decimal('price_paid', 12, 2);

                // تفاصيل قناة وطريقة الدفع
                $table->enum('payment_method', ['cash', 'sham_cash']);
                $table->enum('payment_status', ['paid', 'pending', 'failed'])->default('pending');

                // الحقول الخاصة بالدفع الإلكتروني (شام كاش - Webhook)
                // رقم العملية المرجعي العائد من سيرفر شام كاش للتدقيق المحاسبي
                $table->string('transaction_reference')->nullable()->unique();

                // الحقول الخاصة بالدفع المكتبي (الكاش المباشر)
                // معرف الموظف/المدرب الذي استلم المبلغ نقداً في الصندوق
                $table->foreignId('collected_by')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
