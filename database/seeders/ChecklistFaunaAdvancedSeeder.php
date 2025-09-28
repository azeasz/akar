<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChecklistFaunaAdvancedSeeder extends Seeder
{
    private $logData = [];
    private $logPath;
    private $csvPath;
    private $txtPath;
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Setup log files dengan timestamp
        $timestamp = now()->format('Y-m-d_H-i-s');
        $this->logPath = "logs/checklist_fauna_advanced_{$timestamp}";
        $this->csvPath = "{$this->logPath}.csv";
        $this->txtPath = "{$this->logPath}.txt";
        
        $this->command->info('Memulai proses update checklist fauna dengan matching canggih...');
        $this->writeLog("=== MULAI PROSES UPDATE CHECKLIST FAUNA ADVANCED ===", 'INFO');
        $this->writeLog("Timestamp: " . now()->toDateTimeString(), 'INFO');
        
        // Tahap 1: Update nama_spesies dan nama_latin dari tabel faunas
        $this->tahapPertama();
        
        // Tahap 2: Update fauna_id berdasarkan matching dengan tabel taxas (dengan fallback)
        $this->tahapKedua();
        
        // Generate laporan akhir
        $this->generateFinalReport();
        
        $this->command->info('Proses update checklist fauna selesai!');
        $this->command->info("Log CSV tersimpan di: storage/app/{$this->csvPath}");
        $this->command->info("Log TXT tersimpan di: storage/app/{$this->txtPath}");
    }
    
    /**
     * Tahap 1: Cocokkan faunas.id dengan checklist_faunas.fauna_id
     * Update checklist_faunas.nama_spesies dengan faunas.nameId
     * Update checklist_faunas.nama_latin dengan faunas.nameLat
     */
    private function tahapPertama(): void
    {
        $this->command->info('Tahap 1: Mengupdate nama_spesies dan nama_latin dari tabel faunas...');
        $this->writeLog("=== TAHAP 1: UPDATE DARI TABEL FAUNAS ===", 'INFO');
        
        try {
            // Ambil semua checklist_faunas untuk analisis detail
            $allChecklistFaunas = DB::table('checklist_faunas')->get(['id', 'fauna_id', 'nama_spesies', 'nama_latin']);
            $totalRecords = $allChecklistFaunas->count();
            
            $this->writeLog("Total checklist_faunas records: {$totalRecords}", 'INFO');
            
            $updated = 0;
            $skipped_no_fauna_id = 0;
            $skipped_fauna_not_found = 0;
            $skipped_no_nameId = 0;
            $errors = 0;
            
            foreach ($allChecklistFaunas as $checklistFauna) {
                try {
                    // Log detail record yang diproses
                    $logEntry = [
                        'tahap' => 1,
                        'checklist_fauna_id' => $checklistFauna->id,
                        'fauna_id_lama' => $checklistFauna->fauna_id,
                        'nama_spesies_lama' => $checklistFauna->nama_spesies,
                        'nama_latin_lama' => $checklistFauna->nama_latin,
                        'nama_latin_dicari' => '',
                        'status' => '',
                        'keterangan' => '',
                        'matching_method' => '',
                        'taxa_id_ditemukan' => null,
                        'fauna_id_baru' => null,
                        'nama_spesies_baru' => null,
                        'nama_latin_baru' => null,
                        'timestamp' => now()->toDateTimeString()
                    ];
                    
                    // Cek apakah ada fauna_id
                    if (empty($checklistFauna->fauna_id)) {
                        $skipped_no_fauna_id++;
                        $logEntry['status'] = 'SKIPPED';
                        $logEntry['keterangan'] = 'Tidak ada fauna_id';
                        $this->logData[] = $logEntry;
                        continue;
                    }
                    
                    // Cari data fauna
                    $fauna = DB::table('faunas')
                        ->where('id', $checklistFauna->fauna_id)
                        ->first(['id', 'nameId', 'nameLat']);
                    
                    if (!$fauna) {
                        $skipped_fauna_not_found++;
                        $logEntry['status'] = 'SKIPPED';
                        $logEntry['keterangan'] = "Fauna dengan ID {$checklistFauna->fauna_id} tidak ditemukan";
                        $this->logData[] = $logEntry;
                        continue;
                    }
                    
                    // Cek apakah fauna memiliki nameId
                    if (empty($fauna->nameId)) {
                        $skipped_no_nameId++;
                        $logEntry['status'] = 'SKIPPED';
                        $logEntry['keterangan'] = "Fauna ID {$fauna->id} tidak memiliki nameId";
                        $this->logData[] = $logEntry;
                        continue;
                    }
                    
                    // Update record
                    $updateResult = DB::table('checklist_faunas')
                        ->where('id', $checklistFauna->id)
                        ->update([
                            'nama_spesies' => $fauna->nameId,
                            'nama_latin' => $fauna->nameLat,
                            'updated_at' => now()
                        ]);
                    
                    if ($updateResult) {
                        $updated++;
                        $logEntry['status'] = 'SUCCESS';
                        $logEntry['keterangan'] = 'Berhasil diupdate dari tabel faunas';
                        $logEntry['nama_spesies_baru'] = $fauna->nameId;
                        $logEntry['nama_latin_baru'] = $fauna->nameLat;
                    } else {
                        $logEntry['status'] = 'FAILED';
                        $logEntry['keterangan'] = 'Update gagal (tidak ada perubahan)';
                    }
                    
                    $this->logData[] = $logEntry;
                    
                } catch (\Exception $e) {
                    $errors++;
                    $logEntry['status'] = 'ERROR';
                    $logEntry['keterangan'] = 'Error: ' . $e->getMessage();
                    $this->logData[] = $logEntry;
                }
            }
            
            // Summary tahap 1
            $summary = [
                "Total records diproses: {$totalRecords}",
                "Berhasil diupdate: {$updated}",
                "Dilewati (tidak ada fauna_id): {$skipped_no_fauna_id}",
                "Dilewati (fauna tidak ditemukan): {$skipped_fauna_not_found}",
                "Dilewati (fauna tidak ada nameId): {$skipped_no_nameId}",
                "Error: {$errors}"
            ];
            
            foreach ($summary as $line) {
                $this->command->info($line);
                $this->writeLog($line, 'INFO');
            }
            
        } catch (\Exception $e) {
            $this->command->error("Error pada Tahap 1: " . $e->getMessage());
            $this->writeLog("Error pada Tahap 1: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Tahap 2: Cocokkan checklist_faunas.nama_latin dengan taxas menggunakan multiple fallback
     * Update checklist_faunas.fauna_id dengan taxas.id
     */
    private function tahapKedua(): void
    {
        $this->command->info('Tahap 2: Mengupdate fauna_id dengan matching canggih dari tabel taxas...');
        $this->writeLog("=== TAHAP 2: UPDATE DARI TABEL TAXAS (ADVANCED MATCHING) ===", 'INFO');
        
        try {
            // Ambil semua checklist_faunas untuk analisis detail
            $allChecklistFaunas = DB::table('checklist_faunas')
                ->get(['id', 'nama_latin', 'fauna_id']);
            
            $totalRecords = $allChecklistFaunas->count();
            $this->writeLog("Total checklist_faunas records untuk tahap 2: {$totalRecords}", 'INFO');
            
            $updated = 0;
            $skipped_no_nama_latin = 0;
            $skipped_no_match = 0;
            $errors = 0;
            $processed = 0;
            
            // Statistik matching method
            $matchingStats = [
                'species_exact' => 0,
                'species_clean_sp' => 0,
                'scientific_name' => 0,
                'genus_match' => 0,
                'cname_species_nameId' => 0,
                'cname_two_nameEn' => 0
            ];
            
            foreach ($allChecklistFaunas as $checklistFauna) {
                try {
                    $processed++;
                    
                    // Log detail record yang diproses
                    $logEntry = [
                        'tahap' => 2,
                        'checklist_fauna_id' => $checklistFauna->id,
                        'fauna_id_lama' => $checklistFauna->fauna_id,
                        'nama_spesies_lama' => '',
                        'nama_latin_lama' => '',
                        'nama_latin_dicari' => $checklistFauna->nama_latin,
                        'status' => '',
                        'keterangan' => '',
                        'matching_method' => '',
                        'taxa_id_ditemukan' => null,
                        'fauna_id_baru' => null,
                        'nama_spesies_baru' => '',
                        'nama_latin_baru' => '',
                        'timestamp' => now()->toDateTimeString()
                    ];
                    
                    // Cek apakah ada nama_latin
                    if (empty($checklistFauna->nama_latin)) {
                        $skipped_no_nama_latin++;
                        $logEntry['status'] = 'SKIPPED';
                        $logEntry['keterangan'] = 'Tidak ada nama_latin';
                        $this->logData[] = $logEntry;
                        continue;
                    }
                    
                    // Coba berbagai metode matching
                    $matchResult = $this->findTaxaMatch($checklistFauna->nama_latin);
                    
                    if (!$matchResult) {
                        $skipped_no_match++;
                        $logEntry['status'] = 'SKIPPED';
                        $logEntry['keterangan'] = "Tidak ditemukan match untuk '{$checklistFauna->nama_latin}' dengan semua metode";
                        $this->logData[] = $logEntry;
                        continue;
                    }
                    
                    // Update fauna_id dengan taxa.id
                    $updateResult = DB::table('checklist_faunas')
                        ->where('id', $checklistFauna->id)
                        ->update([
                            'fauna_id' => $matchResult['taxa_id'],
                            'updated_at' => now()
                        ]);
                    
                    if ($updateResult) {
                        $updated++;
                        $matchingStats[$matchResult['method']]++;
                        $logEntry['status'] = 'SUCCESS';
                        $logEntry['keterangan'] = "Berhasil diupdate dengan taxa ID {$matchResult['taxa_id']}";
                        $logEntry['matching_method'] = $matchResult['method'];
                        $logEntry['taxa_id_ditemukan'] = $matchResult['taxa_id'];
                        $logEntry['fauna_id_baru'] = $matchResult['taxa_id'];
                    } else {
                        $logEntry['status'] = 'FAILED';
                        $logEntry['keterangan'] = 'Update gagal (tidak ada perubahan)';
                        $logEntry['matching_method'] = $matchResult['method'];
                        $logEntry['taxa_id_ditemukan'] = $matchResult['taxa_id'];
                    }
                    
                    $this->logData[] = $logEntry;
                    
                    // Progress info setiap 100 record
                    if ($processed % 100 == 0) {
                        $progress = "Progress: {$processed}/{$totalRecords} diproses, {$updated} berhasil diupdate";
                        $this->command->info($progress);
                        $this->writeLog($progress, 'INFO');
                    }
                    
                } catch (\Exception $e) {
                    $errors++;
                    $logEntry['status'] = 'ERROR';
                    $logEntry['keterangan'] = 'Error: ' . $e->getMessage();
                    $this->logData[] = $logEntry;
                }
            }
            
            // Summary tahap 2
            $summary = [
                "Total records diproses: {$totalRecords}",
                "Berhasil diupdate: {$updated}",
                "Dilewati (tidak ada nama_latin): {$skipped_no_nama_latin}",
                "Dilewati (tidak ada match): {$skipped_no_match}",
                "Error: {$errors}",
                "",
                "STATISTIK MATCHING METHOD:",
                "- Species exact match: {$matchingStats['species_exact']}",
                "- Species (sp/sp. dibersihkan): {$matchingStats['species_clean_sp']}",
                "- Scientific name match: {$matchingStats['scientific_name']}",
                "- Genus match (untuk sp/sp.): {$matchingStats['genus_match']}",
                "- Cname_species vs nameId: {$matchingStats['cname_species_nameId']}",
                "- Cname_two vs nameEn: {$matchingStats['cname_two_nameEn']}"
            ];
            
            foreach ($summary as $line) {
                $this->command->info($line);
                $this->writeLog($line, 'INFO');
            }
            
        } catch (\Exception $e) {
            $this->command->error("Error pada Tahap 2: " . $e->getMessage());
            $this->writeLog("Error pada Tahap 2: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Cari match di tabel taxas dengan berbagai metode fallback
     */
    private function findTaxaMatch($namaLatin): ?array
    {
        // Method 1: Exact match dengan kolom species
        $taxa = DB::table('taxas')
            ->where('species', $namaLatin)
            ->first(['id', 'species']);
            
        if ($taxa) {
            return [
                'taxa_id' => $taxa->id,
                'method' => 'species_exact'
            ];
        }
        
        // Method 2: Clean sp/sp. dari nama latin dan coba match lagi
        $cleanedNamaLatin = $this->cleanSpeciesName($namaLatin);
        if ($cleanedNamaLatin !== $namaLatin) {
            $taxa = DB::table('taxas')
                ->where('species', $cleanedNamaLatin)
                ->first(['id', 'species']);
                
            if ($taxa) {
                return [
                    'taxa_id' => $taxa->id,
                    'method' => 'species_clean_sp'
                ];
            }
        }
        
        // Method 3: Match dengan scientific_name
        $taxa = DB::table('taxas')
            ->where('scientific_name', $namaLatin)
            ->first(['id', 'scientific_name']);
            
        if ($taxa) {
            return [
                'taxa_id' => $taxa->id,
                'method' => 'scientific_name'
            ];
        }
        
        // Method 4: Match berdasarkan genus saja (untuk kasus sp/sp.)
        $genus = $this->extractGenus($namaLatin);
        if ($genus) {
            $taxa = DB::table('taxas')
                ->where('genus', $genus)
                ->first(['id', 'genus']);
                
            if ($taxa) {
                return [
                    'taxa_id' => $taxa->id,
                    'method' => 'genus_match'
                ];
            }
        }
        
        // Method 5: Match cname_species dengan nameId dari faunas
        $fauna = DB::table('faunas')
            ->where('nameLat', $namaLatin)
            ->first(['nameId']);
            
        if ($fauna && !empty($fauna->nameId)) {
            $taxa = DB::table('taxas')
                ->where('cname_species', $fauna->nameId)
                ->first(['id', 'cname_species']);
                
            if ($taxa) {
                return [
                    'taxa_id' => $taxa->id,
                    'method' => 'cname_species_nameId'
                ];
            }
        }
        
        // Method 6: Match Cname_two dengan nameEn dari faunas
        if ($fauna) {
            $faunaWithEn = DB::table('faunas')
                ->where('nameLat', $namaLatin)
                ->first(['nameEn']);
                
            if ($faunaWithEn && !empty($faunaWithEn->nameEn)) {
                $taxa = DB::table('taxas')
                    ->where('Cname_two', $faunaWithEn->nameEn)
                    ->first(['id', 'Cname_two']);
                    
                if ($taxa) {
                    return [
                        'taxa_id' => $taxa->id,
                        'method' => 'cname_two_nameEn'
                    ];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Bersihkan nama spesies dari sp/sp.
     */
    private function cleanSpeciesName($namaLatin): string
    {
        // Hapus " sp" atau " sp." di akhir
        $cleaned = preg_replace('/\s+sp\.?$/i', '', trim($namaLatin));
        
        // Hapus "sp " di awal (jika ada)
        $cleaned = preg_replace('/^sp\s+/i', '', $cleaned);
        
        return trim($cleaned);
    }
    
    /**
     * Ekstrak genus dari nama latin (kata pertama)
     */
    private function extractGenus($namaLatin): ?string
    {
        if (empty($namaLatin)) {
            return null;
        }
        
        // Bersihkan nama latin dari sp/sp. terlebih dahulu
        $cleaned = $this->cleanSpeciesName($namaLatin);
        
        // Ambil kata pertama sebagai genus
        $parts = explode(' ', trim($cleaned));
        $genus = $parts[0] ?? null;
        
        // Validasi genus (minimal 2 karakter, tidak boleh "Unidentified")
        if ($genus && strlen($genus) >= 2 && strtolower($genus) !== 'unidentified') {
            return $genus;
        }
        
        return null;
    }
    
    /**
     * Menulis log ke file TXT
     */
    private function writeLog($message, $level = 'INFO'): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $logLine = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        // Pastikan direktori logs ada
        if (!Storage::exists('logs')) {
            Storage::makeDirectory('logs');
        }
        
        Storage::append($this->txtPath, $logLine);
        
        // Juga log ke Laravel log
        Log::info("ChecklistFaunaAdvancedSeeder: {$message}");
    }
    
    /**
     * Generate laporan akhir dalam format CSV dan TXT
     */
    private function generateFinalReport(): void
    {
        $this->writeLog("=== GENERATING FINAL REPORT ===", 'INFO');
        
        // Generate CSV
        $this->generateCSVReport();
        
        // Generate summary report
        $this->generateSummaryReport();
        
        $this->writeLog("=== LAPORAN SELESAI DIBUAT ===", 'INFO');
    }
    
    /**
     * Generate laporan dalam format CSV
     */
    private function generateCSVReport(): void
    {
        if (empty($this->logData)) {
            $this->writeLog("Tidak ada data untuk CSV report", 'WARNING');
            return;
        }
        
        // Header CSV
        $csvHeaders = [
            'Tahap',
            'Checklist_Fauna_ID',
            'Fauna_ID_Lama',
            'Nama_Spesies_Lama',
            'Nama_Latin_Lama',
            'Nama_Latin_Dicari',
            'Status',
            'Keterangan',
            'Matching_Method',
            'Taxa_ID_Ditemukan',
            'Fauna_ID_Baru',
            'Nama_Spesies_Baru',
            'Nama_Latin_Baru',
            'Timestamp'
        ];
        
        // Buat CSV content
        $csvContent = implode(',', $csvHeaders) . PHP_EOL;
        
        foreach ($this->logData as $row) {
            $csvRow = [
                $row['tahap'] ?? '',
                $row['checklist_fauna_id'] ?? '',
                $row['fauna_id_lama'] ?? '',
                $this->escapeCsvValue($row['nama_spesies_lama'] ?? ''),
                $this->escapeCsvValue($row['nama_latin_lama'] ?? ''),
                $this->escapeCsvValue($row['nama_latin_dicari'] ?? ''),
                $row['status'] ?? '',
                $this->escapeCsvValue($row['keterangan'] ?? ''),
                $row['matching_method'] ?? '',
                $row['taxa_id_ditemukan'] ?? '',
                $row['fauna_id_baru'] ?? '',
                $this->escapeCsvValue($row['nama_spesies_baru'] ?? ''),
                $this->escapeCsvValue($row['nama_latin_baru'] ?? ''),
                $row['timestamp'] ?? ''
            ];
            
            $csvContent .= implode(',', $csvRow) . PHP_EOL;
        }
        
        // Simpan CSV
        Storage::put($this->csvPath, $csvContent);
        $this->writeLog("CSV report berhasil dibuat: {$this->csvPath}", 'INFO');
    }
    
    /**
     * Escape CSV values
     */
    private function escapeCsvValue($value): string
    {
        if (empty($value)) {
            return '';
        }
        
        // Escape quotes dan wrap dengan quotes jika mengandung koma atau quotes
        $value = str_replace('"', '""', $value);
        
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            return '"' . $value . '"';
        }
        
        return $value;
    }
    
    /**
     * Generate summary report
     */
    private function generateSummaryReport(): void
    {
        $tahap1Success = collect($this->logData)->where('tahap', 1)->where('status', 'SUCCESS')->count();
        $tahap1Skipped = collect($this->logData)->where('tahap', 1)->where('status', 'SKIPPED')->count();
        $tahap1Failed = collect($this->logData)->where('tahap', 1)->where('status', 'FAILED')->count();
        $tahap1Error = collect($this->logData)->where('tahap', 1)->where('status', 'ERROR')->count();
        
        $tahap2Success = collect($this->logData)->where('tahap', 2)->where('status', 'SUCCESS')->count();
        $tahap2Skipped = collect($this->logData)->where('tahap', 2)->where('status', 'SKIPPED')->count();
        $tahap2Failed = collect($this->logData)->where('tahap', 2)->where('status', 'FAILED')->count();
        $tahap2Error = collect($this->logData)->where('tahap', 2)->where('status', 'ERROR')->count();
        
        // Statistik matching method
        $matchingMethodStats = collect($this->logData)
            ->where('tahap', 2)
            ->where('status', 'SUCCESS')
            ->groupBy('matching_method')
            ->map(function ($group) {
                return $group->count();
            });
        
        $summaryReport = [
            "",
            "=" . str_repeat("=", 80) . "=",
            "                    LAPORAN AKHIR SEEDER ADVANCED",
            "=" . str_repeat("=", 80) . "=",
            "",
            "TAHAP 1 - UPDATE DARI TABEL FAUNAS:",
            "- Berhasil (SUCCESS): {$tahap1Success}",
            "- Dilewati (SKIPPED): {$tahap1Skipped}",
            "- Gagal (FAILED): {$tahap1Failed}",
            "- Error: {$tahap1Error}",
            "- Total Tahap 1: " . ($tahap1Success + $tahap1Skipped + $tahap1Failed + $tahap1Error),
            "",
            "TAHAP 2 - UPDATE DARI TABEL TAXAS (ADVANCED MATCHING):",
            "- Berhasil (SUCCESS): {$tahap2Success}",
            "- Dilewati (SKIPPED): {$tahap2Skipped}",
            "- Gagal (FAILED): {$tahap2Failed}",
            "- Error: {$tahap2Error}",
            "- Total Tahap 2: " . ($tahap2Success + $tahap2Skipped + $tahap2Failed + $tahap2Error),
            "",
            "STATISTIK MATCHING METHOD TAHAP 2:",
        ];
        
        foreach ($matchingMethodStats as $method => $count) {
            $methodName = [
                'species_exact' => 'Species exact match',
                'species_clean_sp' => 'Species (sp/sp. dibersihkan)',
                'scientific_name' => 'Scientific name match',
                'genus_match' => 'Genus match (untuk sp/sp.)',
                'cname_species_nameId' => 'Cname_species vs nameId',
                'cname_two_nameEn' => 'Cname_two vs nameEn'
            ];
            
            $displayName = $methodName[$method] ?? $method;
            $summaryReport[] = "- {$displayName}: {$count}";
        }
        
        $summaryReport = array_merge($summaryReport, [
            "",
            "RINGKASAN KESELURUHAN:",
            "- Total Success: " . ($tahap1Success + $tahap2Success),
            "- Total Skipped: " . ($tahap1Skipped + $tahap2Skipped),
            "- Total Failed: " . ($tahap1Failed + $tahap2Failed),
            "- Total Error: " . ($tahap1Error + $tahap2Error),
            "- Grand Total: " . count($this->logData),
            "",
            "File log detail tersimpan di:",
            "- CSV: storage/app/{$this->csvPath}",
            "- TXT: storage/app/{$this->txtPath}",
            "",
            "Seeder selesai pada: " . now()->toDateTimeString(),
            "=" . str_repeat("=", 80) . "=",
        ]);
        
        // Tulis summary ke log TXT
        foreach ($summaryReport as $line) {
            $this->writeLog($line, 'SUMMARY');
        }
        
        // Tampilkan summary di console
        foreach ($summaryReport as $line) {
            $this->command->info($line);
        }
    }
}
