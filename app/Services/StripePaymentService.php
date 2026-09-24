<?php

namespace App\Services;

use App\Models\ParcelOrder;
use App\Models\PaymentMethod;
use Exception;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\StripeClient;

class StripePaymentService
{
    protected StripeClient $stripe;
    protected PaymentMethod $paymentMethod;

    /**
     * @param bool $requireEnabled false for refunds / webhooks, which must
     *   keep working for orders paid before the admin disabled Stripe.
     */
    public function __construct(bool $requireEnabled = true)
    {
        $query = PaymentMethod::where('code', 'stripe');
        if ($requireEnabled) {
            $query->where('enabled', true);
        }
        $method = $query->first();

        if (!$method || ($requireEnabled && !$method->enabled)) {
            throw new Exception('Stripe is currently disabled.');
        }
        if (empty($method->secret_key)) {
            throw new Exception('Stripe secret key is not configured.');
        }

        $this->paymentMethod = $method;
        // PaymentMethod decrypts secret_key through its encrypted cast.
        $this->stripe = new StripeClient($method->secret_key);
    }

    public static function isEnabled(): bool
    {
        $method = PaymentMethod::where('code', 'stripe')->where('enabled', true)->first();
        return $method !== null && !empty($method->secret_key) && !empty($method->publishable_key);
    }

    public function publishableKey(): ?string
    {
        return $this->paymentMethod->publishable_key;
    }

    /** Statuses in which the app can still confirm the same intent. */
    public const REUSABLE_STATUSES = [
        'requires_payment_method', 'requires_confirmation', 'requires_action', 'processing',
    ];

    /**
     * The intent the app should confirm: the order's existing one while it is
     * still open, otherwise a fresh one. Uses payment_amount, not the free-text
     * `amount` reward.
     */
    public function paymentIntentFor(ParcelOrder $order): PaymentIntent
    {
        if (!empty($order->payment_reference)) {
            try {
                $existing = $this->stripe->paymentIntents->retrieve($order->payment_reference);
                if (in_array($existing->status, self::REUSABLE_STATUSES, true)
                    && (int) $existing->amount === $this->amountInCents($order)) {
                    return $existing;
                }
            } catch (Exception $e) {
                // fall through and create a new one
            }
        }
        return $this->createPaymentIntent($order);
    }

    public function createPaymentIntent(ParcelOrder $order): PaymentIntent
    {
        $amount = $this->amountInCents($order);
        if ($amount <= 0) {
            throw new Exception('Invalid payment amount.');
        }
        if (empty($order->payment_currency)) {
            throw new Exception('Payment currency is missing.');
        }

        return $this->stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => strtolower($order->payment_currency),
            'automatic_payment_methods' => ['enabled' => true],
            'description' => 'CarryOn package #' . $order->id,
            'metadata' => [
                'parcel_order_id' => (string) $order->id,
                'user_id' => (string) $order->user_id,
                'carrier_id' => (string) ($order->carrier_id ?? ''),
            ],
        ]);
    }

    public function retrieve(string $paymentIntentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    /** Full refund of a paid order's intent. */
    public function refund(ParcelOrder $order): Refund
    {
        if (empty($order->payment_reference)) {
            throw new Exception('Order has no Stripe payment to refund.');
        }
        return $this->stripe->refunds->create(
            ['payment_intent' => $order->payment_reference],
            ['idempotency_key' => 'refund-order-' . $order->id]
        );
    }

    public function amountInCents(ParcelOrder $order): int
    {
        return (int) round(((float) $order->payment_amount) * 100);
    }
}
