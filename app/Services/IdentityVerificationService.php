<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\AppUser;
use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Identity checks for app users. Photo mode: signup and re-verification
 * store the photos first, then call [check]. Live mode (admin switch
 * `shufti_live`): [startLive] opens a Shufti onsite session the app shows
 * in a browser. Either way the Shufti callback, api/identity/status and
 * the identity:sync-pending command resolve checks that were still pending.
 */
class IdentityVerificationService
{
    /** How long a pending check may wait for Shufti before it counts as failed. */
    private const PENDING_TTL_HOURS = 24;

    public function __construct(private ShuftiService $shufti, private FirebaseService $firebase)
    {
    }

    /** The admin switch on /app-settings. */
    public static function enabled(): bool
    {
        return AppSetting::getBool(AppSetting::SHUFTI_ENABLED, true);
    }

    /** Shufti's hosted page (live selfie + ID) instead of uploaded photos. */
    public static function liveMode(): bool
    {
        return self::enabled() && AppSetting::getBool(AppSetting::SHUFTI_LIVE, false);
    }

    /** Shufti's event for an onsite session the user has not submitted yet. */
    public const EVENT_NOT_SUBMITTED = 'request.pending';

    /** Statuses that end the attempt without a verified account. */
    public static function isRejection(string $status): bool
    {
        return in_array($status, [
            IdentityVerification::DECLINED,
            IdentityVerification::INVALID,
            IdentityVerification::FAILED,
        ], true);
    }

    /**
     * Sends the stored selfie + ID (file names under upload/selfies and
     * upload/identities) to Shufti and logs the attempt.
     */
    public function check(string $source, string $selfie, string $identity, ?string $email, ?int $userId, ?string $ip): IdentityVerification
    {
        $attempt = IdentityVerification::create([
            'app_user_id' => $userId,
            'reference' => 'CO_' . now()->format('YmdHis') . '_' . Str::upper(Str::random(10)),
            'source' => $source,
            'status' => IdentityVerification::PENDING,
            'selfie' => $selfie,
            'identity' => $identity,
            'ip' => $ip,
        ]);

        // Shufti takes ~20 s; keep PHP's execution limit clear of it.
        @set_time_limit((int) config('services.shufti.timeout', 60) + 60);
        $started = microtime(true);
        $response = $this->shufti->verify(
            $attempt->reference,
            AppUser::SELFIE_DIR . '/' . $selfie,
            AppUser::IDENTITY_DIR . '/' . $identity,
            $email
        );
        $this->record($attempt, $response);

        Log::info('Identity check finished.', [
            'reference' => $attempt->reference,
            'user_id' => $userId,
            'source' => $source,
            'event' => $attempt->event,
            'status' => $attempt->status,
            'seconds' => round(microtime(true) - $started, 1),
        ]);

        return $attempt;
    }

    /**
     * Opens a live (onsite) session for the user. The attempt becomes the
     * user's latest (`shufti_reference`) so the callback can apply its
     * verdict, but the user's status only moves once they submit.
     *
     * @return array{attempt: IdentityVerification, url: ?string}
     */
    public function startLive(AppUser $user, ?string $ip, string $language = 'EN'): array
    {
        $attempt = IdentityVerification::create([
            'app_user_id' => $user->id,
            'reference' => 'CO_' . now()->format('YmdHis') . '_' . Str::upper(Str::random(10)),
            'source' => 'live',
            'status' => IdentityVerification::PENDING,
            'ip' => $ip,
        ]);

        $response = $this->shufti->startOnsite($attempt->reference, $user->email, $language);
        $this->record($attempt, $response);
        $url = $response['verification_url'] ?? null;

        if ($attempt->status !== IdentityVerification::PENDING || !$url) {
            if ($attempt->status === IdentityVerification::PENDING) {
                $attempt->status = IdentityVerification::FAILED;
                $attempt->message = 'Shufti returned no verification_url.';
                $attempt->save();
            }
            Log::warning('Live identity session could not start.', [
                'reference' => $attempt->reference,
                'user_id' => $user->id,
                'event' => $attempt->event,
            ]);
            return ['attempt' => $attempt, 'url' => null];
        }

        $user->shufti_reference = $attempt->reference;
        $user->save();
        Log::info('Live identity session started.', ['reference' => $attempt->reference, 'user_id' => $user->id]);
        return ['attempt' => $attempt, 'url' => $url];
    }

    /** A live session whose page the user has not completed yet. */
    public static function isUnsubmitted(IdentityVerification $attempt): bool
    {
        return $attempt->source === 'live' && $attempt->event === self::EVENT_NOT_SUBMITTED;
    }

