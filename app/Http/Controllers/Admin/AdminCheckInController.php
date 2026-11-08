<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCheckInController extends Controller
{
    public function index(): Response
    {
        // 1. طلبات الدخول المعلقة اليوم
        $pendingRequests = CheckIn::with([
            "member.user",
            "member.activeSubscription.plan",
        ])
            ->where("status", "pending")
            ->whereDate("created_at", Carbon::today())
            ->latest()
            ->get()
            ->map(
                fn($entry) => [
                    "id" => $entry->id,
                    "name" => $entry->member->user->name ?? "غير محدد",
                    "code" => $entry->member->member_code,
                    "plan" =>
                        $entry->member->activeSubscription?->plan?->name ??
                        "بدون باقة",
                    "time" => $entry->created_at->format("h:i A"),
                    "gender" =>
                        $entry->member->gender === "female" ? "أنثى" : "ذكر",
                ],
            );

        // 2. الحركات المسجلة اليوم
        $todayLogs = CheckIn::with([
            "member.user",
            "member.activeSubscription.plan",
        ])
            ->whereDate("created_at", Carbon::today())
            ->whereIn("status", ["valid", "rejected", "expired"])
            ->latest()
            ->take(20)
            ->get()
            ->map(
                fn($entry) => [
                    "id" => $entry->id,
                    "name" => $entry->member->user->name ?? "غير محدد",
                    "code" => $entry->member->member_code,
                    "plan" =>
                        $entry->member->activeSubscription?->plan?->name ??
                        "بدون باقة",
                    "time" => $entry->created_at->format("h:i A"),
                    "gender" =>
                        $entry->member->gender === "female" ? "أنثى" : "ذكر",
                    "status" => $entry->status,
                    "note" => $entry->note,
                ],
            );

        $validCount = CheckIn::whereDate("created_at", Carbon::today())
            ->where("status", "valid")
            ->count();
        $rejectedCount = CheckIn::whereDate("created_at", Carbon::today())
            ->whereIn("status", ["rejected", "expired"])
            ->count();

        return Inertia::render("Admin/Dashboard/CheckInDesk", [
            "pendingRequests" => $pendingRequests,
            "todayLogs" => $todayLogs,
            "stats" => [
                "total_today" => $validCount + $rejectedCount,
                "valid_count" => $validCount,
                "rejected_count" => $rejectedCount,
                "pending_count" => $pendingRequests->count(),
            ],
        ]);
    }

    /**
     * اعتماد طلب الدخول اليدوي مع التحقق من عدم تكرار الدوام
     */
    public function approveRequest(CheckIn $checkIn): RedirectResponse
    {
        $member = $checkIn->member;

        // فحص تكرار الدوام لنفس اليوم
        $alreadyCheckedIn = CheckIn::where("member_id", $member->id)
            ->where("id", "!=", $checkIn->id)
            ->where("status", "valid")
            ->whereDate("created_at", Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            $checkIn->update([
                "status" => "rejected",
                "note" => "رُفض: مسجل له دوام صالح اليوم مسبقاً",
            ]);

            return back()->with("scan_result", [
                "name" => $member->user->name,
                "code" => $member->member_code,
                "plan" => "—",
                "status" => "rejected",
                "note" => "مرفوض: اللاعب سجل حضوره اليوم مسبقاً!",
            ]);
        }

        $activeSub = $member->activeSubscription;

        if (
            !$activeSub ||
            $activeSub->payment_status !== "verified" ||
            $activeSub->end_date->isPast()
        ) {
            $checkIn->update([
                "status" => "rejected",
                "note" => "رُفض تلقائياً: اشتراك اللاعب غير سارٍ",
            ]);

            return back()->with("scan_result", [
                "name" => $member->user->name,
                "code" => $member->member_code,
                "plan" => "منتهٍ",
                "status" => "rejected",
                "note" => "تم رفض الدخول لعدم وجود اشتراك سارٍ",
            ]);
        }

        $checkIn->update([
            "status" => "valid",
            "note" => "دخول يدوي - بموافقة الاستقبال",
        ]);

        return back()->with("scan_result", [
            "name" => $member->user->name,
            "code" => $member->member_code,
            "plan" => $activeSub->plan->name,
            "status" => "valid",
            "note" => "تمت الموافقة والسماح بالدخول للتمرين",
        ]);
    }

    /**
     * رفض طلب الدخول اليدوي
     */
    public function rejectRequest(CheckIn $checkIn): RedirectResponse
    {
        $checkIn->update([
            "status" => "rejected",
            "note" => "تم رفض طلب الدخول من قبل موظف الاستقبال",
        ]);

        return back()->with("scan_result", [
            "name" => $checkIn->member->user->name,
            "code" => $checkIn->member->member_code,
            "plan" => "—",
            "status" => "rejected",
            "note" => "تم رفض طلب الدخول",
        ]);
    }

    /**
     * مسح الباركود أو إدخال كود العضوية مع التحقق من عدم تكرار الدوام
     */
    public function scan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "member_code" => ["required", "string"],
        ]);

        $code = trim($validated["member_code"]);
        $member = Member::with(["user", "activeSubscription.plan"])
            ->where("member_code", $code)
            ->first();

        if (!$member) {
            return back()->withErrors([
                "member_code" => "كود العضوية غير موجود بالنظام",
            ]);
        }

        // فحص تكرار الدوام لنفس اليوم
        $alreadyCheckedIn = CheckIn::where("member_id", $member->id)
            ->where("status", "valid")
            ->whereDate("created_at", Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            CheckIn::create([
                "member_id" => $member->id,
                "status" => "expired",
                "note" => "تكرار دخول: سجل حضوره اليوم مسبقاً",
            ]);

            return back()->with("scan_result", [
                "name" => $member->user->name,
                "code" => $member->member_code,
                "plan" => $member->activeSubscription?->plan?->name ?? "—",
                "status" => "rejected",
                "note" => "مرفوض: تم تسجيل حضور هذا اللاعب اليوم مسبقاً!",
            ]);
        }

        $activeSub = $member->activeSubscription;
        $status = "valid";
        $note = "دخول نظامي عبر المسح";

        if (
            !$activeSub ||
            $activeSub->payment_status !== "verified" ||
            !$activeSub->end_date ||
            $activeSub->end_date->isPast()
        ) {
            $status = "expired";
            $note = "اشتراك منتهٍ أو غير مفعل";
        }

        CheckIn::create([
            "member_id" => $member->id,
            "status" => $status,
            "note" => $note,
        ]);

        return back()->with("scan_result", [
            "name" => $member->user->name,
            "code" => $member->member_code,
            "plan" => $activeSub?->plan?->name ?? "بدون باقة",
            "status" => $status,
            "note" => $note,
        ]);
    }
}
