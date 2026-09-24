<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    protected $link = 'payment-methods/';


    /**
     * Display payment methods list.
     */
    public function index()
    {
        $data = PaymentMethod::orderBy('id', 'ASC')->get();

        return view('payment_methods.index', [
            'data' => $data,
            'link' => $this->link,
        ]);
    }


    /**
     * Display payment method edit page.
     */
    public function edit($id)
    {
        $data = PaymentMethod::findOrFail($id);

        return view('payment_methods.edit', [
            'data' => $data,
            'link' => $this->link,
            'form_url' => $this->link . $id,
        ]);
    }


    /**
     * Update payment method.
     */
    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'enabled' => 'required|boolean',
            'currency' => 'required|string|size:3',

            'publishable_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
        ]);


        /*
        |--------------------------------------------------------------------------
        | At least one payment method must stay enabled
        |--------------------------------------------------------------------------
        |
        | If admin is trying to disable this method, check whether another
        | enabled payment method exists.
        |
        */
        if ((int) $request->enabled === 0) {

            $otherEnabledMethods = PaymentMethod::where(
                    'id',
                    '!=',
                    $method->id
                )
                ->where('enabled', 1)
                ->count();


            if ($otherEnabledMethods === 0) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'enabled' =>
                            'At least one payment method must remain enabled.'
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Stripe Configuration Validation
        |--------------------------------------------------------------------------
        */
        if ($method->code === 'stripe') {


            /*
             * Validate publishable key format.
             *
             * Supports:
             * pk_test_...
             * pk_live_...
             */
            if (
                $request->filled('publishable_key')
                &&
                strpos(
                    $request->publishable_key,
                    'pk_'
                ) !== 0
            ) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'publishable_key' =>
                            'Invalid Stripe publishable key.'
                    ]);
            }


            /*
             * Validate secret key format.
             *
             * Supports:
             * sk_test_...
             * sk_live_...
             */
            if (
                $request->filled('secret_key')
                &&
                strpos(
                    $request->secret_key,
                    'sk_'
                ) !== 0
            ) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'secret_key' =>
                            'Invalid Stripe secret key.'
                    ]);
            }


            /*
             * Validate webhook secret format.
             *
             * Usually:
             * whsec_...
             */
            if (
                $request->filled('webhook_secret')
                &&
                strpos(
                    $request->webhook_secret,
                    'whsec_'
                ) !== 0
            ) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'webhook_secret' =>
                            'Invalid Stripe webhook secret.'
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Stripe cannot be enabled without credentials
            |--------------------------------------------------------------------------
            |
            | If fields are blank, use the already saved encrypted values.
            |
            */
            if ((int) $request->enabled === 1) {

                $publishableKey =
                    $request->filled('publishable_key')
                        ? $request->publishable_key
                        : $method->publishable_key;


                $secretKey =
                    $request->filled('secret_key')
                        ? $request->secret_key
                        : $method->secret_key;


                $webhookSecret =
                    $request->filled('webhook_secret')
                        ? $request->webhook_secret
                        : $method->webhook_secret;


                if (
                    empty($publishableKey)
                    ||
                    empty($secretKey)
                    ||
                    empty($webhookSecret)
                ) {

                    return back()
                        ->withInput()
                        ->withErrors([
                            'stripe' =>
                                'Configure the Stripe publishable key, secret key and webhook secret before enabling Stripe.'
                        ]);
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Update General Settings
        |--------------------------------------------------------------------------
        */
        $method->enabled =
            (int) $request->enabled;


        $method->currency =
            strtoupper(
                $request->currency
            );


        /*
        |--------------------------------------------------------------------------
        | Update Stripe Credentials
        |--------------------------------------------------------------------------
        |
        | Only update credentials if admin entered a new value.
        |
        | This means leaving the input blank keeps the currently stored
        | encrypted credential.
        |
        */
        if ($method->code === 'stripe') {


            if (
                $request->filled(
                    'publishable_key'
                )
            ) {

                $method->publishable_key =
                    trim(
                        $request->publishable_key
                    );
            }


            if (
                $request->filled(
                    'secret_key'
                )
            ) {

                $method->secret_key =
                    trim(
                        $request->secret_key
                    );
            }


            if (
                $request->filled(
                    'webhook_secret'
                )
            ) {

                $method->webhook_secret =
                    trim(
                        $request->webhook_secret
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        |
        | PaymentMethod model encrypted casts will automatically encrypt:
        |
        | publishable_key
        | secret_key
        | webhook_secret
        |
        */
        $method->save();


        /*
        |--------------------------------------------------------------------------
        | Redirect to payment methods page
        |--------------------------------------------------------------------------
        */
        return redirect('payment-methods')
            ->with(
                'success',
                'Payment method updated successfully.'
            );
    }
}