<?php

namespace Tests\Feature;

use App\Models\AppUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Hashed passwords + access / refresh tokens for the mobile API. Local
 * dump-based MySQL (.env.testing; migrations 2026_09_26_000200 run with
 * --path=); rolled back.
 */
class AppAuthTokensTest extends TestCase
{
    use DatabaseTransactions;

    private AppUser $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        config(['services.app_api.legacy_user_id_auth' => false]);
        $this->user = new AppUser();
        $this->user->name = 'Token Person';
        $this->user->email = 'token' . uniqid() . '@example.test';
        $this->user->phone = '00961' . random_int(10000000, 99999999);
        $this->user->password = 'secret123';
        $this->user->role = 1;
        $this->user->status = 1;
        $this->user->save();
    }

    private function login(string $password = 'secret123'): array
    {
        return $this->postJson('/api/login', ['email' => $this->user->email, 'password' => $password])->json();
    }

    public function test_passwords_are_stored_hashed(): void
    {
        $stored = DB::table('app_users')->where('id', $this->user->id)->value('password');
        $this->assertNotSame('secret123', $stored);
        $this->assertTrue(Hash::check('secret123', $stored));
        $this->assertSame('done', $this->login()['msg']);
        $this->assertNotSame('done', $this->login('wrong')['msg']);
    }

    public function test_legacy_plain_password_logs_in_and_is_upgraded(): void
    {
        DB::table('app_users')->where('id', $this->user->id)->update(['password' => 'plain-old']);

        $this->assertSame('done', $this->login('plain-old')['msg']);

        $stored = DB::table('app_users')->where('id', $this->user->id)->value('password');
        $this->assertTrue(AppUser::isHashed($stored));
        $this->assertTrue(Hash::check('plain-old', $stored));
    }

    public function test_hash_passwords_command_is_idempotent(): void
    {
        DB::table('app_users')->where('id', $this->user->id)->update(['password' => 'plain-cmd']);
        $this->artisan('app-users:hash-passwords')->assertSuccessful();
        $first = DB::table('app_users')->where('id', $this->user->id)->value('password');
        $this->assertTrue(Hash::check('plain-cmd', $first));
        $this->artisan('app-users:hash-passwords')->assertSuccessful();
        $this->assertSame($first, DB::table('app_users')->where('id', $this->user->id)->value('password'));
    }

    public function test_login_returns_tokens_and_hides_the_password(): void
    {
        $res = $this->login();
        $this->assertNotEmpty($res['accessToken']);
        $this->assertNotEmpty($res['refreshToken']);
        $this->assertSame(3600, $res['expiresIn']);
        $this->assertArrayNotHasKey('password', $res['user']);
    }

    public function test_refresh_rotates_and_reuse_revokes_the_session(): void
    {
        $first = $this->login();

        $second = $this->postJson('/api/auth/refresh', ['refreshToken' => $first['refreshToken']])->assertOk()->json();
        $this->assertNotSame($first['refreshToken'], $second['refreshToken']);
        $this->assertNotSame($first['accessToken'], $second['accessToken']);

        // the old access token died with the rotation
        $this->withToken($first['accessToken'])->getJson('/api/auth/whoami')->assertStatus(401);
        $this->withToken($second['accessToken'])->getJson('/api/auth/whoami')->assertOk();

        // replaying the used refresh token revokes the family
        $this->postJson('/api/auth/refresh', ['refreshToken' => $first['refreshToken']])->assertStatus(401);
        $this->postJson('/api/auth/refresh', ['refreshToken' => $second['refreshToken']])->assertStatus(401);
        $this->app['auth']->forgetGuards();
        $this->withToken($second['accessToken'])->getJson('/api/auth/whoami')->assertStatus(401);
    }

    public function test_expired_access_token_is_refused(): void
    {
        $res = $this->login();
        $this->travel(61)->minutes();
        $this->withToken($res['accessToken'])->getJson('/api/auth/whoami')->assertStatus(401);
    }

    public function test_logout_ends_only_this_device(): void
    {
        $phone = $this->login();
        $tablet = $this->login();

        $this->withToken($phone['accessToken'])->postJson('/api/auth/logout')->assertOk();
        $this->app['auth']->forgetGuards();

        $this->withToken($phone['accessToken'])->getJson('/api/auth/whoami')->assertStatus(401);
        $this->postJson('/api/auth/refresh', ['refreshToken' => $phone['refreshToken']])->assertStatus(401);
        $this->app['auth']->forgetGuards();
        $this->withToken($tablet['accessToken'])->getJson('/api/auth/whoami')->assertOk();
    }

    public function test_password_reset_signs_out_every_device(): void
    {
        $session = $this->login();
        config(['mail.default' => 'array']);
        $this->postJson('/api/password/request', ['email' => $this->user->email]);
        $body = app('mailer')->getSymfonyTransport()->messages()->last()->getOriginalMessage()->getTextBody();
        preg_match('/\b(\d{6})\b/', $body, $m);
        $ticket = $this->postJson('/api/password/verify', ['email' => $this->user->email, 'code' => $m[1]])->json();
        $this->postJson('/api/password/reset', ['user_id' => $ticket['user_id'], 'token' => $ticket['token'], 'password' => 'brand-new'])->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($session['accessToken'])->getJson('/api/auth/whoami')->assertStatus(401);
        $this->postJson('/api/auth/refresh', ['refreshToken' => $session['refreshToken']])->assertStatus(401);
        $this->assertSame('done', $this->login('brand-new')['msg']);
    }
}
