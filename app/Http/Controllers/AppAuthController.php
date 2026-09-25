<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Services\AppTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Token lifecycle for the mobile app: refresh and logout. */
class AppAuthController extends Controller
{
    public function __construct(private AppTokenService $tokens)
    {
    }

    /**
     * POST api/auth/refresh {refreshToken} -> {accessToken, refreshToken, expiresIn}
     * 401 when the refresh token is unknown, expired, used or revoked.
     */
    public function refresh(Request $request)
    {
        $pair = $this->tokens->refresh($request->input('refreshToken'));
        if ($pair === null) {
            return response()->json(['msg' => 'Session expired. Please log in again.', 'reason' => 'refresh_invalid'], 401);
        }
        return response()->json(['msg' => 'done'] + $pair);
    }

    /** POST api/auth/logout (Bearer): ends this device's login. */
    public function logout(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if ($user instanceof AppUser) {
            $this->tokens->revokeCurrent($user, $user->currentAccessToken());
        }
        return response()->json(['msg' => 'done']);
    }

    /** GET api/auth/whoami (Bearer): the signed-in user's id, to check a token. */
    public function whoami(Request $request)
    {
        return response()->json(['msg' => 'done', 'user_id' => $request->user()->id]);
    }
}
