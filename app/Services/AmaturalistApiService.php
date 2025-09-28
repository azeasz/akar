<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class AmaturalistApiService
{
    private const BASE_URL = 'https://amaturalist.com/api';
    private const TIMEOUT = 30;
    private const MAX_RETRIES = 3;

    /**
     * Search taxa from API
     */
    public function searchTaxa(string $query = '', int $page = 1, int $perPage = 20): array
    {
        try {
            Log::info('[AmaturalistAPI] Starting search', [
                'query' => $query,
                'page' => $page,
                'per_page' => $perPage,
                'base_url' => self::BASE_URL
            ]);

            // For testing, return dummy data if query contains 'test'
            if (strpos(strtolower($query), 'test') !== false) {
                Log::info('[AmaturalistAPI] Returning test data');
                return [
                    'success' => true,
                    'data' => [
                        'results' => [
                            [
                                'id' => 999,
                                'scientific_name' => 'Test Species',
                                'common_name' => 'Test Animal',
                                'iucn_red_list_category' => 'VU',
                                'status_dilindungi' => 'Dilindungi'
                            ]
                        ],
                        'total' => 1
                    ],
                    'source' => 'test'
                ];
            }
            
            // For debugging, add special handling for 'perena' query
            if (strpos(strtolower($query), 'perena') !== false) {
                Log::info('[AmaturalistAPI] Special handling for perena query');
            }

            // Use the same endpoint structure as api.ts
            $endpoint = self::BASE_URL . '/taksa/search/animalia';
            $params = [
                'query' => $query,
                'page' => $page,
                'perpage' => $perPage,
                'akar' => 1
            ];
            
            Log::info('[AmaturalistAPI] Making request', [
                'endpoint' => $endpoint,
                'params' => $params
            ]);

            $response = Http::timeout(self::TIMEOUT)
                ->retry(self::MAX_RETRIES, 1000)
                ->get($endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('[AmaturalistAPI] Raw API response', [
                    'success' => $data['success'] ?? false,
                    'has_data' => isset($data['data']),
                    'data_count' => isset($data['data']) ? count($data['data']) : 0,
                    'raw_structure' => array_keys($data)
                ]);

                // Check if response has the expected structure
                if (isset($data['success']) && $data['success'] && isset($data['data'])) {
                    return [
                        'success' => true,
                        'data' => [
                            'results' => $data['data'], // The actual taxa data is in 'data' field
                            'total' => $data['pagination']['total'] ?? count($data['data']),
                            'pagination' => $data['pagination'] ?? null
                        ],
                        'source' => 'api'
                    ];
                } else {
                    Log::warning('[AmaturalistAPI] Unexpected response structure', [
                        'response' => $data
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Unexpected API response structure',
                        'source' => 'api'
                    ];
                }
            } else {
                Log::warning('[AmaturalistAPI] API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                // Fallback to second database
                return $this->searchTaxaFromSecondDB($query, $page, $perPage);
            }
        } catch (\Exception $e) {
            Log::error('[AmaturalistAPI] API request exception', [
                'error' => $e->getMessage(),
                'query' => $query,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to second database
            return $this->searchTaxaFromSecondDB($query, $page, $perPage);
        }
    }

    /**
     * Get specific taxa by ID
     */
    public function getTaxaById(int $taxaId): array
    {
        try {
            $url = self::BASE_URL . "/taksa/{$taxaId}";
            $params = [
                'akar' => 1,
                '_cb' => time() * 1000,
            ];

            Log::info('[AmaturalistAPI] Getting taxa by ID', [
                'taxa_id' => $taxaId,
                'url' => $url
            ]);

            $response = Http::timeout(self::TIMEOUT)
                ->retry(self::MAX_RETRIES, 1000)
                ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('[AmaturalistAPI] Taxa retrieved successfully', [
                    'taxa_id' => $taxaId,
                    'name' => $data['name'] ?? 'Unknown'
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'source' => 'api'
                ];
            }

            Log::warning('[AmaturalistAPI] Failed to get taxa', [
                'taxa_id' => $taxaId,
                'status' => $response->status()
            ]);

            // Fallback to second database
            return $this->getTaxaFromSecondDB($taxaId);

        } catch (Exception $e) {
            Log::error('[AmaturalistAPI] Exception getting taxa', [
                'taxa_id' => $taxaId,
                'error' => $e->getMessage()
            ]);

            // Fallback to second database
            return $this->getTaxaFromSecondDB($taxaId);
        }
    }

    /**
     * Sync priority fauna data with latest API data
     */
    public function syncPriorityFaunaData(int $priorityFaunaId): bool
    {
        try {
            $priorityFauna = \App\Models\PriorityFauna::find($priorityFaunaId);
            if (!$priorityFauna || !$priorityFauna->taxa_id) {
                return false;
            }

            $result = $this->getTaxaById($priorityFauna->taxa_id);
            
            if ($result['success'] && isset($result['data'])) {
                $taxaData = $result['data'];
                
                // Update priority fauna with latest data
                $priorityFauna->update([
                    'taxa_data' => $taxaData,
                    'scientific_name' => $taxaData['name'] ?? $priorityFauna->scientific_name,
                    'common_name' => $taxaData['common_name'] ?? $priorityFauna->common_name,
                    'iucn_status' => $taxaData['iucn_red_list_category'] ?? $priorityFauna->iucn_status,
                    'protection_status' => $taxaData['status_dilindungi'] ?? $priorityFauna->protection_status,
                    'last_api_sync' => now(),
                ]);

                Log::info('[AmaturalistAPI] Priority fauna synced', [
                    'priority_fauna_id' => $priorityFaunaId,
                    'taxa_id' => $priorityFauna->taxa_id,
                    'source' => $result['source']
                ]);

                return true;
            }

            return false;

        } catch (Exception $e) {
            Log::error('[AmaturalistAPI] Failed to sync priority fauna', [
                'priority_fauna_id' => $priorityFaunaId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Fallback search from second database
     */
    private function searchTaxaFromSecondDB(string $query, int $page, int $perPage): array
    {
        try {
            Log::info('[AmaturalistAPI] Using second DB fallback', [
                'query' => $query,
                'page' => $page
            ]);

            $connection = config('database.connections.second') ? 'second' : 'mysql';
            
            $queryBuilder = DB::connection($connection)
                ->table('taxa')
                ->where('rank', 'species')
                ->orderBy('name');

            if (!empty($query)) {
                $queryBuilder->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('common_name', 'like', "%{$query}%");
                });
            }

            $total = $queryBuilder->count();
            $results = $queryBuilder
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            return [
                'success' => true,
                'data' => [
                    'results' => $results->toArray(),
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                ],
                'source' => 'database'
            ];

        } catch (Exception $e) {
            Log::error('[AmaturalistAPI] Second DB fallback failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to search taxa from both API and database',
                'source' => 'none'
            ];
        }
    }

    /**
     * Fallback get taxa from second database
     */
    private function getTaxaFromSecondDB(int $taxaId): array
    {
        try {
            $connection = config('database.connections.second') ? 'second' : 'mysql';
            
            $taxa = DB::connection($connection)
                ->table('taxa')
                ->where('id', $taxaId)
                ->first();

            if ($taxa) {
                return [
                    'success' => true,
                    'data' => (array) $taxa,
                    'source' => 'database'
                ];
            }

            return [
                'success' => false,
                'error' => 'Taxa not found in database',
                'source' => 'database'
            ];

        } catch (Exception $e) {
            Log::error('[AmaturalistAPI] Failed to get taxa from second DB', [
                'taxa_id' => $taxaId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Database connection failed',
                'source' => 'none'
            ];
        }
    }

    /**
     * Get suggestions for taxa search (for autocomplete)
     */
    public function getTaxaSuggestions(string $query, int $limit = 10): array
    {
        Log::info('[AmaturalistAPI] Getting taxa suggestions', [
            'query' => $query,
            'limit' => $limit
        ]);

        if (strlen($query) < 2) {
            Log::info('[AmaturalistAPI] Query too short, returning empty');
            return ['success' => true, 'data' => [], 'message' => 'Query too short'];
        }

        $result = $this->searchTaxa($query, 1, $limit);
        
        Log::info('[AmaturalistAPI] Search taxa result', [
            'success' => $result['success'] ?? false,
            'has_data' => isset($result['data']),
            'has_results' => isset($result['data']['results']),
            'source' => $result['source'] ?? 'unknown'
        ]);
        
        if ($result['success'] && isset($result['data']['results'])) {
            $suggestions = collect($result['data']['results'])->map(function ($taxa) {
                // Use the correct field names from the API response
                $scientificName = $taxa['scientific_name'] ?? '';
                $commonName = $taxa['common_name'] ?? '';
                
                // Create display name
                $displayName = '';
                if (!empty($commonName)) {
                    $displayName = $commonName;
                    if (!empty($scientificName)) {
                        $displayName .= ' (' . $scientificName . ')';
                    }
                } else {
                    $displayName = $scientificName ?: 'Unknown Taxa';
                }

                return [
                    'id' => $taxa['id'] ?? null,
                    'name' => $scientificName,
                    'scientific_name' => $scientificName,
                    'common_name' => $commonName,
                    'display_name' => $displayName,
                    'iucn_status' => $taxa['iucn_red_list_category'] ?? null,
                    'protection_status' => $taxa['status_dilindungi'] ?? null,
                    'rank' => $taxa['taxon_rank'] ?? null,
                    'kingdom' => $taxa['kingdom'] ?? null,
                    'phylum' => $taxa['phylum'] ?? null,
                    'class' => $taxa['class'] ?? null,
                    'order' => $taxa['order'] ?? null,
                    'family' => $taxa['family'] ?? null,
                    'genus' => $taxa['genus'] ?? null,
                    'species' => $taxa['species'] ?? null,
                ];
            })->toArray();

            Log::info('[AmaturalistAPI] Processed suggestions', [
                'count' => count($suggestions),
                'first_item' => $suggestions[0] ?? null
            ]);

            return [
                'success' => true,
                'data' => $suggestions,
                'source' => $result['source']
            ];
        }

        Log::warning('[AmaturalistAPI] No valid results found', [
            'result' => $result
        ]);

        return $result;
    }
}
