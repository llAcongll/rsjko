<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SafeSpaceScreening;
use Carbon\Carbon;

class SafeSpaceController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('SAFE_SPACE_VIEW')) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return view('dashboard.pages.safe-space.monitoring');
    }

    public function statistics(Request $request)
    {
        if (!auth()->user()->hasPermission('SAFE_SPACE_VIEW')) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        $query = SafeSpaceScreening::query();

        $period = $request->query('period', 'all');
        
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'this_year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                $startDate = $request->query('start_date');
                $endDate = $request->query('end_date');
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }
                break;
        }

        // Optimized single query to calculate all required statistics
        $stats = $query->selectRaw("
            COUNT(id) as total,
            SUM(CASE WHEN anxiety_result = 'none' THEN 1 ELSE 0 END) as anxiety_none,
            SUM(CASE WHEN anxiety_result = 'mild' THEN 1 ELSE 0 END) as anxiety_mild,
            SUM(CASE WHEN anxiety_result = 'severe' THEN 1 ELSE 0 END) as anxiety_severe,
            SUM(CASE WHEN depression_result = 'none' THEN 1 ELSE 0 END) as depression_none,
            SUM(CASE WHEN depression_result = 'mild' THEN 1 ELSE 0 END) as depression_mild,
            SUM(CASE WHEN depression_result = 'severe' THEN 1 ELSE 0 END) as depression_severe,
            SUM(CASE WHEN safety_answer = 'yes' THEN 1 ELSE 0 END) as safety_yes,
            SUM(CASE WHEN safety_answer = 'no' THEN 1 ELSE 0 END) as safety_no,
            SUM(CASE WHEN safety_answer = 'yes' AND safety_status = 'safe' THEN 1 ELSE 0 END) as safety_safe,
            SUM(CASE WHEN safety_answer = 'yes' AND safety_status = 'unsafe' THEN 1 ELSE 0 END) as safety_unsafe
        ")->first();

        $total = (int) ($stats->total ?? 0);

        if ($total === 0) {
            return response()->json([
                'total' => 0,
                'anxiety' => ['none' => 0, 'none_pct' => 0, 'mild' => 0, 'mild_pct' => 0, 'severe' => 0, 'severe_pct' => 0],
                'depression' => ['none' => 0, 'none_pct' => 0, 'mild' => 0, 'mild_pct' => 0, 'severe' => 0, 'severe_pct' => 0],
                'safety' => ['yes' => 0, 'yes_pct' => 0, 'no' => 0, 'no_pct' => 0],
                'safety_status' => ['safe' => 0, 'safe_pct' => 0, 'unsafe' => 0, 'unsafe_pct' => 0]
            ]);
        }

        $calcPct = function ($count) use ($total) {
            return round(((int) $count / $total) * 100, 2);
        };

        return response()->json([
            'total' => $total,
            'anxiety' => [
                'none' => (int) $stats->anxiety_none, 'none_pct' => $calcPct($stats->anxiety_none),
                'mild' => (int) $stats->anxiety_mild, 'mild_pct' => $calcPct($stats->anxiety_mild),
                'severe' => (int) $stats->anxiety_severe, 'severe_pct' => $calcPct($stats->anxiety_severe),
            ],
            'depression' => [
                'none' => (int) $stats->depression_none, 'none_pct' => $calcPct($stats->depression_none),
                'mild' => (int) $stats->depression_mild, 'mild_pct' => $calcPct($stats->depression_mild),
                'severe' => (int) $stats->depression_severe, 'severe_pct' => $calcPct($stats->depression_severe),
            ],
            'safety' => [
                'yes' => (int) $stats->safety_yes, 'yes_pct' => $calcPct($stats->safety_yes),
                'no' => (int) $stats->safety_no, 'no_pct' => $calcPct($stats->safety_no),
            ],
            'safety_status' => [
                'safe' => (int) $stats->safety_safe, 'safe_pct' => $calcPct($stats->safety_safe),
                'unsafe' => (int) $stats->safety_unsafe, 'unsafe_pct' => $calcPct($stats->safety_unsafe),
            ]
        ]);
    }
}
