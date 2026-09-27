<?php

namespace App\Support;

use App\Models\AppUser;
use App\Models\PayoutRequest;

/** The small counters next to sidebar links (things waiting for an admin). */
final class AdminBadges
{
    private static array $cache = [];

    public static function count(?string $key): int
    {
        if (!$key) {
            return 0;
        }
        return self::$cache[$key] ??= (function () use ($key) {
            try {
                return match ($key) {
                    'pending_identity' => AppUser::where('identity_status', AppUser::IDENTITY_PENDING)->count(),
                    'pending_payouts' => PayoutRequest::where('status', PayoutRequest::PENDING)->count(),
                    default => 0,
                };
            } catch (\Throwable $e) {
                return 0; // a missing table must never break the layout
            }
        })();
    }
}
