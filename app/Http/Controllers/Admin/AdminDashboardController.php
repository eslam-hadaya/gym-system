<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {}

    public function index(): Response
    {
        $totalMembers = Member::count();

        $activeSubscriptionsCount = Subscription::currentForAccess()->count();

        $todayCheckInsCount = CheckIn::whereDate(
            'created_at',
            today(),
        )->count();

        $todayIncome = Subscription::where('payment_status', 'verified')
            ->whereDate('created_at', today())
            ->sum('price_paid');

        $retentionRate =
            $totalMembers > 0
                ? round(($activeSubscriptionsCount / $totalMembers) * 100)
                : 0;

        $stats = [
            [
                'label' => 'إجمالي الأعضاء',
                'value' => (string) $totalMembers,
                'change' => Member::whereMonth('created_at', now()->month)->count().
                    '+ هذا الشهر',
                'icon' => '👥',
                'color' => 'text-blue-400',
            ],
            [
                'label' => 'اشتراكات سارية',
                'value' => (string) $activeSubscriptionsCount,
                'change' => "{$retentionRate}% نسبة الالتزام",
                'icon' => '⚡',
                'color' => 'text-amber-400',
            ],
            [
                'label' => 'حضور اليوم بالـ QR',
                'value' => (string) $todayCheckInsCount,
                'change' => 'سجل الدخول اليومي',
                'icon' => '📷',
                'color' => 'text-emerald-400',
            ],
            [
                'label' => 'إيراد اليوم المعتمد',
                'value' => number_format($todayIncome).' ل.س',
                'change' => 'كاش + شام كاش',
                'icon' => '💰',
                'color' => 'text-emerald-400',
            ],
        ];

        // الاشتراكات القريبة من الانتهاء أو الواقعة ضمن مهلة السماح
        $expiringSoonSubscriptions = Subscription::with(['member.user', 'plan'])
            ->latestVerifiedForMember()
            ->whereDate('end_date', '>=', today()->subDay())
            ->whereDate('end_date', '<=', today()->addDays(5))
            ->orderBy('end_date', 'asc')
            ->get()
            ->map(
                fn ($sub) => [
                    'id' => $sub->id,
                    'member_name' => $sub->member->user->name ?? 'غير محدد',
                    'member_code' => $sub->member->member_code,
                    'plan_name' => $sub->plan->name ?? 'باقة رياضية',
                    'end_date' => $sub->end_date?->format('Y-m-d') ?? '—',
                    'alert_status' => $sub->end_date?->lt(today()) ? 'grace' : 'expiring',
                    'remaining_days' => max(
                        0,
                        (int) now()
                            ->startOfDay()
                            ->diffInDays($sub->end_date?->startOfDay(), false),
                    ),
                ],
            );

        $recentCheckIns = CheckIn::with([
            'member.user',
            'member.activeSubscription.plan',
        ])
            ->latest()
            ->take(6)
            ->get()
            ->map(
                fn ($entry) => [
                    'id' => $entry->id,
                    'name' => $entry->member->user->name ?? 'غير محدد',
                    'code' => $entry->member->member_code,
                    'plan' => $entry->member->activeSubscription->plan->name ??
                        'بدون باقة',
                    'time' => $entry->created_at->diffForHumans(),
                    'gender' => $entry->member->gender === 'female' ? 'أنثى' : 'ذكر',
                    'status' => $entry->status,
                ],
            );

        $pendingSubscriptions = Subscription::with(['member.user', 'plan'])
            ->where('payment_status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(
                fn ($sub) => [
                    'id' => $sub->id,
                    'name' => $sub->member->user->name ?? 'غير محدد',
                    'phone' => $sub->member->phone,
                    'plan' => $sub->plan->name ?? 'باقة ملغاة',
                    'method' => $sub->payment_method === 'shamcash'
                            ? 'Sham Cash'
                            : 'Cash Desk',
                    'ref' => $sub->transaction_ref ?:
                        ($sub->payment_method === 'cash'
                            ? 'مكتب الكاشير'
                            : '—'),
                    'amount' => number_format($sub->price_paid).' ل.س',
                ],
            );

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $stats,
            'recentCheckIns' => $recentCheckIns,
            'pendingSubscriptions' => $pendingSubscriptions,
            'expiringSoonSubscriptions' => $expiringSoonSubscriptions,
        ]);
    }

    public function approveSubscription(
        Subscription $subscription,
    ): RedirectResponse {
        if ($subscription->payment_status === 'verified') {
            return back()->with('status', 'already-verified');
        }

        $this->subscriptionService->approveSubscription($subscription);

        return back()->with('status', 'subscription-approved');
    }

    public function rejectSubscription(
        Subscription $subscription,
    ): RedirectResponse {
        $subscription->update([
            'payment_status' => 'rejected',
        ]);

        return back()->with('status', 'subscription-rejected');
    }

    /**
     * إلغاء اشتراك لاعب من قبل الأدمن
     */
    public function cancelSubscription(
        Subscription $subscription,
    ): RedirectResponse {
        DB::transaction(function () use ($subscription) {
            $subscription->update([
                'payment_status' => 'rejected',
                'end_date' => now(),
            ]);

            $hasOtherActive = Subscription::where('member_id', $subscription->member_id)
                ->where('id', '!=', $subscription->id)
                ->currentForAccess()
                ->exists();

            if (! $hasOtherActive) {
                $subscription->member->update([
                    'status' => 'pending',
                ]);
            }
        });

        return back()->with('status', 'subscription-cancelled');
    }
}
