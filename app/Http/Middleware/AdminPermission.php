<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `perm:<module>` or `perm:<module>,<action>` on admin routes: the signed-in
 * admin's group must have that tick (App\Support\AdminModules). Without an
 * explicit action it follows the controller method, as every admin page
 * uses the same names: create/add/store -> create (and show: here the
 * resource's show() is the "Add New" form at <page>/add), edit/update/
 * ...Status toggles -> edit, delete/destroy -> delete, anything else -> view.
 */
class AdminPermission
{
    public function handle(Request $request, Closure $next, string $module, ?string $action = null): Response
    {
        $action ??= self::actionFor($request);
        $user = $request->user();

        if (!$user instanceof User || !$user->canAdmin($module, $action)) {
            $message = 'You do not have permission to ' . ($action === 'view' ? 'open' : $action) . ' this page.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }
            // back to a page they can open, never into a redirect loop
            $home = $user instanceof User ? $user->homeUrl() : null;
            $target = $home && trim($request->path(), '/') !== trim($home, '/') ? url($home) : null;
            return $target ? redirect($target)->with('error', $message) : abort(403, $message);
        }
        return $next($request);
    }

    public static function actionFor(Request $request): string
    {
        $method = strtolower((string) $request->route()?->getActionMethod());
        return match (true) {
            in_array($method, ['create', 'add', 'store', 'show'], true) => 'create',
            in_array($method, ['delete', 'destroy'], true) => 'delete',
            $method === 'edit', $method === 'update', str_ends_with($method, 'status'),
            in_array($method, ['userverification', 'notify', 'refund', 'save', 'adjust', 'paid', 'reject'], true) => 'edit',
            default => $request->isMethod('GET') ? 'view' : 'edit',
        };
    }
}
