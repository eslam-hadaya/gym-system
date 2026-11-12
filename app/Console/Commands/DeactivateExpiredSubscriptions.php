<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\Subscription;
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeactivateExpiredSubscriptions extends Command
{
    protected $signature = "subscriptions:deactivate-expired";
    protected $description = "فحص الاشتراكات، إلغاء المنتهي، وإرسال تنبيهات تحذيرية للمشتركين";

    public function handle(): int
    {
        $this->info("بدء فحص الاشتراكات وإرسال الإشعارات...");
        $now = now();

        // 1. إرسال تنبيهات اقتراب الانتهاء (من يتبقى له بين 1 و 3 أيام)
        $expiringSubscriptions = Subscription::with("member.user", "plan")
            ->where("payment_status", "verified")
            ->whereBetween("end_date", [
                $now,
                $now->copy()->addDays(3)->endOfDay(),
            ])
            ->get();

        foreach ($expiringSubscriptions as $sub) {
            $user = $sub->member?->user;
            if ($user) {
                $days = max(
                    0,
                    (int) now()
                        ->startOfDay()
                        ->diffInDays($sub->end_date->startOfDay(), false),
                );
                $user->notify(
                    new SubscriptionStatusNotification(
                        $sub,
                        "expiring_soon",
                        $days,
                    ),
                );
            }
        }

        // 2. معالجة المنتهين وتحديث حالتهم وإشعارهم
        $expiredMembersCount = 0;

        DB::transaction(function () use ($now, &$expiredMembersCount) {
            $membersToDeactivate = Member::where("status", "active")
                ->whereDoesntHave("subscriptions", function ($query) use (
                    $now,
                ) {
                    $query
                        ->where("payment_status", "verified")
                        ->where("end_date", ">", $now);
                })
                ->with([
                    "user",
                    "subscriptions" => fn($q) => $q
                        ->latest("end_date")
                        ->take(1),
                ])
                ->get();

            $expiredMembersCount = $membersToDeactivate->count();

            foreach ($membersToDeactivate as $member) {
                $member->update(["status" => "inactive"]);

                // إرسال إشعار الانتهاء للمستخدم
                if ($member->user && $member->subscriptions->first()) {
                    $member->user->notify(
                        new SubscriptionStatusNotification(
                            $member->subscriptions->first(),
                            "expired",
                        ),
                    );
                }
            }
        });

        $this->info(
            "تمت المعالجة بنجاح: تنبيه المشتركين الموشكين على الانتهاء، وتحويل {$expiredMembersCount} عضواً إلى غير نشط مع إشعارهم.",
        );

        return self::SUCCESS;
    }
}
