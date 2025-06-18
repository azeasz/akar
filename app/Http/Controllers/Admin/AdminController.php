<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Menampilkan daftar admin
     */
    public function index(Request $request)
    {
        $query = Admin::query();
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 10;
        $admins = $query->paginate($perPage)->withQueryString();
        
        return view('admin.admins.index', compact('admins'));
    }
    
    /**
     * Menampilkan form untuk membuat admin baru
     */
    public function create()
    {
        return view('admin.admins.create');
    }
    
    /**
     * Menyimpan admin baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        
        $admin = Admin::create($validated);
        
        // Log aktivitas
        activity_log('create', 'Admin membuat admin baru: ' . $admin->name);
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil dibuat!');
    }
    
    /**
     * Menampilkan detail admin
     */
    public function show(Admin $admin)
    {
        return view('admin.admins.show', compact('admin'));
    }
    
    /**
     * Menampilkan form untuk edit admin
     */
    public function edit(Admin $admin)
    {
        return view('admin.admins.edit', compact('admin'));
    }
    
    /**
     * Mengupdate data admin
     */
    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'password' => 'nullable|string|min:8',
        ]);
        
        // Update password jika diisi
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        $admin->update($validated);
        
        // Log aktivitas
        activity_log('update', 'Admin mengupdate data admin: ' . $admin->name);
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil diupdate!');
    }
    
    /**
     * Menghapus admin
     */
    public function destroy(Admin $admin)
    {
        // Catat info admin sebelum dihapus
        $adminName = $admin->name;
        
        $admin->delete();
        
        // Log aktivitas
        activity_log('delete', 'Admin menghapus admin: ' . $adminName);
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil dihapus!');
    }
    
    /**
     * Export data admin ke CSV
     */
    public function export(Request $request)
    {
        $query = Admin::query();
        
        // Apply filters like in index method
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $admins = $query->orderBy($sortField, $sortDirection)->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=admins-export-' . date('Y-m-d') . '.csv',
        ];
        
        $columns = ['ID', 'Name', 'Email', 'Related User', 'Created At'];
        
        $callback = function() use($admins, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($admins as $admin) {
                $relatedUser = $admin->user ? $admin->user->name : '-';
                
                $row = [
                    $admin->id,
                    $admin->name,
                    $admin->email,
                    $relatedUser,
                    $admin->created_at->format('Y-m-d H:i:s'),
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        // Log aktivitas
        activity_log('export', 'Admin mengexport data admins');
        
        return response()->stream($callback, 200, $headers);
    }
} 