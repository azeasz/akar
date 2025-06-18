<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Menampilkan daftar laporan
     */
    public function index(Request $request)
    {
        $query = Report::with('user');
        
        // Filter by resolution status
        if ($request->has('is_resolved')) {
            $query->where('is_resolved', $request->is_resolved);
        }
        
        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('masalah', 'like', "%{$search}%");
        }
        
        // Sorting
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 10;
        $reports = $query->paginate($perPage)->withQueryString();
        
        // Data untuk filter
        $users = User::select('id', 'name')->orderBy('name')->get();
        
        return view('admin.reports.index', compact('reports', 'users'));
    }
    
    /**
     * Menampilkan detail laporan
     */
    public function show(Report $report)
    {
        $report->load('user');
        return view('admin.reports.show', compact('report'));
    }
    
    /**
     * Menandai laporan sebagai resolved
     */
    public function markResolved(Report $report)
    {
        $report->update(['is_resolved' => true]);
        
        // Log aktivitas
        activity_log('update', 'Admin menandai laporan #' . $report->id . ' sebagai selesai');
        
        return redirect()->back()->with('success', 'Laporan berhasil ditandai selesai!');
    }
    
    /**
     * Menandai laporan sebagai unresolved
     */
    public function markUnresolved(Report $report)
    {
        $report->update(['is_resolved' => false]);
        
        // Log aktivitas
        activity_log('update', 'Admin menandai laporan #' . $report->id . ' sebagai belum selesai');
        
        return redirect()->back()->with('success', 'Laporan berhasil ditandai belum selesai!');
    }
    
    /**
     * Menghapus laporan
     */
    public function destroy(Report $report)
    {
        $reportId = $report->id;
        
        $report->delete();
        
        // Log aktivitas
        activity_log('delete', 'Admin menghapus laporan #' . $reportId);
        
        return redirect()->route('admin.reports.index')
            ->with('success', 'Laporan berhasil dihapus!');
    }
    
    /**
     * Export data laporan ke CSV
     */
    public function export(Request $request)
    {
        $query = Report::with('user');
        
        // Apply the same filters as index
        if ($request->has('is_resolved')) {
            $query->where('is_resolved', $request->is_resolved);
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('masalah', 'like', "%{$search}%");
        }
        
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $reports = $query->orderBy($sortField, $sortDirection)->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=reports-export-' . date('Y-m-d') . '.csv',
        ];
        
        $columns = ['ID', 'User', 'Masalah', 'Status', 'Tanggal'];
        
        $callback = function() use($reports, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($reports as $report) {
                $row = [
                    $report->id,
                    $report->user ? $report->user->name : 'Unknown',
                    $report->masalah,
                    $report->is_resolved ? 'Selesai' : 'Belum Selesai',
                    $report->created_at->format('Y-m-d H:i:s'),
                ];
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        // Log aktivitas
        activity_log('export', 'Admin mengexport data laporan');
        
        return response()->stream($callback, 200, $headers);
    }
} 