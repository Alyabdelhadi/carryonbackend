<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Models\ParcelOrder;
use App\Models\AppUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class Trip extends Model
{
    public function cityFrom()
    {
        return $this->belongsTo(City::class, 'city_from_id');
    }
    
    public function cityTo()
    {
        return $this->belongsTo(City::class, 'city_to_id');
    }
    
    public function carrier()
    {
        return $this->belongsTo(AppUser::class);
    }
    
    /**
     * Create a new Trip
     */
    public function createTrip($data)
    {
        $trip = new Trip;
        $trip->city_from_id = $data['city_from_id'];
        $trip->city_to_id   = $data['city_to_id'];
        $trip->carrier_id   = $data['carrier_id'];
        $trip->frequency    = $data['frequency']; // one_time, daily, weekdays, weekends

        // Set date only for one_time frequency
        if ($data['frequency'] === 'one_time') {
            $trip->date = $data['date'] ?? null;
        } else {
            $trip->date = null; // clear date for other frequencies
        }

        $trip->save();
        return $trip;
    }

    /**
     * Update an existing Trip
     */
    public function updateTrip($id, $data)
    {
        $trip = Trip::find($id);
        if (!$trip) {
            return null;
        }

        $trip->city_from_id = $data['city_from_id'];
        $trip->city_to_id   = $data['city_to_id'];
        $trip->carrier_id   = $data['carrier_id'];
        $trip->frequency    = $data['frequency'];

        if ($data['frequency'] === 'one_time') {
            $trip->date = $data['date'] ?? null;
        } else {
            $trip->date = null;
        }

        $trip->save();
        return $trip;
    }

    /**
     * Delete a Trip
     */
    public function deleteTrip($id)
    {
        $trip = Trip::find($id);
        if ($trip) {
            $trip->delete();
            return true;
        }
        return false;
    }

    /**
     * Get Trip by ID
     */
    public function getTripById($id)
    {
        $trip = Trip::with(['cityFrom.country', 'cityTo.country'])->find($id);
    
        if (!$trip) {
            return null;
        }
    
        return [
            'trip_id' => $trip->id,
            'carrier_id' => $trip->carrier_id,
            'frequency' => $trip->frequency,
            'date' => $trip->date,
            'city_from' => [
                'id' => $trip->cityFrom->id ?? null,
                'name' => $trip->cityFrom->name ?? null,
                    'name_ar' => $trip->cityFrom->name_ar ?? null,
                'country' => [
                    'id' => $trip->cityFrom->country->id ?? null,
                    'name' => $trip->cityFrom->country->name ?? null,
                        'name_ar' => $trip->cityFrom->country->name_ar ?? null,
                ],
                'image' => $trip->cityFrom->image ?? 'city.jpg',
            ],
            'city_to' => [
                'id' => $trip->cityTo->id ?? null,
                'name' => $trip->cityTo->name ?? null,
                    'name_ar' => $trip->cityTo->name_ar ?? null,
                'country' => [
                    'id' => $trip->cityTo->country->id ?? null,
                    'name' => $trip->cityTo->country->name ?? null,
                        'name_ar' => $trip->cityTo->country->name_ar ?? null,
                ],
                'image' => $trip->cityTo->image ?? 'city.jpg',
            ],
        ];
    }

    /**
     * Get all Trips
     */
    public function getAllTrips()
    {
        $trips = Trip::with(['cityFrom.country', 'cityTo.country', 'carrier'])
            ->orderBy('date', 'desc')
            ->get();
    
        return $trips->map(function ($trip) {
            return [
                'id' => $trip->id,
                'carrier' => $trip->carrier->name ?? null,
                'frequency' => $trip->frequency,
                'date' => $trip->date,
                'city_from' => $trip->cityFrom->name ?? null,
                'city_to' => $trip->cityTo->name ?? null,
            ];
        });
    }
    
    public static function getPaginatedTrips($perPage = 50, $frequency = null, array $filters = [])
    {
        $query = self::with(['cityFrom.country', 'cityTo.country', 'carrier'])
            ->orderByRaw('ISNULL(date), date DESC'); // show frequent (null date) first, then latest dated
    
        // Frequency filter
        if ($frequency === 'one_time') {
            $query->where('frequency', 'one_time');
        } elseif ($frequency === 'frequent') {
            $query->where('frequency', '!=', 'one_time');
        }
    
        // Text filters (case-insensitive contains)
        if (!empty($filters['from'])) {
            $needle = mb_strtolower($filters['from']);
            $query->whereHas('cityFrom', function (Builder $q) use ($needle) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%'.$needle.'%']);
            });
        }
    
        if (!empty($filters['to'])) {
            $needle = mb_strtolower($filters['to']);
            $query->whereHas('cityTo', function (Builder $q) use ($needle) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%'.$needle.'%']);
            });
        }
    
        if (!empty($filters['carrier'])) {
            $needle = mb_strtolower($filters['carrier']);
            $query->whereHas('carrier', function (Builder $q) use ($needle) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%'.$needle.'%']);
            });
        }
    
        // Date range (applies to one_time trips with a date)
        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }
    
        $trips = $query->paginate($perPage)->appends(request()->query());
    
        $trips->getCollection()->transform(function ($trip) {
            return [
                'id'            => $trip->id,
                'carrier'       => $trip->carrier->name ?? null,
                'frequency'     => $trip->frequency === 'one_time' ? 'One Trip' : 'Frequent Route',
                'date'          => $trip->date,
                'city_from'     => $trip->cityFrom->name ?? null,
                'country_from'  => $trip->cityFrom->country->name ?? null,
                'city_to'       => $trip->cityTo->name ?? null,
                'country_to'    => $trip->cityTo->country->name ?? null,
            ];
        });
    
        return $trips;
    }

    /**
     * Get Trips by Carrier ID
     */
    public function getTripsByCarrier($carrierId)
    {
        $trips = Trip::with(['cityFrom.country', 'cityTo.country'])
                     ->where('carrier_id', $carrierId)
                     ->orderByRaw('ISNULL(date), date ASC')
                     ->get();
    
        return $trips->map(function ($trip) {
            return [
                'trip_id' => $trip->id,
                'carrier_id' => $trip->carrier_id,
                'frequency' => $trip->frequency,
                'date' => $trip->date,
                'destination_image' => $trip->destination_image,
                'city_from' => [
                    'id' => $trip->cityFrom->id ?? null,
                    'name' => $trip->cityFrom->name ?? null,
                    'name_ar' => $trip->cityFrom->name_ar ?? null,
                    'country' => [
                        'id' => $trip->cityFrom->country->id ?? null,
                        'name' => $trip->cityFrom->country->name ?? null,
                        'name_ar' => $trip->cityFrom->country->name_ar ?? null,
                    ],
                    'image' => $trip->cityFrom->image ?? 'city.jpg',
                ],
                'city_to' => [
                    'id' => $trip->cityTo->id ?? null,
                    'name' => $trip->cityTo->name ?? null,
                    'name_ar' => $trip->cityTo->name_ar ?? null,
                    'country' => [
                        'id' => $trip->cityTo->country->id ?? null,
                        'name' => $trip->cityTo->country->name ?? null,
                        'name_ar' => $trip->cityTo->country->name_ar ?? null,
                    ],
                    'image' => $trip->cityTo->image ?? 'city.jpg',
                ],
            ];
        });
    }
    
    /**
     * Get MatchingParcelOrders by Country
     */
    // public function getMatchingParcelOrders()
    // {
    //     $fromCountryName = $this->cityFrom->country->name ?? null;
    //     $toCountryName   = $this->cityTo->country->name ?? null;
    
    //     if (!$fromCountryName || !$toCountryName) {
    //         return collect(); // return empty if country names are missing
    //     }
        
    //     // Skip if trip is one-time and date has already passed
    //     if (
    //         $this->frequency === 'one_time' &&
    //         $this->date &&
    //         \Carbon\Carbon::parse($this->date)->isPast()
    //     ) {
    //         return collect();
    //     }
    
    //     $query = ParcelOrder::query()
    //         ->where('s_country', $fromCountryName)
    //         ->where('r_country', $toCountryName)
    //         ->where('parcel_orders.status', 'Unassigned');
    
    //     if ($this->frequency === 'one_time') {
    //         $query->where(function ($q) {
    //             $q->whereNull('order_date')
    //               ->orWhereDate('order_date', '>', $this->date);
    //         });
    //     } else {
    //         // No additional filtering needed. Include all orders.
    //     }
    
    //     $orders = $query->join('parcel_cates', 'parcel_orders.cate_id', '=', 'parcel_cates.id')
    //                     ->leftJoin('app_users', 'parcel_orders.carrier_id', '=', 'app_users.id')
    //                     ->select('parcel_cates.name as cate_name', 'parcel_cates.img as cate_image', 'parcel_orders.*', 'app_users.name as cname')
    //                     ->orderBy('parcel_orders.order_date', 'asc')
    //                     ->get();
    
    //     return $orders;
    // }
    
    public static function getUpcomingRoutesWithCounts()
    {
        $today = Carbon::now()->startOfDay()->toDateString(); // 'YYYY-MM-DD'
    
        $grouped = static::query()
            ->where(function ($q) use ($today) {
                $q->where(function ($q2) use ($today) {
                    $q2->where('frequency', 'one_time')
                       ->whereDate('date', '>=', $today);
                })->orWhereIn('frequency', ['daily', 'weekdays', 'weekends']);
            })
            ->select([
                'city_from_id',
                'city_to_id',
                DB::raw('COUNT(*) as trips_count'),
            ])
            ->groupBy('city_from_id', 'city_to_id')
            ->orderByDesc('trips_count')
            ->get();
    
        if ($grouped->isEmpty()) {
            return collect();
        }
    
        $fromIds = $grouped->pluck('city_from_id')->unique()->values();
        $toIds   = $grouped->pluck('city_to_id')->unique()->values();
        $allIds  = $fromIds->merge($toIds)->unique();
    
        $cities = \App\Models\City::with('country')
            ->whereIn('id', $allIds)
            ->get()
            ->keyBy('id');
    
        return $grouped->map(function ($row) use ($cities) {
            $from = $cities->get($row->city_from_id);
            $to   = $cities->get($row->city_to_id);
    
            return [
                'from' => [
                    'id'      => $row->city_from_id,
                    'city'    => $from->name ?? null,
                    'country' => $from->country->name ?? null,
                    'image'   => $from->image ?? 'city.jpg',
                ],
                'to' => [
                    'id'      => $row->city_to_id,
                    'city'    => $to->name ?? null,
                    'country' => $to->country->name ?? null,
                    'image'   => $to->image ?? 'city.jpg',
                ],
                'count' => (int) $row->trips_count,
            ];
        });
    }
    
    public static function getUpcomingCountryRoutesWithCounts()
    {
        $today = Carbon::now()->startOfDay()->toDateString();
    
        // Step 1: Get routes
        $routes = static::query()
            ->where(function ($q) use ($today) {
                $q->where(function ($q2) use ($today) {
                    $q2->where('frequency', 'one_time')
                       ->whereDate('date', '>=', $today);
                })->orWhereIn('frequency', ['daily', 'weekdays', 'weekends']);
            })
            ->select(['city_from_id', 'city_to_id'])
            ->get();
    
        if ($routes->isEmpty()) {
            return collect();
        }
    
        // Step 2: Load cities with their countries
        $cityIds = $routes->pluck('city_from_id')->merge($routes->pluck('city_to_id'))->unique();
        $cities = \App\Models\City::with('country')->whereIn('id', $cityIds)->get()->keyBy('id');
    
        // Step 3: Map to country pairs
        $countryRoutes = $routes->map(function ($route) use ($cities) {
            $fromCity = $cities->get($route->city_from_id);
            $toCity   = $cities->get($route->city_to_id);
    
            return [
                'country_from_id' => ($fromCity && $fromCity->country) ? $fromCity->country->id : null,
                'country_to_id'   => ($toCity && $toCity->country) ? $toCity->country->id : null,
            ];
        })->filter(function ($r) {
            return $r['country_from_id'] && $r['country_to_id'];
        });
    
        // Step 4: Group and count
        $grouped = $countryRoutes
            ->groupBy(function ($r) {
                return $r['country_from_id'] . '-' . $r['country_to_id'];
            })
            ->map(function ($routes) {
                return [
                    'country_from_id' => $routes[0]['country_from_id'],
                    'country_to_id'   => $routes[0]['country_to_id'],
                    'trips_count'     => count($routes),
                ];
            })
            ->sortByDesc('trips_count')
            ->values();
    
        if ($grouped->isEmpty()) {
            return collect();
        }
    
        // Step 5: Load countries
        $countryIds = $grouped->pluck('country_from_id')->merge($grouped->pluck('country_to_id'))->unique();
        $countries = \App\Models\Country::whereIn('id', $countryIds)->get()->keyBy('id');
    
        // Step 6: Final formatted result
        return $grouped->map(function ($row) use ($countries) {
            $from = $countries->get($row['country_from_id']);
            $to   = $countries->get($row['country_to_id']);
    
            return [
                'from' => [
                    'id'    => $row['country_from_id'],
                    'name'  => $from ? $from->name : null,
                    'image' => $from && $from->image ? $from->image : 'country.jpg',
                ],
                'to' => [
                    'id'    => $row['country_to_id'],
                    'name'  => $to ? $to->name : null,
                    'image' => $to && $to->image ? $to->image : 'country.jpg',
                ],
                'count' => $row['trips_count'],
            ];
        });
    }
    
    public static function matchingParcelOrdersForCarrier($carrierId)
    {
        $trips = self::with(['cityFrom.country', 'cityTo.country'])
            ->where('carrier_id', $carrierId)
            ->get()
            ->filter(function ($trip) {
                $from = optional(optional($trip->cityFrom)->country)->name;
                $to   = optional(optional($trip->cityTo)->country)->name;

                if (!$from || !$to) {
                    return false;
                }

                if ($trip->frequency === 'one_time' && $trip->date && Carbon::parse($trip->date)->isPast()) {
                    return false;
                }

                return true;
            });

        if ($trips->isEmpty()) {
            return collect();
        }

        // Recurring trip pairs (unique by from|to)
        $recurringPairs = $trips->filter(function ($t) {
                return $t->frequency !== 'one_time';
            })
            ->map(function ($t) {
                return [
                    'from' => $t->cityFrom->country->name,
                    'to'   => $t->cityTo->country->name,
                ];
            })
            ->unique(function ($p) {
                return $p['from'].'|'.$p['to'];
            })
            ->values();

        // One-time trips grouped by from/to with min date
        $oneTimeGroups = $trips->filter(function ($t) {
                return $t->frequency === 'one_time';
            })
            ->groupBy(function ($t) {
                return $t->cityFrom->country->name.'||'.$t->cityTo->country->name;
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'from'     => $first->cityFrom->country->name,
                    'to'       => $first->cityTo->country->name,
                    'min_date' => $group->min('date'),
                ];
            })
            ->values();

        $carrierId = (int) $carrierId; // ensure int

        $query = \App\Models\ParcelOrder::query()
            ->where('parcel_orders.status', 'Unassigned')
            ->where(function ($q) use ($recurringPairs, $oneTimeGroups) {
                foreach ($recurringPairs as $pair) {
                    $q->orWhere(function ($qq) use ($pair) {
                        $qq->where('s_country', $pair['from'])
                           ->where('r_country', $pair['to']);
                    });
                }
                foreach ($oneTimeGroups as $g) {
                    $q->orWhere(function ($qq) use ($g) {
                        $qq->where('s_country', $g['from'])
                           ->where('r_country', $g['to'])
                           ->where(function ($w) use ($g) {
                               $w->whereNull('order_date')
                                 ->orWhereDate('order_date', '>', $g['min_date']);
                           });
                    });
                }
            })
            ->join('parcel_cates', 'parcel_orders.cate_id', '=', 'parcel_cates.id')
            ->leftJoin('app_users', 'parcel_orders.carrier_id', '=', 'app_users.id')
            // Join the per-carrier read status
            ->leftJoin('parcel_order_views as pov', function ($join) use ($carrierId) {
                $join->on('pov.parcel_order_id', '=', 'parcel_orders.id')
                     ->where('pov.carrier_id', '=', $carrierId);
            })
            ->select(
                'parcel_cates.name as cate_name',
                'parcel_cates.name_ar as cate_name_ar',
                'parcel_cates.img as cate_image',
                'parcel_orders.*',
                'app_users.name as cname',
                DB::raw('CASE WHEN pov.read_at IS NULL THEN 0 ELSE 1 END AS is_read'),
                DB::raw('pov.read_at as read_at')
            )
            ->orderBy('parcel_orders.order_date', 'asc');
        
        return $query->get()->unique('id')->values();
    }
    
    
}