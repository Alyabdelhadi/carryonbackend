<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\ParcelCate;
use App\Models\ParcelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailTemplate;
use App\Models\NotificationTemplate;
use App\Models\Trip;
use App\Services\FirebaseService;
use App\Services\TemplateService;
use Carbon\Carbon;

class ParcelOrderController extends Controller
{
    public $folder  = "orders.";
    protected $firebaseService;
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
	
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
    {
        $res  = new ParcelOrder;
        $carrier = new AppUser;
    
        $perPage = request()->get('per_page', 50); // Default to 50
    
        return View($this->folder.'index', [
            'data'           => $res->getAllPaginated($perPage),
            'link'           => 'parcel/',
            'title'          => $this->getTitle(),
            'filter_status'  => request()->get('status'),
            'filter_q'       => request()->get('filter_q'),
            'filter_r'       => request()->get('filter_r'),
            'carrier'        => $carrier->getAllByRole(1),
        ]);
    }

	public function getTitle()
	{
	    $status = request()->get('status');
		if($status == "all")
		{
			$title = "All Orders";
		}
		elseif($status == 0)
		{
			$title = "Unassigned Orders"; 
		}
		elseif($status == 1)
		{
			$title = "Running Orders"; 
		}
		elseif($status == 2)
		{
			$title = "Delivered Orders"; 
		}
		elseif($status == 3)
		{
			$title = "Cancelled Orders"; 
		}
		elseif($status == 4)
		{
			$title = "Expired Orders"; 
		}

		return $title;
	}

	public function view()
	{
		$res = ParcelOrder::find($_GET['id']);

		return View($this->folder.'view',[

		'data' => $res,
		'cate' => ParcelCate::find($res->cate_id)

		]);
	}

	public function status()
	{
		$allowed = ['Unassigned', 'Assigned', 'Picked', 'Transit', 'Delivered', 'Cancelled', 'Expired'];
		$res 			= ParcelOrder::find($_GET['id'] ?? null);
		if (!$res || !in_array($_GET['status'] ?? null, $allowed, true)) {
			return Redirect::back()->with('error', 'Unknown order or status.');
		}
		$res->status 	= $_GET['status'];
		$res->save();

		$res->notify($res->id);

		return Redirect::back()->with('message','Status changed successfully');
	}
	
	public function delete($id)
	{
		ParcelOrder::where('id',$id)->delete();

		return Redirect::back()->with('message','Record Deleted Successfully.');
	}
	
