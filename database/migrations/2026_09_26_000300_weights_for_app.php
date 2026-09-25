<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Weights become the app's quick-pick lists (kg): which ones the order
 * form offers and which ones the carbon calculator offers. Seeds the
 * values the app had hard-coded and drops the old ">1" / "<2" / ">4"
 * rows, which nothing reads. Idempotent (re-run with --path=).
 */
return new class extends Migration
{
    /** kg => [order form, carbon calculator], as the app shipped them. */
    private const DEFAULTS = [
        '0.5' => [1, 1], '1' => [1, 1], '2' => [1, 1], '3' => [0, 1], '5' => [1, 1],
        '7' => [0, 1], '10' => [1, 1], '15' => [0, 1], '20' => [1, 0], '23' => [0, 1],
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('weights', 'in_order_form')) {
            Schema::table('weights', function (Blueprint $table) {
                $table->boolean('in_order_form')->default(true)->after('value');
                $table->boolean('in_calculator')->default(true)->after('in_order_form');
            });
        }

        // the legacy text rows are not weights in kg
        DB::table('weights')->whereIn('value', ['>1', '<2', '>4'])->delete();

        if (DB::table('weights')->count() === 0) {
            $sort = 0;
            foreach (self::DEFAULTS as $kg => [$order, $calculator]) {
                DB::table('weights')->insert([
                    'value' => $kg,
                    'in_order_form' => $order,
                    'in_calculator' => $calculator,
                    'status' => 1,
                    'sort_no' => $sort++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('weights', 'in_order_form')) {
            Schema::table('weights', function (Blueprint $table) {
                $table->dropColumn(['in_order_form', 'in_calculator']);
            });
        }
    }
};
