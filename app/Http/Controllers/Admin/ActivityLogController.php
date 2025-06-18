<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan daftar log aktivitas
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');
        
        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
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
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 10;
        $logs = $query->paginate($perPage)->withQueryString();
        
        // Data untuk filter
        $users = User::select('id', 'name')->orderBy('name')->get();
        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        
        return view('admin.logs.index', compact('logs', 'users', 'actions'));
    }
    
    /**
     * Menampilkan detail log
     */
    public function show(ActivityLog $log)
    {
        $log->load('user');
        return view('admin.logs.show', compact('log'));
    }
    
    /**
     * Export data log aktivitas ke CSV
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with('user');
        
        // Apply the same filters as index
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }
        
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }
        
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $logs = $query->orderBy($sortField, $sortDirection)->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=activity-logs-export-' . date('Y-m-d') . '.csv',
        ];
        
        $columns = ['ID', 'User', 'Action', 'Description', 'Date'];
        
        $callback = function() use($logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($logs as $log) {
                $row = [
                    $log->id,
                    $log->user ? $log->user->name : 'System',
                    $log->action,
                    $log->description,
                    $log->created_at->format('Y-m-d H:i:s'),
                ];
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        // Log aktivitas
        activity_log('export', 'Admin mengexport data activity logs');
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Menghapus log aktivitas
     */
    public function destroy(ActivityLog $log)
    {
        $log->delete();
        
        // Log aktivitas
        activity_log('delete', 'Admin menghapus activity log: ' . $log->id);
        
        return redirect()->route('admin.logs.index')
            ->with('success', 'Log aktivitas berhasil dihapus!');
    }
} 