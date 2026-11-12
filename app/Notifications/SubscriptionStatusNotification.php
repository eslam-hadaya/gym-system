<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionStatusNotification extends Notification
{
    use Queueable;

    /**
     * @param Subscription $subscription كائن الاشتراك المعني
     * @param string $type نوع الإشعار: 'expiring_soon' أو 'expired'
     * @param int $daysRemaining الأيام المتبقية في حال كان الإشعار تحذيرياً
     */
    public function __construct(
        public Subscription $subscription,
        public string $type,
        public int $daysRemaining = 0,
    ) {}

    /**
     * تحديد قنوات الإرسال (قاعدة البيانات لعرضها في الداشبورد)
     */
    public function via(object $notifiable): array
    {
        return ["database"];
    }

    /**
     * هيكل البيانات المخزنة في جدول notifications
     */
    public function toDatabase(object $notifiable): array
    {
        $planName = $this->subscription->plan->name ?? "الباقة الرياضية";

        if ($this->type === "expiring_soon") {
            return [
                "title" => "اقتراب موعد انتهاء الاشتراك ⚠️",
                "message" => "عزيزي الكابتن، يتبقى في باقتك ({$planName}) {$this->daysRemaining} أيام فقط. يرجى التجديد لتفادي انقطاع صلاحية الدخول.",
                "type" => "warning",
                "action_url" => "/member/dashboard/subscriptions",
                "subscription_id" => $this->subscription->id,
                "created_at" => now()->format("Y-m-d H:i"),
            ];
        }

        return [
            "title" => "انتهت صلاحية اشتراكك 🛑",
            "message" => "عزيزي الكابتن، انتهت صلاحية باقتك ({$planName}) اليوم. يرجى مراجعة الإدارة وتجديد الاشتراك لاستئناف التمارين ومسح كود الـ QR.",
            "type" => "danger",
            "action_url" => "/member/dashboard/subscriptions",
            "subscription_id" => $this->subscription->id,
            "created_at" => now()->format("Y-m-d H:i"),
        ];
    }
}
