<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AdminFinanceController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {}

    public function index(Request $request): Response
    {
        $method = $request->query("method", "all");

        $query = Subscription::with(["member.user", "plan"])->latest();

        if (in_array($method, ["cash", "shamcash"], true)) {
            $query->where("payment_method", $method);
        }

        $transactions = $query->paginate(15)->through(
            fn($sub) => [
                "id" => "TXN-" . str_pad($sub->id, 5, "0", STR_PAD_LEFT),
                "subscription_id" => $sub->id,
                "member" => $sub->member->user->name ?? "غير محدد",
                "member_code" => $sub->member->member_code ?? "—",
                "phone" =>
                    $sub->member->phone ?? ($sub->member->user->phone ?? "—"),
                "plan_name" => $sub->plan->name ?? "باقة ملغاة",
                "plan_days" => $sub->plan->days ?? 0,
                "amount" => (int) $sub->price_paid,
                "method" => $sub->payment_method,
                "ref" =>
                    $sub->transaction_ref ?:
                    ($sub->payment_method === "cash"
                        ? "مكتب الكاشير"
                        : "—"),
                "date" => $sub->created_at->format("Y-m-d H:i"),
                "status" => $sub->payment_status,
            ],
        );

        $verifiedSubs = Subscription::where("payment_status", "verified");

        $totalIncome = (clone $verifiedSubs)->sum("price_paid");
        $shamCashIncome = (clone $verifiedSubs)
            ->where("payment_method", "shamcash")
            ->sum("price_paid");
        $cashIncome = (clone $verifiedSubs)
            ->where("payment_method", "cash")
            ->sum("price_paid");
        $pendingCount = Subscription::where(
            "payment_status",
            "pending",
        )->count();

        return Inertia::render("Admin/Dashboard/Finance", [
            "transactions" => $transactions,
            "currentMethod" => $method,
            "stats" => [
                "total_income" => (int) $totalIncome,
                "shamcash_income" => (int) $shamCashIncome,
                "cash_income" => (int) $cashIncome,
                "pending_count" => $pendingCount,
            ],
        ]);
    }

    /**
     * اعتماد الدفعة وتفعيل العضوية بالتجديد التراكمي
     */
    public function verify(Subscription $subscription): RedirectResponse
    {
        if ($subscription->payment_status === "verified") {
            return back()->with("status", "already-verified");
        }

        try {
            // محاولة استدعاء ميثود السيرفس إن وُجد، أو التنفيذ الآمن المباشر
            if (
                method_exists($this->subscriptionService, "approveSubscription")
            ) {
                $this->subscriptionService->approveSubscription($subscription);
            } elseif (
                method_exists($this->subscriptionService, "verifySubscription")
            ) {
                $this->subscriptionService->verifySubscription($subscription);
            } else {
                // اعتماد تراكمي مباشر وسليم يحترم قيود قاعدة البيانات
                DB::transaction(function () use ($subscription) {
                    $planDays = $subscription->plan->days ?? 30;
                    $member = $subscription->member;

                    // حساب بداية ونهاية الاشتراك بالتجديد التراكمي
                    $latestActiveSub = Subscription::where(
                        "member_id",
                        $member->id,
                    )
                        ->where("payment_status", "verified")
                        ->where("end_date", ">", now())
                        ->latest("end_date")
                        ->first();

                    $startDate = $latestActiveSub
                        ? $latestActiveSub->end_date
                        : now();
                    $endDate = (clone $startDate)->addDays($planDays);

                    $subscription->update([
                        "payment_status" => "verified",
                        "start_date" => $startDate,
                        "end_date" => $endDate,
                    ]);

                    // تحديث حالة العضو إلى active
                    $member->update([
                        "status" => "active",
                    ]);
                });
            }

            return back()->with("status", "subscription-verified");
        } catch (\Throwable $e) {
            Log::error("Finance Approval Error: " . $e->getMessage(), [
                "exception" => $e,
            ]);

            return back()->withErrors([
                "error" => "تعذر الاعتماد: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * رفض الدفعة وإلغاء الطلب
     */
    public function reject(Subscription $subscription): RedirectResponse
    {
        if ($subscription->payment_status === "rejected") {
            return back()->with("status", "already-rejected");
        }

        try {
            if (
                method_exists($this->subscriptionService, "rejectSubscription")
            ) {
                $this->subscriptionService->rejectSubscription($subscription);
            } else {
                $subscription->update([
                    "payment_status" => "rejected",
                ]);
            }

            return back()->with("status", "subscription-rejected");
        } catch (\Throwable $e) {
            Log::error("Finance Reject Error: " . $e->getMessage(), [
                "exception" => $e,
            ]);

            return back()->withErrors([
                "error" => "تعذر الرفض: " . $e->getMessage(),
            ]);
        }
    }
}
