<?php

namespace App\Services;

use App\Models\AppUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Password reset with a 6-digit code sent by email:
 * request (email) -> verify (email + code, returns a one-time token) ->
 * reset (user_id + token + new password). Answers use the API's usual
 * `msg` shape.
 */
class PasswordResetService
{
    public const CODE_TTL_MINUTES = 10;
    public const RESEND_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;
    public const TOKEN_TTL_MINUTES = 15;
    public const MIN_PASSWORD_LENGTH = 6;

    /**
     * Emails a new code. The answer is the same whether or not the email
     * is registered, so the endpoint cannot be used to find accounts.
     */
    public function request(?string $email, string $lang = 'en'): array
    {
        $email = strtolower(trim((string) $email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['msg' => 'Please enter a valid email address.'];
        }
        $done = ['msg' => 'done', 'expires_in' => self::CODE_TTL_MINUTES * 60, 'resend_in' => self::RESEND_SECONDS];

        $user = AppUser::where('email', $email)->first();
        if (!$user) {
            return $done;
        }
        if ($user->reset_otp_sent_at && $user->reset_otp_sent_at->gt(now()->subSeconds(self::RESEND_SECONDS))) {
            $wait = self::RESEND_SECONDS - $user->reset_otp_sent_at->diffInSeconds(now());
            return ['msg' => 'Please wait a moment before requesting a new code.', 'resend_in' => max(1, $wait)];
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->reset_otp_hash = Hash::make($code);
        $user->reset_otp_expires_at = now()->addMinutes(self::CODE_TTL_MINUTES);
        $user->reset_otp_sent_at = now();
        $user->reset_otp_attempts = 0;
        $user->save();

        try {
            Mail::raw($this->body($user, $code, $lang), function ($message) use ($user, $lang) {
                $message->to($user->email)->subject($lang === 'ar'
                    ? 'رمز إعادة تعيين كلمة المرور في CarryOn'
                    : 'Your CarryOn password reset code');
            });
        } catch (\Throwable $e) {
            Log::error('Password reset code email failed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            // let the user ask again right away
            $user->reset_otp_sent_at = null;
            $user->save();
            return ['msg' => 'We could not send the email right now. Please try again.'];
        }
        return $done;
    }

    /** Checks the code; on success returns a one-time token for [reset]. */
    public function verify(?string $email, ?string $code): array
    {
        $email = strtolower(trim((string) $email));
        $code = preg_replace('/\D/', '', (string) $code);
        $user = $email !== '' ? AppUser::where('email', $email)->first() : null;
        $invalid = ['msg' => 'The code is incorrect. Please check the email and try again.', 'reason' => 'otp_invalid'];

        if (!$user || !$user->reset_otp_hash) {
            return $invalid;
        }
        if (!$user->reset_otp_expires_at || $user->reset_otp_expires_at->isPast()) {
            return ['msg' => 'This code has expired. Please request a new one.', 'reason' => 'otp_expired'];
        }
        if ($user->reset_otp_attempts >= self::MAX_ATTEMPTS) {
            return ['msg' => 'Too many incorrect attempts. Please request a new code.', 'reason' => 'otp_locked'];
        }
        if (strlen($code) !== 6 || !Hash::check($code, $user->reset_otp_hash)) {
            $user->reset_otp_attempts++;
            $user->save();
            return $user->reset_otp_attempts >= self::MAX_ATTEMPTS
                ? ['msg' => 'Too many incorrect attempts. Please request a new code.', 'reason' => 'otp_locked']
                : $invalid;
        }

        $token = Str::random(64);
        $user->reset_token = $token;
        $user->reset_token_expires_at = now()->addMinutes(self::TOKEN_TTL_MINUTES);
        $user->reset_otp_hash = null;
        $user->reset_otp_expires_at = null;
        $user->reset_otp_attempts = 0;
        $user->save();

        return ['msg' => 'done', 'user_id' => $user->id, 'token' => $token];
    }

    /** Sets the new password with the token from [verify]. */
    public function reset($userId, ?string $token, ?string $password): array
    {
        $password = (string) $password;
        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return ['msg' => 'Password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters.'];
        }
        $user = AppUser::find($userId);
        if (!$user || !$token || !$user->reset_token || !hash_equals($user->reset_token, (string) $token)
            || !$user->reset_token_expires_at || $user->reset_token_expires_at->isPast()) {
            return ['msg' => 'This reset session has expired. Please request a new code.', 'reason' => 'token_expired'];
        }

        // passwords are stored as the app sends them (see AppUser::login)
        $user->password = $password;
        $user->reset_token = null;
        $user->reset_token_expires_at = null;
        $user->save();
        // whoever else was signed in with the old password is signed out
        app(AppTokenService::class)->revokeAll($user->id);

        return ['msg' => 'done'];
    }

    private function body(AppUser $user, string $code, string $lang): string
    {
        $minutes = self::CODE_TTL_MINUTES;
        if ($lang === 'ar') {
            return "مرحبًا {$user->name}،\n\nرمز إعادة تعيين كلمة المرور الخاص بك هو: {$code}\n\n"
                . "ينتهي هذا الرمز خلال {$minutes} دقائق. إذا لم تطلب إعادة التعيين، تجاهل هذه الرسالة.\n\nفريق CarryOn";
        }
        return "Hi {$user->name},\n\nYour CarryOn password reset code is: {$code}\n\n"
            . "The code expires in {$minutes} minutes. If you did not ask to reset your password, you can ignore this email.\n\nThe CarryOn team";
    }
}
