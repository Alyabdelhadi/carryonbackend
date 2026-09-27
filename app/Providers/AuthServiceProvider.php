<?php

namespace App\Providers;

use App\Models\User;
use App\Support\AdminModules;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Admin page permissions in views: @can('users.edit')
        Gate::before(function ($user, string $ability) {
            if (!$user instanceof User || !str_contains($ability, '.')) {
                return null;
            }
            [$module, $action] = explode('.', $ability, 2);
            if (AdminModules::get($module) === null || !in_array($action, AdminModules::ACTIONS, true)) {
                return null;
            }
            return $user->canAdmin($module, $action);
        });
    }
}
