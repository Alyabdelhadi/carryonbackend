<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ParcelOrder;

class UpdateParcelOrdersMetrics extends Command
{
    protected $signature = 'orders:update-env-metrics';
    protected $description = 'Calculate and save distance, CO2 saved percentage, and trees saved for existing parcel orders.';

    public function handle()
    {
        $this->info('Updating parcel orders environmental metrics...');

        $orders = ParcelOrder::whereNull('distance_km')
            ->orWhereNull('co2_saved_percent')
            ->orWhereNull('trees_saved')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            // Validate required coordinates and weight
            if (!$order->s_lat || !$order->s_lng || !$order->r_lat || !$order->r_lng || !$order->weight) {
                continue;
            }

            // Clean weight (handles "5 kg" or "5")
            if (preg_match('/([\d\.]+)/', (string) $order->weight, $matches)) {
                $weight = (float) $matches[1];
            } else {
                $weight = 0;
            }

            if ($weight <= 0) continue;

            $distanceKm = $this->haversineDistanceKm(
                (float) $order->s_lat,
                (float) $order->s_lng,
                (float) $order->r_lat,
                (float) $order->r_lng
            );

            // Constants
            $CARGO_FACTOR = 0.0006;
            $CARRY_FACTOR = 0.00002;
            $TREE_ABSORB_KG = 22;

            $cargoCO2 = $weight * $distanceKm * $CARGO_FACTOR;
            $carryonCO2 = $weight * $distanceKm * $CARRY_FACTOR;
            $savedCO2 = max(0, $cargoCO2 - $carryonCO2);
            $percentCO2 = $cargoCO2 > 0 ? ($savedCO2 / $cargoCO2) * 100 : 0;
            $treesSaved = $savedCO2 / $TREE_ABSORB_KG;

            $order->distance_km = round($distanceKm, 3);
            $order->co2_saved_percent = round($percentCO2, 2);
            $order->trees_saved = round($treesSaved, 6);
            $order->save();

            $count++;
        }

        $this->info("✅ Updated {$count} parcel orders.");
        return Command::SUCCESS;
    }

    private function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        if ($lat1 === $lat2 && $lng1 === $lng2) return 0.0;

        $earthRadiusKm = 6371.0088;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) ** 2;

        return $earthRadiusKm * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}