<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Taxa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class TaxaController extends Controller
{
    /**
     * Mencari taxa berdasarkan scientific name atau common name untuk suggestion
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('query', '');
            $perpage = min(intval($request->get('perpage', 10)), 200);
            $page = max(intval($request->get('page', 1)), 1);
            $updateIucn = $request->get('update_iucn', false);
            $akarParam = $request->get('akar', null);

            // Preprocessing query untuk mengabaikan tanda "-"
            $cleanQuery = str_replace('-', '', $query);

            $taxaQuery = Taxa::where(function($q) use ($query, $cleanQuery) {
                // Cari dengan query asli
                $q->where('scientific_name', 'LIKE', "%{$query}%")
                  ->orWhere('Cname', 'LIKE', "%{$query}%")
                  ->orWhere('cname_species', 'LIKE', "%{$query}%");
                
                // Jika query mengandung tanda "-", tambahkan pencarian dengan query yang sudah dibersihkan
                if ($query !== $cleanQuery) {
                    $q->orWhere('scientific_name', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('Cname', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('cname_species', 'LIKE', "%{$cleanQuery}%");
                }
            })
            ->where('status', 'active')
            ->whereIn('taxonomic_status', ['ACCEPTED', 'SYNONYM']) // Tampilkan accepted dan synonym
            ->select([
                'id',
                'scientific_name',
                'Cname as common_name',
                'cname_species',
                'taxon_rank',
                'kingdom',
                'phylum',
                'class',
                'order',
                'family',
                'genus',
                'species',
                'iucn_red_list_category',
                'taxonomic_status',
                'accepted_taxon_key',
                'accepted_scientific_name',
                'updated_at'
            ]);

            // Optional filter: only taxa curated for AKAR app
            if (!is_null($akarParam)) {
                $akarFlag = in_array(strtolower(strval($akarParam)), ['1','true','yes','on'], true) ? 1 : 0;
                $taxaQuery->where('akar', $akarFlag);
            }
            
            // Get total count before pagination
            $total = $taxaQuery->count();
            
            // Apply pagination but get all results first to sort them properly
            $allTaxa = $taxaQuery->get();
            
            // Custom sorting function (prioritaskan ACCEPTED dibanding SYNONYM)
            $sortedTaxa = $allTaxa->sort(function ($a, $b) {
                // Status priority: ACCEPTED first, then SYNONYM
                $statusPriority = [
                    'ACCEPTED' => 1,
                    'SYNONYM' => 2,
                ];
                $statusA = $statusPriority[strtoupper($a->taxonomic_status ?? 'ACCEPTED')] ?? 99;
                $statusB = $statusPriority[strtoupper($b->taxonomic_status ?? 'ACCEPTED')] ?? 99;
                if ($statusA !== $statusB) {
                    return $statusA <=> $statusB;
                }
                // Prioritas urutan rank
                $rankPriority = [
                    'SPECIES' => 1,
                    'SUBSPECIES' => 2,
                    'VARIETY' => 3,
                    'FORM' => 4,
                    'GENUS' => 5,
                    'FAMILY' => 6,
                    'ORDER' => 7,
                    'CLASS' => 8,
                    'PHYLUM' => 9,
                    'KINGDOM' => 10
                ];
                
                $rankA = strtoupper($a->taxon_rank);
                $rankB = strtoupper($b->taxon_rank);
                
                // Jika keduanya SPECIES, maka urut berdasarkan nama
                if ($rankA === 'SPECIES' && $rankB === 'SPECIES') {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                // Jika salah satu SPECIES, prioritaskan
                if ($rankA === 'SPECIES') return -1;
                if ($rankB === 'SPECIES') return 1;
                
                // Jika keduanya sub-level dari SPECIES (SUBSPECIES, VARIETY, FORM), urut berdasarkan abjad
                $subLevels = ['SUBSPECIES', 'VARIETY', 'FORM'];
                if (in_array($rankA, $subLevels) && in_array($rankB, $subLevels)) {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                // Untuk level lain, urut berdasarkan prioritas rank
                $priorityA = $rankPriority[$rankA] ?? 999;
                $priorityB = $rankPriority[$rankB] ?? 999;
                
                if ($priorityA === $priorityB) {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                return $priorityA <=> $priorityB;
            });
            
            // Paginate manually after sorting
            $taxa = $sortedTaxa->slice(($page - 1) * $perpage, $perpage)->values();

            // Enrich SYNONYM taxa with accepted mapping info
            $taxa = $taxa->map(function ($item) {
                $item->is_synonym = strtoupper($item->taxonomic_status ?? '') === 'SYNONYM';
                if ($item->is_synonym) {
                    $accepted = null;
                    if (!empty($item->accepted_taxon_key)) {
                        $accepted = Taxa::where('taxon_key', $item->accepted_taxon_key)
                            ->where('status', 'active')
                            ->where('taxonomic_status', 'ACCEPTED')
                            ->select(['id', 'scientific_name'])
                            ->first();
                    }
                    if (!$accepted && !empty($item->accepted_scientific_name)) {
                        $accepted = Taxa::where('scientific_name', $item->accepted_scientific_name)
                            ->where('status', 'active')
                            ->where('taxonomic_status', 'ACCEPTED')
                            ->select(['id', 'scientific_name'])
                            ->first();
                    }
                    if ($accepted) {
                        $item->accepted = [
                            'id' => $accepted->id,
                            'scientific_name' => $accepted->scientific_name,
                        ];
                    } else {
                        $item->accepted = [
                            'id' => null,
                            'scientific_name' => $item->accepted_scientific_name,
                        ];
                    }
                }
                return $item;
            });

            // Update IUCN status from API if requested
            if ($updateIucn && $taxa->count() > 0) {
                foreach ($taxa as $taxon) {
                    if ($taxon->scientific_name && ($taxon->taxon_rank == 'SPECIES' || $taxon->taxon_rank == 'SUBSPECIES')) {
                        $iucnStatus = $this->getIUCNStatusFromAPI($taxon->scientific_name);
                        if ($iucnStatus) {
                            $taxon->iucn_red_list_category = $iucnStatus;
                            Taxa::where('id', $taxon->id)->update([
                                'iucn_red_list_category' => $iucnStatus,
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data ditemukan',
                'data' => $taxa,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perpage,
                    'current_page' => $page,
                    'total_pages' => ceil($total / $perpage)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data taxa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mencari taxa khusus kingdom Animalia
     */
    public function searchAnimalia(Request $request)
    {
        try {
            $query = $request->get('query', '');
            $perpage = min(intval($request->get('perpage', 10)), 200);
            $page = max(intval($request->get('page', 1)), 1);
            $updateIucn = $request->get('update_iucn', false);
            $akarParam = $request->get('akar', null);

            // Preprocessing query untuk mengabaikan tanda "-"
            $cleanQuery = str_replace('-', '', $query);

            $taxaQuery = Taxa::where(function($q) use ($query, $cleanQuery) {
                // Cari dengan query asli
                $q->where('scientific_name', 'LIKE', "%{$query}%")
                  ->orWhere('Cname', 'LIKE', "%{$query}%")
                  ->orWhere('cname_species', 'LIKE', "%{$query}%");
                
                // Jika query mengandung tanda "-", tambahkan pencarian dengan query yang sudah dibersihkan
                if ($query !== $cleanQuery) {
                    $q->orWhere('scientific_name', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('Cname', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('cname_species', 'LIKE', "%{$cleanQuery}%");
                }
            })
            ->where('status', 'active')
            ->whereIn('taxonomic_status', ['ACCEPTED', 'SYNONYM']) // Tampilkan accepted dan synonym
            ->where('kingdom', 'Animalia')
            ->select([
                'id',
                'scientific_name',
                'Cname as common_name',
                'cname_species',
                'taxon_rank',
                'kingdom',
                'phylum',
                'class',
                'order',
                'family',
                'genus',
                'species',
                'iucn_red_list_category',
                'taxonomic_status',
                'accepted_taxon_key',
                'accepted_scientific_name'
            ]);

            // Optional filter: only taxa curated for AKAR app
            if (!is_null($akarParam)) {
                $akarFlag = in_array(strtolower(strval($akarParam)), ['1','true','yes','on'], true) ? 1 : 0;
                $taxaQuery->where('akar', $akarFlag);
            }
            
            // Get total count before pagination
            $total = $taxaQuery->count();
            
            // Apply pagination but get all results first to sort them properly
            $allTaxa = $taxaQuery->get();
            
            // Custom sorting function
            $sortedTaxa = $allTaxa->sort(function ($a, $b) {
                // Prioritas urutan rank
                $rankPriority = [
                    'SPECIES' => 1,
                    'SUBSPECIES' => 2,
                    'VARIETY' => 3,
                    'FORM' => 4,
                    'GENUS' => 5,
                    'FAMILY' => 6,
                    'ORDER' => 7,
                    'CLASS' => 8,
                    'PHYLUM' => 9,
                    'KINGDOM' => 10
                ];
                
                $rankA = strtoupper($a->taxon_rank);
                $rankB = strtoupper($b->taxon_rank);
                
                // Jika keduanya SPECIES, maka urut berdasarkan nama
                if ($rankA === 'SPECIES' && $rankB === 'SPECIES') {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                // Jika salah satu SPECIES, prioritaskan
                if ($rankA === 'SPECIES') return -1;
                if ($rankB === 'SPECIES') return 1;
                
                // Jika keduanya sub-level dari SPECIES (SUBSPECIES, VARIETY, FORM), urut berdasarkan abjad
                $subLevels = ['SUBSPECIES', 'VARIETY', 'FORM'];
                if (in_array($rankA, $subLevels) && in_array($rankB, $subLevels)) {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                // Untuk level lain, urut berdasarkan prioritas rank
                $priorityA = $rankPriority[$rankA] ?? 999;
                $priorityB = $rankPriority[$rankB] ?? 999;
                
                if ($priorityA === $priorityB) {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                return $priorityA <=> $priorityB;
            });
            
            // Paginate manually after sorting
            $taxa = $sortedTaxa->slice(($page - 1) * $perpage, $perpage)->values();

            // Update IUCN status from API if requested
            if ($updateIucn && $taxa->count() > 0) {
                foreach ($taxa as $taxon) {
                    if ($taxon->scientific_name && ($taxon->taxon_rank == 'SPECIES' || $taxon->taxon_rank == 'SUBSPECIES')) {
                        $iucnStatus = $this->getIUCNStatusFromAPI($taxon->scientific_name);
                        if ($iucnStatus) {
                            $taxon->iucn_red_list_category = $iucnStatus;
                            Taxa::where('id', $taxon->id)->update([
                                'iucn_red_list_category' => $iucnStatus,
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Animalia ditemukan',
                'data' => $taxa,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perpage,
                    'current_page' => $page,
                    'total_pages' => ceil($total / $perpage)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data Animalia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mencari taxa khusus kingdom Plantae
     */
    public function searchPlantae(Request $request)
    {
        try {
            $query = $request->get('query', '');
            $perpage = min(intval($request->get('perpage', 10)), 200);
            $page = max(intval($request->get('page', 1)), 1);
            $updateIucn = $request->get('update_iucn', false);
            $akarParam = $request->get('akar', null);

            // Preprocessing query untuk mengabaikan tanda "-"
            $cleanQuery = str_replace('-', '', $query);

            $taxaQuery = Taxa::where(function($q) use ($query, $cleanQuery) {
                // Cari dengan query asli
                $q->where('scientific_name', 'LIKE', "%{$query}%")
                  ->orWhere('Cname', 'LIKE', "%{$query}%")
                  ->orWhere('cname_species', 'LIKE', "%{$query}%");
                
                // Jika query mengandung tanda "-", tambahkan pencarian dengan query yang sudah dibersihkan
                if ($query !== $cleanQuery) {
                    $q->orWhere('scientific_name', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('Cname', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('cname_species', 'LIKE', "%{$cleanQuery}%");
                }
            })
            ->where('status', 'active')
            ->where('taxonomic_status', 'ACCEPTED') // Hanya tampilkan taksa dengan status accepted
            ->where('kingdom', 'Plantae')
            ->select([
                'id',
                'scientific_name',
                'Cname as common_name',
                'cname_species',
                'taxon_rank',
                'kingdom',
                'phylum',
                'class',
                'order',
                'family',
                'genus',
                'species',
                'iucn_red_list_category'
            ]);

            // Optional filter: only taxa curated for AKAR app
            if (!is_null($akarParam)) {
                $akarFlag = in_array(strtolower(strval($akarParam)), ['1','true','yes','on'], true) ? 1 : 0;
                $taxaQuery->where('akar', $akarFlag);
            }
            
            // Get total count before pagination
            $total = $taxaQuery->count();
            
            // Apply pagination but get all results first to sort them properly
            $allTaxa = $taxaQuery->get();
            
            // Custom sorting function
            $sortedTaxa = $allTaxa->sort(function ($a, $b) {
                // Prioritas urutan rank
                $rankPriority = [
                    'SPECIES' => 1,
                    'SUBSPECIES' => 2,
                    'VARIETY' => 3,
                    'FORM' => 4,
                    'GENUS' => 5,
                    'FAMILY' => 6,
                    'ORDER' => 7,
                    'CLASS' => 8,
                    'PHYLUM' => 9,
                    'KINGDOM' => 10
                ];
                
                $rankA = strtoupper($a->taxon_rank);
                $rankB = strtoupper($b->taxon_rank);
                
                // Jika keduanya SPECIES, maka urut berdasarkan nama
                if ($rankA === 'SPECIES' && $rankB === 'SPECIES') {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                // Jika salah satu SPECIES, prioritaskan
                if ($rankA === 'SPECIES') return -1;
                if ($rankB === 'SPECIES') return 1;
                
                // Jika keduanya sub-level dari SPECIES (SUBSPECIES, VARIETY, FORM), urut berdasarkan abjad
                $subLevels = ['SUBSPECIES', 'VARIETY', 'FORM'];
                if (in_array($rankA, $subLevels) && in_array($rankB, $subLevels)) {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                // Untuk level lain, urut berdasarkan prioritas rank
                $priorityA = $rankPriority[$rankA] ?? 999;
                $priorityB = $rankPriority[$rankB] ?? 999;
                
                if ($priorityA === $priorityB) {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                return $priorityA <=> $priorityB;
            });
            
            // Paginate manually after sorting
            $taxa = $sortedTaxa->slice(($page - 1) * $perpage, $perpage)->values();

            // Update IUCN status from API if requested
            if ($updateIucn && $taxa->count() > 0) {
                foreach ($taxa as $taxon) {
                    if ($taxon->scientific_name && ($taxon->taxon_rank == 'SPECIES' || $taxon->taxon_rank == 'SUBSPECIES')) {
                        $iucnStatus = $this->getIUCNStatusFromAPI($taxon->scientific_name);
                        if ($iucnStatus) {
                            $taxon->iucn_red_list_category = $iucnStatus;
                            Taxa::where('id', $taxon->id)->update([
                                'iucn_red_list_category' => $iucnStatus,
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Plantae ditemukan',
                'data' => $taxa,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perpage,
                    'current_page' => $page,
                    'total_pages' => ceil($total / $perpage)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data Plantae',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mencari taxa khusus kingdom Fungi
     */
    public function searchFungi(Request $request)
    {
        try {
            $query = $request->get('query', '');
            $perpage = min(intval($request->get('perpage', 10)), 200);
            $page = max(intval($request->get('page', 1)), 1);
            $updateIucn = $request->get('update_iucn', false);

            // Preprocessing query untuk mengabaikan tanda "-"
            $cleanQuery = str_replace('-', '', $query);

            $taxaQuery = Taxa::where(function($q) use ($query, $cleanQuery) {
                // Cari dengan query asli
                $q->where('scientific_name', 'LIKE', "%{$query}%")
                  ->orWhere('Cname', 'LIKE', "%{$query}%")
                  ->orWhere('cname_species', 'LIKE', "%{$query}%");
                
                // Jika query mengandung tanda "-", tambahkan pencarian dengan query yang sudah dibersihkan
                if ($query !== $cleanQuery) {
                    $q->orWhere('scientific_name', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('Cname', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('cname_species', 'LIKE', "%{$cleanQuery}%");
                }
            })
            ->where('status', 'active')
            ->where('taxonomic_status', 'ACCEPTED') // Hanya tampilkan taksa dengan status accepted
            ->where('kingdom', 'Fungi')
            ->select([
                'id',
                'scientific_name',
                'Cname as common_name',
                'cname_species',
                'taxon_rank',
                'kingdom',
                'phylum',
                'class',
                'order',
                'family',
                'genus',
                'species',
                'iucn_red_list_category'
            ]);
            
            // Get total count before pagination
            $total = $taxaQuery->count();
            
            // Apply pagination but get all results first to sort them properly
            $allTaxa = $taxaQuery->get();
            
            // Custom sorting function
            $sortedTaxa = $allTaxa->sort(function ($a, $b) {
                // Prioritas urutan rank
                $rankPriority = [
                    'SPECIES' => 1,
                    'SUBSPECIES' => 2,
                    'VARIETY' => 3,
                    'FORM' => 4,
                    'GENUS' => 5,
                    'FAMILY' => 6,
                    'ORDER' => 7,
                    'CLASS' => 8,
                    'PHYLUM' => 9,
                    'KINGDOM' => 10
                ];
                
                $rankA = strtoupper($a->taxon_rank);
                $rankB = strtoupper($b->taxon_rank);
                
                // Jika keduanya SPECIES, maka urut berdasarkan nama
                if ($rankA === 'SPECIES' && $rankB === 'SPECIES') {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                // Jika salah satu SPECIES, prioritaskan
                if ($rankA === 'SPECIES') return -1;
                if ($rankB === 'SPECIES') return 1;
                
                // Jika keduanya sub-level dari SPECIES (SUBSPECIES, VARIETY, FORM), urut berdasarkan abjad
                $subLevels = ['SUBSPECIES', 'VARIETY', 'FORM'];
                if (in_array($rankA, $subLevels) && in_array($rankB, $subLevels)) {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                // Untuk level lain, urut berdasarkan prioritas rank
                $priorityA = $rankPriority[$rankA] ?? 999;
                $priorityB = $rankPriority[$rankB] ?? 999;
                
                if ($priorityA === $priorityB) {
                    return $a->scientific_name <=> $b->scientific_name;
                }
                
                return $priorityA <=> $priorityB;
            });
            
            // Paginate manually after sorting
            $taxa = $sortedTaxa->slice(($page - 1) * $perpage, $perpage)->values();

            // Update IUCN status from API if requested
            if ($updateIucn && $taxa->count() > 0) {
                foreach ($taxa as $taxon) {
                    if ($taxon->scientific_name && ($taxon->taxon_rank == 'SPECIES' || $taxon->taxon_rank == 'SUBSPECIES')) {
                        $iucnStatus = $this->getIUCNStatusFromAPI($taxon->scientific_name);
                        if ($iucnStatus) {
                            $taxon->iucn_red_list_category = $iucnStatus;
                            Taxa::where('id', $taxon->id)->update([
                                'iucn_red_list_category' => $iucnStatus,
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Fungi ditemukan',
                'data' => $taxa,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perpage,
                    'current_page' => $page,
                    'total_pages' => ceil($total / $perpage)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data Fungi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan detail taxa berdasarkan ID
     */
    public function detail($id)
    {
        try {
            $taxa = Taxa::where('id', $id)
                ->where('status', 'active')
                ->select([
                    'id',
                    'scientific_name',
                    'Cname as common_name',
                    'cname_species',
                    'taxon_rank',
                    'kingdom',
                    'phylum',
                    'class',
                    'order',
                    'family',
                    'genus',
                    'species',
                    'iucn_red_list_category',
                    'description',
                    'status_kepunahan'
                ])
                ->first();

            if (!$taxa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data taxa tidak ditemukan'
                ], 404);
            }

            // Jika level taksonomi adalah spesies, coba cek status IUCN dari API
            if ($taxa->taxon_rank == 'SPECIES' || $taxa->taxon_rank == 'SUBSPECIES') {
                $iucnStatus = $this->getIUCNStatusFromAPI($taxa->scientific_name);
                if ($iucnStatus) {
                    // Update di database jika status berubah atau belum ada
                    if ($taxa->iucn_red_list_category != $iucnStatus) {
                        Taxa::where('id', $id)->update([
                            'iucn_red_list_category' => $iucnStatus,
                            'updated_at' => now()
                        ]);
                        $taxa->iucn_red_list_category = $iucnStatus;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data ditemukan',
                'data' => $taxa
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail taxa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghasilkan database SQLite yang berisi data taksa dengan status ACCEPTED
     * untuk digunakan secara offline di frontend
     * 
     * CATATAN: Fungsi ini hanya mengambil data taksa dari kingdom Animalia
     * untuk menghindari masalah memory exhausted. Jika ingin mengambil data
     * dari kingdom lain, buat endpoint terpisah atau gunakan metode lain.
     * 
     * @return \Illuminate\Http\Response File SQLite atau pesan error
     */
    public function generateSqliteDatabase()
    {
        try {
            // Atur batas memori yang lebih tinggi untuk fungsi ini
            ini_set('memory_limit', '256M');
            
            // Buat file SQLite sementara
            $tempFile = tempnam(sys_get_temp_dir(), 'taxa_db_');
            $dbPath = $tempFile . '.sqlite';
            rename($tempFile, $dbPath);
            
            // Buat koneksi SQLite
            $pdo = new \PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Buat tabel taksa
            $pdo->exec('
                CREATE TABLE taxa (
                    id INTEGER PRIMARY KEY,
                    scientific_name TEXT,
                    common_name TEXT,
                    cname_species TEXT,
                    taxon_rank TEXT,
                    kingdom TEXT,
                    phylum TEXT,
                    class TEXT,
                    "order" TEXT,
                    family TEXT,
                    genus TEXT,
                    species TEXT,
                    iucn_red_list_category TEXT
                )
            ');
            
            // Ambil data taksa dari database utama - HANYA KINGDOM ANIMALIA
            // Gunakan pagination untuk mengurangi penggunaan memori
            $page = 1;
            $perPage = 500; // Proses 500 record per batch (dikurangi dari 1000)
            $totalProcessed = 0;
            $maxRecords = 20000; // Batasi jumlah maksimum record untuk mengurangi ukuran database
            
            do {
                $offset = ($page - 1) * $perPage;
                
                $taxaData = Taxa::where('status', 'active')
                    ->where('taxonomic_status', 'ACCEPTED')
                    ->where('kingdom', 'Animalia') // Hanya kingdom Animalia
                    ->whereIn('taxon_rank', ['SPECIES', 'GENUS', 'FAMILY', 'ORDER', 'CLASS', 'PHYLUM', 'KINGDOM']) // Hanya rank utama
                    ->select([
                        'id',
                        'scientific_name',
                        'Cname as common_name',
                        'cname_species',
                        'taxon_rank',
                        'kingdom',
                        'phylum',
                        'class',
                        'order',
                        'family',
                        'genus',
                        'species',
                        'iucn_red_list_category'
                    ])
                    ->offset($offset)
                    ->limit($perPage)
                    ->get();
                
                // Jika tidak ada data lagi atau sudah mencapai batas maksimum, keluar dari loop
                if ($taxaData->isEmpty() || $totalProcessed >= $maxRecords) {
                    break;
                }
                
                // Masukkan data ke database SQLite
                $stmt = $pdo->prepare('
                    INSERT INTO taxa (
                        id, scientific_name, common_name, cname_species, taxon_rank,
                        kingdom, phylum, class, "order", family, genus, species, iucn_red_list_category
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ');
                
                $pdo->beginTransaction();
                foreach ($taxaData as $taxa) {
                    $stmt->execute([
                        $taxa->id,
                        $taxa->scientific_name,
                        $taxa->common_name,
                        $taxa->cname_species,
                        $taxa->taxon_rank,
                        $taxa->kingdom,
                        $taxa->phylum,
                        $taxa->class,
                        $taxa->order,
                        $taxa->family,
                        $taxa->genus,
                        $taxa->species,
                        $taxa->iucn_red_list_category
                    ]);
                    $totalProcessed++;
                }
                $pdo->commit();
                
                // Pindah ke halaman berikutnya
                $page++;
                
                // Bebaskan memori
                unset($taxaData);
                gc_collect_cycles();
                
            } while (true);
            
            // Log jumlah total data yang diproses
            $memoryUsage = memory_get_usage(true) / 1024 / 1024;
            $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
            Log::info("SQLite database generated with $totalProcessed Animalia taxa records. Memory usage: {$memoryUsage}MB, Peak: {$peakMemory}MB");
            
            // Tutup koneksi database
            $pdo = null;
            
            // Baca file database
            $fileContent = file_get_contents($dbPath);
            
            // Hapus file sementara
            unlink($dbPath);
            
            // Kirim file sebagai respons
            return response($fileContent, 200, [
                'Content-Type' => 'application/x-sqlite3',
                'Content-Disposition' => 'attachment; filename="taxa_database_animalia.sqlite"',
                'Content-Length' => strlen($fileContent),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating SQLite database: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'memory_usage' => memory_get_usage(true) / 1024 / 1024 . 'MB',
                'peak_memory' => memory_get_peak_usage(true) / 1024 / 1024 . 'MB'
            ]);
            
            // Periksa apakah error terkait dengan memori
            if (strpos($e->getMessage(), 'memory') !== false || strpos($e->getMessage(), 'exhausted') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan memori saat membuat database SQLite. Coba lagi nanti atau hubungi administrator.',
                    'error' => $e->getMessage(),
                    'memory_info' => [
                        'usage' => memory_get_usage(true) / 1024 / 1024 . 'MB',
                        'peak' => memory_get_peak_usage(true) / 1024 / 1024 . 'MB',
                        'limit' => ini_get('memory_limit')
                    ]
                ], 500);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat database SQLite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil semua data taksa dengan status ACCEPTED untuk penggunaan offline
     */
    public function getAllTaxa(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 500);
            $perPage = min($perPage, 1000);

            $query = Taxa::where('status', 'active')
                ->where('taxonomic_status', 'ACCEPTED')
                ->select([
                    'id',
                    'scientific_name',
                    'Cname as common_name',
                    'cname_species',
                    'taxon_rank',
                    'kingdom',
                    'phylum',
                    'class',
                    'order',
                    'family',
                    'genus',
                    'species',
                    'iucn_red_list_category'
                ]);

            if ($request->has('kingdom')) {
                $query->where('kingdom', $request->input('kingdom'));
            }

            $totalRecords = $query->count();
            $totalPages = ceil($totalRecords / $perPage);

            $taxaData = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $transformedData = $taxaData->map(function($taxa) {
                $fullData = $taxa->toArray();
                // Hapus common_name karena sudah ada di level atas
                unset($fullData['common_name']);

                return [
                    'id' => $taxa->id,
                    'scientific_name' => $taxa->scientific_name,
                    'common_name' => $taxa->common_name,
                    'cname_species' => $taxa->cname_species,
                    'rank' => strtolower($taxa->taxon_rank),
                    'taxon_rank' => $taxa->taxon_rank,
                    'kingdom' => $taxa->kingdom,
                    'phylum' => $taxa->phylum,
                    'class' => $taxa->class,
                    'order' => $taxa->order, // Pastikan nama kolom di DB adalah 'order'
                    'family' => $taxa->family,
                    'genus' => $taxa->genus,
                    'species' => $taxa->species,
                    'iucn_red_list_category' => $taxa->iucn_red_list_category,
                    'full_data' => $fullData
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data taksa berhasil diambil',
                'data' => $transformedData,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $totalRecords,
                    'last_page' => $totalPages,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting all taxa: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data taksa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil status IUCN dari API IUCN Red List
     * 
     * @param string $scientificName Nama ilmiah spesies
     * @return string|null Status IUCN atau null jika tidak ditemukan
     */
    private function getIUCNStatusFromAPI($scientificName)
    {
        try {
            // Pisahkan nama ilmiah menjadi genus dan species
            $nameParts = explode(' ', $scientificName);
            $genusName = $nameParts[0];
            $speciesName = isset($nameParts[1]) ? $nameParts[1] : '';
            
            // Jika tidak ada species name, tidak bisa melakukan pencarian
            if (empty($speciesName)) {
                Log::info('IUCN search skipped: No species name provided', [
                    'scientific_name' => $scientificName
                ]);
                return null;
            }
            
            $client = new Client([
                'timeout' => 10, // Tambahkan timeout untuk mencegah request terlalu lama
                'connect_timeout' => 5,
                'http_errors' => false, // Jangan lempar exception untuk HTTP error
                'verify' => false // Disable SSL verification jika diperlukan
            ]);
            
            Log::info('Requesting IUCN status', [
                'genus' => $genusName,
                'species' => $speciesName,
                'url' => "https://api.iucnredlist.org/api/v4/taxa/scientific_name?genus_name=".urlencode($genusName)."&species_name=".urlencode($speciesName)
            ]);
            
            $response = $client->request('GET', 
                "https://api.iucnredlist.org/api/v4/taxa/scientific_name?genus_name=".urlencode($genusName)."&species_name=".urlencode($speciesName), 
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'H4mxtPMSmNmCDZL1YmFrr85Y7tPJawcyRKhQ'
                    ]
                ]
            );
            
            // Periksa status code
            if ($response->getStatusCode() !== 200) {
                Log::warning('IUCN API returned non-200 status code', [
                    'status_code' => $response->getStatusCode(),
                    'scientific_name' => $scientificName,
                    'reason' => $response->getReasonPhrase()
                ]);
                return null;
            }
            
            // Ambil body respons
            $body = (string) $response->getBody();
            
            // Periksa apakah body kosong
            if (empty($body)) {
                Log::warning('IUCN API returned empty response', [
                    'scientific_name' => $scientificName
                ]);
                return null;
            }
            
            // Coba decode JSON dengan penanganan error
            $data = json_decode($body, true);
            
            // Periksa apakah JSON valid
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('IUCN API returned invalid JSON', [
                    'scientific_name' => $scientificName,
                    'error' => json_last_error_msg(),
                    'body_sample' => substr($body, 0, 500) // Log sebagian dari body untuk debugging
                ]);
                return null;
            }
            
            // Log respons untuk debugging
            Log::info('IUCN API response received', [
                'scientific_name' => $scientificName,
                'has_assessments' => isset($data['assessments']) && !empty($data['assessments']),
                'assessments_count' => isset($data['assessments']) ? count($data['assessments']) : 0
            ]);
            
            // Periksa apakah ada data assessment dan ambil yang terbaru (latest)
            if (isset($data['assessments']) && !empty($data['assessments'])) {
                foreach ($data['assessments'] as $assessment) {
                    if (isset($assessment['latest']) && $assessment['latest'] === true) {
                        Log::info('IUCN status found (latest)', [
                            'scientific_name' => $scientificName,
                            'status' => $assessment['red_list_category_code']
                        ]);
                        return $assessment['red_list_category_code'];
                    }
                }
                
                // Jika tidak ada yang latest, ambil yang pertama
                Log::info('IUCN status found (first)', [
                    'scientific_name' => $scientificName,
                    'status' => $data['assessments'][0]['red_list_category_code']
                ]);
                return $data['assessments'][0]['red_list_category_code'];
            }
            
            Log::info('No IUCN status found', [
                'scientific_name' => $scientificName
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching IUCN status: ' . $e->getMessage(), [
                'scientific_name' => $scientificName,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Endpoint untuk mendapatkan status IUCN langsung dari API
     */
    public function getIUCNStatus(Request $request)
    {
        try {
            $request->validate([
                'scientific_name' => 'required|string'
            ]);

            $scientificName = $request->scientific_name;
            
            Log::info('Fetching IUCN status for: ' . $scientificName);
            
            $iucnStatus = $this->getIUCNStatusFromAPI($scientificName);
            
            if ($iucnStatus === null) {
                Log::info('No IUCN status found for: ' . $scientificName);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'scientific_name' => $scientificName,
                        'iucn_status' => null,
                        'message' => 'Status IUCN tidak ditemukan untuk spesies ini'
                    ]
                ]);
            }

            Log::info('IUCN status found: ' . $iucnStatus . ' for: ' . $scientificName);
            
            // Jika nama ilmiah cocok dengan record di database, update statusnya
            $taxa = Taxa::where('scientific_name', $scientificName)
                ->where('status', 'active')
                ->first();
                
            if ($taxa && $taxa->iucn_red_list_category != $iucnStatus) {
                Taxa::where('scientific_name', $scientificName)
                    ->update([
                        'iucn_red_list_category' => $iucnStatus,
                        'updated_at' => now()
                    ]);
                Log::info('Updated IUCN status in database for: ' . $scientificName);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'scientific_name' => $scientificName,
                    'iucn_status' => $iucnStatus,
                    'database_updated' => isset($taxa) && $taxa->iucn_red_list_category != $iucnStatus
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting IUCN status: ' . $e->getMessage(), [
                'scientific_name' => $request->scientific_name ?? 'unknown',
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil status IUCN: ' . $e->getMessage()
            ], 500);
        }
    }
} 