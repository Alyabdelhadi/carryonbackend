<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Carrier wallet + payout requests for online (Stripe) orders.
 * Idempotent so it can be re-run on the dump-based DB with --path=.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('app_user_id')->index();
                // earning | payout | payout_reversal | refund | adjustment
                $table->string('type', 30)->index();
                // signed: earnings positive, payouts negative
                $table->decimal('amount', 10, 2);
                $table->char('currency', 3);
                $table->unsignedBigInteger('parcel_order_id')->nullable()->index();
                $table->unsignedBigInteger('payout_request_id')->nullable()->index();
                // earnings are withdrawable once this passes (payout hold)
                $table->timestamp('available_at')->nullable();
                $table->decimal('balance_after', 10, 2);
                $table->string('note', 500)->nullable();
                $table->unsignedBigInteger('created_by')->nullable(); // admin users.id for adjustments
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payout_requests')) {
            Schema::create('payout_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('app_user_id')->index();
                $table->decimal('amount', 10, 2);
                $table->char('currency', 3);
                $table->string('method', 30);           // bank_transfer | omt | whish | paypal
                $table->json('details')->nullable();    // IBAN, account name, phone, email...
                // pending | paid | rejected | cancelled
                $table->string('status', 20)->default('pending')->index();
                $table->string('reference')->nullable();   // bank / OMT reference typed by the admin
                $table->string('admin_note', 500)->nullable();
                $table->unsignedBigInteger('processed_by')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('app_users', 'wallet_balance')) {
            Schema::table('app_users', function (Blueprint $table) {
                $table->decimal('wallet_balance', 10, 2)->default(0)->after('wallet');
                $table->char('wallet_currency', 3)->nullable()->after('wallet_balance');
            });
        }

        if (!Schema::hasColumn('parcel_orders', 'commission_amount')) {
            Schema::table('parcel_orders', function (Blueprint $table) {
                $table->decimal('commission_amount', 10, 2)->nullable()->after('payment_currency');
                $table->decimal('carrier_earning', 10, 2)->nullable()->after('commission_amount');
                $table->timestamp('payment_deadline_at')->nullable()->after('paid_at');
                $table->string('refund_reference')->nullable()->after('payment_deadline_at');
                $table->timestamp('refunded_at')->nullable()->after('refund_reference');
            });
        }

        // Cash orders never had a meaningful payment_status; label them.
        DB::table('parcel_orders')
            ->where(function ($q) {
                $q->where('payment_method', '1')->orWhere('payment_method', 'cash_on_delivery');
            })
            ->update(['payment_method' => 'cash_on_delivery', 'payment_status' => 'cash']);
        DB::table('parcel_orders')->where('payment_method', '2')->update(['payment_method' => 'stripe']);

        $now = now();
        foreach ([
            'commission_percent' => '15',
            'payout_minimum' => '20',
            'payout_hold_days' => '3',
            'payment_deadline_hours' => '24',
            'payout_methods' => "bank_transfer|Bank transfer\nomt|OMT\nwhish|Whish Money\npaypal|PayPal",
        ] as $key => $value) {
            if (!DB::table('app_settings')->where('key', $key)->exists()) {
                DB::table('app_settings')->insert([
                    'key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        foreach ([
            ['payment_required', 'Payment Required 💳', ':carrierfirstname accepted your :packagetype package. Pay now to confirm the delivery.'],
            ['payment_received', 'Payment Received ✅', 'Payment for the :packagetype package #:packagenumber is confirmed. You can pick it up.'],
            ['payment_receipt', 'Payment Successful 💳', 'Thanks :userfirstname! Your payment of :amount for package #:packagenumber was received.'],
            ['payment_failed', 'Payment Failed ❌', 'Your payment for package #:packagenumber did not go through. Please try again.'],
            ['assignment_released', 'Package Reopened 📦', 'Package #:packagenumber was not paid in time and is open to other travelers again.'],
            ['payment_refunded', 'Refund Issued 💸', 'Your payment of :amount for package #:packagenumber has been refunded.'],
            ['earning_credited', 'Earning Added 💰', 'Great job :carrierfirstname! :amount was added to your wallet for package #:packagenumber.'],
            ['payout_paid', 'Payout Sent 🏦', 'Your payout of :amount has been sent.'],
            ['payout_rejected', 'Payout Rejected ❌', 'Your payout request of :amount was rejected and returned to your wallet.'],
        ] as [$event, $title, $body]) {
            if (!DB::table('notification_templates')->where('event', $event)->exists()) {
                DB::table('notification_templates')->insert([
                    'event' => $event, 'title' => $title, 'body' => $body,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('wallet_transactions');
        if (Schema::hasColumn('app_users', 'wallet_balance')) {
            Schema::table('app_users', fn (Blueprint $t) => $t->dropColumn(['wallet_balance', 'wallet_currency']));
        }
        if (Schema::hasColumn('parcel_orders', 'commission_amount')) {
            Schema::table('parcel_orders', fn (Blueprint $t) => $t->dropColumn([
                'commission_amount', 'carrier_earning', 'payment_deadline_at', 'refund_reference', 'refunded_at',
            ]));
        }
    }
};
