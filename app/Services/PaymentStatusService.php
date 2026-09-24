<?php

namespace App\Services;

use App\Models\NotificationTemplate;
use App\Models\ParcelOrder;
use Illuminate\Support\Facades\Log;

/**
 * Applies a Stripe PaymentIntent outcome to an order and tells both sides.
 * Used by the webhook and by the app's post-payment sync call, so whichever
 * arrives first wins and the second is a no-op.
 */
class PaymentStatusService
{
    public function __construct(private FirebaseService $firebase)
    {
    }

    /** @return bool true when the order just became paid */
    public function markPaid(ParcelOrder $order, string $intentId): bool
    {
        if ($order->isPaid()) {
            return false;
        }
        $order->payment_reference = $intentId;
        $order->payment_status = 'paid';
        $order->paid_at = now();
        $order->payment_deadline_at = null;
        $order->save();

        Log::info('Stripe payment completed.', ['order_id' => $order->id, 'payment_intent' => $intentId]);

        $this->notify('payment_receipt', $order, $order->user_id,
            'Payment Successful', 'Your payment for package #' . $order->id . ' was received.');
        $this->notify('payment_received', $order, $order->carrier_id,
            'Payment Received', 'Package #' . $order->id . ' is paid. You can pick it up.');
        return true;
    }

    public function markFailed(ParcelOrder $order): void
    {
        if ($order->isPaid()) {
            return;
        }
        $order->payment_status = 'failed';
        $order->save();
        $this->notify('payment_failed', $order, $order->user_id,
            'Payment Failed', 'Your payment for package #' . $order->id . ' did not go through. Please try again.');
    }

    /** The intent is dead; the next "Pay now" creates a fresh one. */
    public function markCancelled(ParcelOrder $order): void
    {
        if ($order->isPaid()) {
            return;
        }
        $order->payment_status = 'unpaid';
        $order->payment_reference = null;
        $order->save();
    }

    /** Map a Stripe intent status onto the order. */
    public function applyIntentStatus(ParcelOrder $order, string $intentId, string $status): void
    {
        switch ($status) {
            case 'succeeded':
                $this->markPaid($order, $intentId);
                break;
            case 'canceled':
                $this->markCancelled($order);
                break;
            case 'requires_payment_method':
                // Sheet was dismissed or the card was declined: let them retry.
                if (!$order->isPaid() && $order->payment_status !== 'unpaid') {
                    $order->payment_status = 'unpaid';
                    $order->save();
                }
                break;
        }
    }

    private function notify(string $event, ParcelOrder $order, $userId, string $fallbackTitle, string $fallbackBody): void
    {
        if (!$userId) {
            return;
        }
        $template = NotificationTemplate::where('event', $event)->first();
        $title = $template ? TemplateService::parse($template->title, $order) : $fallbackTitle;
        $body = $template ? TemplateService::parse($template->body, $order) : $fallbackBody;
        try {
            $this->firebase->sendToUser($userId, $title, $body, ['order_id' => $order->id, 'event' => $event]);
        } catch (\Throwable $e) {
            Log::warning('Push after Stripe event failed: ' . $e->getMessage());
        }
    }
}
