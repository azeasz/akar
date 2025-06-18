<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Admin;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Jika user memiliki level 2 (admin), buat entri di tabel admin
        if ($user->level === 2) {
            Admin::create([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password, // Password sudah dienkripsi di model user
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Jika user level diupdate menjadi 2 (admin)
        if ($user->level === 2 && $user->isDirty('level')) {
            // Cek apakah sudah ada di tabel admin
            $admin = Admin::where('user_id', $user->id)->first();
            
            if (!$admin) {
                // Jika belum, buat baru
                Admin::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'user_id' => $user->id,
                ]);
            } else {
                // Jika sudah ada, update
                $admin->update([
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                ]);
            }
        }
        
        // Jika user level diubah dari 2 ke level lain
        if ($user->level !== 2 && $user->getOriginal('level') === 2) {
            // Hapus dari tabel admin
            Admin::where('user_id', $user->id)->delete();
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Jika user dihapus (soft delete), hapus juga data admin
        if ($user->level === 2) {
            Admin::where('user_id', $user->id)->delete();
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // Jika user dikembalikan dari soft delete, kembalikan juga data admin
        if ($user->level === 2) {
            Admin::withTrashed()->where('user_id', $user->id)->restore();
        }
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Jika user dihapus permanen, hapus juga data admin secara permanen
        if ($user->level === 2) {
            Admin::withTrashed()->where('user_id', $user->id)->forceDelete();
        }
    }
} 