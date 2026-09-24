<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Tip;
use App\Models\Page;
use App\Models\Text;
use App\Models\Rating;
use App\Models\Slider;
use App\Models\Slider2;
use App\Models\Weight;
use App\Models\Address;
use App\Models\AppUser;
use App\Models\Service;
use App\Models\ParcelCate;
use App\Models\ParcelOrder;
use App\Models\NotificationTemplate;
use App\Models\Country;
use App\Models\City;
use App\Models\Trip;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailTemplate;
use App\Services\FirebaseService;
use App\Services\TemplateService;
use App\Models\User;
use App\Models\ParcelOrderView;
use App\Models\PaymentMethod;
use App\Services\StripePaymentService;

class ApiController extends Controller
{
    protected $firebaseService;
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    
    /**
     * AppUser APIs
     */
    
    public function signup(Request $Request)
    {
        $res = new AppUser;
        $response = response()->json($res->signup($Request->all())); 
    
        // Extract the data from the response
        $responseData = $response->getData(true);
    
        // If signup was successful and returns user
        if (isset($responseData['user'])) {
            $user = $responseData['user']; // assuming the user is returned as array or object
    
            // Get the 'welcome' email template
            $emailTemplate = EmailTemplate::where('event', 'welcome')->first();
    
            if ($emailTemplate) {
                // Parse the template
                $parsedBody = TemplateService::parseWithUserId($emailTemplate->body, $user['id'] ?? $user->id);
                $parsedSubject = TemplateService::parseWithUserId($emailTemplate->title, $user['id'] ?? $user->id);
    
                // Send email
                Mail::raw($parsedBody, function ($message) use ($user, $parsedSubject) {
                    $message->to($user['email'] ?? $user->email)
                            ->subject($parsedSubject);
                });
            }
        }
    
        return $response;
    }
    public function login(Request $Request)
	{
		$res  = new AppUser;
		
		return response()->json($res->login($Request->all()));
	}
    public function verify(Request $Request)
	{
		$res  = new AppUser;
		
		return response()->json($res->verify($Request->all()));
	}
	public function submitVerification(Request $Request)
	{
		$res  = new AppUser;
		
		return response()->json($res->submitVerification($Request->all()));
	}
	public function sendPasswordResetLink(Request $Request)
    {
        $res = new \App\Models\AppUser;
    
        return response()->json($res->sendPasswordResetLink($Request->all()));
    }
    
    public function resetPassword(Request $Request)
    {
        $res = new \App\Models\AppUser;
    
        return response()->json($res->resetPassword($Request->all()));
    }
    public function userInfo(Request $Request)
	{
		$res = new AppUser;

		return response()->json($res->userInfo($Request->all())); 
		
	}
    public function updateInfo(Request $Request)
	{
		$res = new AppUser;

		return response()->json($res->updateInfo($Request->all())); 
		
	}
	
	public function deleteUser(Request $Request)
	{
		$res = new AppUser();

		$response = response()->json($res->deleteUser($Request->all())); 
    
        // Extract the data from the response
        $responseData = $response->getData(true);
    
        // If signup was successful and returns user
        if (isset($responseData['user'])) {
            $user = $responseData['user']; // assuming the user is returned as array or object
    
            // Get the 'user_deleted' email template
            $emailTemplate = EmailTemplate::where('event', 'user_deleted')->first();
    
            if ($emailTemplate) {
                // Parse the template
                $parsedBody = TemplateService::parseWithUserId($emailTemplate->body, $user['id'] ?? $user->id);
                $parsedSubject = TemplateService::parseWithUserId($emailTemplate->title, $user['id'] ?? $user->id);
    
                // Send email
                Mail::raw($parsedBody, function ($message) use ($user, $parsedSubject) {
                    $message->to($user['email'] ?? $user->email)
                            ->subject($parsedSubject);
                });
            }
        }
    
        return $response;
	}


	/**
     * ParcelOrder APIs
     */

