<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecomputeCreatorTrees extends Command
{
    protected $signature = 'trees:recompute-creators';
    protected $description = 'Recompute trees_saved for users based on Delivered parcel orders created by them';

    public function handle()
    {
        $this->info('Resetting all users trees_saved to 0...');

        DB::table('app_users')->update(['trees_saved' => 0]);

        $this->info('Aggregating Delivered orders by creator...');

        $rows = DB::table('parcel_orders')
            ->select('user_id', DB::raw('SUM(trees_saved) AS total'))
            ->where('status', 'Delivered')
            ->whereNotNull('user_id')
            ->whereNotNull('trees_saved')
            ->where('trees_saved', '>', 0)
            ->groupBy('user_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('app_users')
                ->where('id', $row->user_id)
                ->update(['trees_saved' => $row->total]);
        }

        $this->info('Recompute complete.');
        return 0;
    }
}