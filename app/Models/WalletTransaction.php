<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One append-only ledger row of an app user's wallet. */
class WalletTransaction extends Model
{
    public const EARNING = 'earning';
    public const PAYOUT = 'payout';
    public const PAYOUT_REVERSAL = 'payout_reversal';
    public const REFUND = 'refund';
    public const ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'app_user_id', 'type', 'amount', 'currency', 'parcel_order_id', 'payout_request_id',
        'available_at', 'balance_after', 'note', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'available_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ParcelOrder::class, 'parcel_order_id');
    }
}
