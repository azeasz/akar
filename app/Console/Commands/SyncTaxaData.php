<?php

namespace App\Console\Commands;

use App\Models\Taxa;
use App\Models\TaxaLocal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncTaxaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taxa:sync {--limit=100} {--offset=0} {--chunk=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data taxa dari database amaturalist ke database lokal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $offset = $this->option('offset');
        $chunkSize = $this->option('chunk');
        
        $this->info("Memulai sinkronisasi data taxa dengan limit: {$limit}, offset: {$offset}, chunk: {$chunkSize}");
        
        try {
            // Gunakan model Taxa untuk mengambil data dari database amaturalist
            $totalRecords = Taxa::count();
            $processedRecords = 0;
            $successCount = 0;
            $errorCount = 0;
            
            $this->info("Total records di database amaturalist: {$totalRecords}");
            
            // Gunakan chunking untuk menghindari memory issues
            Taxa::select('id', 'scientific_name', 'common_name', 'rank', 'kingdom', 
                        'phylum', 'class', 'order', 'family', 'genus', 'species', 
                        'iucn_status', 'image_url', 'description')
                ->offset($offset)
                ->limit($limit)
                ->chunkById($chunkSize, function ($taxas) use (&$processedRecords, &$successCount, &$errorCount) {
                    DB::beginTransaction();
                    
                    try {
                        foreach ($taxas as $taxa) {
                            TaxaLocal::updateOrCreate(
                                ['taxa_id' => $taxa->id],
                                [
                                    'scientific_name' => $taxa->scientific_name,
                                    'common_name' => $taxa->common_name,
                                    'rank' => $taxa->rank,
                                    'kingdom' => $taxa->kingdom,
                                    'phylum' => $taxa->phylum,
                                    'class' => $taxa->class,
                                    'order' => $taxa->order,
                                    'family' => $taxa->family,
                                    'genus' => $taxa->genus,
                                    'species' => $taxa->species,
                                    'iucn_status' => $taxa->iucn_status,
                                    'image_url' => $taxa->image_url,
                                    'description' => $taxa->description,
                                    'last_synced_at' => now(),
                                ]
                            );
                            
                            $processedRecords++;
                            $successCount++;
                        }
                        
                        DB::commit();
                        
                        $this->info("Berhasil memproses {$taxas->count()} records");
                    } catch (\Exception $e) {
                        DB::rollBack();
                        
                        $errorCount += $taxas->count();
                        $this->error("Error saat memproses chunk: " . $e->getMessage());
                        Log::error("Error saat sinkronisasi taxa: " . $e->getMessage());
                    }
                });
            
            $this->info("Sinkronisasi selesai. Total diproses: {$processedRecords}, Sukses: {$successCount}, Error: {$errorCount}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error saat sinkronisasi taxa: " . $e->getMessage());
            Log::error("Error saat sinkronisasi taxa: " . $e->getMessage());
            
            return 1;
        }
    }
} 