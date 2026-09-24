<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\AppUser;
use App\Models\ParcelOrder;
use App\Models\PayoutRequest;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * The carrier wallet. Every movement is a WalletTransaction row written
 * inside a transaction that locks the app_users row, and
 * `app_users.wallet_balance` always equals the sum of the rows.
 */
class WalletService
{
    /** Credit the carrier's share of a delivered card-paid order (idempotent per order). */
    public function creditEarning(ParcelOrder $order): ?WalletTransaction
    {
        if (!$order->isOnlinePayment() || $order->payment_status !== 'paid' || !$order->carrier_id) {
            return null;
        }
        $earning = (float) ($order->carrier_earning ?? 0);
        if ($earning <= 0) {
            return null;
        }
        $existing = WalletTransaction::where('parcel_order_id', $order->id)
            ->where('type', WalletTransaction::EARNING)
            ->first();
        if ($existing) {
            return $existing;
        }
        $holdDays = (int) AppSetting::getFloat(AppSetting::PAYOUT_HOLD_DAYS);

        return $this->post($order->carrier_id, $earning, strtoupper($order->payment_currency), WalletTransaction::EARNING, [
            'parcel_order_id' => $order->id,
            'available_at' => Carbon::now()->addDays($holdDays),
            'note' => 'Package #' . $order->id . ' delivered',
        ]);
    }

    /** Move the requested amount out of the balance while the payout is pending. */
    public function reserveForPayout(PayoutRequest $request): WalletTransaction
    {
        return $this->post($request->app_user_id, -(float) $request->amount, $request->currency, WalletTransaction::PAYOUT, [
            'payout_request_id' => $request->id,
            'note' => 'Payout request #' . $request->id,
        ]);
    }

    /** Give a rejected or cancelled payout back. */
    public function reversePayout(PayoutRequest $request): WalletTransaction
    {
        return $this->post($request->app_user_id, (float) $request->amount, $request->currency, WalletTransaction::PAYOUT_REVERSAL, [
            'payout_request_id' => $request->id,
            'note' => 'Payout request #' . $request->id . ' ' . $request->status,
        ]);
    }

    /** Manual admin correction, positive or negative. */
    public function adjust(AppUser $user, float $amount, string $currency, string $note, ?int $adminId): WalletTransaction
    {
        if ($amount == 0.0) {
            throw new InvalidArgumentException('Amount must not be zero.');
        }
        return $this->post($user->id, $amount, strtoupper($currency), WalletTransaction::ADJUSTMENT, [
            'note' => $note,
            'created_by' => $adminId,
        ]);
    }

    /** Balance, what can be withdrawn now, and what is still on hold. */
    public function summary(AppUser $user): array
    {
        $pending = (float) WalletTransaction::where('app_user_id', $user->id)
            ->where('type', WalletTransaction::EARNING)
            ->where('available_at', '>', Carbon::now())
            ->sum('amount');
        // Always from the database: callers often hold a stale model.
        $row = AppUser::where('id', $user->id)->first(['wallet_balance', 'wallet_currency']);
        $balance = (float) ($row->wallet_balance ?? 0);

        return [
            'balance' => round($balance, 2),
            'available' => round(max(0, $balance - $pending), 2),
            'pending' => round($pending, 2),
            'currency' => $row->wallet_currency ?? $this->defaultCurrency(),
        ];
    }

    public function defaultCurrency(): string
    {
        $stripe = \App\Models\PaymentMethod::where('code', 'stripe')->first();
        return strtoupper($stripe->currency ?? 'USD');
    }

    /** @throws RuntimeException when the wallet holds a different currency */
    private function post(int $userId, float $amount, string $currency, string $type, array $attrs): WalletTransaction
    {
        return DB::transaction(function () use ($userId, $amount, $currency, $type, $attrs) {
            $user = AppUser::where('id', $userId)->lockForUpdate()->first();
            if (!$user) {
                throw new RuntimeException("App user {$userId} not found.");
            }
            if ($user->wallet_currency && $user->wallet_currency !== $currency) {
                throw new RuntimeException("Wallet is in {$user->wallet_currency}, cannot post {$currency}.");
            }
            $balance = round((float) $user->wallet_balance + $amount, 2);

            $row = WalletTransaction::create(array_merge([
                'app_user_id' => $userId,
                'type' => $type,
                'amount' => round($amount, 2),
                'currency' => $currency,
                'balance_after' => $balance,
            ], $attrs));

            $user->wallet_balance = $balance;
            $user->wallet_currency = $currency;
            $user->save();

            return $row;
        });
    }
}