    public function createParcelOrder(Request $request)
    {
        // $request->validate([
        //     'payment_amount' =>
        //         'required|numeric|min:0.01',
        // ]);
        
        $paymentMethod = PaymentMethod::where('id', $request->payment_method)
        ->where('enabled', true)
        ->first();

        if (!$paymentMethod) {
            return response()->json([
                'message' => 'Selected payment method is not available.'
            ], 422);
        }
        
        $res = new ParcelOrder();
        
        $data = $request->all();
        
        $data['payment_currency'] = strtoupper($paymentMethod->currency);
        
        /*
         * Extract numeric value from amount.
         *
         * Examples:
         * "30"          => 30
         * "30$"         => 30
         * "20 euros"    => 20
         * "₹500"        => 500
         * "150 euros..." => 150
         * "Free"        => 0
         * "Other"       => 0
         */
        $amount = trim((string) ($data['amount'] ?? ''));
        
        if (preg_match('/\d+(?:[.,]\d+)?/', $amount, $matches)) {
            $data['payment_amount'] = round(
                (float) str_replace(',', '.', $matches[0]),
                2
            );
        } else {
            $data['payment_amount'] = 0;
        }
        
        $responseData = $res->create($data);
    
        if (!isset($responseData['order'])) {
            return response()->json(['error' => 'Order not created'], 400);
        }
    
        $parcelOrder = ParcelOrder::find($responseData['order']['id']);
    
        // Load notification templates
        $templates = NotificationTemplate::whereIn('event', ['package_created', 'matching_package'])->get()->keyBy('event');
    
        // Notify user
        $userTitle = $templates->get('package_created') 
            ? TemplateService::parse($templates['package_created']->title, $parcelOrder) 
            : "Package Created";
    
        $userBody = $templates->get('package_created') 
            ? TemplateService::parse($templates['package_created']->body, $parcelOrder) 
            : "Your package has been created.";
    
        $this->firebaseService->sendToUser($parcelOrder->user_id, $userTitle, $userBody);
    
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
            ->get();
    
    
        foreach ($matchingTrips as $trip) {
            $carrierId = $trip->carrier_id;
    
            $carrierTitle = $templates->get('matching_package')
                ? TemplateService::parse($templates['matching_package']->title, $parcelOrder)
                : "New Matching Package";
    
            $carrierBody = $templates->get('matching_package')
                ? TemplateService::parse($templates['matching_package']->body, $parcelOrder)
                : "A new package matches your trip route.";
    
            $this->firebaseService->sendToUser($carrierId, $carrierTitle, $carrierBody);
        }
    
        return response()->json($responseData);
    }
	
	public function assignParcelOrder(Request $Request)
	{
		
		$res = new ParcelOrder();
        $response = response()->json($res->assign($Request->all())); 
    
        // Extract the data from the response
        $responseData = $response->getData(true); // Convert JsonResponse to an array
    
        if (isset($responseData['order'])) {
            $parcelOrder = ParcelOrder::find($responseData['order']['id']);
    
            // Get the notification template for package_created
            $template = NotificationTemplate::where('event', 'package_assigned')->first();
    
            if ($template) {
                $title = TemplateService::parse($template->title, $parcelOrder);
                $body = TemplateService::parse($template->body, $parcelOrder);
            } else {
                // Fallback values in case no template is found
                $title = "Package Assigned";
                $body = "Your package has been assigned.";
            }
    
            // Send notification using Firebase service
            $this->firebaseService->sendToUser($parcelOrder['user_id'], $title, $body);
        }
    
        return $response;
		
	}
	
