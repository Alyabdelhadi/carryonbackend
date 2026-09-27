<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\AdminGroup;
use App\Models\AppUser;
use App\Models\ParcelOrder;
use App\Models\Rating;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Admin "Delete user" removes the account and what belongs to it. */
class AppUserPurgeTest extends TestCase
{
    use DatabaseTransactions;

    private const SAME_SITE = ['Sec-Fetch-Site' => 'same-origin'];

    private function appUser(string $tag): AppUser
    {
        $u = new AppUser();
        $u->forceFill([
            'name' => 'Purge ' . $tag, 'email' => $tag . uniqid() . '@example.test',
            'phone' => '00961' . random_int(10000000, 99999999), 'password' => 'secret', 'role' => 1, 'status' => 1,
        ])->save();
        return $u;
    }

    private function order(AppUser $sender, ?AppUser $carrier, string $status): ParcelOrder
    {
        $template = ParcelOrder::firstOrFail()->replicate();
        $template->forceFill(['user_id' => $sender->id, 'carrier_id' => $carrier?->id, 'status' => $status, 'payment_status' => 'cash'])->save();
        return $template;
    }

    private function actAsSuper(): void
    {
        $this->actingAs(User::where('admin_group_id', AdminGroup::where('is_super', true)->value('id'))->firstOrFail());
    }

    public function test_delete_removes_the_user_and_everything_they_own(): void
    {
        $this->actAsSuper();
        $user = $this->appUser('gone');
        $other = $this->appUser('other');

        $sent = $this->order($user, null, 'Unassigned');
        $rated = $this->order($user, $other, 'Delivered');
        Rating::forceCreate(['rating' => 5, 'user_id' => $other->id, 'order_id' => $rated->id]);
        $carrying = $this->order($other, $user, 'Assigned');
        $carried = $this->order($other, $user, 'Delivered');
        $trip = Trip::firstOrFail()->replicate();
        $trip->forceFill(['carrier_id' => $user->id])->save();
        $address = Address::firstOrFail()->replicate();
        $address->forceFill(['user_id' => $user->id])->save();

        $this->get('/users/delete/' . $user->id, self::SAME_SITE)->assertRedirect()->assertSessionHas('message');

        $this->assertNull(AppUser::find($user->id));
        $this->assertNull(ParcelOrder::find($sent->id));
        $this->assertNull(ParcelOrder::find($rated->id));
        $this->assertFalse(Rating::where('order_id', $rated->id)->exists());
        $this->assertNull(Trip::find($trip->id));
        $this->assertNull(Address::find($address->id));
        // packages they carried stay with the sender
        $this->assertSame('Unassigned', $carrying->fresh()->status);
        $this->assertNull($carrying->fresh()->carrier_id);
        $this->assertSame('Delivered', $carried->fresh()->status);
        $this->assertNotNull(AppUser::find($other->id));
    }

    public function test_delete_is_refused_while_a_package_is_in_transit(): void
    {
        $this->actAsSuper();
        $user = $this->appUser('busy');
        $this->order($this->appUser('sender'), $user, 'Transit');

        $this->get('/users/delete/' . $user->id, self::SAME_SITE)->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull(AppUser::find($user->id));
    }
}
