<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\IdentityVerification;
use App\Services\IdentityVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Identity verification for existing accounts (the app's "Verify your
 * identity" screen) and the Shufti callback. Signup runs its own check in
 * AppUser::signup.
 */
class IdentityController extends Controller
{
    public function __construct(private IdentityVerificationService $identity)
    {
    }

    /** POST api/identity/verify (multipart: user_id, selfie, identity) */
    public function verify(Request $request)
    {
        $user = AppUser::find($request->input('user_id'));
        if (!$user) {
            return response()->json(['msg' => 'Oops! Invalid id.']);
        }
        if ($user->is_verified || !IdentityVerificationService::enabled()) {
            return response()->json(['msg' => 'done', 'user' => $this->userJson($user)]);
        }
        if (!$request->hasFile('selfie') || !$request->hasFile('identity')) {
            return response()->json(['msg' => 'error', 'error' => 'Please upload both a selfie and an identity document.']);
        }

        $selfie = AppUser::storeSelfie($request->file('selfie'), false);
        $identity = AppUser::storeIdentity($request->file('identity'), false);
        $attempt = $this->identity->check('reverify', $selfie, $identity, $user->email, $user->id, $request->ip());

        if (IdentityVerificationService::isRejection($attempt->status)) {
            AppUser::discardUploads($selfie, $identity);
            // a failed retry must not undo a check that is still pending
            if ($user->identity_status !== AppUser::IDENTITY_PENDING) {
                $this->identity->applyToUser($user, $attempt);
            }
            return response()->json(AppUser::identityError($attempt));
        }

        AppUser::shrinkUploads($selfie, $identity);
        $this->identity->applyToUser($user, $attempt);
        return response()->json(['msg' => 'done', 'user' => $this->userJson($user->fresh())]);
    }

    /**
     * GET api/identity/status?user_id= : the app's "Check again" on the
     * under-review screen. Asks Shufti directly when a check is pending.
     */
    public function status(Request $request)
    {
        $user = AppUser::find($request->input('user_id'));
        if (!$user) {
            return response()->json(['msg' => 'Oops! Invalid id.']);
        }
        if ($user->identity_status === AppUser::IDENTITY_PENDING && $user->shufti_reference) {
            $attempt = IdentityVerification::where('reference', $user->shufti_reference)->first();
            if ($attempt) {
                $this->identity->resolve($attempt);
                $user->refresh();
            }
        }
        return response()->json(['msg' => 'done', 'user' => $this->userJson($user)]);
    }

    /**
     * POST api/identity/shufti/callback. The body is not trusted: only its
     * reference is used, and the verdict is read back from Shufti with our
     * own keys.
     */
    public function shuftiCallback(Request $request)
    {
        $reference = (string) $request->input('reference', '');
        $attempt = $reference !== '' ? IdentityVerification::where('reference', $reference)->first() : null;
        if (!$attempt) {
            Log::info('Shufti callback for an unknown reference.', ['reference' => $reference, 'event' => $request->input('event')]);
            return response()->json(['ok' => true]);
        }
        $this->identity->resolve($attempt);
        return response()->json(['ok' => true]);
    }

    private function userJson(AppUser $user): AppUser
    {
        return $user->makeHidden(['password']);
    }
}
