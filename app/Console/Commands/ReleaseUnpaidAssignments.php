<?php

namespace App\Console\Commands;

use App\Models\NotificationTemplate;
use App\Models\ParcelOrder;
use App\Services\FirebaseService;
use App\Services\TemplateService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Card-paid orders a carrier accepted but the sender never paid for go back
 * to Unassigned once the payment deadline passes (hourly, see Kernel).
 */
class ReleaseUnpaidAssignments extends Command
{
    protected $signature = 'orders:release-unpaid';
    protected $description = 'Return Assigned card-paid orders to Unassigned when the sender missed the payment deadline.';

    public function handle(FirebaseService $firebase): int
    {
        $orders = ParcelOrder::where('status', 'Assigned')
            ->where('payment_method', 'stripe')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<=', Carbon::now())
            ->get();

        $template = NotificationTemplate::where('event', 'assignment_released')->first();

        foreach ($orders as $order) {
            $carrierId = $order->carrier_id;
            $order->status = 'Unassigned';
            $order->carrier_id = null;
            $order->payment_deadline_at = null;
            $order->save();

            $title = $template ? TemplateService::parse($template->title, $order) : 'Package Reopened';
            $body = $template
                ? TemplateService::parse($template->body, $order)
                : "Package #{$order->id} was not paid in time and is open to other travelers again.";
            $firebase->sendToUser($order->user_id, $title, $body);
            if ($carrierId) {
                $firebase->sendToUser($carrierId, $title, $body);
            }
        }

        $this->info($orders->count() . ' unpaid assignments released.');
        return self::SUCCESS;
    }
}
