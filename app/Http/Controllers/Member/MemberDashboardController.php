<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Plan;
use App\Models\Subscription;
class MemberDashboardController extends Controller
{
    /**
     * شاشة الداشبورد وبطاقة الـ QR الرقمية
     */

    public function subscriptions(): Response
    {
        $user = Auth::user();
        $member = $user->member;

        if (! $member) {
            abort(403, 'الملف الرياضي غير موجود');
        }

        // جلب الباقات المتاحة للاشتراك
        $plans = Plan::where('is_active', true)
            ->orderBy('days')
            ->get(['id', 'name', 'days', 'price']);

        // آخر اشتراك للاعب (سواء كان معلقاً، سارياً، أو منتهياً)
        $latestSubscription = Subscription::with('plan')
            ->where('member_id', $member->id)
            ->latest()
            ->first();

        return Inertia::render('Member/Dashboard/Subscriptions', [
            'plans' => $plans,
            'currentSubscription' => $latestSubscription ? [
                'id' => $latestSubscription->id,
                'plan_name' => $latestSubscription->plan->name ?? 'باقة سابقة',
                'status' => $latestSubscription->payment_status,
                'method' => $latestSubscription->payment_method,
                'ref' => $latestSubscription->transaction_ref,
                'start_date' => $latestSubscription->start_date?->format('Y-m-d') ?? '—',
                'end_date' => $latestSubscription->end_date?->format('Y-m-d') ?? '—',
                'price_paid' => (int) $latestSubscription->price_paid,
            ] : null,
            'memberStatus' => $member->status,
        ]);
    }


    public function index(): Response
    {
        $user = Auth::user();
        $member = $user->member()->with(['activeSubscription.plan'])->first();

        return Inertia::render('Member/Dashboard/Index', [
            'member' => $member ? [
                'id' => $member->id,
                'member_code' => $member->member_code,
                'gender' => $member->gender === 'female' ? 'أنثى' : 'ذكر',
                'status' => $member->status,
                'plan' => $member->activeSubscription?->plan?->name ?? 'لا يوجد اشتراك سارٍ',
                'end_date' => $member->activeSubscription?->end_date?->format('Y-m-d') ?? '—',
            ] : null,
        ]);
    }

    /**
     * سجل حركات الحضور والتمارين للاعب
     */
    public function history(Request $request): Response
    {
        $user = Auth::user();
        $member = $user->member;

        if (! $member) {
            abort(403, 'الملف الرياضي غير موجود');
        }

        $checkIns = CheckIn::where('member_id', $member->id)
            ->latest()
            ->paginate(15)
            ->through(fn ($entry) => [
                'id' => $entry->id,
                'date' => $entry->created_at->format('Y-m-d'),
                'time' => $entry->created_at->format('h:i A'),
                'status' => $entry->status,
                'note' => $entry->note ?? 'حضور تمرين اعتيادي',
            ]);

        $stats = [
            'total_checkins' => CheckIn::where('member_id', $member->id)->where('status', 'valid')->count(),
            'this_month' => CheckIn::where('member_id', $member->id)
                ->where('status', 'valid')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return Inertia::render('Member/Dashboard/History', [
            'member' => [
                'name' => $user->name,
                'member_code' => $member->member_code,
                'status' => $member->status,
            ],
            'checkIns' => $checkIns,
            'stats' => $stats,
        ]);
    }
}
