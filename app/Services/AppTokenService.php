<?php

namespace App\Services;

use App\Models\AppUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Access + refresh tokens for the mobile app.
 *
 * - Access token: a Sanctum personal access token (stored as sha256),
 *   named `app:<family>`, valid ACCESS_TTL_MINUTES.
 * - Refresh token: random 64 chars, stored as sha256 in app_refresh_tokens,
 *   valid REFRESH_TTL_DAYS, single use. Each refresh returns a new pair;
 *   replaying a used refresh token revokes the whole family.
 */
class AppTokenService
{
    public const ACCESS_TTL_MINUTES = 60;
    public const REFRESH_TTL_DAYS = 60;

    /** @return array{accessToken: string, refreshToken: string, expiresIn: int} */
    public function issue(AppUser $user, ?string $device = null, ?string $family = null): array
    {
        $family ??= (string) Str::uuid();
        $access = $user->createToken(
            'app:' . $family,
            ['app'],
            now()->addMinutes(self::ACCESS_TTL_MINUTES)
        )->plainTextToken;

        $refresh = Str::random(64);
        DB::table('app_refresh_tokens')->insert([
            'app_user_id' => $user->id,
            'family' => $family,
            'token_hash' => hash('sha256', $refresh),
            'device' => $device !== null ? Str::limit($device, 110, '') : null,
            'expires_at' => now()->addDays(self::REFRESH_TTL_DAYS),
            'ip' => request()?->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'accessToken' => $access,
            'refreshToken' => $refresh,
            'expiresIn' => self::ACCESS_TTL_MINUTES * 60,
        ];
    }

    /**
     * Swaps a refresh token for a new pair. Null means the token is not
     * (or no longer) valid and the app must log in again.
     */
    public function refresh(?string $refreshToken): ?array
    {
        if (!is_string($refreshToken) || strlen($refreshToken) < 32) {
            return null;
        }

        return DB::transaction(function () use ($refreshToken) {
            $row = DB::table('app_refresh_tokens')
                ->where('token_hash', hash('sha256', $refreshToken))
                ->lockForUpdate()
                ->first();
            if (!$row) {
                return null;
            }
            if ($row->used_at !== null || $row->revoked_at !== null) {
                // a used token came back: someone else holds this family
                if ($row->used_at !== null && $row->revoked_at === null) {
                    Log::warning('Refresh token reuse; revoking the session.', ['user_id' => $row->app_user_id, 'family' => $row->family]);
                }
                $this->revokeFamily($row->app_user_id, $row->family);
                return null;
            }
            if (now()->greaterThan($row->expires_at)) {
                return null;
            }
            $user = AppUser::find($row->app_user_id);
            if (!$user || (int) $user->status !== 1) {
                $this->revokeFamily($row->app_user_id, $row->family);
                return null;
            }

            DB::table('app_refresh_tokens')->where('id', $row->id)->update(['used_at' => now(), 'updated_at' => now()]);
            // the old access tokens of this login stop working right away
            $this->deleteAccessTokens($user->id, $row->family);

            return $this->issue($user, $row->device, $row->family);
        });
    }

    /** Ends one login (the one the access token belongs to). */
    public function revokeCurrent(AppUser $user, ?PersonalAccessToken $token): void
    {
        if ($token && str_starts_with((string) $token->name, 'app:')) {
            $this->revokeFamily($user->id, substr($token->name, 4));
        } elseif ($token) {
            $token->delete();
        }
    }

    /** Ends every login of the user (password change, account deletion). */
    public function revokeAll(int $userId): void
    {
        DB::table('app_refresh_tokens')->where('app_user_id', $userId)->whereNull('revoked_at')
            ->update(['revoked_at' => now(), 'updated_at' => now()]);
        PersonalAccessToken::where('tokenable_type', AppUser::class)->where('tokenable_id', $userId)->delete();
    }

    private function revokeFamily(int $userId, string $family): void
    {
        DB::table('app_refresh_tokens')->where('app_user_id', $userId)->where('family', $family)
            ->whereNull('revoked_at')->update(['revoked_at' => now(), 'updated_at' => now()]);
        $this->deleteAccessTokens($userId, $family);
    }

    private function deleteAccessTokens(int $userId, string $family): void
    {
        PersonalAccessToken::where('tokenable_type', AppUser::class)
            ->where('tokenable_id', $userId)
            ->where('name', 'app:' . $family)
            ->delete();
    }
}
