<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MemberDashboardController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {}

    /**
     * شاشة الداشبورد وبطاقة الـ QR وحالة الحساب مع فحص تنبيه الـ 5 أيام
     */
    public function index(): Response
    {
        $user = Auth::user();
        $member = $user->member;

        if (! $member) {
            abort(403, 'الملف الرياضي غير موجود');
        }

        // جلب آخر اشتراك نشط ومعتمد
        $activeSub = Subscription::query()
            ->select('subscriptions.*', 'plans.name as plan_name')
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->where('member_id', $member->id)
            ->currentForAccess()
            ->latest('end_date')
            ->first();
        $isInGracePeriod = $activeSub?->end_date?->lt(today()) ?? false;

        // جلب آخر 5 حركات حضور للاعب
        $recentCheckIns = CheckIn::where('member_id', $member->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(
                fn ($c) => [
                    'id' => $c->id,
                    'created_at' => $c->created_at->format('Y-m-d h:i A'),
                    'status' => $c->status,
                ],
            );

        $todayCheckIns = CheckIn::where('member_id', $member->id)
            ->whereDate('created_at', Carbon::today())
            ->selectRaw(
                'COALESCE(MAX(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS has_checked_in_today',
                ['valid'],
            )
            ->selectRaw(
                'COALESCE(MAX(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS has_pending_request',
                ['pending'],
            )
            ->first();

        return Inertia::render('Member/Dashboard/Index', [
            'member' => [
                'id' => $member->id,
                'name' => $user->name,
                'member_code' => $member->member_code,
                'gender' => $member->gender,
                'status' => $activeSub ? 'active' : $member->status,
            ],
            'subscription' => $activeSub
                ? [
                    'plan_name' => $activeSub->plan_name ?? 'باقة رياضية',
                    'start_date' => $activeSub->start_date?->format('Y-m-d'),
                    'end_date' => $activeSub->end_date?->format('Y-m-d'),
                    'remaining_days' => $activeSub->days_remaining,
                    'is_expiring_soon' => $activeSub->is_expiring_soon,
                    'is_in_grace_period' => $isInGracePeriod,
                    'grace_expires_at' => $isInGracePeriod
                        ? $activeSub->end_date->copy()->addDays(2)->startOfDay()->toIso8601String()
                        : null,
                ]
                : null,
            'recentCheckIns' => $recentCheckIns,
            'hasCheckedInToday' => (bool) $todayCheckIns->has_checked_in_today,
            'hasPendingCheckIn' => (bool) $todayCheckIns->has_pending_request,
        ]);
    }

    /**
     * شاشة تجديد واختيار الباقات مع فحص شرط الـ 10 أيام
     */
    public function subscriptions(): Response
    {
        $user = Auth::user();
        $member = $user->member;

        if (! $member) {
            abort(403, 'الملف الرياضي غير موجود');
        }

        $plans = Plan::where('is_active', true)
            ->orderBy('days')
            ->get(['id', 'name', 'days', 'price']);

        $latestSubscription = Subscription::with('plan')
            ->where('member_id', $member->id)
            ->latest('id')
            ->first();

        $activeSubscription = Subscription::where('member_id', $member->id)
            ->currentForAccess()
            ->latest('end_date')
            ->first();
        $isInGracePeriod = $activeSubscription?->end_date?->lt(today()) ?? false;
        $latestSubscriptionIsInGracePeriod =
            $latestSubscription?->payment_status === 'verified' &&
            ($latestSubscription->end_date?->lt(today()) ?? false);

        return Inertia::render('Member/Dashboard/Subscriptions', [
            'plans' => $plans,
            'renewal' => [
                'has_active_subscription' => (bool) $activeSubscription,
                'end_date' => $activeSubscription?->end_date?->format(
                    'Y-m-d',
                ),
                'remaining_days' => $activeSubscription?->days_remaining ?? 0,
                'is_in_grace_period' => $isInGracePeriod,
                'grace_expires_at' => $isInGracePeriod
                    ? $activeSubscription->end_date->copy()->addDays(2)->startOfDay()->toIso8601String()
                    : null,
            ],
            'currentSubscription' => $latestSubscription
                ? [
                    'id' => $latestSubscription->id,
                    'plan_name' => $latestSubscription->plan->name ?? 'باقة سابقة',
                    'status' => $latestSubscription->payment_status,
                    'method' => $latestSubscription->payment_method,
                    'ref' => $latestSubscription->transaction_ref,
                    'start_date' => $latestSubscription->start_date?->format('Y-m-d') ??
                        '—',
                    'end_date' => $latestSubscription->end_date?->format('Y-m-d') ?? '—',
                    'price_paid' => (int) $latestSubscription->price_paid,
                    'remaining_days' => $latestSubscription->days_remaining,
                    'is_expiring_soon' => $latestSubscription->is_expiring_soon,
                    'is_in_grace_period' => $latestSubscriptionIsInGracePeriod,
                ]
                : null,
            'memberStatus' => $member->status,
        ]);
    }

    /**
     * استقبال وحفظ طلب الاشتراك الجديد (شام كاش أو نقداً)
     */
    public function storeSubscription(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $member = $user->member;

        if (! $member) {
            abort(403, 'الملف الرياضي غير موجود');
        }

        $activeSub = Subscription::where('member_id', $member->id)
            ->currentForAccess()
            ->latest('end_date')
            ->first();

        if ($activeSub) {
            $remainingDays = $activeSub->days_remaining;
            if (! $activeSub->can_renew) {
                return back()->withErrors([
                    'plan_id' => "لا يمكنك تجديد الاشتراك حالياً؛ يتبقى في اشتراكك {$remainingDays} يوماً. التجديد متاح فقط في آخر 10 أيام.",
                ]);
            }
        }

        if ($request->input('payment_method') === 'cash') {
            $request->merge(['transaction_ref' => null]);
        }

        $validated = $request->validate(
            [
                'plan_id' => [
                    'required',
                    'integer',
                    Rule::exists('plans', 'id')->where('is_active', true),
                ],
                'payment_method' => ['required', 'string', 'in:shamcash,cash'],
                'transaction_ref' => [
                    'nullable',
                    'string',
                    'max:100',
                    Rule::requiredIf($request->payment_method === 'shamcash'),
                ],
            ],
            [
                'plan_id.required' => 'يرجى اختيار باقة رياضية للاشتراك.',
                'plan_id.exists' => 'الباقة المحددة غير متوفرة أو معطلة حالياً.',
                'payment_method.required' => 'يرجى تحديد طريقة الدفع (شام كاش أو نقداً).',
                'payment_method.in' => 'طريقة الدفع المحددة غير مدعومة.',
                'transaction_ref.required_if' => 'يرجى إدخال رقم إشعار عملية تحويل شام كاش.',
            ],
        );

        $this->subscriptionService->createPendingSubscription(
            member: $member,
            planId: (int) $validated['plan_id'],
            paymentMethod: $validated['payment_method'],
            transactionRef: $validated['payment_method'] === 'shamcash'
                ? $validated['transaction_ref']
                : null,
        );

        return redirect('/member/dashboard/subscriptions')->with(
            'status',
            'subscription-requested',
        );
    }

    /**
     * تقديم طلب دخول يدوي للتمرين مع قيد منع تكرار الدوام باليوم الواحد
     */
    public function requestCheckIn(): RedirectResponse
    {
        $user = Auth::user();
        $member = $user->member;

        if (! $member) {
            abort(403, 'الملف الرياضي غير موجود');
        }

        $activeSub = Subscription::where('member_id', $member->id)
            ->currentForAccess()
            ->first();

        if (! $activeSub) {
            return back()->withErrors([
                'checkin' => 'لا يمكنك طلب الدخول؛ ليس لديك اشتراك نشط ومفعّل.',
            ]);
        }

        // قيد منع تكرار الدوام في نفس اليوم
        $alreadyCheckedIn = CheckIn::where('member_id', $member->id)
            ->where('status', 'valid')
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            return back()->withErrors([
                'checkin' => 'لقد سجلت حضورك وتمرينك اليوم بالفعل! لا يُسمح بتسجيل أكثر من دوام في يوم واحد.',
            ]);
        }

        // فحص وجود طلب معلق مسبقاً اليوم
        $existingPending = CheckIn::where('member_id', $member->id)
            ->where('status', 'pending')
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if ($existingPending) {
            return back()->withErrors([
                'checkin' => 'لديك طلب دخول معلق بالفعل بانتظار موافقة الاستقبال اليوم.',
            ]);
        }

        CheckIn::create([
            'member_id' => $member->id,
            'status' => 'pending',
            'note' => 'طلب دخول يدوي من بوابة اللاعب',
        ]);

        return back()->with('status', 'checkin-requested');
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
            ->through(
                fn ($entry) => [
                    'id' => $entry->id,
                    'date' => $entry->created_at->format('Y-m-d'),
                    'time' => $entry->created_at->format('h:i A'),
                    'status' => $entry->status,
                    'note' => $entry->note ?? 'حضور تمرين اعتيادي',
                ],
            );

        $stats = [
            'total_checkins' => CheckIn::where('member_id', $member->id)
                ->where('status', 'valid')
                ->count(),
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

    /**
     * إلغاء الاشتراك الحالي للاعب مع إيقاف الصلاحية فوراً
     */
    public function cancelSubscription(): RedirectResponse
    {
        $user = Auth::user();
        $member = $user->member;

        if (! $member) {
            abort(403, 'الملف الرياضي غير موجود');
        }

        $subscription = Subscription::where('member_id', $member->id)
            ->whereIn('payment_status', ['verified', 'pending'])
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            })
            ->latest('id')
            ->first();

        if (! $subscription) {
            return back()->withErrors([
                'cancel' => 'لا يوجد اشتراك نشط أو معلق لإلغائه حالياً.',
            ]);
        }

        DB::transaction(function () use ($subscription, $member) {
            $subscription->update([
                'payment_status' => 'rejected',
                'end_date' => now(),
            ]);

            $member->update([
                'status' => 'pending',
            ]);
        });

        return redirect('/member/dashboard/subscriptions')->with(
            'status',
            'subscription-cancelled',
        );
    }
}
