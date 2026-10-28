<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount'          => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'last_response'   => 'array',
    ];

    /** Route model binding uses txn_no: /payment/{payment}/result */
    public function getRouteKeyName(): string
    {
        return 'txn_no';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
