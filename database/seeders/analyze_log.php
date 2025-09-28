<?php

/**
 * Script untuk menganalisis log CSV dari ChecklistFaunaDetailedSeeder
 * 
 * Cara menggunakan:
 * php analyze_log.php path/to/logfile.csv
 */

if ($argc < 2) {
    echo "Usage: php analyze_log.php <path_to_csv_file>\n";
    echo "Example: php analyze_log.php storage/app/logs/checklist_fauna_detailed_2024-01-15_14-30-25.csv\n";
    exit(1);
}

$csvFile = $argv[1];

if (!file_exists($csvFile)) {
    echo "Error: File tidak ditemukan: {$csvFile}\n";
    exit(1);
}

echo "=== ANALISIS LOG CHECKLIST FAUNA SEEDER ===\n";
echo "File: {$csvFile}\n";
echo "Waktu analisis: " . date('Y-m-d H:i:s') . "\n\n";

// Baca CSV
$data = [];
$headers = [];

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    $rowNum = 0;
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($rowNum == 0) {
            $headers = $row;
        } else {
            $data[] = array_combine($headers, $row);
        }
        $rowNum++;
    }
    fclose($handle);
}

if (empty($data)) {
    echo "Error: Tidak ada data dalam file CSV\n";
    exit(1);
}

echo "Total records dalam log: " . count($data) . "\n\n";

// Analisis berdasarkan tahap
echo "=== ANALISIS BERDASARKAN TAHAP ===\n";
$tahapStats = [];
foreach ($data as $row) {
    $tahap = $row['Tahap'];
    $status = $row['Status'];
    
    if (!isset($tahapStats[$tahap])) {
        $tahapStats[$tahap] = [
            'SUCCESS' => 0,
            'SKIPPED' => 0,
            'FAILED' => 0,
            'ERROR' => 0,
            'total' => 0
        ];
    }
    
    $tahapStats[$tahap][$status]++;
    $tahapStats[$tahap]['total']++;
}

foreach ($tahapStats as $tahap => $stats) {
    echo "TAHAP {$tahap}:\n";
    echo "  - SUCCESS: {$stats['SUCCESS']} (" . round($stats['SUCCESS']/$stats['total']*100, 1) . "%)\n";
    echo "  - SKIPPED: {$stats['SKIPPED']} (" . round($stats['SKIPPED']/$stats['total']*100, 1) . "%)\n";
    echo "  - FAILED: {$stats['FAILED']} (" . round($stats['FAILED']/$stats['total']*100, 1) . "%)\n";
    echo "  - ERROR: {$stats['ERROR']} (" . round($stats['ERROR']/$stats['total']*100, 1) . "%)\n";
    echo "  - Total: {$stats['total']}\n\n";
}

// Analisis alasan SKIPPED
echo "=== ANALISIS ALASAN SKIPPED ===\n";
$skippedReasons = [];
foreach ($data as $row) {
    if ($row['Status'] == 'SKIPPED') {
        $tahap = $row['Tahap'];
        $reason = $row['Keterangan'];
        
        if (!isset($skippedReasons[$tahap])) {
            $skippedReasons[$tahap] = [];
        }
        
        if (!isset($skippedReasons[$tahap][$reason])) {
            $skippedReasons[$tahap][$reason] = 0;
        }
        
        $skippedReasons[$tahap][$reason]++;
    }
}

foreach ($skippedReasons as $tahap => $reasons) {
    echo "TAHAP {$tahap}:\n";
    arsort($reasons);
    foreach ($reasons as $reason => $count) {
        echo "  - {$reason}: {$count}\n";
    }
    echo "\n";
}

// Analisis ERROR jika ada
$errorData = array_filter($data, function($row) {
    return $row['Status'] == 'ERROR';
});

if (!empty($errorData)) {
    echo "=== ANALISIS ERROR ===\n";
    $errorReasons = [];
    foreach ($errorData as $row) {
        $reason = $row['Keterangan'];
        if (!isset($errorReasons[$reason])) {
            $errorReasons[$reason] = 0;
        }
        $errorReasons[$reason]++;
    }
    
    arsort($errorReasons);
    foreach ($errorReasons as $reason => $count) {
        echo "- {$reason}: {$count}\n";
    }
    echo "\n";
}

// Top 10 record yang berhasil diupdate di tahap 1
echo "=== SAMPLE RECORD BERHASIL TAHAP 1 ===\n";
$successTahap1 = array_filter($data, function($row) {
    return $row['Tahap'] == '1' && $row['Status'] == 'SUCCESS';
});

$count = 0;
foreach ($successTahap1 as $row) {
    if ($count >= 5) break;
    echo "ID {$row['Checklist_Fauna_ID']}: '{$row['Nama_Spesies_Lama']}' -> '{$row['Nama_Spesies_Baru']}'\n";
    $count++;
}

if (count($successTahap1) > 5) {
    echo "... dan " . (count($successTahap1) - 5) . " record lainnya\n";
}
echo "\n";

// Top 10 record yang berhasil diupdate di tahap 2
echo "=== SAMPLE RECORD BERHASIL TAHAP 2 ===\n";
$successTahap2 = array_filter($data, function($row) {
    return $row['Tahap'] == '2' && $row['Status'] == 'SUCCESS';
});

$count = 0;
foreach ($successTahap2 as $row) {
    if ($count >= 5) break;
    echo "ID {$row['Checklist_Fauna_ID']}: fauna_id {$row['Fauna_ID_Lama']} -> {$row['Fauna_ID_Baru']} (match: '{$row['Nama_Latin_Dicari']}')\n";
    $count++;
}

if (count($successTahap2) > 5) {
    echo "... dan " . (count($successTahap2) - 5) . " record lainnya\n";
}
echo "\n";

// Statistik waktu proses
echo "=== STATISTIK WAKTU ===\n";
$timestamps = array_column($data, 'Timestamp');
$timestamps = array_filter($timestamps);

if (!empty($timestamps)) {
    $firstTime = min($timestamps);
    $lastTime = max($timestamps);
    
    echo "Mulai: {$firstTime}\n";
    echo "Selesai: {$lastTime}\n";
    
    $start = new DateTime($firstTime);
    $end = new DateTime($lastTime);
    $duration = $start->diff($end);
    
    echo "Durasi: ";
    if ($duration->h > 0) echo "{$duration->h} jam ";
    if ($duration->i > 0) echo "{$duration->i} menit ";
    echo "{$duration->s} detik\n";
}

echo "\n=== ANALISIS SELESAI ===\n";

/**
 * Fungsi helper untuk export hasil analisis ke file
 */
function exportAnalysisToFile($data, $outputFile) {
    // Implementasi export jika diperlukan
    // Bisa ditambahkan nanti
}
