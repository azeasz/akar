<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SettingsFaq;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Menampilkan daftar pengaturan
     */
    public function index(Request $request)
    {
        $query = SettingsFaq::query();
        
        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 10;
        $settings = $query->paginate($perPage)->withQueryString();
        
        return view('admin.settings.index', compact('settings'));
    }
    
    /**
     * Menampilkan form untuk membuat pengaturan baru
     */
    public function create()
    {
        return view('admin.settings.create');
    }
    
    /**
     * Menyimpan pengaturan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|integer|in:1,2,3,4,5',
        ]);
        
        $setting = SettingsFaq::create($validated);
        
        // Log aktivitas
        activity_log('create', 'Admin membuat ' . $this->getTypeName($setting->type) . ': ' . $setting->title);
        
        return redirect()->route('admin.settings.index')
            ->with('success', $this->getTypeName($setting->type) . ' berhasil dibuat!');
    }
    
    /**
     * Menampilkan detail pengaturan
     */
    public function show(SettingsFaq $setting)
    {
        return view('admin.settings.show', compact('setting'));
    }
    
    /**
     * Menampilkan form untuk edit pengaturan
     */
    public function edit(SettingsFaq $setting)
    {
        return view('admin.settings.edit', compact('setting'));
    }
    
    /**
     * Mengupdate data pengaturan
     */
    public function update(Request $request, SettingsFaq $setting)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|integer|in:1,2,3,4,5',
        ]);
        
        $setting->update($validated);
        
        // Log aktivitas
        activity_log('update', 'Admin mengupdate ' . $this->getTypeName($setting->type) . ': ' . $setting->title);
        
        return redirect()->route('admin.settings.index')
            ->with('success', $this->getTypeName($setting->type) . ' berhasil diupdate!');
    }
    
    /**
     * Menghapus pengaturan
     */
    public function destroy(SettingsFaq $setting)
    {
        $title = $setting->title;
        $type = $setting->type;
        
        $setting->delete();
        
        // Log aktivitas
        activity_log('delete', 'Admin menghapus ' . $this->getTypeName($type) . ': ' . $title);
        
        return redirect()->route('admin.settings.index')
            ->with('success', $this->getTypeName($type) . ' berhasil dihapus!');
    }
    
    /**
     * Helper untuk mendapatkan nama tipe pengaturan
     */
    private function getTypeName($type)
    {
        $types = [
            1 => 'Deskripsi',
            2 => 'Privacy Policy',
            3 => 'Terms & Conditions',
            4 => 'About',
            5 => 'FAQ',
        ];
        
        return $types[$type] ?? 'Pengaturan';
    }
} 