    /**
     * Copies an attempt's outcome onto the user. A verified user is never
     * downgraded by a late or stale attempt.
     */
    public function applyToUser(AppUser $user, IdentityVerification $attempt): void
    {
        if ($attempt->app_user_id === null) {
            $attempt->app_user_id = $user->id;
            $attempt->save();
        }
        if ($user->is_verified && $attempt->status !== IdentityVerification::VERIFIED) {
            return;
        }

        $user->shufti_reference = $attempt->reference;
        switch ($attempt->status) {
            case IdentityVerification::VERIFIED:
                $user->identity_status = AppUser::IDENTITY_VERIFIED;
                $user->identity_verified_at = now();
                // the photos Shufti accepted become the account's photos
                $user->selfie = $attempt->selfie ?? $user->selfie;
                $user->identity = $attempt->identity ?? $user->identity;
                break;
            case IdentityVerification::PENDING:
                $user->identity_status = AppUser::IDENTITY_PENDING;
                break;
            case IdentityVerification::FAILED:
                // no verdict: back to "never verified" so the app asks again
                $user->identity_status = null;
                break;
            default:
                $user->identity_status = $attempt->status; // declined | invalid
        }
        $user->save();
    }

    /**
     * Re-reads a pending attempt from Shufti (callback or scheduled sync)
     * and, when it has a verdict, updates the user and tells them.
     */
    public function resolve(IdentityVerification $attempt): IdentityVerification
    {
        if ($attempt->status !== IdentityVerification::PENDING) {
            return $attempt;
        }

        $wasUnsubmitted = self::isUnsubmitted($attempt);
        $this->record($attempt, $this->shufti->status($attempt->reference));

        if ($attempt->status === IdentityVerification::PENDING
            && $attempt->created_at->lt(now()->subHours(self::PENDING_TTL_HOURS))) {
            $attempt->status = IdentityVerification::FAILED;
            $attempt->message = 'No verdict from Shufti after ' . self::PENDING_TTL_HOURS . ' hours.';
            $attempt->save();
        }

        if (!$attempt->app_user_id) {
            return $attempt;
        }
        $user = AppUser::find($attempt->app_user_id);
        // only the user's latest attempt decides their status
        if (!$user || $user->shufti_reference !== $attempt->reference) {
            return $attempt;
        }

        if ($attempt->status === IdentityVerification::PENDING) {
            // a live session the user just submitted: under review now
            if ($attempt->source === 'live' && !self::isUnsubmitted($attempt)
                && $user->identity_status !== AppUser::IDENTITY_PENDING) {
                $this->applyToUser($user, $attempt);
            }
            return $attempt;
        }

        // a live page left unfinished (expired or cancelled) is not a
        // verdict: the user keeps their status and can start again
        if ($wasUnsubmitted && $attempt->status === IdentityVerification::FAILED) {
            return $attempt;
        }

        $this->applyToUser($user, $attempt);
        $this->notify($user, $attempt);
        return $attempt;
    }

    /** Maps a Shufti event to our status. */
    public static function statusFor(string $event): string
    {
        return match ($event) {
            'verification.accepted' => IdentityVerification::VERIFIED,
            'verification.declined' => IdentityVerification::DECLINED,
            'request.invalid' => IdentityVerification::INVALID,
            'request.pending', 'request.received', 'review.pending', ShuftiService::EVENT_TIMEOUT => IdentityVerification::PENDING,
            // request.unauthorized, request.timeout (live page expired),
            // verification.cancelled, request.deleted, unreachable, ...
            default => IdentityVerification::FAILED,
        };
    }

    private function record(IdentityVerification $attempt, array $response): void
    {
        $status = self::statusFor($response['event']);
        // a status check that cannot reach Shufti leaves the attempt pending
        if ($attempt->exists && $attempt->event !== null
            && $response['event'] === ShuftiService::EVENT_UNREACHABLE) {
            return;
        }
        $attempt->event = $response['event'];
        $attempt->status = $status;
        $attempt->message = $response['message'] !== null ? Str::limit((string) $response['message'], 490) : null;
        $attempt->result = $response['result'] ?? $attempt->result;
        $attempt->save();
    }

    private function notify(AppUser $user, IdentityVerification $attempt): void
    {
        [$title, $body] = $attempt->status === IdentityVerification::VERIFIED
            ? ['Account verified', 'Your identity is verified. You can now send, receive and carry packages.']
            : ['Verification failed', 'We could not verify your identity. Please open CarryOn and try again.'];
        try {
            $this->firebase->sendToUser($user->id, $title, $body, ['type' => 'identity', 'status' => $attempt->status]);
        } catch (\Throwable $e) {
            Log::warning('Identity push failed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
