<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_submit_a_pending_check_in_request(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $user = User::factory()->create(['role' => 'member']);
        $member = Member::create([
            'user_id' => $user->id,
            'member_code' => 'GYM-100',
            'phone' => '0912345690',
            'gender' => 'male',
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
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-20',
        ]);

        $this->actingAs($user)
            ->post('/member/dashboard/checkin-request')
            ->assertRedirect()
            ->assertSessionHas('status', 'checkin-requested');

        $this->assertDatabaseHas('check_ins', [
            'member_id' => $member->id,
            'status' => 'pending',
            'note' => 'طلب دخول يدوي من بوابة اللاعب',
        ]);
    }
}
