<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * GET /analytics/users-evolution?from=2025-07-01&to=2025-08-21
     * Returns daily new users + cumulative between dates.
     */
    public function usersEvolution(Request $request)
    {
        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->subDays(30)->startOfDay();
        $to   = $request->query('to')   ? Carbon::parse($request->query('to'))->endOfDay()     : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        // Query daily new users (UTC). If you need timezone-accurate grouping, see the note below.
        $rows = AppUser::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd'); // ['2025-07-01' => 3, ...]

        // Build full date sequence & fill gaps
        $labels = [];
        $daily  = [];
        $cum    = [];

        // Cumulative: start with count before range
        $pre = AppUser::where('created_at', '<', $from)->count();
        $running = $pre;

        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $key = $day->format('Y-m-d');
            $n = (int) ($rows[$key] ?? 0);
            $running += $n;

            $labels[] = $key;
            $daily[]  = $n;
            $cum[]    = $running;
        }

        return response()->json([
            'labels' => $labels,
            'series' => [
                'daily' => $daily,
                'cumulative' => $cum,
            ],
        ]);
    }
}