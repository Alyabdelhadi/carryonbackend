<?php

namespace App\Http\Controllers;

use App\Models\ParcelOrder;
use App\Models\PaymentMethod;
use App\Services\PaymentStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

/**
 * POST api/payments/stripe/webhook. Stripe tells us when the sender's
 * PaymentIntent succeeded or failed; the order's payment_status follows.
 * Works even when the admin has since disabled Stripe, so in-flight
 * payments still complete.
 */
class StripeWebhookController extends Controller
{
    public function __construct(private PaymentStatusService $status)
    {
    }

    public function handle(Request $request)
    {
        $stripeMethod = PaymentMethod::where('code', 'stripe')->first();
        if (!$stripeMethod || empty($stripeMethod->webhook_secret)) {
            Log::error('Stripe webhook secret is missing.');
            return response()->json(['message' => 'Webhook is not configured.'], 500);
        }

        $signature = $request->header('Stripe-Signature');
        if (!$signature) {
            return response()->json(['message' => 'Stripe signature missing.'], 400);
        }

        try {
            // Stripe signs the raw body.
            $event = Webhook::constructEvent($request->getContent(), $signature, $stripeMethod->webhook_secret);
        } catch (UnexpectedValueException $e) {
            Log::warning('Invalid Stripe webhook payload.');
            return response()->json(['message' => 'Invalid payload.'], 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Invalid Stripe webhook signature.');
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->paymentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->paymentFailed($event->data->object);
                break;
            case 'payment_intent.canceled':
                $this->paymentCancelled($event->data->object);
                break;
        }

        return response()->json(['received' => true]);
    }

    private function orderFor($paymentIntent): ?ParcelOrder
    {
        $order = ParcelOrder::where('payment_reference', $paymentIntent->id)->first();
        if (!$order && !empty($paymentIntent->metadata->parcel_order_id)) {
            $order = ParcelOrder::find((int) $paymentIntent->metadata->parcel_order_id);
        }
        return $order;
    }

    private function paymentSucceeded($paymentIntent): void
    {
        $order = $this->orderFor($paymentIntent);
        if (!$order) {
            Log::warning('Stripe payment succeeded but order not found.', ['payment_intent' => $paymentIntent->id]);
            return;
        }
        $this->status->markPaid($order, $paymentIntent->id);
    }

    private function paymentFailed($paymentIntent): void
    {
        $order = $this->orderFor($paymentIntent);
        if ($order) {
            $this->status->markFailed($order);
        }
    }

    private function paymentCancelled($paymentIntent): void
    {
        $order = $this->orderFor($paymentIntent);
        if ($order) {
            $this->status->markCancelled($order);
        }
    }
}
