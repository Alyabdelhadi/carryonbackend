<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One Shufti check (signup or re-verification), kept for audit and retries. */
class IdentityVerification extends Model
{
    public const PENDING = 'pending';
    public const VERIFIED = 'verified';
    public const DECLINED = 'declined';
    public const INVALID = 'invalid';
    // Shufti never produced a verdict (unreachable, bad keys, unknown reference).
    public const FAILED = 'failed';

    protected $guarded = [];

    protected $casts = ['result' => 'array'];

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }
}
