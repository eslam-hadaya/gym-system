<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SubscriptionAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_dashboard_exposes_grace_period_countdown_deadline(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $plan = Plan::create([
            'name' => 'باقة شهرية',
            'days' => 30,
            'price' => 100000,
        ]);
        $member = $this->createMemberWithSubscription(
            $plan,
            'GYM-201',
            '0912345701',
            'active',
            today()->subDay()->toDateString(),
        );

        $this->actingAs($member->user)
            ->get('/member/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Member/Dashboard/Index')
                ->where('subscription.is_in_grace_period', true)
                ->where(
                    'subscription.grace_expires_at',
                    today()->addDay()->startOfDay()->toIso8601String(),
                ));

        $this->get('/member/dashboard/subscriptions')
            ->assertInertia(fn (Assert $page) => $page
                ->where('renewal.is_in_grace_period', true)
                ->where('renewal.has_active_subscription', true)
                ->where(
                    'renewal.grace_expires_at',
                    today()->addDay()->startOfDay()->toIso8601String(),
                ));
    }

    public function test_admin_sees_expiring_and_grace_badges_beside_member_names(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $plan = Plan::create([
            'name' => 'باقة شهرية',
            'days' => 30,
            'price' => 100000,
        ]);
        $this->createMemberWithSubscription(
            $plan,
            'GYM-202',
            '0912345702',
            'active',
            today()->addDays(3)->toDateString(),
        );
        $this->createMemberWithSubscription(
            $plan,
            'GYM-203',
            '0912345703',
            'active',
            today()->subDay()->toDateString(),
        );
        $this->createMemberWithSubscription(
            $plan,
            'GYM-204',
            '0912345704',
            'expired',
            today()->subDays(3)->toDateString(),
        );

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/dashboard/members')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard/Members')
                ->where('members.data', function (Collection $members): bool {
                    $alerts = $members->mapWithKeys(
                        fn (array $member): array => [$member['code'] => $member['subscription_alert']],
                    );

                    return $alerts->get('GYM-202') === 'expiring' &&
                        $alerts->get('GYM-203') === 'grace' &&
                        $alerts->get('GYM-204') === 'expired';
                }));

        $this->get('/admin/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->where('expiringSoonSubscriptions.0.alert_status', 'grace'));
    }

    private function createMemberWithSubscription(
        Plan $plan,
        string $memberCode,
        string $phone,
        string $memberStatus,
        string $endDate,
    ): Member {
        $user = User::factory()->create(['role' => 'member']);
        $member = Member::create([
            'user_id' => $user->id,
            'member_code' => $memberCode,
            'phone' => $phone,
            'gender' => 'male',
            'status' => $memberStatus,
        ]);

        Subscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'price_paid' => $plan->price,
            'payment_method' => 'cash',
            'payment_status' => 'verified',
            'start_date' => today()->subDays(30),
            'end_date' => $endDate,
        ]);

        return $member;
    }
}
