<?php

namespace App\Http\Controllers;

use App\Models\ParcelOrder;
use App\Models\PaymentMethod;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Stripe\Webhook;
use UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(
        Request $request
    ) {
        /*
         * Don't require Stripe to currently
         * be enabled.
         *
         * A previously-created payment may
         * still complete.
         */
        $stripeMethod =
            PaymentMethod::where(
                'code',
                'stripe'
            )
            ->first();


        if (
            !$stripeMethod
            ||
            empty(
                $stripeMethod->webhook_secret
            )
        ) {
            Log::error(
                'Stripe webhook secret is missing.'
            );

            return response()->json([
                'message' =>
                    'Webhook is not configured.'
            ], 500);
        }


        /*
         * IMPORTANT:
         * Stripe requires the raw body.
         */
        $payload =
            $request->getContent();

        $signature =
            $request->header(
                'Stripe-Signature'
            );


        if (!$signature) {
            return response()->json([
                'message' =>
                    'Stripe signature missing.'
            ], 400);
        }


        try {

            $event =
                Webhook::constructEvent(
                    $payload,
                    $signature,
                    $stripeMethod
                        ->webhook_secret
                );

        } catch (
            UnexpectedValueException $e
        ) {

            Log::warning(
                'Invalid Stripe webhook payload.'
            );

            return response()->json([
                'message' =>
                    'Invalid payload.'
            ], 400);

        } catch (
            SignatureVerificationException $e
        ) {

            Log::warning(
                'Invalid Stripe webhook signature.'
            );

            return response()->json([
                'message' =>
                    'Invalid signature.'
            ], 400);
        }


        switch ($event->type) {

            case 'payment_intent.succeeded':

                $this->paymentSucceeded(
                    $event->data->object
                );

                break;


            case 'payment_intent.payment_failed':

                $this->paymentFailed(
                    $event->data->object
                );

                break;


            case 'payment_intent.canceled':

                $this->paymentCancelled(
                    $event->data->object
                );

                break;
        }


        return response()->json([
            'received' => true
        ]);
    }


    private function paymentSucceeded(
        $paymentIntent
    ): void
    {
        $order =
            ParcelOrder::where(
                'payment_reference',
                $paymentIntent->id
            )
            ->first();


        if (!$order) {

            Log::warning(
                'Stripe payment succeeded but order not found.',
                [
                    'payment_intent' =>
                        $paymentIntent->id,
                ]
            );

            return;
        }


        /*
         * Don't process successful
         * payment twice.
         */
        if (
            $order->payment_status === 'paid'
        ) {
            return;
        }


        $order->payment_status =
            'paid';

        $order->paid_at =
            now();

        $order->save();


        Log::info(
            'Stripe payment completed.',
            [
                'order_id' =>
                    $order->id,

                'payment_intent' =>
                    $paymentIntent->id,
            ]
        );
    }


    private function paymentFailed(
        $paymentIntent
    ): void
    {
        $order =
            ParcelOrder::where(
                'payment_reference',
                $paymentIntent->id
            )
            ->first();


        if (!$order) {
            return;
        }


        if (
            $order->payment_status === 'paid'
        ) {
            return;
        }


        $order->payment_status =
            'failed';

        $order->save();
    }


    private function paymentCancelled(
        $paymentIntent
    ): void
    {
        $order =
            ParcelOrder::where(
                'payment_reference',
                $paymentIntent->id
            )
            ->first();


        if (!$order) {
            return;
        }


        if (
            $order->payment_status === 'paid'
        ) {
            return;
        }


        $order->payment_status =
            'cancelled';

        $order->save();
    }
}