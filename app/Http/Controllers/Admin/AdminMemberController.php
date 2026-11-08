<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminMemberController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query("search");
        $status = $request->query("status", "all");

        $query = Member::with(["user", "activeSubscription.plan"])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where("member_code", "ilike", "%{$search}%")
                    ->orWhere("phone", "like", "%{$search}%")
                    ->orWhereHas("user", function ($uq) use ($search) {
                        $uq->where("name", "ilike", "%{$search}%");
                    });
            });
        }

        if (in_array($status, ["active", "expired", "pending"], true)) {
            $query->where("status", $status);
        }

        $members = $query
            ->paginate(15)
            ->withQueryString()
            ->through(function ($member) {
                $activeSub = $member->activeSubscription;

                return [
                    "id" => $member->id,
                    "name" => $member->user->name ?? "غير محدد",
                    "phone" => $member->phone,
                    "code" => $member->member_code,
                    "gender" => $member->gender === "female" ? "أنثى" : "ذكر",
                    "plan" => $activeSub?->plan?->name ?? "لم يشترك بعد",
                    "status" => $member->status,
                    "expiry" => $activeSub?->end_date
                        ? $activeSub->end_date->format("Y-m-d")
                        : "—",
                ];
            });

        return Inertia::render("Admin/Dashboard/Members", [
            "members" => $members,
            "filters" => [
                "search" => $search ?? "",
                "status" => $status,
            ],
        ]);
    }
    public function cancelSubscription(
        \App\Models\Member $member,
    ): \Illuminate\Http\RedirectResponse {
        // جلب آخر اشتراك نشط أو معلق لهذا اللاعب
        $activeSub = \App\Models\Subscription::where("member_id", $member->id)
            ->whereIn("payment_status", ["verified", "pending"])
            ->where(function ($q) {
                $q->whereNull("end_date")->orWhere("end_date", ">", now());
            })
            ->latest("id")
            ->first();

        if (!$activeSub) {
            return back()->withErrors([
                "error" => "لا يوجد اشتراك نشط أو معلق لهذا اللاعب لإلغائه.",
            ]);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $activeSub,
            $member,
        ) {
            // نستخدم rejected لتوافق قيد PostgreSQL
            $activeSub->update([
                "payment_status" => "rejected",
                "end_date" => now(),
            ]);

            // إعادة حالة اللاعب إلى pending
            $member->update([
                "status" => "pending",
            ]);
        });

        return back()->with("status", "member-subscription-cancelled");
    }
}