	public function unassignParcelOrder(Request $request)
	{
		
		$res = new ParcelOrder();
        $responseData = $res->unassign($request->all());
    
        if (!isset($responseData['order'])) {
            return response()->json(['error' => 'Failed to unassign package'], 400);
        }
    
        $parcelOrder = ParcelOrder::find($responseData['order']['id']);
    
        // Load notification templates
        $templates = NotificationTemplate::whereIn('event', ['package_dropped', 'matching_package'])->get()->keyBy('event');
    
        // Notify user
        $userTitle = $templates->get('package_dropped') 
            ? TemplateService::parse($templates['package_dropped']->title, $parcelOrder) 
            : "Package Dropped";
    
        $userBody = $templates->get('package_dropped') 
            ? TemplateService::parse($templates['package_dropped']->body, $parcelOrder) 
            : "Your package has been dropped.";
    
        $this->firebaseService->sendToUser($parcelOrder->user_id, $userTitle, $userBody);
    
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
            ->get();
    
    
        foreach ($matchingTrips as $trip) {
            $carrierId = $trip->carrier_id;
    
            $carrierTitle = $templates->get('matching_package')
                ? TemplateService::parse($templates['matching_package']->title, $parcelOrder)
                : "New Matching Package";
    
            $carrierBody = $templates->get('matching_package')
                ? TemplateService::parse($templates['matching_package']->body, $parcelOrder)
                : "A new package matches your trip route.";
    
            $this->firebaseService->sendToUser($carrierId, $carrierTitle, $carrierBody);
        }
    
        return response()->json($responseData);
		
	}
	
	public function extendParcelOrder(Request $request)
	{
		
		$res = new ParcelOrder();
        $responseData = $res->extendDate($request->all());
    
        if (!isset($responseData['order'])) {
            return response()->json(['error' => 'Order not extended'], 400);
        }
    
        $parcelOrder = ParcelOrder::find($responseData['order']['id']);
    
        // Load notification templates
        $templates = NotificationTemplate::whereIn('event', ['package_extended', 'matching_package'])->get()->keyBy('event');
    
        // Notify user
        $userTitle = $templates->get('package_extended') 
            ? TemplateService::parse($templates['package_extended']->title, $parcelOrder) 
            : "Package Extended";
    
        $userBody = $templates->get('package_extended') 
            ? TemplateService::parse($templates['package_extended']->body, $parcelOrder) 
            : "Your package has been extend.";
    
        $this->firebaseService->sendToUser($parcelOrder->user_id, $userTitle, $userBody);
    
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
            ->get();
    
    
        foreach ($matchingTrips as $trip) {
            $carrierId = $trip->carrier_id;
    
            $carrierTitle = $templates->get('matching_package')
                ? TemplateService::parse($templates['matching_package']->title, $parcelOrder)
                : "New Matching Package";
    
            $carrierBody = $templates->get('matching_package')
                ? TemplateService::parse($templates['matching_package']->body, $parcelOrder)
                : "A new package matches your trip route.";
    
            $this->firebaseService->sendToUser($carrierId, $carrierTitle, $carrierBody);
        }
    
        return response()->json($responseData);
		
	}
	
	
	
	public function pickupParcelOrder(Request $Request)
	{
		
		$res = new ParcelOrder();
        $response = response()->json($res->pickup($Request->all())); 
    
        // Extract the data from the response
        $responseData = $response->getData(true); // Convert JsonResponse to an array
    
        if (isset($responseData['order'])) {
            $parcelOrder = ParcelOrder::find($responseData['order']['id']);
    
            // Get the notification template for package_created
            $template = NotificationTemplate::where('event', 'package_pickup')->first();
    
            if ($template) {
                $title = TemplateService::parse($template->title, $parcelOrder);
                $body = TemplateService::parse($template->body, $parcelOrder);
            } else {
                // Fallback values in case no template is found
                $title = "Package Pickup";
                $body = "Your package has been picked up.";
            }
    
            // Send notification using Firebase service
            $this->firebaseService->sendToUser($parcelOrder['user_id'], $title, $body);
        }
    
        return $response;
		
	}
	
