<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class UpdateChecklistFaunasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai update nama_spesies dan nama_latin di checklist_faunas...');
        
        // Ambil semua checklist_faunas yang memiliki fauna_id
        $checklistFaunas = DB::table('checklist_faunas')
            ->whereNotNull('fauna_id')
            ->where(function($query) {
                $query->whereNull('nama_spesies')
                      ->orWhereNull('nama_latin')
                      ->orWhere('nama_spesies', 'LIKE', 'Fauna#%')
                      ->orWhere('nama_latin', 'LIKE', 'Fauna#%');
            })
            ->get();
            
        $this->command->info('Ditemukan ' . $checklistFaunas->count() . ' data checklist fauna untuk diupdate.');
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($checklistFaunas as $fauna) {
            try {
                // Coba dapatkan data taxa dari database second (amaturalist)
                $taxa = DB::connection('second')
                    ->table('taxas')
                    ->where('id', $fauna->fauna_id)
                    ->first();
                
                if ($taxa) {
                    // Update nama_spesies dan nama_latin
                    $updateData = [];
                    
                    // Update nama_spesies hanya jika null atau berformat 'Fauna#{id}'
                    $needUpdateNamaSpesies = is_null($fauna->nama_spesies) || 
                                            preg_match('/^Fauna#\d+$/', $fauna->nama_spesies);
                    
                    if ($needUpdateNamaSpesies) {
                        // Jika ada cname_species, gunakan untuk nama_spesies
                        if (!empty($taxa->cname_species)) {
                            $updateData['nama_spesies'] = $taxa->cname_species;
                        } 
                        // Jika tidak ada cname_species tapi ada accepted_scientific_name, gunakan untuk nama_spesies
                        elseif (!empty($taxa->accepted_scientific_name)) {
                            $updateData['nama_spesies'] = $taxa->accepted_scientific_name;
                        }
                    }
                    
                    // Update nama_latin hanya jika null atau berformat 'Fauna#{id}'
                    $needUpdateNamaLatin = is_null($fauna->nama_latin) || 
                                          preg_match('/^Fauna#\d+$/', $fauna->nama_latin);
                    
                    if ($needUpdateNamaLatin && !empty($taxa->accepted_scientific_name)) {
                        $updateData['nama_latin'] = $taxa->accepted_scientific_name;
                    }
                    
                    // Jika ada data yang akan diupdate
                    if (!empty($updateData)) {
                        DB::table('checklist_faunas')
                            ->where('id', $fauna->id)
                            ->update($updateData);
                            
                        $updated++;
                        $this->command->info("Updated ID {$fauna->id}: " . json_encode($updateData));
                    } else {
                        $skipped++;
                    }
                } else {
                    // Jika taxa tidak ditemukan, catat sebagai skipped
                    $skipped++;
                }
            } catch (\Exception $e) {
                Log::error('Error saat mengupdate checklist_fauna ID: ' . $fauna->id, [
                    'error' => $e->getMessage(),
                    'fauna_id' => $fauna->fauna_id
                ]);
                $errors++;
            }
        }
        
        $this->command->info('Proses update selesai:');
        $this->command->info('- ' . $updated . ' data berhasil diupdate');
        $this->command->info('- ' . $skipped . ' data dilewati (tidak ada data taxa atau data sudah lengkap)');
        $this->command->info('- ' . $errors . ' data error saat diupdate');
    }
} 