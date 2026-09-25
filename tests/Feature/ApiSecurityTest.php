<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\AppUser;
use App\Models\ParcelOrder;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The security audit's findings, each as an attack that must fail. Local
 * dump-based MySQL (.env.testing), rolled back.
 */
class ApiSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private AppUser $alice; // sender
    private AppUser $bob;   // carrier
    private AppUser $eve;   // attacker

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        config(['services.app_api.legacy_user_id_auth' => false, 'services.firebase.enabled' => false]);
        $this->alice = $this->makeUser('alice');
        $this->bob = $this->makeUser('bob');
        $this->eve = $this->makeUser('eve');
    }

    private function makeUser(string $tag): AppUser
    {
        $u = new AppUser();
        $u->name = 'Test ' . ucfirst($tag);
        $u->email = $tag . uniqid() . '@example.test';
        $u->phone = '00961' . random_int(10000000, 99999999);
        $u->password = 'secret123';
        $u->role = 1;
        $u->status = 1;
        $u->save();
        return $u;
    }

    private function as(AppUser $user): static
    {
        Sanctum::actingAs($user, ['app']);
        return $this;
    }

    private function order(array $attrs = []): ParcelOrder
    {
        $o = new ParcelOrder();
        $o->forceFill(array_merge([
            'user_id' => $this->alice->id, 'cate_id' => 4, 'type' => 1, 'status' => 'Unassigned',
            'r_name' => 'Receiver', 'r_phone' => '0096170000000', 's_name' => 'Sender', 's_phone' => '0096171111111',
            's_addressname' => 'Home', 'r_addressname' => 'Office',
            's_city' => 'Beirut', 's_country' => 'Lebanon', 's_lat' => 33.893811, 's_lng' => 35.501777,
            's_street' => 'Hamra St', 's_building' => 'B1', 's_apartment' => '4',
            'r_city' => 'Dubai', 'r_country' => 'United Arab Emirates', 'r_lat' => 25.2048, 'r_lng' => 55.2708,
            'r_street' => 'Marina', 'r_building' => 'T2', 'r_apartment' => '9',
            'payment_method' => 'cash_on_delivery', 'payment_status' => 'pending', 'amount' => '10',
            'weight' => '1 kg', 'order_date' => now()->addDays(3),
        ], $attrs));
        $o->save();
        return $o;
    }

    public function test_protected_routes_need_a_token(): void
    {
        $this->getJson('/api/myCreatedParcelOrders?user_id=' . $this->alice->id)->assertStatus(401);
        $this->getJson('/api/wallet?user_id=' . $this->alice->id)->assertStatus(401);
        $this->postJson('/api/updateInfo?id=' . $this->alice->id, ['name' => 'x'])->assertStatus(401);
        // public catalog stays open
        $this->getJson('/api/appSettings')->assertOk();
    }

    public function test_a_token_cannot_act_for_another_user(): void
    {
        $this->as($this->eve)->getJson('/api/myCreatedParcelOrders?user_id=' . $this->alice->id)->assertStatus(403);
        $this->as($this->eve)->postJson('/api/updateInfo?id=' . $this->alice->id, [
            'name' => 'Pwned', 'email' => 'pwned@example.test', 'phone' => '1', 'password' => 'hacked',
        ])->assertStatus(403);
        $this->assertNotSame('Pwned', $this->alice->fresh()->name);
        $this->as($this->eve)->getJson('/api/trips/carrier/' . $this->bob->id)->assertStatus(403);
    }

    public function test_legacy_mode_keeps_the_old_app_working_until_switched_off(): void
    {
        config(['services.app_api.legacy_user_id_auth' => true]);
        $this->getJson('/api/myCreatedParcelOrders?user_id=' . $this->alice->id)->assertOk();
        config(['services.app_api.legacy_user_id_auth' => false]);
        $this->getJson('/api/myCreatedParcelOrders?user_id=' . $this->alice->id)->assertStatus(401);
    }

    public function test_orders_cannot_be_hijacked_or_skip_steps(): void
    {
        $order = $this->order();
        // own package
        $this->as($this->alice)->postJson('/api/assignParcelOrder', ['order_id' => $order->id])
            ->assertJsonMissing(['message' => 'done']);
        // bob takes it
        $this->as($this->bob)->postJson('/api/assignParcelOrder', ['order_id' => $order->id]);
        $this->assertSame($this->bob->id, (int) $order->fresh()->carrier_id);
        // eve cannot take it over
        $this->as($this->eve)->postJson('/api/assignParcelOrder', ['order_id' => $order->id]);
        $this->assertSame($this->bob->id, (int) $order->fresh()->carrier_id);
        // no delivering before pickup
        $this->as($this->bob)->postJson('/api/deliverParcelOrder', ['order_id' => $order->id]);
        $this->assertSame('Assigned', $order->fresh()->status);
        // no transit before pickup
        $this->as($this->bob)->postJson('/api/transitParcelOrder', ['order_id' => $order->id]);
        $this->assertSame('Assigned', $order->fresh()->status);

        $this->as($this->bob)->postJson('/api/pickupParcelOrder', ['order_id' => $order->id]);
        $this->assertSame('Picked', $order->fresh()->status);
        // no cancelling once picked up
        $this->as($this->alice)->postJson('/api/cancelParcelOrder', ['order_id' => $order->id]);
        $this->assertSame('Picked', $order->fresh()->status);
    }

    public function test_sender_can_drop_the_carrier_but_strangers_cannot(): void
    {
        $order = $this->order(['status' => 'Assigned', 'carrier_id' => $this->bob->id]);
        // the app sends the carrier id as user_id; the token decides who acts
        $this->as($this->eve)->postJson('/api/unassignParcelOrder', ['order_id' => $order->id, 'user_id' => $this->bob->id]);
        $this->assertSame('Assigned', $order->fresh()->status);
        $this->as($this->alice)->postJson('/api/unassignParcelOrder', ['order_id' => $order->id, 'user_id' => $this->bob->id]);
        $this->assertSame('Unassigned', $order->fresh()->status);
    }

    public function test_only_the_sender_rates_and_the_rating_goes_to_the_carrier(): void
    {
        $order = $this->order(['status' => 'Delivered', 'carrier_id' => $this->bob->id]);
        $this->as($this->eve)->postJson('/api/rate', ['order_id' => $order->id, 'user_id' => $this->eve->id, 'rating' => 5])
            ->assertJsonMissing(['msg' => 'done']);
        $this->as($this->alice)->postJson('/api/rate', ['order_id' => $order->id, 'user_id' => $this->eve->id, 'rating' => 4])
            ->assertJsonPath('msg', 'done');
        $this->assertSame($this->bob->id, (int) DB::table('ratings')->where('order_id', $order->id)->value('user_id'));
    }

    public function test_other_peoples_orders_are_redacted_or_refused(): void
    {
        $open = $this->order();
        $res = $this->as($this->eve)->getJson('/api/parcelOrder?id=' . $open->id)->assertOk()->json();
        foreach (['s_phone', 'r_phone', 's_street', 'r_apartment', 'payment_reference'] as $key) {
            $this->assertArrayNotHasKey($key, $res);
        }
        $this->assertEquals(33.89, $res['s_lat']);
        // the sender still sees everything
        $mine = $this->as($this->alice)->getJson('/api/parcelOrder?id=' . $open->id)->json();
        $this->assertSame('0096171111111', $mine['s_phone']);

        $taken = $this->order(['status' => 'Picked', 'carrier_id' => $this->bob->id]);
        $this->as($this->eve)->getJson('/api/parcelOrder?id=' . $taken->id)->assertStatus(403);

        $list = $this->as($this->eve)->getJson('/api/unassginedParcelOrders')->json();
        $row = collect($list)->firstWhere('id', $open->id);
        $this->assertNotNull($row);
        $this->assertArrayNotHasKey('r_phone', $row);
    }

    public function test_other_users_profile_is_public_fields_only(): void
    {
        $res = $this->as($this->eve)->getJson('/api/userInfo?id=' . $this->bob->id)->assertOk()->json('user');
        $this->assertSame($this->bob->name, $res['name']);
        foreach (['email', 'phone', 'password', 'identity', 'wallet', 'wallet_balance', 'rcode'] as $key) {
            $this->assertArrayNotHasKey($key, $res);
        }
        $own = $this->as($this->bob)->getJson('/api/userInfo?id=' . $this->bob->id)->json('user');
        $this->assertSame($this->bob->email, $own['email']);
        $this->assertArrayNotHasKey('password', $own);
    }

    public function test_addresses_and_trips_stay_with_their_owner(): void
    {
        $address = new Address();
        $address->forceFill(['user_id' => $this->alice->id, 'name' => 'Home', 'city' => 'Beirut', 'country' => 'Lebanon',
            'lat' => 33.9, 'lng' => 35.5, 'street' => 'S', 'building' => 'B', 'apartment' => '1', 'type' => 0])->save();
        $this->as($this->eve)->postJson('/api/updateAddress?id=' . $address->id, [
            'name' => 'Stolen', 'city' => 'X', 'country' => 'X', 'lat' => 0, 'lng' => 0, 'street' => 'x', 'building' => 'x', 'apartment' => 'x',
        ]);
        $this->assertSame('Home', $address->fresh()->name);
        $this->assertSame($this->alice->id, (int) $address->fresh()->user_id);

        $trip = new Trip();
        $trip->forceFill(['carrier_id' => $this->bob->id, 'city_from_id' => 50, 'city_to_id' => 51, 'frequency' => 'daily'])->save();
        $this->as($this->eve)->putJson('/api/trips/' . $trip->id, ['city_from_id' => 51, 'city_to_id' => 50, 'frequency' => 'daily'])
            ->assertStatus(403);
        $this->as($this->eve)->deleteJson('/api/trips/' . $trip->id)->assertStatus(403);
        $this->assertNotNull(Trip::find($trip->id));
    }

    public function test_password_change_needs_the_current_password_and_photos_cannot_be_swapped(): void
    {
        $payload = ['name' => 'Alice', 'email' => $this->alice->email, 'phone' => $this->alice->phone, 'password' => 'new-secret'];
        $this->as($this->alice)->postJson('/api/updateInfo', $payload + ['current_password' => 'wrong'])
            ->assertJsonPath('reason', 'current_password');
        $res = $this->as($this->alice)->postJson('/api/updateInfo', $payload + ['current_password' => 'secret123'])->json();
        $this->assertSame('done', $res['msg']);
        $this->assertNotEmpty($res['accessToken']);
        $this->assertTrue($this->alice->fresh()->checkPassword('new-secret'));

        $before = $this->alice->fresh()->selfie;
        Sanctum::actingAs($this->alice, ['app']);
        $this->post('/api/updateInfo', ['name' => 'Alice', 'email' => $this->alice->email, 'phone' => $this->alice->phone,
            'selfie' => UploadedFile::fake()->image('new.jpg')]);
        $this->assertSame($before, $this->alice->fresh()->selfie);
    }

    public function test_uploads_must_be_real_images(): void
    {
        config(['services.firebase.enabled' => false]);
        \App\Models\AppSetting::setBool(\App\Models\AppSetting::SHUFTI_ENABLED, false);
        $res = $this->post('/api/signup', [
            'name' => 'Shell', 'email' => 'shell' . uniqid() . '@example.test', 'phone' => '00961' . random_int(10000000, 99999999),
            'password' => 'secret123',
            'selfie' => UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]); ?>'),
            'identity' => UploadedFile::fake()->image('id.jpg'),
        ])->json();
        $this->assertSame('invalid_upload', $res['reason'] ?? null);
        $this->assertEmpty(glob(AppUser::SELFIE_DIR . '/*.php'));
    }

    public function test_legacy_reset_rejects_empty_tokens(): void
    {
        $this->postJson('/api/resetPassword', ['user_id' => $this->alice->id, 'token' => '', 'password' => 'hacked'])
            ->assertJsonPath('status', 'error');
        $this->assertTrue($this->alice->fresh()->checkPassword('secret123'));
        $this->postJson('/api/submitVerification', ['user_id' => $this->alice->id, 'token' => ''])
            ->assertJsonPath('status', 'error');
    }

    public function test_admin_get_actions_refuse_cross_site_requests_and_id_files_need_login(): void
    {
        $this->get('/users/' . $this->alice->id . '/identity')->assertRedirect();

        $admin = User::first();
        $this->actingAs($admin);
        $_GET['id'] = $this->alice->id;
        $this->get('/userStatus?id=' . $this->alice->id, ['Sec-Fetch-Site' => 'cross-site'])->assertStatus(403);
        $this->assertSame(1, (int) $this->alice->fresh()->status);
        $_GET['id'] = $this->alice->id; // the admin controllers read $_GET
        $this->get('/userVerification?id=' . $this->alice->id, ['Sec-Fetch-Site' => 'same-origin'])->assertRedirect();
        $this->assertTrue($this->alice->fresh()->is_verified);

        // the users list and edit form still render, and link ID files through the admin route
        $this->get('/users')->assertOk()->assertDontSee('upload/identities/', false);
        $this->get('/users/' . $this->alice->id . '/edit')->assertOk()->assertDontSee('secret123');
    }
}