	public function deliverParcelOrder(Request $Request)
	{
		$res = new ParcelOrder();
        $response = response()->json($res->deliver($Request->all())); 
    
        // Extract the data from the response
        $responseData = $response->getData(true); // Convert JsonResponse to an array
    
        if (isset($responseData['order'])) {
            $parcelOrder = ParcelOrder::find($responseData['order']['id']);
    
            // Get the notification template for package_delivered
            $template = NotificationTemplate::where('event', 'package_delivered')->first();
    
            if ($template) {
                $title = TemplateService::parse($template->title, $parcelOrder);
                $body = TemplateService::parse($template->body, $parcelOrder);
            } else {
                // Fallback values in case no template is found
                $title = "Package Delivered";
                $body = "Your package has been delivered.";
            }
    
            // Send notification using Firebase service
            $this->firebaseService->sendToUser($parcelOrder['user_id'], $title, $body);
        }
    
        return $response;
	}
	
	
	public function transitParcelOrder(Request $Request)
	{
		$res = new ParcelOrder();
        $response = response()->json($res->transit($Request->all())); 
    
        // Extract the data from the response
        $responseData = $response->getData(true); // Convert JsonResponse to an array
    
        if (isset($responseData['order'])) {
            $parcelOrder = ParcelOrder::find($responseData['order']['id']);
    
            // Get the notification template for package_in_transit
            $template = NotificationTemplate::where('event', 'package_in_transit')->first();
    
            if ($template) {
                $title = TemplateService::parse($template->title, $parcelOrder);
                $body = TemplateService::parse($template->body, $parcelOrder);
            } else {
                // Fallback values in case no template is found
                $title = "Package In Transit";
                $body = "Your package is in transit.";
            }
    
            // Send notification using Firebase service
            $this->firebaseService->sendToUser($parcelOrder['user_id'], $title, $body);
        }
    
        return $response;
	}


    public function cancelParcelOrder(Request $Request)
    {
        $res = new ParcelOrder();
        $response = response()->json($res->cancel($Request->all())); 
    
        // Extract the data from the response
        $responseData = $response->getData(true); // Convert JsonResponse to an array
    
        if (isset($responseData['order'])) {
            $parcelOrder = ParcelOrder::find($responseData['order']['id']);
    
            // Get the notification template for package_cancelled
            $template = NotificationTemplate::where('event', 'package_cancelled')->first();
    
            if ($template) {
                $title = TemplateService::parse($template->title, $parcelOrder);
                $body = TemplateService::parse($template->body, $parcelOrder);
            } else {
                // Fallback values in case no template is found
                $title = "Package Cancelled";
                $body = "Your package has been cancelled.";
            }
    
            // Send notification using Firebase service
            $this->firebaseService->sendToUser($parcelOrder['user_id'], $title, $body);
        }
    
        return $response;
    }
	
	public function getParcelOrderById(Request $Request)
	{
		$res = new ParcelOrder;

		return response()->json($res->getParcelOrderById($Request->all()));
	}
	
	public function getUnassginedParcelOrders(Request $Request)
	{
		$res = new ParcelOrder;

		return response()->json($res->getAllUnassigned($Request->all()));
	}
	
	public function getMyCreatedParcelOrders(Request $Request)
	{
		$res = new ParcelOrder;

		return response()->json($res->getMyCreated($Request->all()));
	}
	
	public function getMyCarriedParcelOrders(Request $Request)
	{
		$res = new ParcelOrder;

		return response()->json($res->getMyCarried($Request->all()));
	}
	
	/**
     * App Versions APIs
     */
    public function getAppVersions()
    {
        $res = new Version;
    
        $data = $res->first();
    
        return response()->json($data);
    }


	/**
     * Slider APIs
     */
	public function getSliders()
	{
		$res = new Slider;

		return response()->json($res->getAll("1"));
	}
	
	/**
     * Slider2 APIs
     */
	public function getSliders2()
	{
		$res = new Slider2;

		return response()->json($res->getAll("1"));
	}


	/**
     * Tip APIs
     */
	public function getTips()
	{
		$res = new Tip;

		return response()->json($res->getAll("1"));
	}


	/**
     * Weight APIs
     */
	public function getWeights()
	{
		$res = new Weight;

		return response()->json($res->getAll("1"));
	}


	/**
     * Page APIs
     */
	public function getPages()
	{
		$res = new Page;

		return response()->json($res->getAll("1"));
	}
	public function getPageById(Request $Request)
	{
		$res = new Page;

		return response()->json($res->getPageById($Request->all()));
	}
	


	/**
     * Text APIs
     */
	public function getTexts()
	{
		$res = new Text;

		return response()->json($res->getAppData());
	}


