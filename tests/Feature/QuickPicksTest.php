<?php

namespace Tests\Feature;

use App\Models\Tip;
use App\Models\User;
use App\Models\Weight;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Weights and tips (rewards) as the app's quick picks: API shape, admin
 * validation. Local dump-based MySQL with migrations 2026_09_26_000300 /
 * 000400 run (--path=); rolled back.
 */
class QuickPicksTest extends TestCase
{
    use DatabaseTransactions;

    public function test_weights_api_serves_the_seeded_lists(): void
    {
        $rows = $this->getJson('/api/weights')->assertOk()->json();
        $order = collect($rows)->where('in_order_form', true)->pluck('kg')->all();
        $calc = collect($rows)->where('in_calculator', true)->pluck('kg')->all();
        $this->assertEquals([0.5, 1, 2, 5, 10, 20], $order);
        $this->assertEquals([0.5, 1, 2, 3, 5, 7, 10, 15, 23], $calc);
    }

    public function test_tips_api_serves_rewards_with_free_first(): void
    {
        $rows = $this->getJson('/api/tips')->assertOk()->json();
        $this->assertEquals([0, 10, 20, 50, 100, 150, 200], collect($rows)->pluck('amount')->all());
        $this->assertTrue($rows[0]['is_free']);
    }

    public function test_inactive_and_non_numeric_rows_are_not_served(): void
    {
        Weight::query()->update(['status' => 0]);
        $w = new Weight();
        $w->forceFill(['value' => 'heavy', 'status' => 1, 'sort_no' => 0, 'in_order_form' => 1, 'in_calculator' => 1])->save();
        $this->assertSame([], $this->getJson('/api/weights')->json());
    }

    public function test_admin_saves_normalized_numbers_and_refuses_text(): void
    {
        $this->actingAs(User::first());

        $this->post('/weights', ['value' => 'abc', 'status' => 1, 'sort_no' => 99, 'in_order_form' => 1]);
        $this->assertFalse(Weight::where('sort_no', 99)->exists());

        $this->post('/weights', ['value' => '1,5 kg', 'status' => 1, 'sort_no' => 99, 'in_order_form' => 1, 'in_calculator' => 0]);
        $saved = Weight::where('sort_no', 99)->first();
        $this->assertSame('1.5', $saved->value);
        $this->assertTrue($saved->in_order_form);
        $this->assertFalse($saved->in_calculator);

        $this->post('/tips', ['value' => 'Free', 'status' => 1, 'sort_no' => 99]);
        $this->assertSame('0', Tip::where('sort_no', 99)->value('value'));
        $this->post('/tips', ['value' => 'lots', 'status' => 1, 'sort_no' => 98]);
        $this->assertFalse(Tip::where('sort_no', 98)->exists());

        $this->get('/weights')->assertOk()->assertSee('Order form');
        $this->get('/tips')->assertOk()->assertSee('Free');
    }
}
