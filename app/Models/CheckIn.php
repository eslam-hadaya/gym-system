<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    protected $fillable = [
        'member_id',
        'status',
        'note',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