	/**
     * Service APIs
     */
	public function getServices()
	{
		$res = new Service;

		return response()->json($res->getAll("1"));
	}



	/**
     * ParcelCate APIs
     */
	public function getParcelCategories()
	{
		$res = new ParcelCate;

		return response()->json($res->getAll("1"));
	}
	
	

	/**
     * Rating APIs
     */
	public function rate(Request $Request)
	{
		$res = new Rating;

		return response()->json($res->rate($Request->all())); 
	}


	/**
     * Address APIs
     */
	public function getAddresses(Request $Request)
	{
		$res = new Address();

		return response()->json($res->getAddresses($Request->all()));
	}
	public function updateAddress(Request $Request)
	{
		$res = new Address();

		return response()->json($res->updateAddress($Request->all()));
	}
	public function deleteAddress(Request $Request)
	{
		$res = new Address();

		return response()->json($res->deleteAddress($Request->all()));
	}
	public function createAddress(Request $Request)
	{
		$res = new Address();

		return response()->json($res->createAddress($Request->all()));
	}
	
	
	
	
	/**
     * Country APIs
     */
	public function getCountries()
	{
		$res = new Country;

		return response()->json($res->getAll("1"));
	}
	
	
	/**
     * City APIs
     */
	public function getCitiesByCountry($countryId)
    {
        $city = new \App\Models\City;
    
        return response()->json($city->getCitiesByCountryId($countryId, "1"));
    }
    
    
    /**
     * Trip APIs
    */

    // Get All Trips
    public function getTrips()
    {
        $trip = new \App\Models\Trip;

        return response()->json($trip->getAllTrips());
    }

    // Get Trip By ID
    public function getTrip($id)
    {
        $trip = new \App\Models\Trip;

        return response()->json($trip->getTripById($id));
    }

    // Get Trips by Carrier
    public function getTripsByCarrier($carrierId)
    {
        $trip = new \App\Models\Trip;

        return response()->json($trip->getTripsByCarrier($carrierId));
    }

    // Create Trip
    public function createTrip(Request $request)
    {
        $trip = new \App\Models\Trip;

        $data = $request->all();

        return response()->json($trip->createTrip($data));
    }

    // Update Trip
    public function updateTrip(Request $request, $id)
    {
        $trip = new \App\Models\Trip;

        $data = $request->all();

        return response()->json($trip->updateTrip($id, $data));
    }

    // Delete Trip
    public function deleteTrip($id)
    {
        $trip = new \App\Models\Trip;

        return response()->json([
            'success' => $trip->deleteTrip($id)
        ]);
    }
    
    // Get matching parcel orders
    // public function getMatchingParcelOrdersByCarrier($carrierId)
    // {
    //     $trips = Trip::where('carrier_id', $carrierId)->get();
    
    //     if ($trips->isEmpty()) {
    //         return response()->json(['error' => 'No trips found for this carrier'], 404);
    //     }
    
    //     $allOrders = collect();
    
    //     foreach ($trips as $trip) {
    //         $orders = $trip->getMatchingParcelOrders();
    //         $allOrders = $allOrders->merge($orders);
    //     }
    
    //     return response()->json($allOrders->unique('id')->values()); // unique by order id to avoid duplicates
    // }
    
    public function getMatchingParcelOrdersByCarrier($carrierId)
    {
        $orders = Trip::matchingParcelOrdersForCarrier((int)$carrierId);

        if ($orders->isEmpty()) {
            return response()->json(['error' => 'No trips found for this carrier'], 404);
        }

        return response()->json($orders);
    }
    
