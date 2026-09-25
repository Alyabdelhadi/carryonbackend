<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\AppSetting;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Exception;

class ParcelOrder extends Model
{
    /** Arabic place names for the app's Arabic mode (null when unknown). */
    protected $appends = ['s_city_ar', 'r_city_ar', 's_country_ar', 'r_country_ar'];

    public function getSCityArAttribute(): ?string
    {
        return \App\Services\PlaceNameService::city($this->attributes['s_city'] ?? null, $this->attributes['s_country'] ?? null);
    }

    public function getRCityArAttribute(): ?string
    {
        return \App\Services\PlaceNameService::city($this->attributes['r_city'] ?? null, $this->attributes['r_country'] ?? null);
    }

    public function getSCountryArAttribute(): ?string
    {
        return \App\Services\PlaceNameService::country($this->attributes['s_country'] ?? null);
    }

    public function getRCountryArAttribute(): ?string
    {
        return \App\Services\PlaceNameService::country($this->attributes['r_country'] ?? null);
    }

    use HasFactory;

    public function create($data)
    {
        try {
            $parcelOrder = new ParcelOrder;
    
            // === Base Fields ===
            $parcelOrder->user_id        = $data['user_id'];
            $parcelOrder->cate_id        = $data['cate_id'] ?? 4;
            $parcelOrder->r_name         = $data['r_name'];
            $parcelOrder->r_phone        = $data['r_phone'];
            $parcelOrder->r_addressname  = $data['r_address']['name'];
            $parcelOrder->r_city         = $data['r_address']['city'] ?? null;
            $parcelOrder->r_country      = $data['r_address']['country'];
            $parcelOrder->r_lat          = $data['r_address']['lat'];
            $parcelOrder->r_lng          = $data['r_address']['lng'];
            $parcelOrder->r_street       = $data['r_address']['street'];
            $parcelOrder->r_building     = $data['r_address']['building'];
            $parcelOrder->r_apartment    = $data['r_address']['apartment'];
            $parcelOrder->r_notes        = $data['r_address']['notes'] ?? null;
    
            $parcelOrder->s_name         = $data['s_name'];
            $parcelOrder->s_phone        = $data['s_phone'];
            $parcelOrder->s_addressname  = $data['s_address']['name'];
            $parcelOrder->s_city         = $data['s_address']['city'] ?? null;
            $parcelOrder->s_country      = $data['s_address']['country'];
            $parcelOrder->s_lat          = $data['s_address']['lat'];
            $parcelOrder->s_lng          = $data['s_address']['lng'];
            $parcelOrder->s_street       = $data['s_address']['street'];
            $parcelOrder->s_building     = $data['s_address']['building'];
            $parcelOrder->s_apartment    = $data['s_address']['apartment'];
            $parcelOrder->s_notes        = $data['s_address']['notes'] ?? null;
    
            $parcelOrder->payment_method = self::paymentMethodCode($data['payment_method'] ?? null);
            
            $parcelOrder->payment_amount = round((float) ($data['payment_amount'] ?? 0), 2);
            
            $parcelOrder->payment_currency = strtoupper($data['payment_currency'] ?? 'USD');
            
            // Card orders are paid once a carrier accepts; cash never goes through us.
            $parcelOrder->payment_status = $parcelOrder->payment_method === 'stripe' ? 'unpaid' : 'cash';
            
            $parcelOrder->payment_reference = null;
            
            $parcelOrder->paid_at = null;

            $parcelOrder->applyCommission();
            
            $parcelOrder->notes = $data['notes'] ?? null;
            
            $parcelOrder->value = $data['value'] ?? null;
            
            $parcelOrder->weight = $data['weight'];
            
            $parcelOrder->amount =$data['amount'];
            
            $parcelOrder->description    = $data['description'] ?? null;
    
            $excluded_dates = ['Flexible Date', 'Needed Soon'];
            $parcelOrder->order_date = (isset($data['date_to']) && !in_array($data['date_to'], $excluded_dates)) ? $data['date_to'] : null;
    
            $parcelOrder->type   = $data['type'];
            $parcelOrder->status = 'Unassigned';
    
            // === Environmental Calculation ===
            $sLat = (float) $data['s_address']['lat'];
            $sLng = (float) $data['s_address']['lng'];
            $rLat = (float) $data['r_address']['lat'];
            $rLng = (float) $data['r_address']['lng'];
            
            $weightString = trim($data['weight']);

            // Extract numeric part (works for "1", "1kg", "1 kg", "2.5 kg", etc.)
            if (preg_match('/([\d\.]+)/', $weightString, $matches)) {
                $weight = (float) $matches[1];
            } else {
                $weight = 0.0; // fallback if invalid input
            }
    
            // Haversine distance in KM
            $distanceKm = $this->haversineDistanceKm($sLat, $sLng, $rLat, $rLng);
    
            // Formulas
            $CARGO_FACTOR = 0.0006;
            $CARRY_FACTOR = 0.00002;
            $TREE_ABSORB_KG = 22;
    
            $cargoCO2 = $weight * $distanceKm * $CARGO_FACTOR;
            $carryonCO2 = $weight * $distanceKm * $CARRY_FACTOR;
            $savedCO2 = max(0, $cargoCO2 - $carryonCO2);
            $percentCO2 = $cargoCO2 > 0 ? ($savedCO2 / $cargoCO2) * 100 : 0;
            $savedTrees = $savedCO2 / $TREE_ABSORB_KG;
    
            // Save only requested values
            $parcelOrder->distance_km = round($distanceKm, 3);
            $parcelOrder->co2_saved_percent = round($percentCO2, 3);
            $parcelOrder->trees_saved = round($savedTrees, 6);
    
            $parcelOrder->save();
    
            // === Address Save Logic ===
            $address = new Address;
    
            if (!empty($data['s_address']['save'])) {
                $address->addNew([
                    'user_id' => $data['user_id'],
                    'name' => $data['s_address']['name'],
                    'city' => $data['s_address']['city'] ?? null,
                    'country' => $data['s_address']['country'],
                    'lat' => $data['s_address']['lat'],
                    'lng' => $data['s_address']['lng'],
                    'street' => $data['s_address']['street'],
                    'building' => $data['s_address']['building'],
                    'apartment' => $data['s_address']['apartment'],
                    'addressnotes' => $data['s_address']['notes'] ?? null,
                    'type' => 1
                ]);
            }
    
            if (!empty($data['r_address']['save'])) {
                $address->addNew([
                    'user_id' => $data['user_id'],
                    'name' => $data['r_address']['name'],
                    'city' => $data['r_address']['city'] ?? null,
                    'country' => $data['r_address']['country'],
                    'lat' => $data['r_address']['lat'],
                    'lng' => $data['r_address']['lng'],
                    'street' => $data['r_address']['street'],
                    'building' => $data['r_address']['building'],
                    'apartment' => $data['r_address']['apartment'],
                    'addressnotes' => $data['r_address']['notes'] ?? null,
                    'type' => 2
                ]);
            }
    
            return ['message' => 'done', 'order' => $parcelOrder];
    
        } catch (\Throwable $e) {
            // no request body in the log: it holds names, phones and addresses
            \Log::error('Parcel Order Creation Failed: '.$e->getMessage(), [
                'user_id' => $data['user_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'message' => 'An error occurred while creating the order.',
            ], 500);
        }
    }
    
    /**
     * Haversine distance calculator (km)
     */
    private function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        if ($lat1 === $lat2 && $lng1 === $lng2) {
            return 0.0;
        }
    
        $earthRadiusKm = 6371.0088;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
    
        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) ** 2;
    
