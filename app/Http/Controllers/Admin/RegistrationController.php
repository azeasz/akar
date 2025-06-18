<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Menampilkan daftar pendaftaran baru
     * (user yang belum diapprove, implementasi sederhana dengan status email_verified_at = null)
     */
    public function index(Request $request)
    {
        $query = User::whereNull('email_verified_at')
                     ->where('status', 0);
        
        // Search
        if ($request->has('search') && $request->search) {
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
        $registrations = $query->paginate($perPage)->withQueryString();
        
        return view('admin.registrations.index', compact('registrations'));
    }
    
    /**
     * Menampilkan detail pendaftaran
     */
    public function show(User $registration)
    {
        return view('admin.registrations.show', compact('registration'));
    }
    
    /**
     * Mengapprove pendaftaran
     */
    public function approve(User $registration)
    {
        $registration->update([
            'email_verified_at' => now(),
            'status' => 1,
        ]);
        
        // Log aktivitas
        activity_log('approve', 'Admin menyetujui pendaftaran user: ' . $registration->name);
        
        return redirect()->route('admin.registrations.index')
            ->with('success', 'Pendaftaran berhasil disetujui!');
    }
    
    /**
     * Menolak pendaftaran (hapus user)
     */
    public function reject(User $registration)
    {
        $userName = $registration->name;
        
        $registration->delete();
        
        // Log aktivitas
        activity_log('reject', 'Admin menolak pendaftaran user: ' . $userName);
        
        return redirect()->route('admin.registrations.index')
            ->with('success', 'Pendaftaran berhasil ditolak!');
    }
} 