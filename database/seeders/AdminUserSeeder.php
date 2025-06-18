<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Cek apakah admin sudah ada
            $adminExists = User::where('username', 'admin')->orWhere('email', 'admin@example.com')->exists();
            $adminInAdminTableExists = Admin::where('email', 'admin@example.com')->exists();
            
            if (!$adminExists) {
                // Membuat user level 2 (admin)
                DB::beginTransaction();
                
                $user = User::create([
                    'username' => 'admin',
                    'name' => 'Administrator',
                    'email' => 'admin@example.com',
                    'password' => Hash::make('password'),
                    'level' => 2, // level admin
                ]);

                // Secara otomatis membuat admin entry untuk user level 2 jika belum ada
                if (!$adminInAdminTableExists) {
                    Admin::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'password' => $user->password,
                        'user_id' => $user->id,
                    ]);
                }
                
                DB::commit();
                $this->command->info('Admin telah dibuat dengan username: admin dan password: password');
            } else {
                $this->command->info('Admin sudah ada, tidak perlu membuat yang baru');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
        }
    }
} 