	public function edit($id)
    {
        $order = ParcelOrder::findOrFail($id);
        $users = AppUser::all();
        $categories = ParcelCate::all();
        $carriers = AppUser::all();
    
        return view($this->folder . 'edit', [
            'order'      => $order,
            'users'      => $users,
            'categories' => $categories,
            'carriers' => $carriers,
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $order = ParcelOrder::findOrFail($id);
    
        $data = $request->all();
    
        $order->user_id           = $data['user_id'];
        $order->cate_id           = $data['cate_id'];
        $order->r_name            = $data['r_name'];
        $order->r_phone           = $data['r_phone'];
        $order->r_addressname     = $data['r_address']['name'];
        $order->r_city            = $data['r_address']['city'] ?? null;
        $order->r_country         = $data['r_address']['country'];
        $order->r_lat             = $data['r_address']['lat'];
        $order->r_lng             = $data['r_address']['lng'];
        $order->r_street          = $data['r_address']['street'];
        $order->r_building        = $data['r_address']['building'];
        $order->r_apartment       = $data['r_address']['apartment'];
        $order->s_name            = $data['s_name'];
        $order->s_phone           = $data['s_phone'];
        $order->s_addressname     = $data['s_address']['name'];
        $order->s_city            = $data['s_address']['city'] ?? null;
        $order->s_country         = $data['s_address']['country'];
        $order->s_lat             = $data['s_address']['lat'];
        $order->s_lng             = $data['s_address']['lng'];
        $order->s_street          = $data['s_address']['street'];
        $order->s_building        = $data['s_address']['building'];
        $order->s_apartment       = $data['s_address']['apartment'];
        $order->notes             = $data['notes'] ?? null;
        $order->value             = $data['value'] ?? null;
        $order->weight            = $data['weight'];
        $order->amount            = $data['amount'];
        $order->description       = $data['description'] ?? null;
        $order->carrier_id           = $data['carrier_id'];
    
        $excluded_dates = ['Flexible Date', 'Needed Soon'];
        $order->order_date = (isset($data['date_to']) && !in_array($data['date_to'], $excluded_dates)) ? $data['date_to'] : null;
    
        $order->status  = $data['status'];

        // Card orders not yet paid follow the edited reward.
        if ($order->isOnlinePayment() && !$order->isPaid()) {
            if (preg_match('/\d+(?:[.,]\d+)?/', (string) $order->amount, $m)) {
                $order->payment_amount = round((float) str_replace(',', '.', $m[0]), 2);
            }
            $order->applyCommission();
        }
        $order->save();
    
        return redirect()->route('parcel.edit', $order->id)->with('success', 'Package updated successfully!');
    }

    /** Admin: refund a paid card order (e.g. cancelled after pickup, or a retry after a Stripe outage). */
    public function refund($id)
    {
        $order = ParcelOrder::findOrFail($id);
        if (!$order->isOnlinePayment() || !in_array($order->payment_status, ['paid', 'refund_pending'], true)) {
            return back()->with('error', 'This package has no card payment to refund.');
        }
        if ($order->payment_status === 'refund_pending') {
            $order->payment_status = 'paid';
        }
        $result = $order->refundPayment();
        if ($result !== 'refunded') {
            return back()->with('error', 'Stripe refused the refund; check the Laravel log and try again.');
        }

        $template = NotificationTemplate::where('event', 'payment_refunded')->first();
        $title = $template ? TemplateService::parse($template->title, $order) : 'Refund Issued';
        $body = $template ? TemplateService::parse($template->body, $order) : 'Your payment for package #' . $order->id . ' has been refunded.';
        app(FirebaseService::class)->sendToUser($order->user_id, $title, $body);

        return back()->with('success', 'Refund sent to Stripe (' . $order->refund_reference . ').');
    }
    
    public function notify($id)
    {
        // Find the parcel order
        $parcelOrder = ParcelOrder::find($id);
    
        if (!$parcelOrder) {
            return Redirect::back()->with('error', 'Package not found.');
        }
    
        if ($parcelOrder->status !== 'Unassigned') {
            return Redirect::back()->with('error', 'Only Unassigned packages can be notified.');
        }
    
        // Load templates
        $templates = NotificationTemplate::where('event', 'matching_package')
            ->get()
            ->keyBy('event');
    
        // Find matching trips
        $today = Carbon::today()->toDateString();

        $matchingTrips = Trip::whereHas('cityFrom.country', function ($q) use ($parcelOrder) {
                $q->where('name', $parcelOrder->s_country);
            })
            ->whereHas('cityTo.country', function ($q) use ($parcelOrder) {
                $q->where('name', $parcelOrder->r_country);
            })
            ->where(function ($query) use ($parcelOrder, $today) {
                $query
                    // Recurring trips are always valid for this route
                    ->where('frequency', '!=', 'one_time')
                    ->orWhere(function ($q) use ($parcelOrder, $today) {
                        $q->where('frequency', 'one_time')
                          ->where(function ($sub) use ($parcelOrder, $today) {
                              // 1) Flexible one-time trips without a fixed date
                              $sub->whereNull('date');
        
                              // 2) One-time trips with a date:
                              //    - must NOT be in the past
                              //    - must be on/before the package "needed before" date (if provided)
                              if (!empty($parcelOrder->order_date)) {
                                  $neededBefore = Carbon::parse($parcelOrder->order_date)->toDateString();
        
                                  $sub->orWhereBetween('date', [$today, $neededBefore]);
                              } else {
                                  // No needed-before date: just require future (or today)
                                  $sub->orWhereDate('date', '>=', $today);
                              }
                          });
                    });
            })
            ->get(['carrier_id']);
        
        if ($matchingTrips->isEmpty()) {
            return Redirect::back()->with('error', 'No matching trips found for this package.');
        }
    
        // Prepare title/body
        $carrierTitle = $templates->get('matching_package')
            ? TemplateService::parse($templates['matching_package']->title, $parcelOrder)
            : "New Matching Package";
    
        $carrierBody = $templates->get('matching_package')
            ? TemplateService::parse($templates['matching_package']->body, $parcelOrder)
            : "A package matches your trip route.";
    
        // Send notification to each carrier
        foreach ($matchingTrips as $trip) {
            if (!empty($trip->carrier_id)) {
                $this->firebaseService->sendToUser($trip->carrier_id, $carrierTitle, $carrierBody);
            }
        }
    
        return Redirect::back()->with('message', 'Notified Successfully.');
    }
    
}
