<?php

namespace Tests\Feature;

use App\Models\AdminGroup;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

/**
 * Admin groups and per-page permissions (view / create / edit / delete).
 * Needs the 2026_09_27_000000 migration on the test database.
 */
class AdminPermissionsTest extends TestCase
{
    use DatabaseTransactions;

    private const SAME_SITE = ['Sec-Fetch-Site' => 'same-origin'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    private function group(array $permissions, string $name = null): AdminGroup
    {
        $group = new AdminGroup();
        $group->fill(['name' => $name ?? 'Group ' . uniqid(), 'permissions' => $permissions])->save();
        return $group;
    }

    private function admin(AdminGroup $group, bool $active = true): User
    {
        $user = new User();
        $user->forceFill([
            'name' => 'Test Admin',
            'username' => 'adm' . uniqid(),
            'email' => 'adm' . uniqid() . '@example.test',
            'password' => 'secret-pass',
            'admin_group_id' => $group->id,
            'is_active' => $active,
            'point_who' => '0',
            'point_use' => '0',
        ])->save();
        return $user;
    }

    private function superGroup(): AdminGroup
    {
        return AdminGroup::where('is_super', true)->firstOrFail();
    }

    public function test_view_only_group_can_open_but_not_change_a_page(): void
    {
        $admin = $this->admin($this->group(['services' => ['view'], 'dashboard' => ['view']]));
        $this->actingAs($admin);

        $this->get('/services')->assertOk()
            ->assertSee('Services')
            ->assertDontSee('services/add', false)
            ->assertDontSee('confirmAlert(', false);

        $this->get('/services/add')->assertRedirect(url('home'))->assertSessionHas('error');
        $service = Service::first();
        if ($service) {
            $status = $service->status;
            $this->get('/serviceStatus?id=' . $service->id, self::SAME_SITE)->assertRedirect();
            $this->assertSame($status, $service->fresh()->status);
            $this->get('/services/delete/' . $service->id, self::SAME_SITE)->assertRedirect();
            $this->assertNotNull($service->fresh());
        }

        // pages without a tick are refused, and hidden from the sidebar
        $this->get('/users')->assertRedirect(url('home'));
        $this->get('/admins')->assertRedirect(url('home'));
        $this->get('/home')->assertOk()->assertDontSee('App Users')->assertSee('Services');
    }

    public function test_every_tick_opens_its_action(): void
    {
        $admin = $this->admin($this->group(['services' => ['view', 'create', 'edit', 'delete']]));
        $this->actingAs($admin);

        $this->get('/services')->assertOk()->assertSee('confirmAlert(', false);
        $this->get('/services/add')->assertOk();
        // no dashboard tick: the refusal lands on the first page they can open
        $this->get('/home')->assertRedirect(url('services'));
    }

    public function test_view_only_wallets_can_open_a_wallet(): void
    {
        $this->actingAs($this->admin($this->group(['wallets' => ['view']])));
        $id = \App\Models\AppUser::value('id');
        $this->get('/wallets/' . $id)->assertOk()->assertDontSee('wallets/' . $id . '/adjust', false);
    }

    public function test_login_lands_on_the_first_allowed_page_and_disabled_admins_are_refused(): void
    {
        $admin = $this->admin($this->group(['cities' => ['view']]));
        $this->post('/login', ['username' => $admin->username, 'password' => 'secret-pass'])
            ->assertRedirect(url('cities'));
        $this->assertNotNull($admin->fresh()->last_login_at);
        $this->get('/logout');

        $disabled = $this->admin($this->group(['cities' => ['view']]), false);
        $this->post('/login', ['username' => $disabled->username, 'password' => 'secret-pass'])
            ->assertRedirect(url('login'));
        $this->assertGuest();

        $empty = $this->admin($this->group([]));
        $this->post('/login', ['username' => $empty->username, 'password' => 'secret-pass'])
            ->assertRedirect(url('login'));
        $this->assertGuest();
    }

    public function test_group_form_saves_ticks_and_implies_view(): void
    {
        $this->actingAs($this->admin($this->superGroup()));

        $this->post('/admin-groups', [
            'name' => 'Support ' . uniqid(),
            'permissions' => [
                'users' => ['edit'],
                'orders' => ['view', 'create'], // orders has no "create"
                'nope' => ['view'],
            ],
        ])->assertRedirect(url('admin-groups'));

        $group = AdminGroup::latest('id')->first();
        $this->assertSame(['users' => ['view', 'edit'], 'orders' => ['view']], $group->permissions);
    }

    public function test_non_super_admins_cannot_escalate(): void
    {
        $managers = $this->group([
            'admins' => ['view', 'create', 'edit', 'delete'],
            'admin_groups' => ['view', 'create', 'edit'],
            'services' => ['view'],
        ]);
        $manager = $this->admin($managers);
        $this->actingAs($manager);

        // own group is off limits, and so is the Super Admin group
        $this->get('/admin-groups/' . $managers->id . '/edit')->assertRedirect(url('admin-groups'));
        $this->put('/admin-groups/' . $this->superGroup()->id, ['name' => 'x'])->assertRedirect(url('admin-groups'));

        // a new group only gets ticks the manager holds
        $this->post('/admin-groups', [
            'name' => 'Helpers ' . uniqid(),
            'permissions' => ['services' => ['view', 'delete'], 'wallets' => ['view', 'edit']],
        ]);
        $this->assertSame(['services' => ['view']], AdminGroup::latest('id')->first()->permissions);

        // cannot hand out Super Admin or touch a super admin account
        $this->post('/admins', [
            'name' => 'Sneaky', 'username' => 'sneaky' . uniqid(), 'email' => 'sneaky' . uniqid() . '@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'admin_group_id' => $this->superGroup()->id, 'is_active' => '1',
        ])->assertSessionHasErrors('admin_group_id');
        $super = $this->admin($this->superGroup());
        $this->get('/admins/' . $super->id . '/edit')->assertRedirect(url('admins'));
    }

    public function test_own_account_cannot_be_deleted_or_disabled(): void
    {
        $me = $this->admin($this->superGroup());
        $this->actingAs($me);

        $this->delete('/admins/' . $me->id)->assertSessionHas('error');
        $this->put('/admins/' . $me->id, [
            'name' => 'Me', 'username' => $me->username, 'email' => $me->email, 'is_active' => '0',
        ])->assertRedirect(url('admins'));
        $this->assertTrue($me->fresh()->is_active);
    }

    public function test_a_group_with_admins_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin($this->superGroup()));
        $group = $this->group(['services' => ['view']]);
        $this->admin($group);

        $this->delete('/admin-groups/' . $group->id)->assertSessionHas('error');
        $this->assertNotNull($group->fresh());
    }
}
