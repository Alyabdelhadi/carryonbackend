<?php

namespace App\Models;

use App\Models\AppUser;
use App\Models\ParcelOrder;
use App\Models\Trip;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function group()
    {
        return $this->belongsTo(AdminGroup::class, 'admin_group_id');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->group?->is_super;
    }

    /** Permission check for dashboard pages, see App\Support\AdminModules. */
    public function canAdmin(string $module, string $action = 'view'): bool
    {
        return $this->is_active !== false && (bool) $this->group?->allows($module, $action);
    }

    /** The first page this admin may open (where login lands). */
    public function homeUrl(): ?string
    {
        foreach (\App\Support\AdminModules::all() as $key => $module) {
            if ($module['url'] && $this->canAdmin($key, 'view')) {
                return $module['url'];
            }
        }
        return null;
    }

    public function overview()
    {
        $order       = ParcelOrder::count();
        $unassign    = ParcelOrder::where('status','Unassigned')->count();
        $complete    = ParcelOrder::where('status','Delivered')->count();
        $cancel      = ParcelOrder::where('status','Cancelled')->count();
        $running     = ParcelOrder::whereIn('status',['Assigned','Picked'])->count();
        $expired     = ParcelOrder::where('status','Expired')->count();
        $user        = AppUser::where('role',1)->count();
        $trips       = Trip::count();
        $activeUsers   = AppUser::where('role', 1)->where('status', 1)->count();
        $inactiveUsers = AppUser::where('role', 1)->where('status', 0)->count();
    
        // Carriers
        $carriers = Trip::distinct('carrier_id')->count('carrier_id');
    
        $today = Carbon::today();
    
        $pastOneTimeTrips = Trip::where('frequency', 'one_time')
            ->where('date', '<', $today)
            ->count();
    
        $upcomingOneTimeTrips = Trip::where('frequency', 'one_time')
            ->where('date', '>=', $today)
            ->count();
    
        $recurringTrips = Trip::whereIn('frequency', ['daily', 'weekdays', 'weekends'])->count();
    
        $pastTrips = $pastOneTimeTrips;
        $upcomingTrips = $upcomingOneTimeTrips + $recurringTrips;
    
        // Total cities in DB (requires City model)
        $totalCities = class_exists(\App\Models\City::class)
            ? \App\Models\City::count()
            : null;
    
        // Cities covered by trips
        $citiesCovered = DB::query()
            ->fromSub(function ($q) {
                $q->from('trips as t')
                  ->select('t.city_from_id as city_id')
                  ->union(
                      DB::table('trips as t2')
                        ->select('t2.city_to_id as city_id')
                  );
            }, 'u')
            ->distinct()
            ->count('city_id');
    
        // Total countries
        $totalCountries = class_exists(\App\Models\Country::class)
            ? \App\Models\Country::count()
            : null;
    
        // Countries covered by trips
        $countriesCovered = DB::query()
            ->fromSub(function ($q) {
                $q->from('trips as t')
                  ->join('cities as cf', 'cf.id', '=', 't.city_from_id')
                  ->select('cf.country_id as country_id')
                  ->union(
                      DB::table('trips as t2')
                        ->join('cities as ct', 'ct.id', '=', 't2.city_to_id')
                        ->select('ct.country_id as country_id')
                  );
            }, 'u')
            ->distinct()
            ->count('country_id');
    
        return [
            'order'           => $order,
            'user'            => $user,
            'complete'        => $complete,
            'cancel'          => $cancel,
            'running'         => $running,
            'unassign'        => $unassign,
            'expired'         => $expired,
            'trips'           => $trips,
            'past'            => $pastTrips,
            'upcoming'        => $upcomingTrips,
            'frequent'        => $recurringTrips,
            'active_users'    => $activeUsers,
            'inactive_users'  => $inactiveUsers,
            'carriers'        => $carriers,
            'countries_total' => $totalCountries,
            'countries_covered'=> $countriesCovered,
            'cities_total'     => $totalCities,
            'cities_covered'   => $citiesCovered,
        ];
    }
    
    

    public function matchPassword($password)
    {
      if(auth()->attempt(['username' => Auth()->user()->username, 'password' => $password]))
      {
          return false;
      }
      else
      {
          return true;
      }
    }

    public function updateData($data)
    {
        $update                     = User::find(Auth::user()->id);
        $update->name               = isset($data['name']) ? $data['name'] : null;
        $update->email              = isset($data['email']) ? $data['email'] : null;
        $update->username           = isset($data['username']) ? $data['username'] : null;
        // referral settings: only on the Super Admin form, never cleared
        if (isset($data['point_who'])) {
            $update->point_who = $data['point_who'];
        }
        if (isset($data['point_use'])) {
            $update->point_use = $data['point_use'];
        }
        
        if(isset($data['new_password']))
        {
            $update->password = bcrypt($data['new_password']);
        }

        $update->save();
    }


}
