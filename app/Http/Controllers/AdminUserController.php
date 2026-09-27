<?php

namespace App\Http\Controllers;

use App\Models\AdminGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Dashboard accounts (`users` table). Guards: nobody can remove the last
 * active super admin, disable or delete themselves, or, without being a
 * super admin, create or edit super admins.
 */
class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $admins = User::with('group')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admins.index', ['admins' => $admins, 'search' => $search]);
    }

    public function create()
    {
        return view('admins.form', ['admin' => new User(['is_active' => true]), 'groups' => $this->assignableGroups()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        $admin = new User();
        $admin->forceFill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'admin_group_id' => $data['admin_group_id'],
            'is_active' => $request->boolean('is_active'),
            // legacy NOT NULL referral settings, only read from account #1
            'point_who' => '0',
            'point_use' => '0',
        ])->save();

        return redirect('admins')->with('message', 'Admin "' . $admin->name . '" created.');
    }

    public function edit($id)
    {
        $admin = User::findOrFail($id);
        if ($denied = $this->denyEditing($admin)) {
            return $denied;
        }
        return view('admins.form', ['admin' => $admin, 'groups' => $this->assignableGroups()]);
    }

    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);
        if ($denied = $this->denyEditing($admin)) {
            return $denied;
        }
        $data = $this->validated($request, $admin);
        $isSelf = $admin->id === $request->user()->id;
        $active = $isSelf ? true : $request->boolean('is_active');
        $groupId = $isSelf ? $admin->admin_group_id : (int) $data['admin_group_id'];

        if ($this->wouldLoseLastSuper($admin, $groupId, $active)) {
            return back()->withInput()->with('error', 'Keep at least one active Super Admin.');
        }

        $admin->forceFill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'admin_group_id' => $groupId,
            'is_active' => $active,
        ]);
        if (!empty($data['password'])) {
            $admin->password = $data['password'];
        }
        $admin->save();

        return redirect('admins')->with('message', 'Admin "' . $admin->name . '" updated.');
    }

    public function destroy(Request $request, $id)
    {
        $admin = User::findOrFail($id);
        if ($admin->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($denied = $this->denyEditing($admin)) {
            return $denied;
        }
        if ($this->wouldLoseLastSuper($admin, null, false)) {
            return back()->with('error', 'Keep at least one active Super Admin.');
        }
        $admin->delete();
        return redirect('admins')->with('message', 'Admin deleted.');
    }

    private function validated(Request $request, ?User $admin): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($admin?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin?->id)],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'admin_group_id' => [
                $admin && $admin->id === $request->user()->id ? 'nullable' : 'required',
                Rule::in($this->assignableGroups()->pluck('id')->all()),
            ],
        ], [
            'admin_group_id.in' => 'Pick a group you are allowed to assign.',
        ]);
    }

    /** Only super admins hand out (or touch accounts in) the Super Admin group. */
    private function assignableGroups()
    {
        return AdminGroup::orderByDesc('is_super')->orderBy('name')->get()
            ->filter(fn ($g) => !$g->is_super || auth()->user()->isSuperAdmin())
            ->values();
    }

    private function denyEditing(User $admin)
    {
        if ($admin->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect('admins')->with('error', 'Only a Super Admin can change a Super Admin account.');
        }
        return null;
    }

    private function wouldLoseLastSuper(User $admin, ?int $newGroupId, bool $active): bool
    {
        if (!$admin->isSuperAdmin()) {
            return false;
        }
        $stillSuper = $active && $newGroupId && AdminGroup::whereKey($newGroupId)->value('is_super');
        if ($stillSuper) {
            return false;
        }
        $superGroups = AdminGroup::where('is_super', true)->pluck('id');
        return User::whereIn('admin_group_id', $superGroups)->where('is_active', true)
            ->where('id', '!=', $admin->id)->doesntExist();
    }
}
