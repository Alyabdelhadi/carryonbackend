<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The four counters the app shows on its home screen. Each is computed
 * from the database (cached for ten minutes) unless the admin typed a
 * fixed value on the App Settings page.
 */
class HomeStatsService
{
    public const CACHE_KEY = 'home_stats_computed';

    /** @return array{packages:int,users:int,trees_saved:int,cities:int} */
    public static function stats(): array
    {
        $computed = self::computed();
        $out = [];
        foreach (AppSetting::statDefinitions() as $key => $definition) {
            $override = AppSetting::getInt($key);
            $out[$definition['field']] = $override ?? $computed[$definition['field']];
        }

        return $out;
    }

    /** @return array{packages:int,users:int,trees_saved:int,cities:int} */
    public static function computed(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function () {
            $cities = DB::selectOne(
                'SELECT COUNT(*) AS n FROM ('
                .'SELECT LOWER(s_city) AS c FROM parcel_orders WHERE s_city IS NOT NULL AND s_city <> "" '
                .'UNION SELECT LOWER(r_city) FROM parcel_orders WHERE r_city IS NOT NULL AND r_city <> "" '
                .'UNION SELECT LOWER(ci.name) FROM trips t JOIN cities ci ON ci.id = t.city_from_id '
                .'UNION SELECT LOWER(ci.name) FROM trips t JOIN cities ci ON ci.id = t.city_to_id'
                .') x'
            );

            return [
                'packages' => (int) DB::table('parcel_orders')->count(),
                'users' => (int) DB::table('app_users')->count(),
                'trees_saved' => (int) round((float) DB::table('app_users')->sum('trees_saved')),
                'cities' => (int) ($cities->n ?? 0),
            ];
        });
    }
}
