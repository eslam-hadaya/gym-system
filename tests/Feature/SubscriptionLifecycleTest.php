<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionStatusNotification;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_member_keeps_access_during_grace_then_is_deactivated(): void
    {
        $this->travelTo('2026-11-12 00:01:00');

        $user = User::factory()->create();
        $member = Member::create([
            'user_id' => $user->id,
            'member_code' => 'GYM-001',
            'phone' => '0912345678',
            'gender' => 'male',
            'status' => 'active',
        ]);
        $plan = Plan::create([
            'name' => 'باقة شهرية',
            'days' => 30,
            'price' => 100000,
        ]);
        $subscription = Subscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'price_paid' => 100000,
            'payment_method' => 'cash',
            'payment_status' => 'verified',
            'start_date' => '2026-10-13',
            'end_date' => '2026-11-12',
        ]);

        $this->artisan('subscriptions:deactivate-expired')->assertSuccessful();

        $this->assertSame('active', $member->fresh()->status);
        $this->assertTrue($member->activeSubscription()->exists());

        $this->travelTo('2026-11-13 00:01:00');

        $this->artisan('subscriptions:deactivate-expired')->assertSuccessful();

        $this->assertSame('active', $member->fresh()->status);
        $this->assertTrue($member->activeSubscription()->exists());

        $this->travelTo('2026-11-14 00:01:00');

        $this->artisan('subscriptions:deactivate-expired')->assertSuccessful();

        $this->assertSame('expired', $member->fresh()->status);
        $this->assertFalse($member->activeSubscription()->exists());
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => SubscriptionStatusNotification::class,
        ]);
        $this->assertModelExists($subscription);
    }

    public function test_admin_dashboard_lists_subscriptions_ending_today(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $memberUser = User::factory()->create();
        $member = Member::create([
            'user_id' => $memberUser->id,
            'member_code' => 'GYM-002',
            'phone' => '0912345679',
            'gender' => 'female',
            'status' => 'active',
        ]);
        $plan = Plan::create([
            'name' => 'باقة شهرية',
            'days' => 30,
            'price' => 100000,
        ]);
        Subscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'price_paid' => 100000,
            'payment_method' => 'cash',
            'payment_status' => 'verified',
            'start_date' => '2026-10-14',
            'end_date' => '2026-11-13',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard/Index')
                ->has('expiringSoonSubscriptions', 1)
                ->where('expiringSoonSubscriptions.0.member_name', $memberUser->name)
                ->where('expiringSoonSubscriptions.0.remaining_days', 0));
    }

    public function test_approving_payment_activates_member_and_clears_subscription_alerts(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $user = User::factory()->create();
        $member = Member::create([
            'user_id' => $user->id,
            'member_code' => 'GYM-003',
            'phone' => '0912345680',
            'gender' => 'male',
            'status' => 'expired',
        ]);
        $plan = Plan::create([
            'name' => 'باقة شهرية',
            'days' => 30,
            'price' => 100000,
        ]);
        Subscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'price_paid' => 100000,
            'payment_method' => 'cash',
            'payment_status' => 'verified',
            'start_date' => '2026-10-16',
            'end_date' => '2026-11-15',
        ]);
        $subscription = Subscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'price_paid' => 100000,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
        ]);
        $user->notify(
            new SubscriptionStatusNotification($subscription, 'expiring_soon', 1),
        );
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'other-alert',
            'data' => ['title' => 'تنبيه آخر'],
        ]);

        app(SubscriptionService::class)->approveSubscription($subscription);

        $this->assertSame('verified', $subscription->fresh()->payment_status);
        $this->assertSame('2026-12-15', $subscription->fresh()->end_date->toDateString());
        $this->assertSame('active', $member->fresh()->status);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'type' => SubscriptionStatusNotification::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => 'other-alert',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->where('expiringSoonSubscriptions', []));
    }
}
