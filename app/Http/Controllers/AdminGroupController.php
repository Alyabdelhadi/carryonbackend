<?php

namespace App\Http\Controllers;

use App\Models\AdminGroup;
use App\Support\AdminModules;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Groups and their permission ticks (page x view/create/edit/delete).
 * The Super Admin group is fixed. An admin who is not a super admin cannot
 * change their own group or grant ticks they do not have themselves.
 */
class AdminGroupController extends Controller
{
    public function index()
    {
        $groups = AdminGroup::withCount('users')->orderByDesc('is_super')->orderBy('name')->get();
        return view('admin_groups.index', ['groups' => $groups]);
    }

    public function create()
    {
        return view('admin_groups.form', ['group' => new AdminGroup(['permissions' => []])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        $group = new AdminGroup();
        $group->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'permissions' => $this->grantable($request),
        ])->save();

        return redirect('admin-groups')->with('message', 'Group "' . $group->name . '" created.');
    }

    public function edit($id)
    {
        $group = AdminGroup::findOrFail($id);
        if ($denied = $this->denyEditing($group)) {
            return $denied;
        }
        return view('admin_groups.form', ['group' => $group]);
    }

    public function update(Request $request, $id)
    {
        $group = AdminGroup::findOrFail($id);
        if ($denied = $this->denyEditing($group)) {
            return $denied;
        }
        $data = $this->validated($request, $group);
        $group->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'permissions' => $this->grantable($request),
        ])->save();

        return redirect('admin-groups')->with('message', 'Group "' . $group->name . '" updated.');
    }

    public function destroy($id)
    {
        $group = AdminGroup::withCount('users')->findOrFail($id);
        if ($denied = $this->denyEditing($group)) {
            return $denied;
        }
        if ($group->users_count > 0) {
            return back()->with('error', 'Move the ' . $group->users_count . ' admin(s) in "' . $group->name . '" to another group first.');
        }
        $group->delete();
        return redirect('admin-groups')->with('message', 'Group deleted.');
    }

    private function validated(Request $request, ?AdminGroup $group): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('admin_groups', 'name')->ignore($group?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);
    }

    /** The submitted ticks, limited to what the editor holds themselves. */
    private function grantable(Request $request): array
    {
        $permissions = AdminModules::sanitize((array) $request->input('permissions', []));
        $editor = $request->user();
        if ($editor->isSuperAdmin()) {
            return $permissions;
        }
        foreach ($permissions as $module => $actions) {
            $permissions[$module] = array_values(array_filter($actions, fn ($a) => $editor->canAdmin($module, $a)));
        }
        return array_filter($permissions);
    }

    private function denyEditing(AdminGroup $group)
    {
        if ($group->is_super) {
            return redirect('admin-groups')->with('error', 'The Super Admin group always has full access and cannot be changed.');
        }
        $editor = auth()->user();
        if (!$editor->isSuperAdmin() && $editor->admin_group_id === $group->id) {
            return redirect('admin-groups')->with('error', 'Ask a Super Admin to change your own group.');
        }
        return null;
    }
}
