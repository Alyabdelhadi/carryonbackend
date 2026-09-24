<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\AppUser;
use App\Models\ParcelOrder;
use App\Models\PaymentMethod;
use App\Models\PayoutRequest;
use App\Models\WalletTransaction;
use App\Services\PayoutService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Runs against the local dump-based MySQL (.env.testing); every test is
 * rolled back. No Stripe network calls: only cash and already-paid states.
 */
class WalletFlowTest extends TestCase
{
    use DatabaseTransactions;

    private AppUser $sender;
    private AppUser $carrier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sender = $this->makeUser('sender');
        $this->carrier = $this->makeUser('carrier');
        AppSetting::setNumber(AppSetting::COMMISSION_PERCENT, 15);
        AppSetting::setNumber(AppSetting::PAYOUT_MINIMUM, 20);
        AppSetting::setNumber(AppSetting::PAYOUT_HOLD_DAYS, 0);
        AppSetting::setNumber(AppSetting::PAYMENT_DEADLINE_HOURS, 24);
        PaymentMethod::where('code', 'stripe')->update(['enabled' => 1]);
    }

    private function makeUser(string $tag): AppUser
    {
        $u = new AppUser();
        $u->name = 'Test ' . ucfirst($tag);
        $u->email = $tag . uniqid() . '@example.test';
        $u->phone = '00961' . random_int(10000000, 99999999);
        $u->password = 'secret';
        $u->role = 1;
        $u->status = 1;
        $u->save();
        return $u;
    }

    private function orderPayload(array $overrides = []): array
    {
        $address = [
            'name' => 'Home', 'city' => 'Beirut', 'country' => 'Lebanon', 'lat' => '33.89', 'lng' => '35.50',
            'street' => 'Street', 'building' => 'B', 'apartment' => '1',
        ];
        return array_merge([
            'user_id' => $this->sender->id, 'cate_id' => 4, 'type' => 1,
            'r_name' => 'Receiver', 'r_phone' => '0096170000000', 's_name' => 'Sender', 's_phone' => '0096171111111',
            's_address' => $address, 'r_address' => array_merge($address, ['city' => 'Dubai', 'country' => 'United Arab Emirates']),
            'weight' => '1 kg', 'amount' => '100', 'payment_method' => 2, 'date_to' => 'Needed Soon',
        ], $overrides);
    }

    public function test_card_order_stores_code_status_and_commission(): void
    {
        $response = $this->postJson('/api/createParcelOrder', $this->orderPayload());
        $response->assertOk()->assertJsonPath('message', 'done');

        $order = ParcelOrder::find($response->json('order.id'));
        $this->assertSame('stripe', $order->payment_method);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertEquals(100.00, (float) $order->payment_amount);
        $this->assertEquals(15.00, (float) $order->commission_amount);
        $this->assertEquals(85.00, (float) $order->carrier_earning);
    }

    public function test_cash_order_is_labelled_cash_and_has_no_commission(): void
    {
        $response = $this->postJson('/api/createParcelOrder', $this->orderPayload(['payment_method' => 1, 'amount' => 'Free']));
        $order = ParcelOrder::find($response->json('order.id'));
        $this->assertSame('cash_on_delivery', $order->payment_method);
        $this->assertSame('cash', $order->payment_status);
        $this->assertNull($order->commission_amount);
    }

    public function test_card_order_needs_numeric_reward(): void
    {
        $this->postJson('/api/createParcelOrder', $this->orderPayload(['amount' => 'Free']))
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Please enter the reward as a number to pay by card.']);
    }

    public function test_pickup_and_pay_endpoint_wait_for_payment(): void
    {
        $order = ParcelOrder::find($this->postJson('/api/createParcelOrder', $this->orderPayload())->json('order.id'));

        // Pay endpoint refuses before a carrier accepts.
        $this->postJson('/api/payments/stripe/create', ['order_id' => $order->id, 'user_id' => $this->sender->id])
            ->assertStatus(422);

        $this->postJson('/api/assignParcelOrder', ['order_id' => $order->id, 'user_id' => $this->carrier->id])
            ->assertJsonPath('message', 'done');
        $order->refresh();
        $this->assertSame('Assigned', $order->status);
        $this->assertNotNull($order->payment_deadline_at);

        $this->postJson('/api/pickupParcelOrder', ['order_id' => $order->id, 'user_id' => $this->carrier->id])
            ->assertJsonPath('message', 'The sender has not paid for this package yet.');
        $this->assertSame('Assigned', $order->fresh()->status);
    }

    public function test_delivery_credits_carrier_wallet_once(): void
    {
        $order = ParcelOrder::find($this->postJson('/api/createParcelOrder', $this->orderPayload())->json('order.id'));
        $order->carrier_id = $this->carrier->id;
        $order->status = 'Transit';
        $order->payment_status = 'paid';   // as the webhook would set it
        $order->paid_at = now();
        $order->save();

        $this->postJson('/api/deliverParcelOrder', ['order_id' => $order->id, 'user_id' => $this->carrier->id])
            ->assertJsonPath('message', 'done');
        // A second call must not double-credit.
        $this->postJson('/api/deliverParcelOrder', ['order_id' => $order->id, 'user_id' => $this->carrier->id]);

        $this->carrier->refresh();
        $this->assertEquals(85.00, (float) $this->carrier->wallet_balance);
        $this->assertSame('USD', $this->carrier->wallet_currency);
        $this->assertSame(1, WalletTransaction::where('parcel_order_id', $order->id)->count());

        $this->getJson('/api/wallet?user_id=' . $this->carrier->id)
            ->assertJsonPath('msg', 'done')
            ->assertJsonPath('wallet.balance', 85)
            ->assertJsonPath('wallet.available', 85)
            ->assertJsonPath('rules.commission_percent', 15);
    }

    public function test_payout_request_reserves_and_reject_returns_it(): void
    {
        $wallet = app(WalletService::class);
        $wallet->adjust($this->carrier, 100, 'USD', 'seed', null);

        $this->postJson('/api/payouts', ['user_id' => $this->carrier->id, 'amount' => 10, 'method' => 'omt', 'details' => ['phone' => '70123456']])
            ->assertJsonPath('msg', 'The minimum payout is 20.00 USD.');
        $this->postJson('/api/payouts', ['user_id' => $this->carrier->id, 'amount' => 500, 'method' => 'omt', 'details' => ['phone' => '70123456']])
            ->assertJsonPath('msg', 'Only 100.00 USD is available to withdraw.');

        $created = $this->postJson('/api/payouts', ['user_id' => $this->carrier->id, 'amount' => 60, 'method' => 'omt', 'details' => ['phone' => '70123456']])
            ->assertJsonPath('msg', 'done')
            ->assertJsonPath('wallet.balance', 40);
        $payoutId = $created->json('payout.id');

        $this->postJson('/api/payouts', ['user_id' => $this->carrier->id, 'amount' => 20, 'method' => 'omt', 'details' => ['phone' => '70123456']])
            ->assertJsonPath('msg', 'You already have a payout request waiting to be processed.');

        app(PayoutService::class)->reject(PayoutRequest::find($payoutId), 1, 'wrong number');
        $this->assertEquals(100.00, (float) $this->carrier->fresh()->wallet_balance);

        $paid = app(PayoutService::class)->request($this->carrier, 100, 'bank_transfer', ['iban' => 'LB00']);
        app(PayoutService::class)->markPaid($paid, 1, 'TRX-1');
        $this->assertEquals(0.00, (float) $this->carrier->fresh()->wallet_balance);
        $this->assertSame('paid', $paid->fresh()->status);
    }

    public function test_hold_keeps_fresh_earnings_pending(): void
    {
        AppSetting::setNumber(AppSetting::PAYOUT_HOLD_DAYS, 3);
        $order = ParcelOrder::find($this->postJson('/api/createParcelOrder', $this->orderPayload())->json('order.id'));
        $order->carrier_id = $this->carrier->id;
        $order->status = 'Transit';
        $order->payment_status = 'paid';
        $order->save();
        $this->postJson('/api/deliverParcelOrder', ['order_id' => $order->id, 'user_id' => $this->carrier->id]);

        $summary = app(WalletService::class)->summary($this->carrier->fresh());
        $this->assertEquals(85, $summary['balance']);
        $this->assertEquals(0, $summary['available']);
        $this->assertEquals(85, $summary['pending']);

        $this->expectException(InvalidArgumentException::class);
        app(PayoutService::class)->request($this->carrier->fresh(), 50, 'omt', ['phone' => '1']);
    }

    public function test_unpaid_assignment_is_released_after_deadline(): void
    {
        $order = ParcelOrder::find($this->postJson('/api/createParcelOrder', $this->orderPayload())->json('order.id'));
        $this->postJson('/api/assignParcelOrder', ['order_id' => $order->id, 'user_id' => $this->carrier->id]);
        $order->refresh();
        $order->payment_deadline_at = now()->subMinute();
        $order->save();

        Artisan::call('orders:release-unpaid');

        $order->refresh();
        $this->assertSame('Unassigned', $order->status);
        $this->assertNull($order->carrier_id);
        $this->assertNull($order->payment_deadline_at);
    }

    public function test_app_settings_expose_payment_rules(): void
    {
        $this->getJson('/api/appSettings')
            ->assertJsonPath('payments.commission_percent', 15)
            ->assertJsonPath('payments.payout_minimum', 20)
            ->assertJsonStructure(['payments' => ['online_payment_enabled', 'currency', 'payout_methods']]);
    }
}
