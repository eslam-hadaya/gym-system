<?php

namespace Tests\Unit;

use App\Models\Subscription;
use Carbon\Carbon;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_remaining_days_decrease_as_the_current_date_advances(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');
        $subscription = new Subscription(['end_date' => '2026-12-09']);

        $this->assertSame(30, $subscription->days_remaining);

        Carbon::setTestNow('2026-11-10 12:00:00');

        $this->assertSame(29, $subscription->days_remaining);
    }

    public function test_renewal_is_allowed_only_during_the_last_ten_days(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');
        $subscription = new Subscription([
            'payment_status' => 'verified',
            'end_date' => '2026-11-19',
        ]);

        $this->assertTrue($subscription->can_renew);

        $subscription->end_date = '2026-11-20';

        $this->assertFalse($subscription->can_renew);
    }
}
