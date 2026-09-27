<?php

namespace App\Models;

use App\Support\AdminModules;
use Illuminate\Database\Eloquent\Model;

/** A dashboard role: which pages its admins may view, create, edit, delete. */
class AdminGroup extends Model
{
    protected $fillable = ['name', 'description', 'permissions'];

    protected $casts = [
        'is_super' => 'boolean',
        'permissions' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'admin_group_id');
    }

    public function allows(string $module, string $action): bool
    {
        if ($this->is_super) {
            return AdminModules::get($module) !== null;
        }
        return in_array($action, (array) (($this->permissions ?? [])[$module] ?? []), true);
    }

    /** Every tick there is, over all pages. */
    public static function totalPermissions(): int
    {
        return array_sum(array_map(fn ($m) => count($m['actions']), AdminModules::all()));
    }

    /** How many ticks are on, for the groups list. */
    public function permissionCount(): int
    {
        if ($this->is_super) {
            return self::totalPermissions();
        }
        return array_sum(array_map('count', $this->permissions ?? []));
    }
}
