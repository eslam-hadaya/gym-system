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
        "start_date" => "datetime",
        "end_date" => "datetime",
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
}
