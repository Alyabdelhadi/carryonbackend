<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\AppUser;
use App\Models\IdentityVerification;
use App\Services\FirebaseService;
use App\Services\IdentityVerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Identity verification after signup: Shufti live sessions, manual review
 * by the admin, and the app.verified gate. Runs against the local
 * dump-based MySQL (.env.testing, run the 2026_09_26_000000 migration with
 * --path= first); every test is rolled back and Shufti is faked, so no
 * paid request goes out.
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
        AppSetting::setChoice(AppSetting::IDENTITY_METHOD, AppSetting::IDENTITY_MANUAL);
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

    public function test_signup_runs_no_check_and_leaves_the_account_unverified(): void
    {
        AppSetting::setChoice(AppSetting::IDENTITY_METHOD, AppSetting::IDENTITY_SHUFTI);
        Http::fake();

        $res = $this->post('/api/signup', $this->signupPayload())->assertOk()->json();

        $this->assertSame('done', $res['msg']);
        $this->assertFalse($res['user']['is_verified']);
        $this->assertNull($res['user']['identity_status']);
        $this->assertArrayNotHasKey('shufti_reference', $res['user']);
        $this->assertArrayNotHasKey('password', $res['user']);
        $this->assertFalse(IdentityVerification::where('app_user_id', $res['user']['id'])->exists());
        Http::assertNothingSent();
    }

    public function test_manual_signup_with_both_photos_goes_to_review(): void
    {
        // app builds that still upload the ID at signup
        Http::fake();

        $res = $this->post('/api/signup', $this->signupPayload())->json();

        $this->assertSame('done', $res['msg']);
        $this->assertSame('pending', $res['user']['identity_status']);
        $attempt = IdentityVerification::where('app_user_id', $res['user']['id'])->firstOrFail();
        $this->assertSame('manual', $attempt->source);
        $this->assertSame('pending', $attempt->status);
        Http::assertNothingSent();
    }

    public function test_signup_without_a_verification_requirement_creates_an_unverified_account(): void
    {
        AppSetting::setBool(AppSetting::SHUFTI_ENABLED, false);
        Http::fake();

        $res = $this->post('/api/signup', $this->signupPayload())->json();

        $this->assertSame('done', $res['msg']);
        $this->assertFalse($res['user']['is_verified']);
        $this->assertNull($res['user']['identity_status']);
        Http::assertNothingSent();
    }

    public function test_unverified_accounts_cannot_send_carry_or_add_trips(): void
    {
        $user = $this->existingAccount();
        Sanctum::actingAs($user, ['app']);

        foreach ([
            ['/api/createParcelOrder', ['user_id' => $user->id]],
            ['/api/assignParcelOrder', ['user_id' => $user->id, 'order_id' => 0]],
            ['/api/trips', ['carrier_id' => $user->id]],
        ] as [$url, $body]) {
            $res = $this->post($url, $body)->json();
            $this->assertSame('identity_required', $res['reason'] ?? null, $url);
        }

        // switched off by the admin: nobody is blocked
        AppSetting::setBool(AppSetting::SHUFTI_ENABLED, false);
        $res = $this->post('/api/assignParcelOrder', ['user_id' => $user->id, 'order_id' => 0])->json();
        $this->assertNotSame('identity_required', $res['reason'] ?? null);

        AppSetting::setBool(AppSetting::SHUFTI_ENABLED, true);
        app(IdentityVerificationService::class)->review($user, true);
        $res = $this->post('/api/assignParcelOrder', ['user_id' => $user->id, 'order_id' => 0])->json();
        $this->assertNotSame('identity_required', $res['reason'] ?? null);
    }

    public function test_manual_review_approve_and_reject(): void
    {
        Http::fake();
        $user = $this->existingAccount();
        Sanctum::actingAs($user, ['app']);

        $res = $this->post('/api/identity/verify', ['user_id' => $user->id])->json();
        $this->assertSame('error', $res['msg']);

        $res = $this->post('/api/identity/verify', [
            'user_id' => $user->id,
            'selfie' => UploadedFile::fake()->image('s.jpg'),
            'identity' => UploadedFile::fake()->image('i.jpg', 1600, 1000),
        ])->json();
        $this->assertSame('done', $res['msg']);
        $this->assertSame('pending', $res['user']['identity_status']);
        $user->refresh();
        $this->assertNotNull($user->selfie);
        $this->assertFileExists(AppUser::IDENTITY_DIR . '/' . $user->identity);

        // the status check and the sync command leave manual reviews alone
        $res = $this->get('/api/identity/status?user_id=' . $user->id)->json();
        $this->assertSame('pending', $res['user']['identity_status']);
        $this->artisan('identity:sync-pending')->assertSuccessful();
        $this->assertSame('pending', $user->fresh()->identity_status);
        Http::assertNothingSent();

        app(IdentityVerificationService::class)->review($user->fresh(), false);
        $this->assertSame('declined', $user->fresh()->identity_status);
        $this->assertSame('declined', IdentityVerification::where('reference', $user->fresh()->shufti_reference)->value('status'));

        // new photos, approved this time
        $this->post('/api/identity/verify', [
            'user_id' => $user->id,
            'selfie' => UploadedFile::fake()->image('s.jpg'),
            'identity' => UploadedFile::fake()->image('i.jpg'),
        ])->assertOk();
        app(IdentityVerificationService::class)->review($user->fresh(), true);
        $this->assertTrue($user->fresh()->is_verified);

        // a stale declined verdict arriving later does not undo it
        $old = IdentityVerification::where('app_user_id', $user->id)->where('status', 'declined')->first();
        app(IdentityVerificationService::class)->applyToUser($user->fresh(), $old);
        $this->assertTrue($user->fresh()->is_verified);
    }

    public function test_app_settings_expose_the_method(): void
    {
        AppSetting::setChoice(AppSetting::IDENTITY_METHOD, AppSetting::IDENTITY_SHUFTI);
        $res = $this->get('/api/appSettings')->json();
        $this->assertSame('shufti', $res['identity_method']);
        $this->assertTrue($res['shufti_live']);

        AppSetting::setChoice(AppSetting::IDENTITY_METHOD, AppSetting::IDENTITY_MANUAL);
        $res = $this->get('/api/appSettings')->json();
        $this->assertSame('manual', $res['identity_method']);
        $this->assertFalse($res['shufti_live']);
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

    public function test_live_mode_refuses_photo_verification(): void
    {
        AppSetting::setChoice(AppSetting::IDENTITY_METHOD, AppSetting::IDENTITY_SHUFTI);
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
        AppSetting::setChoice(AppSetting::IDENTITY_METHOD, AppSetting::IDENTITY_SHUFTI);
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
        AppSetting::setChoice(AppSetting::IDENTITY_METHOD, AppSetting::IDENTITY_SHUFTI);
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
        AppSetting::setChoice(AppSetting::IDENTITY_METHOD, AppSetting::IDENTITY_SHUFTI);
        $user = $this->existingAccount();
        Sanctum::actingAs($user, ['app']);

        $this->fakeShufti('request.invalid', ['error' => ['service' => '', 'message' => 'callback domain is not registered']]);
        $res = $this->post('/api/identity/live', ['user_id' => $user->id])->json();

        $this->assertSame('identity_unavailable', $res['reason']);
        $this->assertNull($user->fresh()->shufti_reference);
    }

    public function test_callback_url_is_sent_when_configured(): void
    {
        AppSetting::setChoice(AppSetting::IDENTITY_METHOD, AppSetting::IDENTITY_SHUFTI);
        config(['services.shufti.callback_url' => 'https://carryon.app/admin/api/identity/shufti/callback']);
        $user = $this->existingAccount();
        Sanctum::actingAs($user, ['app']);
        $this->fakeShufti('request.pending', ['verification_url' => 'https://app.shuftipro.com/verification/abc']);

        $this->post('/api/identity/live', ['user_id' => $user->id])->assertOk();

        Http::assertSent(fn ($request) => ($request['callback_url'] ?? null) === 'https://carryon.app/admin/api/identity/shufti/callback');
    }

    public function test_login_exposes_is_verified(): void
    {
        $payload = $this->signupPayload();
        $user = AppUser::findOrFail($this->post('/api/signup', $payload)->json('user.id'));
        app(IdentityVerificationService::class)->review($user, true);

        $login = $this->post('/api/login', ['email' => $payload['email'], 'password' => 'secret'])->json();
        $this->assertTrue($login['user']['is_verified']);
    }
}
