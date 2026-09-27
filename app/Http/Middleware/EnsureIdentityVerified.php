<?php

namespace App\Http\Middleware;

use App\Models\AppUser;
use App\Services\IdentityVerificationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `app.verified`, after `app.auth`: while the admin requires identity
 * verification, only verified accounts may send, receive or carry a
 * package or add a trip. The app shows its own prompt first; this is the
 * server-side guarantee.
 */
class EnsureIdentityVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!IdentityVerificationService::enabled()) {
            return $next($request);
        }

        $user = $request->attributes->get('app_user');
        if (!$user instanceof AppUser) {
            // legacy token-less call (API_LEGACY_USER_ID_AUTH): the id it sent
            $id = $request->input('user_id') ?? $request->input('carrier_id');
            $user = $id ? AppUser::find($id) : null;
        }
        if (!$user || $user->is_verified) {
            return $next($request);
        }

        return response()->json([
            'msg' => 'error',
            'reason' => 'identity_required',
            'identity_status' => $user->identity_status,
            'error' => $user->identity_status === AppUser::IDENTITY_PENDING
                ? 'Your identity is under review. You can send, receive and carry packages once it is verified.'
                : 'Please verify your identity before sending, receiving or carrying packages.',
        ]);
    }
}
