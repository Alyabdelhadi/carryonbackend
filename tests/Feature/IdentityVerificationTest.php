<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\AppUser;
use App\Models\IdentityVerification;
use App\Services\FirebaseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Server-side Shufti checks. Runs against the local dump-based MySQL
 * (.env.testing, run the 2026_09_26_000000 migration with --path= first);
 * every test is rolled back and Shufti is faked, so no paid request goes out.
 */
class IdentityVerificationTest extends TestCase
{
    use DatabaseTransactions;

    /** @var string[] files the tests stored under upload/ */
    private array $stored = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        Mail::fake();
        $this->mock(FirebaseService::class)->shouldReceive('sendToUser')->andReturnNull();
        config([
            'services.shufti.client_id' => 'test-client',
            'services.shufti.secret_key' => 'test-secret',
            'services.shufti.base_url' => 'https://shufti.test',
        ]);
        AppSetting::setBool(AppSetting::SHUFTI_ENABLED, true);
        AppSetting::setBool(AppSetting::SHUFTI_LIVE, false);
    }

    protected function tearDown(): void
    {
        foreach (AppUser::whereIn('email', $this->emails)->get() as $user) {
            AppUser::discardUploads($user->selfie, $user->identity);
        }
        parent::tearDown();
    }

    private array $emails = [];

    private function signupPayload(): array
    {
        $email = 'shufti' . uniqid() . '@example.test';
        $this->emails[] = $email;
        return [
            'name' => 'Test Person',
            'email' => $email,
            'phone' => '00961' . random_int(10000000, 99999999),
            'password' => 'secret',
            'selfie' => UploadedFile::fake()->image('selfie.jpg', 600, 800),
            'identity' => UploadedFile::fake()->image('passport.jpg', 1200, 800),
        ];
    }

    private function fakeShufti(string $event, array $extra = []): void
    {
        // a second Http::fake() would only append; start from a clean factory
        Http::swap(new HttpFactory());
        Http::fake(['shufti.test/*' => Http::response(array_merge([
            'reference' => 'x',
            'event' => $event,
            'verification_result' => ['face' => 1, 'document' => ['document' => 1]],
            'verification_data' => ['document' => ['name' => ['first_name' => 'SHOULD NOT BE STORED']]],
        ], $extra))]);
    }

    public function test_signup_with_accepted_check_creates_a_verified_account(): void
    {
        $this->fakeShufti('verification.accepted');

        $res = $this->post('/api/signup', $this->signupPayload())->assertOk()->json();

        $this->assertSame('done', $res['msg']);
        $this->assertTrue($res['user']['is_verified']);
        $this->assertSame('verified', $res['user']['identity_status']);
        $this->assertArrayNotHasKey('shufti_reference', $res['user']);
        $this->assertArrayNotHasKey('password', $res['user']);

        $attempt = IdentityVerification::where('app_user_id', $res['user']['id'])->firstOrFail();
        $this->assertSame('verified', $attempt->status);
        $this->assertSame('signup', $attempt->source);
        $this->assertStringNotContainsString('SHOULD NOT BE STORED', json_encode($attempt->result));

        // no callback_url unless SHUFTI_CALLBACK_URL is set (Shufti refuses
        // unregistered callback domains)
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization')
            && !isset($request['callback_url'])
            && !empty($request['face']['proof'])
            && !empty($request['document']['proof']));
    }

    public function test_signup_with_declined_check_creates_no_account(): void
    {
        $this->fakeShufti('verification.declined');
        $payload = $this->signupPayload();

        $res = $this->post('/api/signup', $payload)->assertOk()->json();

        $this->assertSame('error', $res['msg']);
        $this->assertSame('identity_declined', $res['reason']);
        $this->assertFalse(AppUser::where('email', $payload['email'])->exists());
        $attempt = IdentityVerification::latest('id')->first();
        $this->assertSame('declined', $attempt->status);
        $this->assertFileDoesNotExist(AppUser::SELFIE_DIR . '/' . $attempt->selfie);
    }

    public function test_signup_with_unreadable_photos_returns_shufti_detail(): void
    {
        Http::fake(['shufti.test/*' => Http::response([
            'reference' => 'x', 'event' => 'request.invalid',
            'error' => ['service' => 'document', 'key' => 'proof', 'message' => 'Document proof is blurry'],
        ], 400)]);

        $res = $this->post('/api/signup', $this->signupPayload())->json();

        $this->assertSame('identity_invalid', $res['reason']);
        $this->assertSame('Document proof is blurry', $res['detail']);
    }

    public function test_shufti_setup_errors_are_not_blamed_on_the_photos(): void
    {
        Http::fake(['shufti.test/*' => Http::response([
            'reference' => 'x', 'event' => 'request.invalid',
            'error' => ['service' => '', 'key' => '', 'message' => 'The given callback domain is not registered in your account.'],
        ], 400)]);
        $payload = $this->signupPayload();

        $res = $this->post('/api/signup', $payload)->json();

        $this->assertSame('identity_unavailable', $res['reason']);
        $this->assertFalse(AppUser::where('email', $payload['email'])->exists());
        $this->assertSame('failed', IdentityVerification::latest('id')->first()->status);
    }

    public function test_callback_url_is_sent_when_configured(): void
    {
        config(['services.shufti.callback_url' => 'https://carryon.app/admin/api/identity/shufti/callback']);
        $this->fakeShufti('verification.accepted');

        $this->post('/api/signup', $this->signupPayload());

        Http::assertSent(fn ($request) => ($request['callback_url'] ?? null) === 'https://carryon.app/admin/api/identity/shufti/callback');
    }

    public function test_signup_when_shufti_is_unreachable_asks_to_retry(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 7: Failed to connect'));
        $payload = $this->signupPayload();

        $res = $this->post('/api/signup', $payload)->json();

        $this->assertSame('identity_unavailable', $res['reason']);
        $this->assertFalse(AppUser::where('email', $payload['email'])->exists());
    }

    public function test_pending_signup_is_resolved_by_the_callback(): void
    {
        $this->fakeShufti('request.pending');
        $res = $this->post('/api/signup', $this->signupPayload())->json();

        $this->assertSame('done', $res['msg']);
        $this->assertFalse($res['user']['is_verified']);
        $this->assertSame('pending', $res['user']['identity_status']);
        $user = AppUser::find($res['user']['id']);

        // the callback body is ignored apart from the reference; the
        // verdict comes from Shufti's status endpoint
        $this->fakeShufti('verification.accepted');
        $this->post('/api/identity/shufti/callback', [
            'reference' => $user->shufti_reference,
            'event' => 'verification.declined',
        ])->assertOk();

        $this->assertTrue($user->fresh()->is_verified);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/status'));
    }

    public function test_signup_without_shufti_creates_an_unverified_account(): void
    {
        AppSetting::setBool(AppSetting::SHUFTI_ENABLED, false);
        Http::fake();

        $res = $this->post('/api/signup', $this->signupPayload())->json();

        $this->assertSame('done', $res['msg']);
        $this->assertFalse($res['user']['is_verified']);
        $this->assertNull($res['user']['identity_status']);
        Http::assertNothingSent();
    }

    private function existingAccount(): AppUser
    {
        $user = new AppUser();
        $user->name = 'Old Account';
        $user->email = $this->emails[] = 'old' . uniqid() . '@example.test';
        $user->phone = '00961' . random_int(10000000, 99999999);
        $user->password = 'secret';
        $user->role = 1;
        $user->status = 1;
        $user->save();
        return $user;
    }

    public function test_live_mode_signup_skips_the_photo_check_and_refuses_photo_verification(): void
    {
        AppSetting::setBool(AppSetting::SHUFTI_LIVE, true);
        Http::fake();

        $res = $this->post('/api/signup', $this->signupPayload())->json();
        $this->assertSame('done', $res['msg']);
        $this->assertNull($res['user']['identity_status']);
        Http::assertNothingSent();

        $user = AppUser::findOrFail($res['user']['id']);
        Sanctum::actingAs($user, ['app']);
        $res = $this->post('/api/identity/verify', [
            'user_id' => $user->id,
            'selfie' => UploadedFile::fake()->image('s.jpg'),
            'identity' => UploadedFile::fake()->image('i.jpg'),
        ])->json();
        $this->assertSame('identity_live_required', $res['reason']);
        Http::assertNothingSent();
    }

    public function test_live_session_goes_from_unsubmitted_to_review_to_verified(): void
    {
        AppSetting::setBool(AppSetting::SHUFTI_LIVE, true);
        $user = $this->existingAccount();
        Sanctum::actingAs($user, ['app']);

        $this->fakeShufti('request.pending', ['verification_url' => 'https://app.shuftipro.com/verification/abc']);
        $res = $this->post('/api/identity/live', ['user_id' => $user->id, 'lang' => 'ar'])->json();

        $this->assertSame('done', $res['msg']);
        $this->assertSame('https://app.shuftipro.com/verification/abc', $res['verification_url']);
        Http::assertSent(fn ($request) => !isset($request['face']['proof'])
            && $request['face']['allow_offline'] === '0'
            && $request['language'] === 'AR'
            && !isset($request['document']['proof']));
        $user->refresh();
        $this->assertNull($user->identity_status);
        $attempt = IdentityVerification::where('reference', $user->shufti_reference)->firstOrFail();
        $this->assertSame('live', $attempt->source);

        // the browser closed before the user finished: nothing changes
        $this->fakeShufti('request.pending');
        $res = $this->get('/api/identity/status?user_id=' . $user->id)->json();
        $this->assertTrue($res['live_unsubmitted']);
        $this->assertNull($res['user']['identity_status']);

        // submitted, Shufti still reviewing: under review
        $this->fakeShufti('review.pending');
        $res = $this->get('/api/identity/status?user_id=' . $user->id)->json();
        $this->assertFalse($res['live_unsubmitted']);
        $this->assertSame('pending', $res['user']['identity_status']);

        $this->fakeShufti('verification.accepted');
        $this->post('/api/identity/shufti/callback', ['reference' => $attempt->reference])->assertOk();
        $this->assertTrue($user->fresh()->is_verified);
    }

    public function test_expired_unfinished_live_session_keeps_the_users_status(): void
    {
        AppSetting::setBool(AppSetting::SHUFTI_LIVE, true);
        $user = $this->existingAccount();
        $user->identity_status = 'declined';
        $user->save();
        Sanctum::actingAs($user, ['app']);

        $this->fakeShufti('request.pending', ['verification_url' => 'https://app.shuftipro.com/verification/abc']);
        $this->post('/api/identity/live', ['user_id' => $user->id])->assertOk();

        $this->fakeShufti('request.timeout');
        $res = $this->get('/api/identity/status?user_id=' . $user->id)->json();
        $this->assertFalse($res['live_unsubmitted']);
        $this->assertSame('declined', $user->fresh()->identity_status);
        $this->assertSame('failed', IdentityVerification::where('reference', $user->fresh()->shufti_reference)->value('status'));
    }

    public function test_live_session_that_cannot_start_asks_to_retry(): void
    {
        AppSetting::setBool(AppSetting::SHUFTI_LIVE, true);
        $user = $this->existingAccount();
        Sanctum::actingAs($user, ['app']);

        $this->fakeShufti('request.invalid', ['error' => ['service' => '', 'message' => 'callback domain is not registered']]);
        $res = $this->post('/api/identity/live', ['user_id' => $user->id])->json();

        $this->assertSame('identity_unavailable', $res['reason']);
        $this->assertNull($user->fresh()->shufti_reference);
    }

    public function test_existing_account_can_verify_and_is_not_downgraded(): void
    {
        $user = new AppUser();
        $user->name = 'Old Account';
        $user->email = $this->emails[] = 'old' . uniqid() . '@example.test';
        $user->phone = '00961' . random_int(10000000, 99999999);
        $user->password = 'secret';
        $user->role = 1;
        $user->status = 1;
        $user->save();
        $this->assertFalse($user->is_verified);
        Sanctum::actingAs($user, ['app']);

        $this->fakeShufti('verification.declined');
        $res = $this->post('/api/identity/verify', [
            'user_id' => $user->id,
            'selfie' => UploadedFile::fake()->image('s.jpg'),
            'identity' => UploadedFile::fake()->image('i.jpg'),
        ])->json();
        $this->assertSame('identity_declined', $res['reason']);
        $this->assertSame('declined', $user->fresh()->identity_status);

        $this->fakeShufti('verification.accepted');
        $res = $this->post('/api/identity/verify', [
            'user_id' => $user->id,
            'selfie' => UploadedFile::fake()->image('s.jpg'),
            'identity' => UploadedFile::fake()->image('i.jpg'),
        ])->json();
        $this->assertSame('done', $res['msg']);
        $this->assertTrue($res['user']['is_verified']);
        $this->assertNotNull($user->fresh()->selfie);

        // a stale declined verdict arriving later does not undo it
        $old = IdentityVerification::where('app_user_id', $user->id)->where('status', 'declined')->first();
        app(\App\Services\IdentityVerificationService::class)->applyToUser($user->fresh(), $old);
        $this->assertTrue($user->fresh()->is_verified);
    }

    public function test_login_exposes_is_verified(): void
    {
        $this->fakeShufti('verification.accepted');
        $payload = $this->signupPayload();
        $this->post('/api/signup', $payload);

        $login = $this->post('/api/login', ['email' => $payload['email'], 'password' => 'secret'])->json();
        $this->assertTrue($login['user']['is_verified']);
    }
}
