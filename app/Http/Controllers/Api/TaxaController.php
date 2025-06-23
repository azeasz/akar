<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Taxa;
use App\Models\TaxaLocal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TaxaController extends Controller
{
    /**
     * Base URL API amaturalist
     */
    protected $apiBaseUrl = 'https://amaturalist.com/api';

    /**
     * Pencarian taxa
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $limit = $request->input('limit', 10);
        
        if (empty($query)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Query pencarian diperlukan',
            ], 400);
        }
        
        try {
            // Coba cari di database lokal terlebih dahulu
            $localResults = TaxaLocal::where('scientific_name', 'like', "%{$query}%")
                ->orWhere('common_name', 'like', "%{$query}%")
                ->limit($limit)
                ->get();
                
            if ($localResults->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'data' => $localResults,
                    'source' => 'local',
                ]);
            }
            
            // Jika tidak ditemukan, cari di API
            $response = Http::get("{$this->apiBaseUrl}/taksa/search", [
                'q' => $query,
                'limit' => $limit,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Simpan hasil ke database lokal
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $item) {
                        $this->storeOrUpdateTaxaLocal($item);
                    }
                }
                
                return response()->json([
                    'status' => 'success',
                    'data' => $data['data'] ?? [],
                    'source' => 'api',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data dari API',
                    'error' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error searching taxa: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencari taxa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Pencarian taxa animalia
     */
    public function searchAnimalia(Request $request)
    {
        $query = $request->input('q');
        $limit = $request->input('limit', 10);
        
        if (empty($query)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Query pencarian diperlukan',
            ], 400);
        }
        
        try {
            // Coba cari di database lokal terlebih dahulu
            $localResults = TaxaLocal::where('kingdom', 'Animalia')
                ->where(function ($q) use ($query) {
                    $q->where('scientific_name', 'like', "%{$query}%")
                      ->orWhere('common_name', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get();
                
            if ($localResults->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'data' => $localResults,
                    'source' => 'local',
                ]);
            }
            
            // Jika tidak ditemukan, cari di API
            $response = Http::get("{$this->apiBaseUrl}/taksa/search/animalia", [
                'q' => $query,
                'limit' => $limit,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Simpan hasil ke database lokal
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $item) {
                        $this->storeOrUpdateTaxaLocal($item);
                    }
                }
                
                return response()->json([
                    'status' => 'success',
                    'data' => $data['data'] ?? [],
                    'source' => 'api',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data dari API',
                    'error' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error searching animalia taxa: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencari taxa animalia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Pencarian taxa plantae
     */
    public function searchPlantae(Request $request)
    {
        $query = $request->input('q');
        $limit = $request->input('limit', 10);
        
        if (empty($query)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Query pencarian diperlukan',
            ], 400);
        }
        
        try {
            // Coba cari di database lokal terlebih dahulu
            $localResults = TaxaLocal::where('kingdom', 'Plantae')
                ->where(function ($q) use ($query) {
                    $q->where('scientific_name', 'like', "%{$query}%")
                      ->orWhere('common_name', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get();
                
            if ($localResults->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'data' => $localResults,
                    'source' => 'local',
                ]);
            }
            
            // Jika tidak ditemukan, cari di API
            $response = Http::get("{$this->apiBaseUrl}/taksa/search/plantae", [
                'q' => $query,
                'limit' => $limit,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Simpan hasil ke database lokal
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $item) {
                        $this->storeOrUpdateTaxaLocal($item);
                    }
                }
                
                return response()->json([
                    'status' => 'success',
                    'data' => $data['data'] ?? [],
                    'source' => 'api',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data dari API',
                    'error' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error searching plantae taxa: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencari taxa plantae',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Pencarian taxa fungi
     */
    public function searchFungi(Request $request)
    {
        $query = $request->input('q');
        $limit = $request->input('limit', 10);
        
        if (empty($query)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Query pencarian diperlukan',
            ], 400);
        }
        
        try {
            // Coba cari di database lokal terlebih dahulu
            $localResults = TaxaLocal::where('kingdom', 'Fungi')
                ->where(function ($q) use ($query) {
                    $q->where('scientific_name', 'like', "%{$query}%")
                      ->orWhere('common_name', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get();
                
            if ($localResults->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'data' => $localResults,
                    'source' => 'local',
                ]);
            }
            
            // Jika tidak ditemukan, cari di API
            $response = Http::get("{$this->apiBaseUrl}/taksa/search/fungi", [
                'q' => $query,
                'limit' => $limit,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Simpan hasil ke database lokal
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $item) {
                        $this->storeOrUpdateTaxaLocal($item);
                    }
                }
                
                return response()->json([
                    'status' => 'success',
                    'data' => $data['data'] ?? [],
                    'source' => 'api',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data dari API',
                    'error' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error searching fungi taxa: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencari taxa fungi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Mendapatkan status IUCN
     */
    public function getIUCNStatus(Request $request)
    {
        $taxaId = $request->input('id');
        
        if (empty($taxaId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID taxa diperlukan',
            ], 400);
        }
        
        try {
            // Coba cari di database lokal terlebih dahulu
            $localTaxa = TaxaLocal::where('taxa_id', $taxaId)->first();
            
            if ($localTaxa && $localTaxa->iucn_status) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'id' => $taxaId,
                        'iucn_status' => $localTaxa->iucn_status,
                    ],
                    'source' => 'local',
                ]);
            }
            
            // Jika tidak ditemukan, cari di API
            $response = Http::get("{$this->apiBaseUrl}/taksa/iucn-status", [
                'id' => $taxaId,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Update data lokal jika ada
                if ($localTaxa) {
                    $localTaxa->update([
                        'iucn_status' => $data['data']['iucn_status'] ?? null,
                        'last_synced_at' => now(),
                    ]);
                }
                
                return response()->json([
                    'status' => 'success',
                    'data' => $data['data'] ?? [],
                    'source' => 'api',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data dari API',
                    'error' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error getting IUCN status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil status IUCN',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Detail taxa
     */
    public function detail($id)
    {
        if (empty($id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID taxa diperlukan',
            ], 400);
        }
        
        try {
            // Coba cari di database lokal terlebih dahulu
            $localTaxa = TaxaLocal::where('taxa_id', $id)->first();
            
            if ($localTaxa) {
                return response()->json([
                    'status' => 'success',
                    'data' => $localTaxa,
                    'source' => 'local',
                ]);
            }
            
            // Jika tidak ditemukan, cari di API
            $response = Http::get("{$this->apiBaseUrl}/taksa/{$id}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Simpan hasil ke database lokal
                if (isset($data['data'])) {
                    $this->storeOrUpdateTaxaLocal($data['data']);
                }
                
                return response()->json([
                    'status' => 'success',
                    'data' => $data['data'] ?? [],
                    'source' => 'api',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data dari API',
                    'error' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error getting taxa detail: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil detail taxa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Menyimpan atau memperbarui data taxa di database lokal
     */
    protected function storeOrUpdateTaxaLocal($data)
    {
        if (empty($data['id'])) {
            return null;
        }
        
        try {
            return TaxaLocal::updateOrCreate(
                ['taxa_id' => $data['id']],
                [
                    'scientific_name' => $data['scientific_name'] ?? null,
                    'common_name' => $data['common_name'] ?? null,
                    'rank' => $data['rank'] ?? null,
                    'kingdom' => $data['kingdom'] ?? null,
                    'phylum' => $data['phylum'] ?? null,
                    'class' => $data['class'] ?? null,
                    'order' => $data['order'] ?? null,
                    'family' => $data['family'] ?? null,
                    'genus' => $data['genus'] ?? null,
                    'species' => $data['species'] ?? null,
                    'iucn_status' => $data['iucn_status'] ?? null,
                    'image_url' => $data['image_url'] ?? null,
                    'description' => $data['description'] ?? null,
                    'last_synced_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error storing taxa local: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Sinkronisasi data taxa dari database amaturalist ke database lokal
     */
    public function syncTaxa(Request $request)
    {
        $limit = $request->input('limit', 100);
        $offset = $request->input('offset', 0);
        
        try {
            // Gunakan model Taxa untuk mengambil data dari database amaturalist
            $taxas = Taxa::select('id', 'scientific_name', 'common_name', 'rank', 'kingdom', 
                                'phylum', 'class', 'order', 'family', 'genus', 'species', 
                                'iucn_status', 'image_url', 'description')
                        ->offset($offset)
                        ->limit($limit)
                        ->get();
            
            $count = 0;
            
            DB::beginTransaction();
            
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
                
                $count++;
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => "Berhasil menyinkronkan {$count} data taxa",
                'offset' => $offset,
                'limit' => $limit,
                'total_synced' => $count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error syncing taxa: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat sinkronisasi data taxa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
} 