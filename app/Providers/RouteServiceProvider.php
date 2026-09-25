<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Every attempt is a billed Shufti request.
        RateLimiter::for('identity', function (Request $request) {
            return Limit::perHour(10)->by('identity|' . $request->ip());
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return [
                Limit::perMinute(10)->by('admin-login|' . $request->ip()),
                Limit::perMinute(5)->by('admin-login|' . strtolower((string) $request->input('username', ''))),
            ];
        });

        // password guessing: per IP and per account
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(10)->by('login|' . $request->ip()),
                Limit::perMinute(5)->by('login|' . strtolower((string) $request->input('email', ''))),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return [
                Limit::perMinute(10)->by('password-reset|' . $request->ip()),
                Limit::perHour(30)->by('password-reset|' . strtolower((string) $request->input('email', $request->input('user_id', '')))),
            ];
        });

        RateLimiter::for('identity-status', function (Request $request) {
            return Limit::perMinute(6)->by('identity-status|' . ($request->input('user_id') ?: $request->ip()));
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
