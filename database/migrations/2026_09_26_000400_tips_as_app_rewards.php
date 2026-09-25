<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tips become the reward chips of the app's order form (amounts in the
 * payment currency, 0 = "Free"). Replaces the old "No Tip" / 5 / 10 / 15
 * rows with the values the app had hard-coded, once. Re-run safe.
 */
return new class extends Migration
{
    private const DEFAULTS = ['0', '10', '20', '50', '100', '150', '200'];

    public function up(): void
    {
        $legacy = DB::table('tips')->pluck('value')->map(fn ($v) => (string) $v)->sort()->values()->all();
        $old = ['10', '15', '5', 'No Tip'];
        sort($old);
        if ($legacy === $old || $legacy === []) {
            DB::table('tips')->delete();
            foreach (self::DEFAULTS as $i => $amount) {
                DB::table('tips')->insert([
                    'value' => $amount,
                    'status' => 1,
                    'sort_no' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
    }
};
