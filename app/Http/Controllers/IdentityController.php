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
        if (IdentityVerificationService::liveMode()) {
            // photos cannot prove liveness: this app version must update
            return response()->json(AppUser::liveRequiredError());
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
     * POST api/identity/live (user_id, optional lang=ar): opens a Shufti
     * onsite session and returns its `verification_url` for the app to
     * show in a browser. The app then calls api/identity/status.
     */
    public function live(Request $request)
    {
        $user = AppUser::find($request->input('user_id'));
        if (!$user) {
            return response()->json(['msg' => 'Oops! Invalid id.']);
        }
        // nothing to do, or a submitted check is still being reviewed
        if ($user->is_verified || !IdentityVerificationService::liveMode()
            || $user->identity_status === AppUser::IDENTITY_PENDING) {
            return response()->json(['msg' => 'done', 'user' => $this->userJson($user)]);
        }

        $language = strtolower((string) $request->input('lang')) === 'ar' ? 'AR' : 'EN';
        $started = $this->identity->startLive($user, $request->ip(), $language);
        if (!$started['url']) {
            return response()->json(AppUser::identityError($started['attempt']));
        }
        return response()->json([
            'msg' => 'done',
            'verification_url' => $started['url'],
            'user' => $this->userJson($user->fresh()),
        ]);
    }

    /**
     * GET api/identity/status?user_id= : "Check again" on the under-review
     * screen, and the return from a live session. Asks Shufti directly
     * when the latest check is pending. `live_unsubmitted` is true while
     * the user's live session page has not been completed.
     */
    public function status(Request $request)
    {
        $user = AppUser::find($request->input('user_id'));
        if (!$user) {
            return response()->json(['msg' => 'Oops! Invalid id.']);
        }
        $attempt = $user->shufti_reference
            ? IdentityVerification::where('reference', $user->shufti_reference)->first()
            : null;
        if ($attempt && $attempt->status === IdentityVerification::PENDING) {
            $this->identity->resolve($attempt);
            $user->refresh();
        }
        return response()->json([
            'msg' => 'done',
            'user' => $this->userJson($user),
            'live_unsubmitted' => $attempt !== null
                && $attempt->status === IdentityVerification::PENDING
                && IdentityVerificationService::isUnsubmitted($attempt),
        ]);
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

    /**
     * GET api/identity/shufti/done: where Shufti's page sends the browser
     * when the user is done (SHUFTI_REDIRECT_URL). The app checks the
     * result itself once the browser closes.
     */
    public function shuftiDone()
    {
        return response(
            '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>CarryOn</title></head><body style="font-family:-apple-system,system-ui,sans-serif;text-align:center;padding:48px 24px">'
            . '<h2>Thank you</h2><p>You can close this page and return to CarryOn.</p>'
            . '<p dir="rtl">شكرًا لك. يمكنك إغلاق هذه الصفحة والعودة إلى CarryOn.</p></body></html>'
        )->header('Content-Type', 'text/html; charset=utf-8');
    }

    private function userJson(AppUser $user): AppUser
    {
        return $user->makeHidden(['password']);
    }
}
