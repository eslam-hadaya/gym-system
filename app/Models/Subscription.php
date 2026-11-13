<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'plan_id',
        'price_paid',
        'payment_method',
        'payment_status',
        'transaction_ref',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price_paid' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function scopeCurrentForAccess(Builder $query): Builder
    {
        return $query
            ->where('payment_status', 'verified')
            ->whereDate('end_date', '>=', today()->subDay());
    }

    public function scopeLatestVerifiedForMember(Builder $query): Builder
    {
        return $query
            ->where('payment_status', 'verified')
            ->whereNotNull('end_date')
            ->whereNotExists(function (QueryBuilder $newerSubscriptions): void {
                $newerSubscriptions
                    ->selectRaw('1')
                    ->from('subscriptions as newer_subscriptions')
                    ->whereColumn('newer_subscriptions.member_id', 'subscriptions.member_id')
                    ->where('newer_subscriptions.payment_status', 'verified')
                    ->whereNotNull('newer_subscriptions.end_date')
                    ->where(function (QueryBuilder $query): void {
                        $query
                            ->whereColumn('newer_subscriptions.end_date', '>', 'subscriptions.end_date')
                            ->orWhere(function (QueryBuilder $query): void {
                                $query
                                    ->whereColumn('newer_subscriptions.end_date', 'subscriptions.end_date')
                                    ->whereColumn('newer_subscriptions.id', '>', 'subscriptions.id');
                            });
                    });
            });
    }

    /**
     * حساب عدد الأيام المتبقية حتى نهاية الاشتراك بدقة
     */
    public function getDaysRemainingAttribute(): int
    {
        if (! $this->end_date) {
            return 0;
        }

        return max(
            0,
            (int) today()->diffInDays($this->end_date, false),
        );
    }

    public function getCanRenewAttribute(): bool
    {
        return $this->payment_status === 'verified' &&
            $this->end_date !== null &&
            $this->end_date->copy()->addDay()->greaterThanOrEqualTo(today()) &&
            $this->days_remaining <= 10;
    }

    /**
     * التحقق مما إذا كان الاشتراك معتمداً ويقترب من الانتهاء (5 أيام أو أقل)
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->payment_status === 'verified' &&
            $this->end_date !== null &&
            $this->days_remaining <= 5 &&
            $this->end_date->copy()->addDay()->greaterThanOrEqualTo(today());
    }
}
