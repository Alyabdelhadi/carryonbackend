<?php

namespace App\Http\Middleware;

use App\Models\AppUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * `app.auth:<param>,<param>`: the request must carry a valid app access
 * token (Authorization: Bearer ...). The listed request / route
 * parameters name the acting user (`user_id`, `carrier_id`, ...): a value
 * that differs from the token's user is refused with 403, a missing one is
 * filled in, so the legacy model code that reads $_GET / $data acts as the
 * authenticated user only.
 *
 * During the switch from the old app, API_LEGACY_USER_ID_AUTH=true lets
 * requests without a token through on the ids they send (the old
 * behaviour). Turn it off once the old app is gone.
 */
class AuthenticateAppUser
{
    public function handle(Request $request, Closure $next, string ...$params): Response
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user instanceof AppUser || !$user->currentAccessToken()?->can('app')) {
            if ($request->bearerToken() === null && config('services.app_api.legacy_user_id_auth')) {
                Log::channel(config('logging.default'))->info('Legacy unauthenticated API call.', ['path' => $request->path()]);
                return $next($request);
            }
            return response()->json(['msg' => 'Unauthenticated.', 'reason' => 'unauthenticated'], 401);
        }
        if ((int) $user->status !== 1) {
            return response()->json(['msg' => 'This account is disabled.', 'reason' => 'account_disabled'], 403);
        }

        $id = (string) $user->id;
        foreach ($params as $param) {
            $route = $request->route();
            $sent = $route?->parameter($param) ?? $request->input($param);
            if ($sent !== null && $sent !== '' && (string) $sent !== $id) {
                return response()->json(['msg' => 'You can only act on your own account.', 'reason' => 'forbidden'], 403);
            }
            if ($route && $route->hasParameter($param)) {
                $route->setParameter($param, $id);
            } else {
                $request->merge([$param => $id]);
                $request->query->set($param, $id);
                // the legacy model methods read the superglobals directly
                $_GET[$param] = $id;
                if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
                    $_POST[$param] = $id;
                }
            }
        }

        $request->attributes->set('app_user', $user);
        $request->setUserResolver(fn () => $user);
        return $next($request);
    }
}
