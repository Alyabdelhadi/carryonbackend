<?php

namespace App\Console\Commands;

use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Fills `cities.name_ar` with Google Cloud Translation (v2, API key from
 * GOOGLE_TRANSLATE_KEY). Billed per character (~10 chars per city), so by
 * default it only translates cities that trips, orders or addresses
 * actually reference; `--country=LB` or `--all` widen the set.
 *
 *   php artisan places:translate-ar                 # cities in use
 *   php artisan places:translate-ar --country=LB    # every city of Lebanon
 *   php artisan places:translate-ar --all --limit=5000
 *   php artisan places:translate-ar --dry-run       # list, no API calls
 */
class TranslatePlaceNamesToArabic extends Command
{
    protected $signature = 'places:translate-ar
        {--country= : ISO code of one country whose cities to translate}
        {--all : Translate every city without an Arabic name}
        {--limit=1000 : Maximum number of cities per run}
        {--dry-run : Show what would be translated without calling Google}';

    protected $description = 'Fill cities.name_ar using Google Translate';

    public function handle(): int
    {
        $key = config('services.google.translate_key');
        if (!$key && !$this->option('dry-run')) {
            $this->error('GOOGLE_TRANSLATE_KEY is not set in .env (needs the Cloud Translation API enabled on that key).');

            return self::FAILURE;
        }

        $query = City::query()->whereNull('name_ar');
        if ($code = $this->option('country')) {
            $query->whereIn('country_id', DB::table('countries')->where('code', strtoupper($code))->pluck('id'));
        } elseif (!$this->option('all')) {
            $used = DB::table('trips')->select('city_from_id as id')
                ->union(DB::table('trips')->select('city_to_id as id'))
                ->pluck('id')->filter()->unique();
            $usedNames = DB::table('parcel_orders')->select('s_city as name')
                ->union(DB::table('parcel_orders')->select('r_city as name'))
                ->union(DB::table('addresses')->select('city as name'))
                ->pluck('name')->filter()->map(fn ($n) => mb_strtolower(trim($n)))->unique();
            $query->where(function ($q) use ($used, $usedNames) {
                $q->whereIn('id', $used)->orWhereIn(DB::raw('LOWER(name)'), $usedNames->all());
            });
        }

        $cities = $query->orderBy('id')->limit((int) $this->option('limit'))->get(['id', 'name']);
        if ($cities->isEmpty()) {
            $this->info('Nothing to translate.');

            return self::SUCCESS;
        }
        $this->info($cities->count().' cities to translate.');
        if ($this->option('dry-run')) {
            foreach ($cities->take(50) as $city) {
                $this->line(" - {$city->name}");
            }

            return self::SUCCESS;
        }

        $done = 0;
        foreach ($cities->chunk(100) as $chunk) {
            $response = Http::asForm()->post('https://translation.googleapis.com/language/translate/v2', [
                'key' => $key,
                'source' => 'en',
                'target' => 'ar',
                'format' => 'text',
                'q' => $chunk->pluck('name')->all(),
            ]);
            if (!$response->successful()) {
                $this->error('Google Translate error: '.($response->json('error.message') ?? $response->status()));

                return self::FAILURE;
            }
            $translations = $response->json('data.translations', []);
            foreach ($chunk->values() as $i => $city) {
                $ar = trim((string) ($translations[$i]['translatedText'] ?? ''));
                if ($ar !== '' && $ar !== $city->name) {
                    City::where('id', $city->id)->update(['name_ar' => $ar]);
                    $done++;
                }
            }
            $this->line("… {$done}");
        }
        $this->info("Translated {$done} cities.");

        return self::SUCCESS;
    }
}
