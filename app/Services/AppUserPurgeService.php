<?php

namespace App\Services;

use App\Models\Address;
use App\Models\AppUser;
use App\Models\IdentityVerification;
use App\Models\ParcelOrder;
use App\Models\ParcelOrderView;
use App\Models\PayoutRequest;
use App\Models\Rating;
use App\Models\Trip;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin "Delete user": removes the account and everything that belongs to
 * it (the packages they sent, their trips, addresses, ratings, wallet
 * history, payout requests, identity checks, tokens and photos).
 *
 * Packages they only carried stay with their senders: an accepted one
 * goes back to Unassigned, finished ones just lose the carrier link.
 * Refused while money or a parcel is still in play, so the admin settles
 * that first.
 */
class AppUserPurgeService
{
    /** Why the user cannot be deleted yet, or null. */
    public function blocker(AppUser $user): ?string
    {
        $inTransit = ParcelOrder::where(fn ($q) => $q->where('user_id', $user->id)->orWhere('carrier_id', $user->id))
            ->whereIn('status', ['Picked', 'Transit'])->count();
        if ($inTransit) {
            return "{$inTransit} package(s) with this user are picked up or in transit. Finish or cancel them first.";
        }
        $paidOpen = ParcelOrder::where('user_id', $user->id)
            ->whereIn('payment_status', ['paid', 'refund_pending'])
            ->whereNotIn('status', ['Delivered', 'Cancelled'])->count();
        if ($paidOpen) {
            return "{$paidOpen} card-paid package(s) are not delivered yet. Cancel them (the payment is refunded) first.";
        }
        if (PayoutRequest::where('app_user_id', $user->id)->where('status', PayoutRequest::PENDING)->exists()) {
            return 'This user has a pending payout request. Pay or reject it first.';
        }
        if ((float) $user->wallet_balance > 0) {
            return 'This user still has ' . number_format((float) $user->wallet_balance, 2) . ' in their wallet. Pay it out or adjust it to zero first.';
        }
        return null;
    }

    /** @return array{orders: int, trips: int} what was removed */
    public function purge(AppUser $user): array
    {
        $files = [[$user->selfie, $user->identity]];
        foreach (IdentityVerification::where('app_user_id', $user->id)->get(['selfie', 'identity']) as $attempt) {
            $files[] = [$attempt->selfie, $attempt->identity];
        }

        $counts = DB::transaction(function () use ($user) {
            $orderIds = ParcelOrder::where('user_id', $user->id)->pluck('id');

            ParcelOrderView::whereIn('parcel_order_id', $orderIds)->orWhere('carrier_id', $user->id)->delete();
            Rating::whereIn('order_id', $orderIds)->orWhere('user_id', $user->id)->delete();
            // other carriers' earnings on these packages stay, without the link
            WalletTransaction::whereIn('parcel_order_id', $orderIds)->where('app_user_id', '!=', $user->id)
                ->update(['parcel_order_id' => null]);
            $orders = ParcelOrder::whereIn('id', $orderIds)->delete();

            // packages they carried for others
            ParcelOrder::where('carrier_id', $user->id)->where('status', 'Assigned')
                ->update(['status' => 'Unassigned', 'carrier_id' => null]);
            ParcelOrder::where('carrier_id', $user->id)->update(['carrier_id' => null]);

            $trips = Trip::where('carrier_id', $user->id)->delete();
            Address::where('user_id', $user->id)->delete();
            WalletTransaction::where('app_user_id', $user->id)->delete();
            PayoutRequest::where('app_user_id', $user->id)->delete();
            IdentityVerification::where('app_user_id', $user->id)->delete();
            DB::table('app_refresh_tokens')->where('app_user_id', $user->id)->delete();
            $user->tokens()->delete();
            $user->delete();

            return ['orders' => $orders, 'trips' => $trips];
        });

        foreach ($files as [$selfie, $identity]) {
            AppUser::discardUploads($selfie, $identity);
        }
        Log::info('App user deleted by admin.', ['app_user_id' => $user->id, 'admin_id' => auth()->id()] + $counts);
        return $counts;
    }
}
