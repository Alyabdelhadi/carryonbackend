<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\AppUser;
use App\Models\PayoutRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Carrier payout requests: create, cancel, and the admin's paid / rejected decisions. */
class PayoutService
{
    public function __construct(private WalletService $wallet)
    {
    }

    /** @throws InvalidArgumentException with a message safe to show the user */
    public function request(AppUser $user, float $amount, string $method, array $details): PayoutRequest
    {
        $amount = round($amount, 2);
        $summary = $this->wallet->summary($user);
        $minimum = AppSetting::getFloat(AppSetting::PAYOUT_MINIMUM);
        $methods = array_column(AppSetting::payoutMethods(), 'name', 'code');

        if (!isset($methods[$method])) {
            throw new InvalidArgumentException('Please choose a valid payout method.');
        }
        if ($amount <= 0) {
            throw new InvalidArgumentException('Please enter an amount.');
        }
        if ($amount < $minimum) {
            throw new InvalidArgumentException(sprintf('The minimum payout is %s %s.', number_format($minimum, 2), $summary['currency']));
        }
        if ($amount > $summary['available']) {
            throw new InvalidArgumentException(sprintf('Only %s %s is available to withdraw.', number_format($summary['available'], 2), $summary['currency']));
        }
        if (PayoutRequest::where('app_user_id', $user->id)->where('status', PayoutRequest::PENDING)->exists()) {
            throw new InvalidArgumentException('You already have a payout request waiting to be processed.');
        }
        $details = array_filter(array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : null, $details), fn ($v) => $v !== null && $v !== '');
        if (empty($details)) {
            throw new InvalidArgumentException('Please enter where to send the money.');
        }

        return DB::transaction(function () use ($user, $amount, $method, $details, $summary) {
            $request = PayoutRequest::create([
                'app_user_id' => $user->id,
                'amount' => $amount,
                'currency' => $summary['currency'],
                'method' => $method,
                'details' => $details,
                'status' => PayoutRequest::PENDING,
            ]);
            $this->wallet->reserveForPayout($request);
            return $request;
        });
    }

    public function cancel(AppUser $user, PayoutRequest $request): PayoutRequest
    {
        if ((int) $request->app_user_id !== (int) $user->id) {
            throw new InvalidArgumentException('This payout request is not yours.');
        }
        if (!$request->isPending()) {
            throw new InvalidArgumentException('Only pending payout requests can be cancelled.');
        }
        return DB::transaction(function () use ($request) {
            $request->status = PayoutRequest::CANCELLED;
            $request->processed_at = Carbon::now();
            $request->save();
            $this->wallet->reversePayout($request);
            return $request;
        });
    }

    public function markPaid(PayoutRequest $request, ?int $adminId, ?string $reference, ?string $note = null): PayoutRequest
    {
        if (!$request->isPending()) {
            throw new InvalidArgumentException('Only pending payout requests can be marked paid.');
        }
        $request->status = PayoutRequest::PAID;
        $request->reference = $reference;
        $request->admin_note = $note;
        $request->processed_by = $adminId;
        $request->processed_at = Carbon::now();
        $request->save();
        return $request;
    }

    public function reject(PayoutRequest $request, ?int $adminId, ?string $note): PayoutRequest
    {
        if (!$request->isPending()) {
            throw new InvalidArgumentException('Only pending payout requests can be rejected.');
        }
        return DB::transaction(function () use ($request, $adminId, $note) {
            $request->status = PayoutRequest::REJECTED;
            $request->admin_note = $note;
            $request->processed_by = $adminId;
            $request->processed_at = Carbon::now();
            $request->save();
            $this->wallet->reversePayout($request);
            return $request;
        });
    }
}
