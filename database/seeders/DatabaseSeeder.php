<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            AdminUserSeeder::class,
            MigrateOldDataSeeder::class, // Uncomment jika ingin migrasi data dari tabel lama
            UpdateVerifiedUsersSeeder::class, // Seeder untuk mengupdate user yang status=1 tapi email_verified_at=NULL
            UpdateChecklistFaunasSeeder::class, // Seeder untuk mengupdate nama_spesies dan nama_latin di checklist_faunas
        ]);
    }
}
