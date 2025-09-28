<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FobiApiService
{
    private $baseUrl;
    private $apiToken;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.fobi_api.base_url', env('FOBI_API_BASE_URL'));
        $this->apiToken = config('services.fobi_api.token', env('FOBI_API_TOKEN')); // Optional
        $this->timeout = config('services.fobi_api.timeout', 30);
    }

    /**
     * Get HTTP client with optional token authentication
     */
    private function getHttpClient()
    {
        $client = Http::timeout($this->timeout);
        
        // Add token authentication only if token is provided
        if (!empty($this->apiToken)) {
            $client = $client->withToken($this->apiToken);
        }
        
        return $client;
    }

    /**
     * Get all badges for Akar application
     */
    public function getBadges($filters = [])
    {
        try {
            $cacheKey = 'fobi_api_badges_' . md5(serialize($filters));
            
            return Cache::remember($cacheKey, 300, function () use ($filters) { // 5 minutes cache
                $response = $this->getHttpClient()
                    ->get($this->baseUrl . '/api/public/badges/app/akar', $filters);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('FOBI API Error - Get Badges', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'filters' => $filters
                ]);

                return null;
            });
        } catch (\Exception $e) {
            Log::error('FOBI API Exception - Get Badges', [
                'message' => $e->getMessage(),
                'filters' => $filters
            ]);
            return null;
        }
    }

    /**
     * Get single badge by ID
     */
    public function getBadge($id)
    {
        try {
            $cacheKey = "fobi_api_badge_{$id}";
            
            return Cache::remember($cacheKey, 300, function () use ($id) {
                $response = $this->getHttpClient()
                    ->get($this->baseUrl . "/api/public/badges/{$id}");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('FOBI API Error - Get Badge', [
                    'badge_id' => $id,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return null;
            });
        } catch (\Exception $e) {
            Log::error('FOBI API Exception - Get Badge', [
                'badge_id' => $id,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create new badge
     */
    public function createBadge($data)
    {
        try {
            $client = $this->getHttpClient();
            
            // Handle file attachments if present
            if (isset($data['icon_active'])) {
                $client = $client->attach('icon_active', $data['icon_active']);
            }
            if (isset($data['icon_unactive'])) {
                $client = $client->attach('icon_unactive', $data['icon_unactive']);
            }
            if (isset($data['images_congrats'])) {
                $client = $client->attach('images_congrats', $data['images_congrats']);
            }
            
            $response = $client->post($this->baseUrl . '/api/badges', $data);

            if ($response->successful()) {
                // Clear cache
                Cache::flush(); // Or more specific cache clearing
                return $response->json();
            }

            Log::error('FOBI API Error - Create Badge', [
                'status' => $response->status(),
                'body' => $response->body(),
                'data' => $data
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FOBI API Exception - Create Badge', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            return null;
        }
    }

    /**
     * Update badge
     */
    public function updateBadge($id, $data)
    {
        try {
            $client = $this->getHttpClient();
            
            // Handle file attachments if present
            if (isset($data['icon_active'])) {
                $client = $client->attach('icon_active', $data['icon_active']);
            }
            if (isset($data['icon_unactive'])) {
                $client = $client->attach('icon_unactive', $data['icon_unactive']);
            }
            if (isset($data['images_congrats'])) {
                $client = $client->attach('images_congrats', $data['images_congrats']);
            }
            
            $response = $client->put($this->baseUrl . "/api/badges/{$id}", $data);

            if ($response->successful()) {
                // Clear specific cache
                Cache::forget("fobi_api_badge_{$id}");
                Cache::flush(); // Or more specific cache clearing
                return $response->json();
            }

            Log::error('FOBI API Error - Update Badge', [
                'badge_id' => $id,
                'status' => $response->status(),
                'body' => $response->body(),
                'data' => $data
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FOBI API Exception - Update Badge', [
                'badge_id' => $id,
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            return null;
        }
    }

    /**
     * Delete badge
     */
    public function deleteBadge($id)
    {
        try {
            $response = $this->getHttpClient()
                ->delete($this->baseUrl . "/api/badges/{$id}");

            if ($response->successful()) {
                // Clear cache
                Cache::forget("fobi_api_badge_{$id}");
                Cache::flush(); // Or more specific cache clearing
                return true;
            }

            Log::error('FOBI API Error - Delete Badge', [
                'badge_id' => $id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('FOBI API Exception - Delete Badge', [
                'badge_id' => $id,
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get badge types
     */
    public function getBadgeTypes()
    {
        try {
            $cacheKey = 'fobi_api_badge_types';
            
            return Cache::remember($cacheKey, 3600, function () { // 1 hour cache
                $response = $this->getHttpClient()
                    ->get($this->baseUrl . '/api/public/badges/types');

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('FOBI API Error - Get Badge Types', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return null;
            });
        } catch (\Exception $e) {
            Log::error('FOBI API Exception - Get Badge Types', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Health check API
     */
    public function healthCheck()
    {
        try {
            $response = $this->getHttpClient()
                ->get($this->baseUrl . '/api/health');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FOBI API Health Check Failed', [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }
}
