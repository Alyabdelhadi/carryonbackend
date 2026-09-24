<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\ParcelOrder;
use Stripe\StripeClient;
use Exception;

class StripePaymentService
{
    protected $stripe;
    protected $paymentMethod;

    public function __construct()
    {
        $this->paymentMethod = PaymentMethod::where('code', 'stripe')
            ->where('enabled', true)
            ->first();

        if (!$this->paymentMethod) {
            throw new Exception(
                'Stripe is currently disabled.'
            );
        }

        if (empty($this->paymentMethod->secret_key)) {
            throw new Exception(
                'Stripe secret key is not configured.'
            );
        }

        /*
         * Laravel automatically decrypts secret_key
         * because PaymentMethod uses the encrypted cast.
         */
        $this->stripe = new StripeClient(
            $this->paymentMethod->secret_key
        );
    }

    public function createPaymentIntent(ParcelOrder $order)
    {
        /*
         * IMPORTANT:
         * Use payment_amount, NOT the old amount field.
         */
        $amount = (int) round(
            ((float) $order->payment_amount) * 100
        );

        if ($amount <= 0) {
            throw new Exception(
                'Invalid payment amount.'
            );
        }

        if (empty($order->payment_currency)) {
            throw new Exception(
                'Payment currency is missing.'
            );
        }

        return $this->stripe
            ->paymentIntents
            ->create([
                'amount' => $amount,

                'currency' => strtolower(
                    $order->payment_currency
                ),

                'automatic_payment_methods' => [
                    'enabled' => true,
                ],

                'metadata' => [
                    'parcel_order_id' =>
                        (string) $order->id,

                    'user_id' =>
                        (string) $order->user_id,
                ],
            ]);
    }
}