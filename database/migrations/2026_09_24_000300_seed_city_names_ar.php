<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Fills `cities.name_ar` for well-known cities from the bundled list. */
return new class extends Migration
{
    public function up(): void
    {
        $names = require database_path('seeders/data/city_names_ar.php');
        $countries = DB::table('countries')->pluck('id', 'code');
        foreach ($names as $key => $ar) {
            [$code, $name] = explode('|', $key, 2);
            $countryId = $countries[$code] ?? null;
            if ($countryId === null) {
                continue;
            }
            DB::table('cities')->where('country_id', $countryId)->where('name', $name)->whereNull('name_ar')->update(['name_ar' => $ar]);
        }
    }

    public function down(): void
    {
        // Data only; the names stay.
    }
};
