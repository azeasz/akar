<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checklist;
use App\Models\User;
use App\Models\ChecklistFauna;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Provide global statistics for the application.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function global(Request $request)
    {
        try {
            // 1. Total Laporan (Checklists)
            $totalReports = Checklist::count();

            // 2. Total Observer (Users who have submitted at least one checklist)
            $totalObservers = Checklist::distinct('user_id')->count('user_id');

            // 3. Total Spesies (Unique species observed)
            $totalSpecies = ChecklistFauna::distinct('fauna_id')->count('fauna_id');

            // 4. Total Individu (Sum of all quantities in checklist_fauna)
            $totalIndividuals = ChecklistFauna::sum('jumlah');

            // 5. Data for chart: checklists per day for a variable period
            $period = $request->input('period', 7); // Default to 7 days
            if (!in_array($period, [7, 30, 90])) {
                $period = 7;
            }

            $chartData = Checklist::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_reports' => $totalReports,
                    'total_observers' => $totalObservers,
                    'total_species' => $totalSpecies,
                    'total_individuals' => $totalIndividuals,
                    'chart_data' => $chartData,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data statistik.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
