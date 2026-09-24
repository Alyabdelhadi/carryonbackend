<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use App\Models\PayoutRequest;
use App\Services\FirebaseService;
use App\Services\PayoutService;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/** Admin: carrier payout requests (/payouts). The money itself is sent by hand. */
class PayoutRequestController extends Controller
{
    public function __construct(private PayoutService $payouts, private FirebaseService $firebase)
    {
    }

    public function index(Request $request)
    {
        $status = $request->input('status', PayoutRequest::PENDING);
        $q = trim((string) $request->input('q', ''));

        $query = PayoutRequest::with('user')->orderByDesc('id');
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $query->whereHas('user', function ($u) use ($q) {
                $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%");
            });
        }

        return view('payouts.index', [
            'data' => $query->paginate(50)->withQueryString(),
            'filter_status' => $status,
            'filter_q' => $q,
            'pendingCount' => PayoutRequest::where('status', PayoutRequest::PENDING)->count(),
        ]);
    }

    public function paid(Request $request, int $id)
    {
        $payout = PayoutRequest::findOrFail($id);
        try {
            $this->payouts->markPaid($payout, Auth::id(), $request->input('reference'), $request->input('admin_note'));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        $this->notify('payout_paid', $payout, 'Payout Sent', 'Your payout of ' . TemplateService::money($payout->amount, $payout->currency) . ' has been sent.');

        return back()->with('success', 'Payout #' . $payout->id . ' marked as paid.');
    }

    public function reject(Request $request, int $id)
    {
        $payout = PayoutRequest::findOrFail($id);
        try {
            $this->payouts->reject($payout, Auth::id(), $request->input('admin_note'));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        $this->notify('payout_rejected', $payout, 'Payout Rejected', 'Your payout request was rejected and the amount returned to your wallet.');

        return back()->with('success', 'Payout #' . $payout->id . ' rejected; the amount is back in the wallet.');
    }

    private function notify(string $event, PayoutRequest $payout, string $fallbackTitle, string $fallbackBody): void
    {
        $template = NotificationTemplate::where('event', $event)->first();
        $extra = [':amount' => TemplateService::money($payout->amount, $payout->currency)];
        $title = $template ? TemplateService::parseWithUserId($template->title, $payout->app_user_id, $extra) : $fallbackTitle;
        $body = $template ? TemplateService::parseWithUserId($template->body, $payout->app_user_id, $extra) : $fallbackBody;
        try {
            $this->firebase->sendToUser($payout->app_user_id, $title, $body);
        } catch (\Throwable $e) {
            \Log::warning('Payout push failed: ' . $e->getMessage());
        }
    }
}
