<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    /**
     * تسجيل طلب اشتراك جديد معتمد على الباقة الديناميكية
     */
    public function createPendingSubscription(
        Member $member,
        int $planId,
        string $paymentMethod,
        ?string $transactionRef = null,
    ): Subscription {
        // 1. جلب الباقة مباشرة من الداتابيز والتأكد من تفعيلها
        $plan = Plan::where("id", $planId)
            ->where("is_active", true)
            ->firstOrFail();

        // 2. التحقق من حوالة شام كاش ومنع تكرار الإشعار
        if ($paymentMethod === "shamcash") {
            if (empty($transactionRef)) {
                throw ValidationException::withMessages([
                    "transaction_ref" =>
                        "يرجى إدخال رقم إشعار عملية تحويل شام كاش.",
                ]);
            }

            $alreadyUsed = Subscription::where(
                "transaction_ref",
                $transactionRef,
            )->exists();
            if ($alreadyUsed) {
                throw ValidationException::withMessages([
                    "transaction_ref" =>
                        "رقم هذه الحوالة مسجل مسبقاً في النظام ولا يمكن استخدامه مجدداً.",
                ]);
            }
        }

        // 3. تثبيت سعر الباقة وقت الطلب لمنع تأثره بتغيرات الأسعار اللاحقة
        return Subscription::create([
            "member_id" => $member->id,
            "plan_id" => $plan->id,
            "price_paid" => $plan->price,
            "payment_method" => $paymentMethod,
            "payment_status" => "pending",
            "transaction_ref" =>
                $paymentMethod === "shamcash" ? $transactionRef : null,
        ]);
    }

    /**
     * اعتماد الدفعة وحساب التواريخ تراكمياً بناءً على حقل days في الباقة
     */
    public function approveSubscription(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $member = $subscription->member;
            $plan = $subscription->plan;

            // جلب آخر اشتراك نشط وسارٍ للاعب إن وجد
            $currentActiveSub = Subscription::where("member_id", $member->id)
                ->where("payment_status", "verified")
                ->whereDate("end_date", ">=", Carbon::today())
                ->orderByDesc("end_date")
                ->first();

            // التجديد التراكمي: إذا كان لديه أيام متبقية نبدأ من نهايتها، وإلا نبدأ من لحظة الاعتماد
            $startDate = $currentActiveSub
                ? Carbon::parse($currentActiveSub->end_date)
                : Carbon::now();

            // إضافة الأيام المحددة في الباقة ديناميكياً
            $endDate = (clone $startDate)->addDays($plan->days);

            $subscription->update([
                "payment_status" => "verified",
                "start_date" => $startDate,
                "end_date" => $endDate,
            ]);

            // تفعيل حالة اللاعب في الصالة ليفتح له الباب بالـ QR مباشرة
            $member->update([
                "status" => "active",
            ]);
        });
    }

    /**
     * رفض الطلب
     */
    public function rejectSubscription(Subscription $subscription): void
    {
        $subscription->update([
            "payment_status" => "rejected",
        ]);
    }
}
