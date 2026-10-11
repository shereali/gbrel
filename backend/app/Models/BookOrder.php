<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One purchase of the e-book. Status: pending → paid, or failed (gateway refused) / underpaid (amount too low).
 */
class BookOrder extends Model
{
    public const PENDING = 'pending';

    public const PAID = 'paid';

    public const FAILED = 'failed';

    public const UNDERPAID = 'underpaid';

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'paid_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'tracking' => 'array',
        'gateway_response' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }
}
