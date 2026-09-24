<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\AppUser;
use App\Models\PayoutRequest;
use App\Models\WalletTransaction;
use App\Services\PayoutService;
use App\Services\StripePaymentService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Carrier wallet endpoints for the app. Like the rest of the API the caller
 * identifies itself with `user_id`; responses use `msg: done` on success.
 */
class WalletApiController extends Controller
{
    public function __construct(private WalletService $wallet, private PayoutService $payouts)
    {
    }

    /** GET api/wallet?user_id= : balances, rules, latest movements, open payout. */
    public function show(Request $request)
    {
        $user = $this->user($request);
        if (!$user) {
            return response()->json(['msg' => 'Oops! Invalid id.']);
        }
        $summary = $this->wallet->summary($user);

        return response()->json([
            'msg' => 'done',
            'wallet' => $summary,
            'rules' => self::rules($summary['currency']),
            'transactions' => WalletTransaction::where('app_user_id', $user->id)
                ->orderByDesc('id')->limit(20)->get(),
            'open_payout' => PayoutRequest::where('app_user_id', $user->id)
                ->where('status', PayoutRequest::PENDING)->first(),
        ]);
    }

    /** GET api/wallet/transactions?user_id=&page= */
    public function transactions(Request $request)
    {
        $user = $this->user($request);
        if (!$user) {
            return response()->json(['msg' => 'Oops! Invalid id.']);
        }
        $page = WalletTransaction::where('app_user_id', $user->id)->orderByDesc('id')->paginate(30);

        return response()->json([
            'msg' => 'done',
            'transactions' => $page->items(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    /** GET api/payouts?user_id= */
    public function payouts(Request $request)
    {
        $user = $this->user($request);
        if (!$user) {
            return response()->json(['msg' => 'Oops! Invalid id.']);
        }
        return response()->json([
            'msg' => 'done',
            'payouts' => PayoutRequest::where('app_user_id', $user->id)->orderByDesc('id')->limit(50)->get(),
        ]);
    }

    /** POST api/payouts : user_id, amount, method, details{...} */
    public function requestPayout(Request $request)
    {
        $user = $this->user($request);
        if (!$user) {
            return response()->json(['msg' => 'Oops! Invalid id.']);
        }
        $details = $request->input('details', []);
        if (is_string($details)) {
            $details = json_decode($details, true) ?: [];
        }
        try {
            $payout = $this->payouts->request(
                $user,
                (float) $request->input('amount', 0),
                (string) $request->input('method', ''),
                is_array($details) ? $details : []
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['msg' => $e->getMessage()]);
        }
        return response()->json([
            'msg' => 'done',
            'payout' => $payout,
            'wallet' => $this->wallet->summary($user->fresh()),
        ]);
    }

    /** POST api/payouts/{id}/cancel : user_id */
    public function cancelPayout(Request $request, int $id)
    {
        $user = $this->user($request);
        $payout = PayoutRequest::find($id);
        if (!$user || !$payout) {
            return response()->json(['msg' => 'Oops! Invalid id.']);
        }
        try {
            $payout = $this->payouts->cancel($user, $payout);
        } catch (InvalidArgumentException $e) {
            return response()->json(['msg' => $e->getMessage()]);
        }
        return response()->json([
            'msg' => 'done',
            'payout' => $payout,
            'wallet' => $this->wallet->summary($user->fresh()),
        ]);
    }

    /** Wallet rules the app shows; also embedded in api/appSettings. */
    public static function rules(?string $currency = null): array
    {
        $numbers = AppSetting::allNumbers();
        return [
            'online_payment_enabled' => StripePaymentService::isEnabled(),
            'currency' => $currency ?? (new WalletService)->defaultCurrency(),
            'commission_percent' => $numbers[AppSetting::COMMISSION_PERCENT],
            'payout_minimum' => $numbers[AppSetting::PAYOUT_MINIMUM],
            'payout_hold_days' => (int) $numbers[AppSetting::PAYOUT_HOLD_DAYS],
            'payment_deadline_hours' => (int) $numbers[AppSetting::PAYMENT_DEADLINE_HOURS],
            'payout_methods' => AppSetting::payoutMethods(),
        ];
    }

    private function user(Request $request): ?AppUser
    {
        $id = $request->input('user_id', $request->input('id'));
        return $id ? AppUser::find((int) $id) : null;
    }
}
