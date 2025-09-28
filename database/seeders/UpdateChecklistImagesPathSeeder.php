<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateChecklistImagesPathSeeder extends Seeder
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
        $this->logPath = "logs/update_checklist_images_path_{$timestamp}";
        $this->csvPath = "{$this->logPath}.csv";
        $this->txtPath = "{$this->logPath}.txt";
        
        $this->command->info('Memulai proses update path checklist_images...');
        $this->writeLog("=== MULAI PROSES UPDATE PATH CHECKLIST_IMAGES ===", 'INFO');
        $this->writeLog("Timestamp: " . now()->toDateTimeString(), 'INFO');
        
        try {
            // Ambil semua checklist_images yang menggunakan path 'checklistimg/'
            $images = DB::table('checklist_images')
                ->where('images', 'LIKE', 'checklistimg/%')
                ->get(['id', 'images']);
            
            $totalRecords = $images->count();
            $this->command->info("Total records ditemukan dengan path 'checklistimg/': {$totalRecords}");
            $this->writeLog("Total records ditemukan dengan path 'checklistimg/': {$totalRecords}", 'INFO');
            
            if ($totalRecords === 0) {
                $this->command->info('Tidak ada record yang perlu diupdate.');
                $this->writeLog('Tidak ada record yang perlu diupdate.', 'INFO');
                return;
            }
            
            $updated = 0;
            $errors = 0;
            $processed = 0;
            
            foreach ($images as $image) {
                try {
                    $processed++;
                    
                    // Log detail record yang diproses
                    $logEntry = [
                        'id' => $image->id,
                        'path_lama' => $image->images,
                        'path_baru' => '',
                        'status' => '',
                        'keterangan' => '',
                        'timestamp' => now()->toDateTimeString()
                    ];
                    
                    // Ubah path dari 'checklistimg/' menjadi 'checklist_images/'
                    $newPath = str_replace('checklistimg/', 'checklist_images/', $image->images);
                    
                    // Update record
                    $updateResult = DB::table('checklist_images')
                        ->where('id', $image->id)
                        ->update([
                            'images' => $newPath,
                            'updated_at' => now()
                        ]);
                    
                    if ($updateResult) {
                        $updated++;
                        $logEntry['path_baru'] = $newPath;
                        $logEntry['status'] = 'SUCCESS';
                        $logEntry['keterangan'] = 'Path berhasil diupdate';
                    } else {
                        $logEntry['status'] = 'FAILED';
                        $logEntry['keterangan'] = 'Update gagal (tidak ada perubahan)';
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
                    
                    $this->writeLog("Error pada ID {$image->id}: " . $e->getMessage(), 'ERROR');
                }
            }
            
            // Summary
            $summary = [
                "=== RINGKASAN HASIL ===",
                "Total records diproses: {$totalRecords}",
                "Berhasil diupdate: {$updated}",
                "Error: {$errors}",
                "Success rate: " . ($totalRecords > 0 ? round(($updated / $totalRecords) * 100, 2) : 0) . "%"
            ];
            
            foreach ($summary as $line) {
                $this->command->info($line);
                $this->writeLog($line, 'INFO');
            }
            
            // Generate laporan
            $this->generateReports();
            
        } catch (\Exception $e) {
            $this->command->error("Error utama: " . $e->getMessage());
            $this->writeLog("Error utama: " . $e->getMessage(), 'ERROR');
        }
        
        $this->command->info('Proses update checklist_images path selesai!');
        $this->command->info("Log CSV tersimpan di: storage/app/{$this->csvPath}");
        $this->command->info("Log TXT tersimpan di: storage/app/{$this->txtPath}");
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
        Log::info("UpdateChecklistImagesPathSeeder: {$message}");
    }
    
    /**
     * Generate laporan dalam format CSV dan TXT
     */
    private function generateReports(): void
    {
        $this->writeLog("=== GENERATING REPORTS ===", 'INFO');
        
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
            'ID',
            'Path_Lama',
            'Path_Baru',
            'Status',
            'Keterangan',
            'Timestamp'
        ];
        
        // Buat CSV content
        $csvContent = implode(',', $csvHeaders) . PHP_EOL;
        
        foreach ($this->logData as $row) {
            $csvRow = [
                $row['id'] ?? '',
                $this->escapeCsvValue($row['path_lama'] ?? ''),
                $this->escapeCsvValue($row['path_baru'] ?? ''),
                $row['status'] ?? '',
                $this->escapeCsvValue($row['keterangan'] ?? ''),
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
        $successCount = collect($this->logData)->where('status', 'SUCCESS')->count();
        $failedCount = collect($this->logData)->where('status', 'FAILED')->count();
        $errorCount = collect($this->logData)->where('status', 'ERROR')->count();
        $totalCount = count($this->logData);
        
        $summaryReport = [
            "",
            "=" . str_repeat("=", 70) . "=",
            "           LAPORAN AKHIR UPDATE CHECKLIST IMAGES PATH",
            "=" . str_repeat("=", 70) . "=",
            "",
            "RINGKASAN HASIL:",
            "- Total records diproses: {$totalCount}",
            "- Berhasil (SUCCESS): {$successCount}",
            "- Gagal (FAILED): {$failedCount}",
            "- Error: {$errorCount}",
            "- Success rate: " . ($totalCount > 0 ? round(($successCount / $totalCount) * 100, 2) : 0) . "%",
            "",
            "PERUBAHAN YANG DILAKUKAN:",
            "- Path 'checklistimg/' → 'checklist_images/'",
            "- Update kolom 'updated_at' dengan timestamp saat ini",
            "",
            "CONTOH PERUBAHAN:",
            "- checklistimg/20240723_034759.jpeg → checklist_images/20240723_034759.jpeg",
            "- checklistimg/20240801_123456.jpg → checklist_images/20240801_123456.jpg",
            "",
            "File log detail tersimpan di:",
            "- CSV: storage/app/{$this->csvPath}",
            "- TXT: storage/app/{$this->txtPath}",
            "",
            "Seeder selesai pada: " . now()->toDateTimeString(),
            "=" . str_repeat("=", 70) . "=",
        ];
        
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
