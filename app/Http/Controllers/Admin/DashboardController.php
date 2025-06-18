<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Checklist;
use App\Models\ChecklistFauna;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard admin
     */
    public function index(Request $request)
    {
        // Filter data berdasarkan tanggal jika ada
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        
        // Statistik dasar
        $stats = [
            'users' => User::count(),
            'checklists' => Checklist::count(),
            'faunas' => ChecklistFauna::count(),
            'new_users' => User::whereBetween('created_at', [$startDate, $endDate])->count()
        ];
        
        // Data untuk chart pengguna baru per hari
        $newUsersChart = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'x' => $item->date,
                    'y' => $item->total
                ];
            });
            
        // Data untuk chart checklist per kategori
        $checklistsPerCategory = Checklist::select(
                'type as category',
                DB::raw('count(*) as total')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('type')
            ->get();
            
        // Checklist terbaru
        $latestChecklists = Checklist::with(['user', 'faunas'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        // Data untuk map (semua lokasi checklist)
        $mapData = Checklist::select('id', 'nama_lokasi', 'latitude', 'longitude', 'type')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
            
        // Log aktivitas terbaru
        $activityLogs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
            
        // Data untuk leaderboard (user dengan checklist terbanyak)
        $leaderboard = User::withCount('checklists')
            ->orderBy('checklists_count', 'desc')
            ->take(10)
            ->get();
        
        return view('admin.dashboard', compact(
            'stats',
            'newUsersChart',
            'checklistsPerCategory',
            'latestChecklists',
            'mapData',
            'activityLogs',
            'leaderboard',
            'startDate',
            'endDate'
        ));
    }
} 