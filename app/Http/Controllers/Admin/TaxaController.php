<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxaLocal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TaxaController extends Controller
{
    /**
     * Base URL API amaturalist
     */
    protected $apiBaseUrl = 'https://amaturalist.com/api';
    
    /**
     * Menampilkan daftar taxa lokal
     */
    public function index(Request $request)
    {
        $query = TaxaLocal::query();
        
        // Filter berdasarkan kingdom
        if ($request->has('kingdom') && $request->kingdom) {
            $query->where('kingdom', $request->kingdom);
        }
        
        // Filter berdasarkan pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('scientific_name', 'like', "%{$search}%")
                  ->orWhere('common_name', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortField = $request->sort_by ?? 'scientific_name';
        $sortDirection = $request->sort_order ?? 'asc';
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 10;
        $taxas = $query->paginate($perPage)->withQueryString();
        
        return view('admin.taxas.index', compact('taxas'));
    }
    
    /**
     * Menampilkan form pencarian taxa
     */
    public function search()
    {
        return view('admin.taxas.search');
    }
    
    /**
     * Melakukan pencarian taxa
     */
    public function searchResults(Request $request)
    {
        $query = $request->input('q');
        $kingdom = $request->input('kingdom');
        
        if (empty($query)) {
            return redirect()->back()->with('error', 'Query pencarian diperlukan');
        }
        
        try {
            // Coba cari di database lokal terlebih dahulu
            $localQuery = TaxaLocal::query();
            
            if ($kingdom) {
                $localQuery->where('kingdom', $kingdom);
            }
            
            $localQuery->where(function ($q) use ($query) {
                $q->where('scientific_name', 'like', "%{$query}%")
                  ->orWhere('common_name', 'like', "%{$query}%");
            });
            
            $results = $localQuery->paginate(10)->withQueryString();
            
            // Jika hasil lokal kosong, cari di API
            if ($results->isEmpty()) {
                $apiEndpoint = "{$this->apiBaseUrl}/taksa/search";
                
                if ($kingdom) {
                    $apiEndpoint = "{$this->apiBaseUrl}/taksa/search/" . strtolower($kingdom);
                }
                
                $response = Http::get($apiEndpoint, [
                    'q' => $query,
                    'limit' => 20,
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Simpan hasil ke database lokal
                    if (isset($data['data']) && is_array($data['data'])) {
                        foreach ($data['data'] as $item) {
                            $this->storeOrUpdateTaxaLocal($item);
                        }
                        
                        // Ambil lagi dari database lokal setelah disimpan
                        $results = $localQuery->paginate(10)->withQueryString();
                    }
                }
            }
            
            return view('admin.taxas.search_results', compact('results', 'query', 'kingdom'));
        } catch (\Exception $e) {
            Log::error('Error searching taxa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mencari taxa: ' . $e->getMessage());
        }
    }
    
    /**
     * Menampilkan detail taxa
     */
    public function show($id)
    {
        try {
            // Coba cari di database lokal terlebih dahulu
            $taxa = TaxaLocal::where('taxa_id', $id)->first();
            
            if (!$taxa) {
                // Jika tidak ditemukan, cari di API
                $response = Http::get("{$this->apiBaseUrl}/taksa/{$id}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Simpan hasil ke database lokal
                    if (isset($data['data'])) {
                        $taxa = $this->storeOrUpdateTaxaLocal($data['data']);
                    }
                }
            }
            
            if (!$taxa) {
                return redirect()->route('admin.taxas.index')->with('error', 'Taxa tidak ditemukan');
            }
            
            return view('admin.taxas.show', compact('taxa'));
        } catch (\Exception $e) {
            Log::error('Error showing taxa: ' . $e->getMessage());
            return redirect()->route('admin.taxas.index')->with('error', 'Terjadi kesalahan saat menampilkan taxa: ' . $e->getMessage());
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
     * Menampilkan form untuk sinkronisasi taxa
     */
    public function sync()
    {
        return view('admin.taxas.sync');
    }
    
    /**
     * Memproses sinkronisasi taxa
     */
    public function processSync(Request $request)
    {
        $limit = $request->input('limit', 100);
        $offset = $request->input('offset', 0);
        
        try {
            // Validasi input
            $validated = $request->validate([
                'limit' => 'required|integer|min:1|max:1000',
                'offset' => 'required|integer|min:0',
            ]);
            
            // Ambil data dari database amaturalist
            $taxa = Taxa::skip($offset)->take($limit)->get();
            
            $count = 0;
            $updated = 0;
            $errors = 0;
            
            foreach ($taxa as $taxon) {
                try {
                    // Cari atau buat taxa lokal
                    $localTaxa = TaxaLocal::updateOrCreate(
                        ['id' => $taxon->id],
                        [
                            'kingdom' => $taxon->kingdom,
                            'phylum' => $taxon->phylum,
                            'class' => $taxon->class,
                            'order' => $taxon->order,
                            'family' => $taxon->family,
                            'genus' => $taxon->genus,
                            'species' => $taxon->species,
                            'subspecies' => $taxon->subspecies,
                            'common_name' => $taxon->common_name,
                            'local_name' => $taxon->local_name,
                            'scientific_name' => $taxon->scientific_name,
                            'author' => $taxon->author,
                            'rank' => $taxon->taxon_rank,
                            'taxonomic_status' => $taxon->taxonomic_status,
                            'iucn_status' => $taxon->iucn_status,
                            'iucn_criteria' => $taxon->iucn_criteria,
                            'cites_status' => $taxon->cites_status,
                            'cites_source' => $taxon->cites_source,
                            'cites_listing_date' => $taxon->cites_listing_date,
                            'image_url' => $taxon->image_url,
                            'updated_at' => now(),
                        ]
                    );
                    
                    if ($localTaxa->wasRecentlyCreated) {
                        $count++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    \Log::error('Error syncing taxa ID: ' . $taxon->id . ' - ' . $e->getMessage());
                    $errors++;
                }
            }
            
            $message = "Sinkronisasi berhasil: $count data baru, $updated data diperbarui";
            if ($errors > 0) {
                $message .= ", $errors data gagal (lihat log untuk detail)";
            }
            
            return redirect()->route('admin.taxas.sync')->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Taxa sync error: ' . $e->getMessage());
            return redirect()->route('admin.taxas.sync')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Menampilkan modal pencarian taxa untuk dipilih
     */
    public function selectModal(Request $request)
    {
        $query = $request->input('q', '');
        $kingdom = $request->input('kingdom', 'Animalia');
        
        $results = collect([]);
        
        if (!empty($query)) {
            // Coba cari di database lokal terlebih dahulu
            $localQuery = TaxaLocal::query();
            
            if ($kingdom) {
                $localQuery->where('kingdom', $kingdom);
            }
            
            $localQuery->where(function ($q) use ($query) {
                $q->where('scientific_name', 'like', "%{$query}%")
                  ->orWhere('common_name', 'like', "%{$query}%");
            });
            
            $results = $localQuery->limit(10)->get();
            
            // Jika hasil lokal kosong, cari di API
            if ($results->isEmpty()) {
                $apiEndpoint = "{$this->apiBaseUrl}/taksa/search";
                
                if ($kingdom) {
                    $apiEndpoint = "{$this->apiBaseUrl}/taksa/search/" . strtolower($kingdom);
                }
                
                $response = Http::get($apiEndpoint, [
                    'q' => $query,
                    'limit' => 10,
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Simpan hasil ke database lokal
                    if (isset($data['data']) && is_array($data['data'])) {
                        foreach ($data['data'] as $item) {
                            $this->storeOrUpdateTaxaLocal($item);
                        }
                        
                        // Ambil lagi dari database lokal setelah disimpan
                        $results = $localQuery->limit(10)->get();
                    }
                }
            }
        }
        
        return view('admin.taxas.select_modal', compact('results', 'query', 'kingdom'));
    }

    /**
     * Memperbarui status IUCN dari API eksternal
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateIucnStatus($id)
    {
        try {
            $taxa = TaxaLocal::findOrFail($id);
            
            // Ambil nama ilmiah untuk pencarian
            $scientificName = $taxa->scientific_name;
            if (empty($scientificName)) {
                return redirect()->back()->with('error', 'Nama ilmiah tidak tersedia untuk pencarian IUCN');
            }
            
            // Panggil API IUCN
            $response = Http::get('https://apiv3.iucnredlist.org/api/v3/species/' . urlencode($scientificName), [
                'token' => config('services.iucn.token', 'demo_token'),
            ]);
            
            if ($response->successful() && isset($response['result'][0])) {
                $iucnData = $response['result'][0];
                
                // Update data IUCN
                $taxa->update([
                    'iucn_status' => $iucnData['category'] ?? null,
                    'iucn_criteria' => $iucnData['criteria'] ?? null,
                    'updated_at' => now(),
                ]);
                
                \Log::info('IUCN status updated for taxa ID: ' . $id);
                return redirect()->back()->with('success', 'Status IUCN berhasil diperbarui');
            } else {
                \Log::warning('IUCN API returned no data for: ' . $scientificName);
                return redirect()->back()->with('warning', 'Tidak ada data IUCN yang ditemukan untuk spesies ini');
            }
        } catch (\Exception $e) {
            \Log::error('Error updating IUCN status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui status CITES dari API eksternal
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCitesStatus($id)
    {
        try {
            $taxa = TaxaLocal::findOrFail($id);
            
            // Ambil nama ilmiah untuk pencarian
            $scientificName = $taxa->scientific_name;
            if (empty($scientificName)) {
                return redirect()->back()->with('error', 'Nama ilmiah tidak tersedia untuk pencarian CITES');
            }
            
            // Panggil API CITES Checklist
            $response = Http::get('https://api.speciesplus.net/api/v1/taxon_concepts', [
                'name' => $scientificName,
                'X-Authentication-Token' => config('services.cites.token', 'demo_token'),
            ]);
            
            if ($response->successful() && isset($response['taxon_concepts'][0])) {
                $citesData = $response['taxon_concepts'][0];
                
                // Update data CITES
                $taxa->update([
                    'cites_status' => $citesData['cites_listing'] ?? null,
                    'cites_source' => 'Species+',
                    'cites_listing_date' => $citesData['listing_updated_at'] ?? null,
                    'updated_at' => now(),
                ]);
                
                \Log::info('CITES status updated for taxa ID: ' . $id);
                return redirect()->back()->with('success', 'Status CITES berhasil diperbarui');
            } else {
                \Log::warning('CITES API returned no data for: ' . $scientificName);
                return redirect()->back()->with('warning', 'Tidak ada data CITES yang ditemukan untuk spesies ini');
            }
        } catch (\Exception $e) {
            \Log::error('Error updating CITES status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Membandingkan data taxa dari database lokal dan amaturalist
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function compare(Request $request)
    {
        $search = $request->input('search');
        $query = Taxa::query();
        $localQuery = TaxaLocal::query();
        
        // Filter berdasarkan pencarian jika ada
        if ($search) {
            $query->where('scientific_name', 'like', "%{$search}%");
            $localQuery->where('scientific_name', 'like', "%{$search}%");
        }
        
        // Ambil data dari kedua database
        $amaturalistTaxa = $query->take(100)->get();
        $localTaxa = $localQuery->get();
        
        // Gabungkan data dan tandai sumbernya
        $combinedData = collect();
        
        // Tambahkan data dari amaturalist
        foreach ($amaturalistTaxa as $taxa) {
            $localMatch = $localTaxa->where('id', $taxa->id)->first();
            
            $iucnBadgeColor = 'secondary';
            if ($taxa->iucn_status) {
                $iucnBadgeColor = match ($taxa->iucn_status) {
                    'LC' => 'success',
                    'NT' => 'info',
                    'VU' => 'warning',
                    'EN', 'CR', 'EW', 'EX' => 'danger',
                    default => 'secondary',
                };
            }
            
            $citesBadgeColor = 'secondary';
            if ($taxa->cites_status) {
                $citesBadgeColor = match ($taxa->cites_status) {
                    'I' => 'danger',
                    'II' => 'warning',
                    'III' => 'info',
                    default => 'secondary',
                };
            }
            
            $combinedData->push([
                'id' => $taxa->id,
                'scientific_name' => $taxa->scientific_name,
                'in_local' => $localMatch ? true : false,
                'in_amaturalist' => true,
                'iucn_status' => $taxa->iucn_status,
                'iucn_badge_color' => $iucnBadgeColor,
                'cites_status' => $taxa->cites_status,
                'cites_badge_color' => $citesBadgeColor,
                'updated_at' => $localMatch ? $localMatch->updated_at->format('d M Y H:i') : '-',
            ]);
        }
        
        // Tambahkan data dari lokal yang tidak ada di amaturalist
        foreach ($localTaxa as $taxa) {
            if (!$amaturalistTaxa->where('id', $taxa->id)->first()) {
                $combinedData->push([
                    'id' => $taxa->id,
                    'scientific_name' => $taxa->scientific_name,
                    'in_local' => true,
                    'in_amaturalist' => false,
                    'iucn_status' => $taxa->iucn_status,
                    'iucn_badge_color' => $taxa->iucn_badge_color,
                    'cites_status' => $taxa->cites_status,
                    'cites_badge_color' => $taxa->cites_badge_color,
                    'updated_at' => $taxa->updated_at->format('d M Y H:i'),
                ]);
            }
        }
        
        // Urutkan berdasarkan nama ilmiah
        $sortedData = $combinedData->sortBy('scientific_name');
        
        // Buat paginator dari collection
        $perPage = 20;
        $page = $request->input('page', 1);
        $results = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedData->forPage($page, $perPage),
            $sortedData->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return view('admin.taxas.compare', compact('results'));
    }

    /**
     * Mengimpor data taxa dari amaturalist ke database lokal
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import($id)
    {
        try {
            $taxa = Taxa::findOrFail($id);
            
            // Import data ke database lokal
            TaxaLocal::updateOrCreate(
                ['id' => $taxa->id],
                [
                    'kingdom' => $taxa->kingdom,
                    'phylum' => $taxa->phylum,
                    'class' => $taxa->class,
                    'order' => $taxa->order,
                    'family' => $taxa->family,
                    'genus' => $taxa->genus,
                    'species' => $taxa->species,
                    'subspecies' => $taxa->subspecies,
                    'common_name' => $taxa->common_name,
                    'local_name' => $taxa->local_name,
                    'scientific_name' => $taxa->scientific_name,
                    'author' => $taxa->author,
                    'rank' => $taxa->rank,
                    'taxonomic_status' => $taxa->taxonomic_status,
                    'iucn_status' => $taxa->iucn_status,
                    'iucn_criteria' => $taxa->iucn_criteria,
                    'cites_status' => $taxa->cites_status,
                    'cites_source' => $taxa->cites_source,
                    'cites_listing_date' => $taxa->cites_listing_date,
                    'image_url' => $taxa->image_url,
                    'description' => $taxa->description,
                    'updated_at' => now(),
                ]
            );
            
            \Log::info('Taxa imported successfully', ['id' => $id]);
            return redirect()->back()->with('success', 'Data taxa berhasil diimpor');
        } catch (\Exception $e) {
            \Log::error('Error importing taxa', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menyinkronkan data taxa tunggal dari amaturalist ke database lokal
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncSingle($id)
    {
        try {
            $taxa = Taxa::findOrFail($id);
            $localTaxa = TaxaLocal::findOrFail($id);
            
            // Update data di database lokal
            $localTaxa->update([
                'kingdom' => $taxa->kingdom,
                'phylum' => $taxa->phylum,
                'class' => $taxa->class,
                'order' => $taxa->order,
                'family' => $taxa->family,
                'genus' => $taxa->genus,
                'species' => $taxa->species,
                'subspecies' => $taxa->subspecies,
                'common_name' => $taxa->common_name,
                'local_name' => $taxa->local_name,
                'scientific_name' => $taxa->scientific_name,
                'author' => $taxa->author,
                'rank' => $taxa->rank,
                'taxonomic_status' => $taxa->taxonomic_status,
                'iucn_status' => $taxa->iucn_status,
                'iucn_criteria' => $taxa->iucn_criteria,
                'cites_status' => $taxa->cites_status,
                'cites_source' => $taxa->cites_source,
                'cites_listing_date' => $taxa->cites_listing_date,
                'image_url' => $taxa->image_url,
                'description' => $taxa->description,
                'updated_at' => now(),
            ]);
            
            \Log::info('Taxa synced successfully', ['id' => $id]);
            return redirect()->back()->with('success', 'Data taxa berhasil disinkronkan');
        } catch (\Exception $e) {
            \Log::error('Error syncing taxa', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 