<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class MigrateOldDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai proses migrasi data lama ke struktur baru...');
        
        // Pastikan tabel-tabel baru sudah dibuat
        if (!Schema::hasTable('users') || !Schema::hasTable('checklists') || !Schema::hasTable('checklist_faunas')) {
            $this->command->error('Tabel-tabel baru belum dibuat. Jalankan migrasi terlebih dahulu.');
            return;
        }
        
        // Cek tabel-tabel lama
        if (!Schema::hasTable('members')) {
            $this->command->warn('Tabel members tidak ditemukan. Migrasi user dilewati.');
        } else {
            $this->migrateUsers();
        }
        
        if (!Schema::hasTable('checklists_olds') || !Schema::hasTable('checklist_fauna_olds')) {
            $this->command->warn('Tabel checklists_olds atau checklist_fauna_olds tidak ditemukan. Migrasi checklist dilewati.');
        } else {
            $this->migrateChecklists();
        }
        
        $this->command->info('Proses migrasi data selesai!');
    }

    /**
     * Migrasi data member ke users
     */
    private function migrateUsers()
    {
        $this->command->info('Memulai migrasi data member ke users...');
        
        try {
            $members = DB::table('members')->get();
            $count = 0;
            
            $this->command->info('Ditemukan ' . $members->count() . ' members yang akan dimigrasi...');
            $bar = $this->command->getOutput()->createProgressBar($members->count());
            
            foreach ($members as $member) {
                // Cek apakah user sudah ada dengan email yang sama
                $userExists = DB::table('users')
                    ->where('email', $member->email)
                    ->exists();
                
                if (!$userExists) {
                    DB::table('users')->insert([
                        'id' => $member->id,
                        'username' => $member->username ?? ($member->email ? explode('@', $member->email)[0] : 'user' . $member->id),
                        'name' => $member->name,
                        'firstname' => $member->firstname,
                        'lastname' => $member->lastname,
                        'email' => $member->email,
                        'email_verified_at' => $member->email_verified_at,
                        'password' => $member->password,
                        'reason' => $member->reason,
                        'alias_name' => $member->alias_name,
                        'organisasi' => $member->organisasi,
                        'phone_number' => $member->phone,
                        'social_media' => $member->sosial_media,
                        'profile_picture' => $member->avatar,
                        'avatar' => $member->avatar,
                        'domisili' => $member->domisili,
                        'pengamatan_satwa' => $member->pengamatan_satwa,
                        'phone' => $member->phone,
                        'status' => $member->status,
                        'level' => 1, // semua member menjadi user biasa
                        'remember_token' => $member->remember_token,
                        'created_at' => $member->created_at,
                        'updated_at' => $member->updated_at,
                        'deleted_at' => $member->deleted_at,
                    ]);
                    
                    $count++;
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->command->newLine();
            $this->command->info("Berhasil memindahkan $count member ke tabel users");
            
        } catch (\Exception $e) {
            $this->command->error('Error saat migrasi data member: ' . $e->getMessage());
            Log::error('Error saat migrasi data member: ' . $e->getMessage());
        }
    }

    /**
     * Migrasi data checklist dan checklist_fauna
     */
    private function migrateChecklists()
    {
        $this->command->info('Memulai migrasi data checklist...');
        
        try {
            $oldChecklists = DB::table('checklists_olds')->get();
            $this->command->info('Ditemukan ' . $oldChecklists->count() . ' checklists yang akan dimigrasi...');
            
            $bar = $this->command->getOutput()->createProgressBar($oldChecklists->count());
            $count = 0;
            $faunaCount = 0;
            
            foreach ($oldChecklists as $old) {
                // Konversi member_id menjadi user_id
                $userId = $old->member_id;
                
                // Cek apakah user ini ada di tabel users
                $userExists = DB::table('users')->where('id', $userId)->exists();
                if (!$userExists) {
                    $this->command->line("  <fg=yellow>Warning:</> User dengan id $userId tidak ditemukan, checklist id: {$old->id} dilewati");
                    $bar->advance();
                    continue;
                }
                
                // Tentukan tipe checklist berdasarkan category_id
                $type = $this->getCategoryType($old->category_id);
                
                // Buat checklist baru
                $newChecklistId = DB::table('checklists')->insertGetId([
                    'user_id' => $userId,
                    'type' => $type,
                    'is_completed' => $old->confirmed ? true : false,
                    'status' => $old->confirmed ? 'published' : 'draft',
                    'tanggal' => $old->record_at,
                    'nama_lokasi' => $old->nama_lokasi ?? $old->name,
                    'latitude' => $old->latitude,
                    'longitude' => $old->longitude,
                    'pemilik' => $old->nama_pemilik,
                    'catatan' => $old->notes,
                    
                    // Kolom-kolom untuk kompatibilitas
                    'app_id' => $old->app_id,
                    'category_id' => $old->category_id,
                    'name' => $old->name,
                    'nama_event' => $old->nama_event,
                    'nama_arena' => $old->nama_arena,
                    'total_hunter' => $old->total_hunter,
                    'teknik_berburu' => $old->teknik_berburu,
                    'status_tangkapan' => $old->status_tangkapan,
                    'category_tempat_id' => $old->category_tempat_id,
                    'nama_toko' => $old->nama_toko,
                    'nama_penjual' => $old->nama_penjual,
                    'domisili_penjual' => $old->domisili_penjual,
                    'profesi_penjual_id' => $old->profesi_penjual_id,
                    'nama_pemilik' => $old->nama_pemilik,
                    'domisili_pemilik' => $old->domisili_pemilik,
                    'profesi_pemilik_id' => $old->profesi_pemilik_id,
                    'confirmed' => $old->confirmed,
                    'created_at' => $old->created_at,
                    'updated_at' => $old->updated_at,
                    'deleted_at' => $old->deleted_at,
                ]);
                
                $count++;
                
                // Migrasi data fauna untuk checklist ini
                $oldFaunas = DB::table('checklist_fauna_olds')
                    ->where('checklist_id', $old->id)
                    ->get();
                
                foreach ($oldFaunas as $fauna) {
                    // Dapatkan nama spesies dari fauna_id jika tersedia
                    $namaSpesies = $this->getFaunaName($fauna->fauna_id);
                    
                    // Konversi status dan gender
                    $statusBuruan = $fauna->kondisi == 1 ? 'hidup' : ($fauna->kondisi == 2 ? 'mati' : null);
                    $genderText = $this->getGenderText($fauna->gender);
                    
                    DB::table('checklist_faunas')->insert([
                        'checklist_id' => $newChecklistId,
                        'nama_spesies' => $namaSpesies,
                        'jumlah' => $fauna->total,
                        'gender' => $genderText,
                        'cincin' => $fauna->cincin ? true : false,
                        'tagging' => false, // default ke false karena tidak ada di struktur lama
                        'catatan' => $fauna->notes,
                        'status_buruan' => $statusBuruan,
                        'alat_buru' => null, // tidak ada di struktur lama
                        
                        // Kolom-kolom untuk kompatibilitas
                        'fauna_id' => $fauna->fauna_id,
                        'asal' => $fauna->asal,
                        'harga' => $fauna->harga,
                        'kondisi' => $fauna->kondisi,
                        'ijin' => $fauna->ijin,
                        'created_at' => $fauna->created_at,
                        'updated_at' => $fauna->updated_at,
                        'deleted_at' => $fauna->deleted_at,
                    ]);
                    
                    $faunaCount++;
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->command->newLine();
            $this->command->info("Berhasil memindahkan $count checklist dan $faunaCount fauna ke tabel baru");
            
        } catch (\Exception $e) {
            $this->command->error('Error saat migrasi data checklist: ' . $e->getMessage());
            Log::error('Error saat migrasi data checklist: ' . $e->getMessage());
        }
    }
    
    /**
     * Mendapatkan tipe checklist berdasarkan category_id
     */
    private function getCategoryType($categoryId)
    {
        // Mapping category_id ke tipe checklist
        // Ganti ini sesuai dengan kategori yang ada di sistem lama
        $categoryMap = [
            1 => 'pemeliharaan',
            2 => 'penangkaran',
            3 => 'perburuan',
            4 => 'lomba', 
            5 => 'perdagangan',
            // tambahkan sesuai kebutuhan
        ];
        
        return $categoryMap[$categoryId] ?? 'lainnya';
    }
    
    /**
     * Mendapatkan nama fauna dari fauna_id
     */
    private function getFaunaName($faunaId)
    {
        // Coba cari nama fauna di tabel fauna jika ada
        if (Schema::hasTable('faunas')) {
            $fauna = DB::table('faunas')->where('id', $faunaId)->first();
            if ($fauna && isset($fauna->name)) {
                return $fauna->name;
            }
        }
        
        // Jika tidak ditemukan, gunakan placeholder
        return "Fauna #" . $faunaId;
    }
    
    /**
     * Mendapatkan teks gender dari kode
     */
    private function getGenderText($genderCode)
    {
        switch ($genderCode) {
            case 1:
                return 'jantan';
            case 2:
                return 'betina';
            default:
                return null;
        }
    }
}; 