    public function markMatchingParcelOrdersAsRead(Request $request, $carrierId)
    {
        $carrierId = (int) $carrierId;
        $orderId   = $request->input('order_id'); // must be a single value
    
        if (!$orderId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'order_id is required'
            ], 422);
        }
    
        // Check if this order belongs to matching list for that carrier
        $order = Trip::matchingParcelOrdersForCarrier($carrierId)
            ->where('id', $orderId)
            ->first();
    
        if (!$order) {
            return response()->json([
                'status'  => 'ok',
                'message' => 'Order not found or not matching for carrier'
            ], 200);
        }
    
        $now = Carbon::now();
    
        DB::table('parcel_order_views')->upsert(
            [[
                'parcel_order_id' => (int)$order->id,
                'carrier_id'      => $carrierId,
                'read_at'         => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]],
            ['parcel_order_id', 'carrier_id'],
            ['read_at', 'updated_at']
        );
    
        return response()->json([
            'status'       => 'ok',
            'marked_count' => 1
        ], 200);
    }
    
    
    
    public function getAnalytics(Request $Request)
	{
		$res = new User;

		return response()->json($res->overview($Request->all()));
	}
	
	
	
	public function getPaymentMethods()
    {
        $methods = PaymentMethod::where(
                'enabled',
                true
            )
            ->get()
            ->map(function ($method) {
    
                return [
                    'id' =>
                        $method->id,
    
                    'name' =>
                        $method->name,
    
                    'code' =>
                        $method->code,
    
                    'currency' =>
                        strtoupper(
                            $method->currency
                        ),
    
                    'publishable_key' =>
                        $method->code === 'stripe'
                            ? $method->publishable_key
                            : null,
                ];
            });
    
        return response()->json([
            'payment_methods' =>
                $methods
        ]);
    }
    
    
    public function createStripePayment(
            Request $request,
            StripePaymentService $stripeService
        ) {
            $request->validate([
                'order_id' =>
                    'required|integer',
        
                'user_id' =>
                    'required|integer',
            ]);
        
            $order = ParcelOrder::where(
                    'id',
                    $request->order_id
                )
                ->where(
                    'user_id',
                    $request->user_id
                )
                ->first();
        
            if (!$order) {
                return response()->json([
                    'message' =>
                        'Order not found.'
                ], 404);
            }
        
        
            if (
                $order->payment_method !== 'stripe'
            ) {
                return response()->json([
                    'message' =>
                        'This order is not configured for Stripe payment.'
                ], 422);
            }
        
        
            if (
                $order->payment_status === 'paid'
            ) {
                return response()->json([
                    'message' =>
                        'Order is already paid.'
                ], 422);
            }
        
        
            if (
                $order->payment_status === 'processing'
                &&
                !empty($order->payment_reference)
            ) {
                return response()->json([
                    'message' =>
                        'A payment is already in progress for this order.'
                ], 422);
            }
        
        
            if (
                empty($order->payment_amount)
                ||
                (float) $order->payment_amount <= 0
            ) {
                return response()->json([
                    'message' =>
                        'Invalid payment amount.'
                ], 422);
            }
        
        
            $stripeMethod =
                PaymentMethod::where(
                    'code',
                    'stripe'
                )
                ->where(
                    'enabled',
                    true
                )
                ->first();
        
            if (!$stripeMethod) {
                return response()->json([
                    'message' =>
                        'Stripe payment is currently unavailable.'
                ], 422);
            }
        
        
            try {
        
                $paymentIntent =
                    $stripeService
                        ->createPaymentIntent(
                            $order
                        );
        
        
                $order->payment_reference =
                    $paymentIntent->id;
        
                $order->payment_status =
                    'processing';
        
                $order->save();
        
        
                return response()->json([
                    'message' =>
                        'Payment created successfully.',
        
                    'client_secret' =>
                        $paymentIntent->client_secret,
        
                    'payment_intent_id' =>
                        $paymentIntent->id,
        
                    'publishable_key' =>
                        $stripeMethod->publishable_key,
        
                    'payment_amount' =>
                        $order->payment_amount,
        
                    'payment_currency' =>
                        $order->payment_currency,
                ]);
        
            } catch (\Throwable $e) {
        
                Log::error(
                    'Stripe PaymentIntent creation failed',
                    [
                        'order_id' =>
                            $order->id,
        
                        'error' =>
                            $e->getMessage(),
                    ]
                );
        
        
                return response()->json([
                    'message' =>
                        'Unable to create Stripe payment.'
                ], 500);
            }
        }
    
}
