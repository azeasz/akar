<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Filter berdasarkan level
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('organisasi', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 10;
        $users = $query->paginate($perPage)->withQueryString();
        
        return view('admin.users.index', compact('users'));
    }
    
    /**
     * Menampilkan form untuk membuat user baru
     */
    public function create()
    {
        return view('admin.users.create');
    }
    
    /**
     * Menyimpan user baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'reason' => 'nullable|string',
            'alias_name' => 'nullable|string|max:255',
            'organisasi' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'social_media' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|max:2048',
            'level' => 'required|integer|in:1,2',
        ]);
        
        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $validated['profile_picture'] = $path;
        }
        
        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);
        
        // Log aktivitas
        activity_log('create', 'Admin membuat user baru: ' . $user->name);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dibuat!');
    }
    
    /**
     * Menampilkan detail user
     */
    public function show(User $user)
    {
        $user->load('checklists');
        return view('admin.users.show', compact('user'));
    }
    
    /**
     * Menampilkan form untuk edit user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }
    
    /**
     * Mengupdate data user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'reason' => 'nullable|string',
            'alias_name' => 'nullable|string|max:255',
            'organisasi' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'social_media' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|max:2048',
            'level' => 'required|integer|in:1,2',
        ]);
        
        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $validated['profile_picture'] = $path;
        }
        
        // Update password jika diisi
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        // Cek perubahan level user (promosi/demosi admin)
        $wasAdmin = $user->level === 2;
        $willBeAdmin = $validated['level'] === 2;
        
        $user->update($validated);
        
        // Log aktivitas
        if (!$wasAdmin && $willBeAdmin) {
            activity_log('promote', 'Admin mempromosikan user ' . $user->name . ' menjadi admin');
        } elseif ($wasAdmin && !$willBeAdmin) {
            activity_log('demote', 'Admin menurunkan admin ' . $user->name . ' menjadi user biasa');
        } else {
            activity_log('update', 'Admin mengupdate data user: ' . $user->name);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate!');
    }
    
    /**
     * Menghapus user
     */
    public function destroy(User $user)
    {
        // Catat info user sebelum dihapus
        $userName = $user->name;
        
        $user->delete();
        
        // Log aktivitas
        activity_log('delete', 'Admin menghapus user: ' . $userName);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus!');
    }
    
    /**
     * Export data user ke CSV
     */
    public function export(Request $request)
    {
        $query = User::query();
        
        // Apply filters like in index method
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('organisasi', 'like', "%{$search}%");
            });
        }
        
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $users = $query->orderBy($sortField, $sortDirection)->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=users-export-' . date('Y-m-d') . '.csv',
        ];
        
        $columns = ['ID', 'Username', 'Name', 'Email', 'Level', 'Organisasi', 'Phone', 'Created At'];
        
        $callback = function() use($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($users as $user) {
                $row = [
                    $user->id,
                    $user->username,
                    $user->name,
                    $user->email,
                    $user->level === 2 ? 'Admin' : 'User',
                    $user->organisasi ?? '-',
                    $user->phone_number ?? '-',
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        // Log aktivitas
        activity_log('export', 'Admin mengexport data users');
        
        return response()->stream($callback, 200, $headers);
    }
} 