        return $earthRadiusKm * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /*
    |--------------------------------------------------------------------------
    | Online payment helpers
    |--------------------------------------------------------------------------
    */

    /** The app historically sent the payment_methods.id; store the code. */
    public static function paymentMethodCode($value): string
    {
        if ($value === null || $value === '') {
            return 'cash_on_delivery';
        }
        if (is_numeric($value)) {
            $method = PaymentMethod::find((int) $value);
            return $method ? $method->code : 'cash_on_delivery';
        }
        return (string) $value;
    }

    public function isOnlinePayment(): bool
    {
        return $this->payment_method === 'stripe';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /** Card orders block pickup until the sender has paid. */
    public function awaitingPayment(): bool
    {
        return $this->isOnlinePayment() && !$this->isPaid();
    }

    /** CarryOn's cut and the carrier's share, from the commission setting. */
    public function applyCommission(): void
    {
        if (!$this->isOnlinePayment() || (float) $this->payment_amount <= 0) {
            $this->commission_amount = null;
            $this->carrier_earning = null;
            return;
        }
        $percent = AppSetting::getFloat(AppSetting::COMMISSION_PERCENT);
        $commission = round((float) $this->payment_amount * $percent / 100, 2);
        $this->commission_amount = $commission;
        $this->carrier_earning = round((float) $this->payment_amount - $commission, 2);
    }

    /** Human label used by the admin pages. */
    public function paymentLabel(): string
    {
        if (!$this->isOnlinePayment()) {
            return 'Cash';
        }
        return ucfirst(str_replace('_', ' ', $this->payment_status ?? 'unpaid'));
    }

    /**
     * Refund a paid card order in full. Returns the new payment_status:
     * `refunded`, or `refund_pending` when Stripe could not be reached so the
     * admin can retry from the order page.
     */
    public function refundPayment(): string
    {
        if (!$this->isOnlinePayment() || !$this->isPaid()) {
            return $this->payment_status ?? 'unpaid';
        }
        try {
            $refund = (new \App\Services\StripePaymentService(false))->refund($this);
            $this->payment_status = 'refunded';
            $this->refund_reference = $refund->id;
            $this->refunded_at = now();
        } catch (\Throwable $e) {
            \Log::error('Stripe refund failed for order ' . $this->id . ': ' . $e->getMessage());
            $this->payment_status = 'refund_pending';
        }
        $this->save();
        return $this->payment_status;
    }
    
    public function extendDate($data)
    {
        $userId = $data['user_id'];
        $user   = AppUser::find($userId);
    
        // Check if the user exists
        if ($user) {
    
            $orderId = $data['order_id'];
            $parcelOrder = ParcelOrder::find($orderId);
    
            // Check if the order exists
            if ($parcelOrder) {
    
                // Check if the user is the creator of this order
                if ($user->id == $parcelOrder->user_id) {

                    if (!in_array($parcelOrder->status, ['Unassigned', 'Expired'], true)) {
                        return ['message' => 'Only open or expired packages can get a new date.'];
                    }
    
                    // Extend with new date if provided and valid
                    if (isset($data['date_to'])) {
                        $parcelOrder->order_date = $data['date_to'];
    
                        // If status is Expired, reset to Unassigned
                        if ($parcelOrder->status === 'Expired') {
                            $parcelOrder->status = 'Unassigned';
                        }
    
                        $parcelOrder->save();
    
                        $message = "done";
                        $response = [
                            'message' => $message,
                            'order' => $parcelOrder
                        ];
                    } else {
                        $response = ['message' => "Invalid date provided"];
                    }
    
                } else {
                    $response = ['message' => "User ID does not match the user associated with this parcel order"];
                }
    
            } else {
                $response = ['message' => "Parcel order with ID {$orderId} not found"];
            }
    
        } else {
            $response = ['message' => "There is no AppUser with ID {$userId}"];
        }
    
        return $response;
    }
    
    
    public function assign($data)
    {        
        $carrierId = $data['user_id'];
        $carrier   = AppUser::find($carrierId);
        
        if ($carrier) {
            
            $orderId = $data['order_id'];
            $parcelOrder = ParcelOrder::find($orderId);
            if ($parcelOrder) {
                // only an open package, and never your own
                if ($parcelOrder->status !== 'Unassigned' || $parcelOrder->carrier_id !== null) {
                    return ['message' => 'This package is no longer available.'];
                }
                if ((int) $parcelOrder->user_id === (int) $carrierId) {
                    return ['message' => 'You cannot carry your own package.'];
                }
                // two carriers accepting at once: only the first one wins
                $claimed = ParcelOrder::where('id', $parcelOrder->id)
                    ->where('status', 'Unassigned')->whereNull('carrier_id')
                    ->update(['carrier_id' => $carrierId, 'status' => 'Assigned']);
                if ($claimed === 0) {
                    return ['message' => 'This package is no longer available.'];
                }
                $parcelOrder->refresh();
                $parcelOrder->carrier_id = $carrierId;
                $parcelOrder->status = "Assigned";
                if ($parcelOrder->awaitingPayment()) {
                    $hours = (int) AppSetting::getFloat(AppSetting::PAYMENT_DEADLINE_HOURS);
                    $parcelOrder->payment_deadline_at = now()->addHours($hours);
                }
                $parcelOrder->save();
    
                $message = "done";
                $response = ['message' => $message, 'order' => $parcelOrder];
                
            } else {
                $response = ['message' => "Parcel order with ID {$orderId} not found"];
            }
            
        } else {
            
            $response = ['message' => "There is no AppUser with ID {$carrierId}"];
            
        }

        return $response;
    }
    

    
    public function unassign($data)
    {

        // the signed-in user (actor_id, set by the controller); the app still
        // sends the carrier as user_id when the sender drops the carrier
        $userId = $data['actor_id'] ?? $data['user_id'];
        $user   = AppUser::find($userId);
        
        // Check if the user exist
        if ($user) {
        
            $orderId = $data['order_id'];
            $parcelOrder = ParcelOrder::find($orderId);
            
            // Check if the order exist
            if ($parcelOrder) {
                
                // the carrier drops the package, or the sender drops the carrier
                if ($parcelOrder->carrier_id !== null
                    && ($user->id == $parcelOrder->carrier_id || $user->id == $parcelOrder->user_id)) {

                    if ($parcelOrder->status !== 'Assigned') {
                        return ['message' => 'The package can only be released before pickup.'];
                    }
                    
                    // Change the status of the order to 'Unassigned' and remove the carrier id
                    $parcelOrder->status = "Unassigned";
                    $parcelOrder->carrier_id = NULL;
                    $parcelOrder->payment_deadline_at = null;
                    $parcelOrder->save();
        
                    $message = "done";
                    $response = ['message' => $message, 'order' => $parcelOrder];
                    
                } else {
                    $response = ['message' => "User ID does not match the carrier associated with the parcel order"];
                }
                
            } else {
                $response = ['message' => "Parcel order with ID {$orderId} not found"];
            }
            
        } else {
            
            $response = ['message' => "There is no AppUser with ID {$userId}"];
            
        }

        return $response;
    }
    
    public function pickup($data)
    {

        $userId = $data['user_id'];
        $user   = AppUser::find($userId);
        
        // Check if the user exist
        if ($user) {
        
            $orderId = $data['order_id'];
            $parcelOrder = ParcelOrder::find($orderId);
            
            // Check if the order exist
            if ($parcelOrder) {
                
                // Check if the user is the carrier of this order
                if ($user->id == $parcelOrder->carrier_id) {
                    
                    if ($parcelOrder->status !== 'Assigned') {
                        return ['message' => 'This package cannot be picked up now.'];
                    }
                    if ($parcelOrder->awaitingPayment()) {
                        return ['message' => 'The sender has not paid for this package yet.'];
                    }
                    
                    // Change the status of the order to 'Picked'
                    $parcelOrder->status = "Picked";
                    $parcelOrder->save();
        
                    $message = "done";
                    $response = ['message' => $message, 'order' => $parcelOrder];
                    
                } else {
                    $response = ['message' => "User ID does not match the carrier associated with the parcel order"];
                }
                
            } else {
                $response = ['message' => "Parcel order with ID {$orderId} not found"];
            }
            
        } else {
            
            $response = ['message' => "There is no AppUser with ID {$userId}"];
            
        }

        return $response;
    }
    
    public function deliver($data)
    {
        // The requester is the carrier
        $carrierId = isset($data['user_id']) ? (int) $data['user_id'] : 0;
        $orderId   = isset($data['order_id']) ? (int) $data['order_id'] : 0;
    
        $carrier = AppUser::find($carrierId);
        if (!$carrier) {
            return ['message' => "Carrier (AppUser) with ID {$carrierId} not found"];
        }
    
        $order = ParcelOrder::find($orderId);
        if (!$order) {
            return ['message' => "Parcel order with ID {$orderId} not found"];
        }
    
        // ✅ Ensure the requester is the order's carrier
        if ((int) $order->carrier_id !== $carrierId) {
            return ['message' => 'This carrier is not assigned to the order'];
        }
    
        // Creator of the package (who gets the trees)
        $creatorId = (int) $order->user_id; // change if your creator column is named differently
        if ($creatorId <= 0) {
            return ['message' => 'Order has no creator (user_id)'];
        }
    
        // If it’s already delivered, do nothing (no double counting)
        if ($order->status === 'Delivered') {
            return ['message' => 'Order already delivered (no update)', 'order' => $order];
        }
        if (!in_array($order->status, ['Picked', 'Transit'], true)) {
            return ['message' => 'This package has not been picked up.'];
        }
    
        // Decimal trees saved for this order; do NOT fallback
        $treesForThisOrder = $order->trees_saved; // decimal or null
    
        if ($order->awaitingPayment()) {
            return ['message' => 'The sender has not paid for this package yet.'];
        }

        $earning = null;
        DB::transaction(function () use ($order, $creatorId, $treesForThisOrder, &$earning) {
            // 1) Mark the order as delivered
            $order->status = 'Delivered';
            $order->save();

            // 1b) Card orders: the carrier's share lands in their wallet
            $earning = (new \App\Services\WalletService)->creditEarning($order);
    
            // 2) Increment creator's total only if > 0 and not null
            if (!is_null($treesForThisOrder) && (float) $treesForThisOrder > 0) {
                // atomic decimal increment
                DB::table('app_users')
                    ->where('id', $creatorId)
                    ->update([
                        'trees_saved' => DB::raw('trees_saved + ' . (float) $treesForThisOrder)
                    ]);
            }
        });
    
        $order->refresh();
    
        return [
            'message' => 'done',
            'order'   => $order,
            'earning' => $earning,
        ];
    }
    
    public function transit($data)
    {

        $userId = $data['user_id'];
        $user   = AppUser::find($userId);
        
        // Check if the user exist
        if ($user) {
        
            $orderId = $data['order_id'];
            $parcelOrder = ParcelOrder::find($orderId);
            
            // Check if the order exist
            if ($parcelOrder) {
                
                // Check if the user is the carrier of this order
                if ($user->id == $parcelOrder->carrier_id) {
                    
                    if ($parcelOrder->status !== 'Picked') {
                        return ['message' => 'This package cannot be marked in transit now.'];
                    }
                    if ($parcelOrder->awaitingPayment()) {
                        return ['message' => 'The sender has not paid for this package yet.'];
                    }
                    
                    // Change the status of the order to 'Transit'
                    $parcelOrder->status = "Transit";
                    $parcelOrder->save();
        
                    $message = "done";
                    $response = ['message' => $message, 'order' => $parcelOrder];
                    
                } else {
                    $response = ['message' => "User ID does not match the carrier associated with the parcel order"];
                }
                
            } else {
                $response = ['message' => "Parcel order with ID {$orderId} not found"];
            }
            
        } else {
            
            $response = ['message' => "There is no AppUser with ID {$userId}"];
            
        }

        return $response;
    }
    
    public function cancel($data)
    {

        $userId = $data['user_id'];
        $user   = AppUser::find($userId);
        
        // Check if the user exist
        if ($user) {
        
            $orderId = $data['order_id'];
            $parcelOrder = ParcelOrder::find($orderId);
            
            // Check if the order exist
            if ($parcelOrder) {
                
                // Check if the user is the creator of this order
                if ($user->id == $parcelOrder->user_id) {
                    
                    // once the carrier has it, cancelling goes through support
                    if (!in_array($parcelOrder->status, ['Unassigned', 'Assigned', 'Expired'], true)) {
                        return ['message' => 'This package can no longer be cancelled. Please contact support.'];
                    }

                    $refund = null;
                    $wasPickedUp = in_array($parcelOrder->status, ['Picked', 'Transit', 'Delivered'], true);

                    // Change the status of the order to 'Cancelled'
                    $parcelOrder->status = "Cancelled";
                    $parcelOrder->payment_deadline_at = null;
                    $parcelOrder->save();

                    // Paid by card and never picked up: money goes straight back.
                    // After pickup the admin decides from the order page.
                    if ($parcelOrder->isOnlinePayment() && $parcelOrder->isPaid() && !$wasPickedUp) {
                        $refund = $parcelOrder->refundPayment();
                    }
        
                    $message = "done";
                    $response = ['message' => $message, 'order' => $parcelOrder, 'refund' => $refund];
                    
                } else {
                    $response = ['message' => "User ID does not match the user associated with the parcel order"];
                }
                
            } else {
                $response = ['message' => "Parcel order with ID {$orderId} not found"];
            }
            
        } else {
            
            $response = ['message' => "There is no AppUser with ID {$userId}"];
            
        }

        return $response;
    }
    
    public function getParcelOrderById($data = [])
    {
        return ParcelOrder::where('parcel_orders.id', $data['id'] ?? $_GET['id'] ?? null)
            ->join('parcel_cates', 'parcel_orders.cate_id', '=', 'parcel_cates.id')
            ->select('parcel_orders.*', 'parcel_cates.name as cate_name', 'parcel_cates.name_ar as cate_name_ar', 'parcel_cates.img as cate_image')
            ->first();
    }
    
    /**
     * An order as [viewerId] may see it. The sender and the carrier get
     * everything; anyone else (a carrier browsing open packages) gets no
     * phones, no street / building / apartment, no payment references, and
     * coordinates rounded to about 1 km.
     */
    public static function redactForViewer($order, int $viewerId)
    {
        if ((int) $order->user_id === $viewerId || (int) $order->carrier_id === $viewerId) {
            return $order;
        }
        $order->makeHidden([
            's_phone', 'r_phone', 's_street', 'r_street', 's_building', 'r_building',
            's_apartment', 'r_apartment', 's_notes', 'r_notes',
            'payment_reference', 'refund_reference', 'paid_at', 'refunded_at',
            'commission_amount', 'payment_deadline_at',
        ]);
        foreach (['s_lat', 's_lng', 'r_lat', 'r_lng'] as $key) {
            if (is_numeric($order->{$key})) {
                $order->{$key} = round((float) $order->{$key}, 2);
            }
        }
        return $order;
    }

    public function getAllUnassigned()
    {
        return ParcelOrder::where(function($query){
            
            $query->where('parcel_orders.status','Unassigned');
    
        })->join('parcel_cates','parcel_orders.cate_id','=','parcel_cates.id')
          ->select('parcel_orders.*','parcel_cates.name as cate_name','parcel_cates.name_ar as cate_name_ar','parcel_cates.img as cate_image')
          ->orderBy('parcel_orders.id','DESC')
          ->get();
    }
    
    
    public function getMyCreated()
    {
        $userId = request('user_id');
    
        return ParcelOrder::where('parcel_orders.user_id', $userId)
            ->join('parcel_cates', 'parcel_orders.cate_id', '=', 'parcel_cates.id')
            ->leftJoin('app_users', 'parcel_orders.carrier_id', '=', 'app_users.id')
            ->select(
                'parcel_orders.*',
                'parcel_cates.name as cate_name',
                'parcel_cates.name_ar as cate_name_ar',
                'parcel_cates.img as cate_image',
                'app_users.name as carrier_name',
                'app_users.selfie as carrier_selfie',
                'app_users.phone as carrier_phone',
                DB::raw('(SELECT ROUND(AVG(rating), 2) FROM ratings WHERE ratings.user_id = app_users.id) as carrier_average_rating'),
                DB::raw('(SELECT COUNT(*) FROM ratings WHERE ratings.user_id = app_users.id) as carrier_ratings_count')
            )
            ->orderByRaw("FIELD(parcel_orders.status, 'Unassigned', 'Assigned', 'Picked', 'Delivered', 'Cancelled', 'Expired')")
            ->orderBy('parcel_orders.id', 'DESC')
            ->get();
    }
    
    public function getMyCarried()
    {
        return ParcelOrder::where(function ($query) {
                $query->where('parcel_orders.carrier_id', $_GET['user_id']);
            })
            ->join('parcel_cates', 'parcel_orders.cate_id', '=', 'parcel_cates.id')
            ->leftJoin('app_users', 'parcel_orders.user_id', '=', 'app_users.id')
            ->select(
                'parcel_orders.*',
                'parcel_cates.name as cate_name',
                'parcel_cates.name_ar as cate_name_ar',
                'parcel_cates.img as cate_image',
                'app_users.name as client_name',
                'app_users.phone as client_phone'
            )
            ->orderByRaw("FIELD(parcel_orders.status, 'Unassigned', 'Assigned', 'Picked', 'Delivered', 'Cancelled', 'Expired')")
            ->orderBy('parcel_orders.id', 'DESC')
            ->get();
    }
    

    public function getAll()
    {
        return ParcelOrder::where(function($query){

            if(isset($_GET['status']))
            {
                if($_GET['status'] == 0)
                {
                    $query->where('parcel_orders.status','Unassigned');
                }
                elseif($_GET['status'] == 1)
                {
                    $query->whereIn('parcel_orders.status',['Assigned','Picked']);
                }
                elseif($_GET['status'] == 2)
                {
                    $query->where('parcel_orders.status','Delivered');
                }
                elseif($_GET['status'] == 3)
                {
                    $query->where('parcel_orders.status','Cancelled');
                } 
                elseif($_GET['status'] ==4)
                {
                    $query->where('parcel_orders.status','Expired');
                }
            } 
    
            if(isset($_GET['filter_q']) && $_GET['filter_q'] != "")
            {
                $query->where(DB::raw('lower(parcel_orders.s_name)'), 'like', '%' . strtolower($_GET['filter_q']) . '%')->orWhere('parcel_orders.s_phone','Like','%'.$_GET['filter_q'].'%');
            }
    
            if(isset($_GET['filter_r']) && $_GET['filter_r'] != "")
            {
                $query->where(DB::raw('lower(parcel_orders.r_name)'), 'like', '%' . strtolower($_GET['filter_q']) . '%')->orWhere('parcel_orders.r_phone','Like','%'.$_GET['filter_q'].'%');
            }
    
        })->join('parcel_cates','parcel_orders.cate_id','=','parcel_cates.id')
          ->leftjoin('app_users','parcel_orders.carrier_id','=','app_users.id')
          ->select('parcel_cates.name as cate','parcel_cates.name_ar as cate_name_ar','parcel_cates.img as cate_image','parcel_orders.*','app_users.name as cname')
          ->orderBy('parcel_orders.id','DESC')
          ->get();
    }
    
    
    public function getAllPaginated($perPage = 10)
    {
        $query = ParcelOrder::where(function($query) {
            if (isset($_GET['status'])) {
                switch ($_GET['status']) {
                    case 0:
                        $query->where('parcel_orders.status', 'Unassigned');
                        break;
                    case 1:
                        $query->whereIn('parcel_orders.status', ['Assigned', 'Picked']);
                        break;
                    case 2:
                        $query->where('parcel_orders.status', 'Delivered');
                        break;
                    case 3:
                        $query->where('parcel_orders.status', 'Cancelled');
                        break;
                    case 4:
                        $query->where('parcel_orders.status', 'Expired');
                        break;
                }
            }
    
            if (!empty($_GET['filter_q'])) {
                $query->where(function($q) {
                    $q->where(DB::raw('LOWER(parcel_orders.s_name)'), 'like', '%' . strtolower($_GET['filter_q']) . '%')
                      ->orWhere('parcel_orders.s_phone', 'like', '%' . $_GET['filter_q'] . '%');
                });
            }
    
            if (!empty($_GET['filter_r'])) {
                $query->where(function($q) {
                    $q->where(DB::raw('LOWER(parcel_orders.r_name)'), 'like', '%' . strtolower($_GET['filter_r']) . '%')
                      ->orWhere('parcel_orders.r_phone', 'like', '%' . $_GET['filter_r'] . '%');
                });
            }
        });
    
        $results = $query->join('parcel_cates', 'parcel_orders.cate_id', '=', 'parcel_cates.id')
                         ->leftJoin('app_users as carrier', 'parcel_orders.carrier_id', '=', 'carrier.id')
                         ->leftJoin('app_users as user', 'parcel_orders.user_id', '=', 'user.id')
                         ->select(
                             'parcel_cates.name as cate',
                             'parcel_cates.name_ar as cate_name_ar',
                             'parcel_cates.img as cate_image',
                             'parcel_orders.*',
                             'carrier.name as carrier_name',
                             'user.name as user_name'
                         )
                         ->orderBy('parcel_orders.id', 'DESC')
                         ->paginate($perPage);
    
        return $results;
    }
    
    
    public function views()
    {
        return $this->hasMany(\App\Models\ParcelOrderView::class, 'parcel_order_id');
    }
    
}
