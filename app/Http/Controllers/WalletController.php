<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\PayoutRequest;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Admin: carrier wallets (/wallets): balances, ledger, manual adjustments. */
class WalletController extends Controller
{
    public function __construct(private WalletService $wallet)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $query = AppUser::query()
            ->select('app_users.*')
            ->selectSub(
                WalletTransaction::selectRaw('count(*)')->whereColumn('app_user_id', 'app_users.id'),
                'movements'
            )
            ->orderByDesc('wallet_balance')
            ->orderByDesc('id');

        if ($q !== '') {
            $query->where(function ($u) use ($q) {
                $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")->orWhere('id', is_numeric($q) ? (int) $q : 0);
            });
        } else {
            $query->where(function ($u) {
                $u->where('wallet_balance', '!=', 0)
                    ->orWhereExists(WalletTransaction::selectRaw('1')->whereColumn('app_user_id', 'app_users.id'));
            });
        }

        return view('wallets.index', [
            'data' => $query->paginate(50)->withQueryString(),
            'filter_q' => $q,
            'totalBalance' => (float) AppUser::sum('wallet_balance'),
            'currency' => $this->wallet->defaultCurrency(),
        ]);
    }

    public function show(int $id)
    {
        $user = AppUser::findOrFail($id);

        return view('wallets.show', [
            'user' => $user,
            'summary' => $this->wallet->summary($user),
            'transactions' => WalletTransaction::where('app_user_id', $id)->orderByDesc('id')->paginate(50),
            'payouts' => PayoutRequest::where('app_user_id', $id)->orderByDesc('id')->limit(20)->get(),
        ]);
    }

    public function adjust(Request $request, int $id)
    {
        $user = AppUser::findOrFail($id);
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'note' => 'required|string|max:500',
        ]);
        try {
            $this->wallet->adjust(
                $user,
                (float) $request->amount,
                $user->wallet_currency ?? $this->wallet->defaultCurrency(),
                $request->note,
                Auth::id()
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Wallet adjusted.');
    }
}
