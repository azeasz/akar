<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ExportWormsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worms:export
                            {--limit=1000 : Maximum number of records to fetch}
                            {--output= : Output file path (default: storage/app/worms_indonesia.csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export marine species data from WoRMS for Indonesian waters';

    /**
     * Indonesian marine regions coordinates (approximate bounding box)
     * Covers the Indonesian archipelago
     */
    protected $indonesianRegions = [
        ['west' => 95.0, 'east' => 141.0, 'south' => -11.0, 'north' => 6.0],   // Main Indonesian waters
        ['west' => 118.0, 'east' => 135.0, 'south' => -9.0, 'north' => -4.0],  // Lesser Sunda Islands
        ['west' => 104.0, 'east' => 119.0, 'south' => -4.0, 'north' => 1.0],   // Java Sea
        ['west' => 130.0, 'east' => 141.0, 'south' => -3.0, 'north' => 3.0],   // Maluku Sea
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $outputPath = $this->option('output') ?: storage_path('app/worms_indonesia.csv');

        $this->info("Starting WoRMS data export for Indonesian marine species");
        $this->info("Limit: {$limit} records");
        $this->info("Output: {$outputPath}");

        try {
            $speciesData = $this->fetchIndonesianMarineSpecies($limit);

            if ($speciesData->isEmpty()) {
                $this->warn("No species data found for Indonesian waters");
                return 1;
            }

            $this->exportToCsv($speciesData, $outputPath);

            $this->info("Export completed successfully!");
            $this->info("Total species exported: " . $speciesData->count());
            $this->info("File saved to: {$outputPath}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Error during export: " . $e->getMessage());
            Log::error("WoRMS export error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Fetch marine species data for Indonesian waters
     */
    protected function fetchIndonesianMarineSpecies(int $limit): Collection
    {
        $allSpecies = collect();
        $fetchedCount = 0;

        $this->info("Fetching marine species data from WoRMS...");

        // Use WoRMS API to get species by geographic area
        // Since WoRMS doesn't have direct geographic filtering in basic API,
        // we'll use a combination of approaches:
        // 1. Get species by marine environment
        // 2. Filter by known Indonesian marine regions

        $marineEnvironments = [
            'marine',
            'brackish',
            'saltwater'
        ];

        $bar = $this->output->createProgressBar($limit);
        $bar->start();

        foreach ($marineEnvironments as $environment) {
            if ($fetchedCount >= $limit) break;

            try {
                // Get AphiaIDs for marine species
                $response = Http::timeout(30)->get("https://www.marinespecies.org/rest/AphiaRecordsByName/{$environment}", [
                    'like' => 'true',
                    'marine_only' => 'true'
                ]);

                if ($response->successful()) {
                    $records = $response->json();

                    foreach ($records as $record) {
                        if ($fetchedCount >= $limit) break;

                        // Get detailed information for each species
                        $speciesDetails = $this->getSpeciesDetails($record['AphiaID']);

                        if ($speciesDetails && $this->isIndonesianSpecies($speciesDetails)) {
                            $allSpecies->push($this->formatSpeciesData($speciesDetails));
                            $fetchedCount++;
                            $bar->advance();
                        }
                    }
                }

                // Add delay to be respectful to the API
                sleep(1);

            } catch (\Exception $e) {
                $this->warn("Error fetching data for environment {$environment}: " . $e->getMessage());
                continue;
            }
        }

        $bar->finish();
        $this->newLine();

        return $allSpecies;
    }

    /**
     * Get detailed species information from WoRMS
     */
    protected function getSpeciesDetails(int $aphiaId): ?array
    {
        try {
            $response = Http::timeout(30)->get("https://www.marinespecies.org/rest/AphiaRecordByAphiaID/{$aphiaId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            Log::warning("Error fetching details for AphiaID {$aphiaId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if species is found in Indonesian waters
     */
    protected function isIndonesianSpecies(array $speciesData): bool
    {
        // Check if species has distribution information
        if (!isset($speciesData['distribution'])) {
            return false;
        }

        $distributions = is_array($speciesData['distribution'])
            ? $speciesData['distribution']
            : [$speciesData['distribution']];

        foreach ($distributions as $distribution) {
            $locality = strtolower($distribution['locality'] ?? '');

            // Check for Indonesian location keywords
            $indonesianKeywords = [
                'indonesia',
                'indonesian',
                'java sea',
                'sulawesi',
                'sumatra',
                'borneo',
                'kalimantan',
                'papua',
                'maluku',
                'bali',
                'lombok',
                'sumbawa',
                'flores',
                'timor',
                'sumba',
                'alor',
                'riau',
                'jawa',
                'madura',
                'banten',
                'jakarta',
                'surabaya',
                'semarang',
                'medan',
                'palembang',
                'makassar',
                'manado',
                'jayapura'
            ];

            foreach ($indonesianKeywords as $keyword) {
                if (strpos($locality, $keyword) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Format species data for CSV export
     */
    protected function formatSpeciesData(array $speciesData): array
    {
        return [
            'aphia_id' => $speciesData['AphiaID'] ?? '',
            'scientific_name' => $speciesData['scientificname'] ?? '',
            'authority' => $speciesData['authority'] ?? '',
            'rank' => $speciesData['rank'] ?? '',
            'status' => $speciesData['status'] ?? '',
            'kingdom' => $speciesData['kingdom'] ?? '',
            'phylum' => $speciesData['phylum'] ?? '',
            'class' => $speciesData['class'] ?? '',
            'order' => $speciesData['order'] ?? '',
            'family' => $speciesData['family'] ?? '',
            'genus' => $speciesData['genus'] ?? '',
            'species' => $speciesData['species'] ?? '',
            'subspecies' => $speciesData['subspecies'] ?? '',
            'valid_name' => $speciesData['valid_name'] ?? '',
            'valid_aphia_id' => $speciesData['valid_AphiaID'] ?? '',
            'is_marine' => $speciesData['isMarine'] ?? '',
            'is_brackish' => $speciesData['isBrackish'] ?? '',
            'is_freshwater' => $speciesData['isFreshwater'] ?? '',
            'is_terrestrial' => $speciesData['isTerrestrial'] ?? '',
            'citation' => $speciesData['citation'] ?? '',
            'url' => $speciesData['url'] ?? '',
            'lsid' => $speciesData['lsid'] ?? '',
        ];
    }

    /**
     * Export data to CSV file
     */
    protected function exportToCsv(Collection $data, string $filePath): void
    {
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($filePath, 'w');

        // Write CSV headers
        if ($data->isNotEmpty()) {
            fputcsv($file, array_keys($data->first()));
        }

        // Write data rows
        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);
    }
}