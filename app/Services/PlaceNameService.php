<?php

namespace App\Services;

use App\Models\City;
use App\Models\Country;

/**
 * Arabic names for the free-text city/country strings stored on orders and
 * addresses (they come from Google Places in English). Looks the English
 * name up in `countries` / `cities` and memoises per request. Never throws:
 * a failed lookup just yields null and the app shows the stored name.
 */
class PlaceNameService
{
    /** @var array<string, string|null> */
    private static array $countries = [];

    /** @var array<string, string|null> */
    private static array $cities = [];

    public static function country(?string $name): ?string
    {
        $key = self::key($name);
        if ($key === null) {
            return null;
        }
        if (!array_key_exists($key, self::$countries)) {
            self::$countries[$key] = self::safely(
                fn () => Country::whereRaw('LOWER(name) = ?', [$key])->value('name_ar')
            ) ?? self::alias($key);
        }

        return self::$countries[$key];
    }

    public static function city(?string $name, ?string $country = null): ?string
    {
        $key = self::key($name);
        if ($key === null) {
            return null;
        }
        $countryKey = self::key($country);
        $memo = $key.'|'.($countryKey ?? '');
        if (!array_key_exists($memo, self::$cities)) {
            self::$cities[$memo] = self::safely(function () use ($key, $countryKey) {
                $query = City::whereRaw('LOWER(name) = ?', [$key])->whereNotNull('name_ar');
                if ($countryKey !== null) {
                    $countryId = Country::whereRaw('LOWER(name) = ?', [$countryKey])->value('id');
                    if ($countryId) {
                        $query->where('country_id', $countryId);
                    }
                }

                return $query->value('name_ar');
            }) ?? self::alias($key);
        }

        return self::$cities[$memo];
    }

    /** @var array<string, string>|null */
    private static ?array $aliases = null;

    private static function alias(string $key): ?string
    {
        self::$aliases ??= require database_path('seeders/data/place_aliases_ar.php');

        return self::$aliases[$key] ?? null;
    }

    private static function safely(callable $lookup): ?string
    {
        try {
            $value = $lookup();
        } catch (\Throwable $e) {
            return null;
        }

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * Lower-cased lookup key, or null when there is nothing to look up:
     * empty, or containing characters outside Latin-1 (an Arabic or Turkish
     * name stored on an order is not the English key, and the latin1
     * `countries` table cannot be compared against such a parameter).
     */
    private static function key(?string $name): ?string
    {
        $trimmed = trim((string) $name);
        if ($trimmed === '' || preg_match('/[^\x{0000}-\x{00FF}]/u', $trimmed)) {
            return null;
        }

        return mb_strtolower($trimmed);
    }
}
