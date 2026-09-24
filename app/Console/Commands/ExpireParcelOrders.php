<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ParcelOrder;
use Carbon\Carbon;

class ExpireParcelOrders extends Command
{
    protected $signature = 'parcel-orders:expire';
    protected $description = 'Set parcel orders status to Expired if they are Unassigned and past the expiration time based on order_date.';

    public function handle()
    {
        $expiredOrdersCount = ParcelOrder::where('status', 'Unassigned')
            ->whereNotNull('order_date')
            ->where('order_date', '<=', Carbon::now())
            ->update(['status' => 'Expired']);

        $this->info("{$expiredOrdersCount} parcel orders have been expired.");
    }
}