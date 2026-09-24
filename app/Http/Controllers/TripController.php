<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TripController extends Controller
{
    public $folder  = "trips.";
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
    {
        $res = new Trip;
        $perPage   = request()->get('per_page', 50);
    
        $filters = [
            'from'      => request()->get('from'),       // city from name
            'to'        => request()->get('to'),         // city to name
            'carrier'   => request()->get('carrier'),    // carrier name
            'date_from' => request()->get('date_from'),  // YYYY-MM-DD
            'date_to'   => request()->get('date_to'),    // YYYY-MM-DD
        ];
    
        return view($this->folder . 'index', [
            'data'  => $res->getPaginatedTrips($perPage, null, $filters),
            'link'  => 'trips/',
            'title' => 'All Trips',
        ]);
    }
    
    public function oneTime()
    {
        $res = new Trip;
        $perPage = request()->get('per_page', 50);
        $filters = [
            'from'      => request()->get('from'),
            'to'        => request()->get('to'),
            'carrier'   => request()->get('carrier'),
            'date_from' => request()->get('date_from'),
            'date_to'   => request()->get('date_to'),
        ];
    
        return view($this->folder . 'index', [
            'data'  => $res->getPaginatedTrips($perPage, 'one_time', $filters),
            'link'  => 'trips/one-time',
            'title' => 'One-Time Trips',
        ]);
    }
    
    public function frequent()
    {
        $res = new Trip;
        $perPage = request()->get('per_page', 50);
        $filters = [
            'from'      => request()->get('from'),
            'to'        => request()->get('to'),
            'carrier'   => request()->get('carrier'),
            'date_from' => request()->get('date_from'),
            'date_to'   => request()->get('date_to'),
        ];
    
        return view($this->folder . 'index', [
            'data'  => $res->getPaginatedTrips($perPage, 'frequent', $filters),
            'link'  => 'trips/frequent',
            'title' => 'Frequent Routes',
        ]);
    }	
    
    public function upcomingRoutes(Request $request)
    {
        $minCount = (int) $request->query('min_count', 0);
        $limit    = (int) $request->query('limit', 0); // optional hard cap AFTER filtering (kept since you had it)

        // Optional search + pagination
        $from     = trim((string) $request->query('from', ''));
        $to       = trim((string) $request->query('to', ''));
        $perPage  = (int) $request->query('per_page', 25);
        $page     = (int) $request->query('page', 1);

        // Get grouped routes
        $routes = Trip::getUpcomingRoutesWithCounts()

            // min_count filter
            ->when($minCount > 0, function ($c) use ($minCount) {
                return $c->filter(function ($r) use ($minCount) {
                    return (int) ($r['count'] ?? 0) >= $minCount;
                });
            })

            // search by From/To city (case-insensitive contains)
            ->when($from !== '', function ($c) use ($from) {
                $needle = mb_strtolower($from);
                return $c->filter(function ($r) use ($needle) {
                    return mb_strpos(mb_strtolower($r['from']['city'] ?? ''), $needle) !== false;
                });
            })
            ->when($to !== '', function ($c) use ($to) {
                $needle = mb_strtolower($to);
                return $c->filter(function ($r) use ($needle) {
                    return mb_strpos(mb_strtolower($r['to']['city'] ?? ''), $needle) !== false;
                });
            })
            ->values();

        // Optional hard limit (kept since you had it)
        if ($limit > 0) {
            $routes = $routes->take($limit)->values();
        }

        // Paginate the collection
        $total = $routes->count();
        $items = $routes->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view($this->folder . 'upcoming-routes', [
            'routes' => $paginator, // paginator of arrays
            'title'  => 'Upcoming Routes',
        ]);
    }
    
    public function upcomingCountryRoutes(Request $request)
    {
        $minCount        = (int) $request->query('min_count', 0);
        $limit           = (int) $request->query('limit', 0);
        $from            = trim((string) $request->query('from', '')); // country name contains
        $to              = trim((string) $request->query('to', ''));   // country name contains
        $excludeDomestic = filter_var($request->query('exclude_domestic', false), FILTER_VALIDATE_BOOLEAN);
        $perPage         = (int) $request->query('per_page', 25);
        $page            = (int) $request->query('page', 1);
    
        // Get grouped country routes (array items shaped like your country method's output)
        $routes = Trip::getUpcomingCountryRoutesWithCounts()
    
            // optionally exclude domestic (same country -> same id)
            ->when($excludeDomestic, function ($c) {
                return $c->filter(function ($r) {
                    $fromId = isset($r['from']['id']) ? $r['from']['id'] : null;
                    $toId   = isset($r['to']['id']) ? $r['to']['id'] : null;
                    return $fromId !== $toId;
                });
            })
    
            // min_count filter
            ->when($minCount > 0, function ($c) use ($minCount) {
                return $c->filter(function ($r) use ($minCount) {
                    return (int) (isset($r['count']) ? $r['count'] : 0) >= $minCount;
                });
            })
    
            // search by From/To country (case-insensitive contains)
            ->when($from !== '', function ($c) use ($from) {
                $needle = mb_strtolower($from);
                return $c->filter(function ($r) use ($needle) {
                    $hay = mb_strtolower(isset($r['from']['name']) ? $r['from']['name'] : '');
                    return mb_strpos($hay, $needle) !== false;
                });
            })
            ->when($to !== '', function ($c) use ($to) {
                $needle = mb_strtolower($to);
                return $c->filter(function ($r) use ($needle) {
                    $hay = mb_strtolower(isset($r['to']['name']) ? $r['to']['name'] : '');
                    return mb_strpos($hay, $needle) !== false;
                });
            })
            ->values();
    
        // Optional hard cap after filtering
        if ($limit > 0) {
            $routes = $routes->take($limit)->values();
        }
    
        // Paginate the collection
        $total = $routes->count();
        $items = $routes->slice(($page - 1) * $perPage, $perPage)->values();
    
        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);
    
        return view($this->folder . 'upcoming-country-routes', [
            'routes' => $paginator, // paginator of arrays
            'title'  => 'Upcoming Country Routes',
        ]);
    }
}
