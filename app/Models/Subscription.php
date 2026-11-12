<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        "member_id",
        "plan_id",
        "price_paid",
        "payment_method",
        "payment_status",
        "transaction_ref",
        "start_date",
        "end_date",
    ];

    protected $casts = [
        "start_date" => "date",
        "end_date" => "date",
        "price_paid" => "decimal:2",
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * حساب عدد الأيام المتبقية حتى نهاية الاشتراك بدقة
     */
    public function getDaysRemainingAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        return max(
            0,
            (int) today()->diffInDays($this->end_date, false),
        );
    }

    public function getCanRenewAttribute(): bool
    {
        return $this->payment_status === "verified" &&
            $this->end_date !== null &&
            $this->end_date->greaterThanOrEqualTo(today()) &&
            $this->days_remaining <= 10;
    }

    /**
     * التحقق مما إذا كان الاشتراك معتمداً ويقترب من الانتهاء (5 أيام أو أقل)
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->payment_status === "verified" &&
            $this->end_date !== null &&
            $this->days_remaining <= 5 &&
            $this->end_date->greaterThanOrEqualTo(today());
    }
}
