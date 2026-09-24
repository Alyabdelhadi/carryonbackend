<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A carrier asking to withdraw wallet money; the admin pays it out by hand. */
class PayoutRequest extends Model
{
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'app_user_id', 'amount', 'currency', 'method', 'details', 'status',
        'reference', 'admin_note', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'details' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
