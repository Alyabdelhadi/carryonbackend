<?php

namespace Tests\Feature;

use App\Models\AppUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

/**
 * Password reset by emailed code (api/password/*). Local dump-based MySQL
 * (.env.testing, run the 2026_09_26_000100 migration with --path= first);
 * mail goes to the in-memory array transport.
 */
class PasswordResetOtpTest extends TestCase
{
    use DatabaseTransactions;

    private AppUser $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        config(['mail.default' => 'array']);
        $this->user = new AppUser();
        $this->user->name = 'Reset Person';
        $this->user->email = 'reset' . uniqid() . '@example.test';
        $this->user->phone = '00961' . random_int(10000000, 99999999);
        $this->user->password = 'old-password';
        $this->user->role = 1;
        $this->user->status = 1;
        $this->user->save();
    }

    /** Requests a code and returns it from the sent email. */
    private function requestCode(string $lang = 'en'): string
    {
        $res = $this->postJson('/api/password/request', ['email' => strtoupper($this->user->email), 'lang' => $lang])->json();
        $this->assertSame('done', $res['msg']);
        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages->last()->getOriginalMessage();
        $this->assertSame($this->user->email, $email->getTo()[0]->getAddress());
        $this->assertMatchesRegularExpression('/\b(\d{6})\b/', $email->getTextBody());
        preg_match('/\b(\d{6})\b/', $email->getTextBody(), $m);
        return $m[1];
    }

    public function test_full_flow_changes_the_password(): void
    {
        $code = $this->requestCode();

        $verify = $this->postJson('/api/password/verify', ['email' => $this->user->email, 'code' => $code])->json();
        $this->assertSame('done', $verify['msg']);
        $this->assertSame($this->user->id, $verify['user_id']);

        $reset = $this->postJson('/api/password/reset', [
            'user_id' => $verify['user_id'], 'token' => $verify['token'], 'password' => 'new-password',
        ])->json();
        $this->assertSame('done', $reset['msg']);

        $login = $this->postJson('/api/login', ['email' => $this->user->email, 'password' => 'new-password'])->json();
        $this->assertSame('done', $login['msg']);
        // the token is single use
        $again = $this->postJson('/api/password/reset', [
            'user_id' => $verify['user_id'], 'token' => $verify['token'], 'password' => 'another-one',
        ])->json();
        $this->assertSame('token_expired', $again['reason']);
    }

    public function test_code_is_stored_hashed_and_secrets_never_reach_the_user_json(): void
    {
        $code = $this->requestCode();
        $fresh = $this->user->fresh();
        $this->assertNotSame($code, $fresh->reset_otp_hash);

        $login = $this->postJson('/api/login', ['email' => $this->user->email, 'password' => 'old-password'])->json();
        foreach (['reset_otp_hash', 'reset_token', 'vcode', 'reset_otp_attempts', 'password'] as $key) {
            $this->assertArrayNotHasKey($key, $login['user']);
        }
    }

    public function test_unknown_email_gets_the_same_answer_and_no_email(): void
    {
        $res = $this->postJson('/api/password/request', ['email' => 'nobody' . uniqid() . '@example.test'])->json();
        $this->assertSame('done', $res['msg']);
        $this->assertCount(0, app('mailer')->getSymfonyTransport()->messages());
    }

    public function test_wrong_codes_lock_after_five_attempts(): void
    {
        $code = $this->requestCode();
        $wrong = $code === '000000' ? '111111' : '000000';
        for ($i = 1; $i <= 4; $i++) {
            $res = $this->postJson('/api/password/verify', ['email' => $this->user->email, 'code' => $wrong])->json();
            $this->assertSame('otp_invalid', $res['reason']);
        }
        $res = $this->postJson('/api/password/verify', ['email' => $this->user->email, 'code' => $wrong])->json();
        $this->assertSame('otp_locked', $res['reason']);
        // even the right code no longer works
        $res = $this->postJson('/api/password/verify', ['email' => $this->user->email, 'code' => $code])->json();
        $this->assertSame('otp_locked', $res['reason']);
    }

    public function test_expired_code_is_refused(): void
    {
        $code = $this->requestCode();
        $this->travel(11)->minutes();
        $res = $this->postJson('/api/password/verify', ['email' => $this->user->email, 'code' => $code])->json();
        $this->assertSame('otp_expired', $res['reason']);
    }

    public function test_resend_waits_a_minute_and_new_code_replaces_old(): void
    {
        $first = $this->requestCode();
        $res = $this->postJson('/api/password/request', ['email' => $this->user->email])->json();
        $this->assertNotSame('done', $res['msg']);
        $this->assertGreaterThan(0, $res['resend_in']);

        $this->travel(61)->seconds();
        app('mailer')->getSymfonyTransport()->flush();
        $second = $this->requestCode();
        if ($first !== $second) {
            $res = $this->postJson('/api/password/verify', ['email' => $this->user->email, 'code' => $first])->json();
            $this->assertSame('otp_invalid', $res['reason']);
        }
    }

    public function test_short_password_and_arabic_email(): void
    {
        $code = $this->requestCode('ar');
        $body = app('mailer')->getSymfonyTransport()->messages()->last()->getOriginalMessage()->getTextBody();
        $this->assertStringContainsString('رمز إعادة تعيين كلمة المرور', $body);

        $verify = $this->postJson('/api/password/verify', ['email' => $this->user->email, 'code' => $code])->json();
        $res = $this->postJson('/api/password/reset', [
            'user_id' => $verify['user_id'], 'token' => $verify['token'], 'password' => '123',
        ])->json();
        $this->assertStringContainsString('at least 6', $res['msg']);
    }
}
