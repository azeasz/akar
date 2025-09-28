<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SpeciesDataService
{
    protected $iucnApiKey;
    protected $speciesPlusApiKey;

    public function __construct()
    {
        $this->iucnApiKey = config('services.iucn.key');
        $this->speciesPlusApiKey = config('services.speciesplus.token');
    }

    private function getGenusAndSpecies(string $speciesName): array
    {
        $parts = explode(' ', $speciesName);
        return [
            'genus' => $parts[0] ?? '',
            'species' => $parts[1] ?? ''
        ];
    }

    public function getIucnStatus(string $speciesName): ?string
    {
        if (empty($speciesName)) {
            return null;
        }

        $nameParts = $this->getGenusAndSpecies($speciesName);
        $genus = $nameParts['genus'];
        $species = $nameParts['species'];

        if (empty($genus) || empty($species)) {
            return null;
        }

        $cacheKey = 'iucn_status_' . Str::slug($genus . ' ' . $species);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($genus, $species) {
            try {
                $client = new \GuzzleHttp\Client([
                    'timeout' => 10,
                    'connect_timeout' => 5,
                    'http_errors' => false,
                ]);

                $response = $client->request('GET',
                    "https://api.iucnredlist.org/api/v4/taxa/scientific_name?genus_name=".urlencode($genus)."&species_name=".urlencode($species),
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => $this->iucnApiKey
                            ]
                    ]
                );

                if ($response->getStatusCode() === 200) {
                    $data = json_decode((string) $response->getBody(), true);

                    if (!empty($data['assessments'])) {
                        foreach ($data['assessments'] as $assessment) {
                            if (isset($assessment['latest']) && $assessment['latest'] === true) {
                                return $assessment['red_list_category_code'];
                            }
                        }
                        // Fallback to the first assessment if no 'latest' is found.
                        if (isset($data['assessments'][0])) {
                            return $data['assessments'][0]['red_list_category_code'];
                        }
                    }
                }

                Log::warning('IUCN API request failed for species: ' . $genus . ' ' . $species, [
                    'status' => $response->getStatusCode(),
                    'response' => (string) $response->getBody()
                ]);
                return null;
            } catch (\Exception $e) {
                Log::error('Error fetching IUCN status for ' . $genus . ' ' . $species . ': ' . $e->getMessage());
                return null;
            }
        });
    }

    public function getCitesAppendix(string $speciesName): ?string
    {
        if (empty($speciesName)) {
            return null;
        }

        $nameParts = $this->getGenusAndSpecies($speciesName);
        $cleanSpeciesName = trim($nameParts['genus'] . ' ' . $nameParts['species']);

        if (empty($cleanSpeciesName)) {
            return null;
        }

        $cacheKey = 'cites_appendix_' . Str::slug($cleanSpeciesName);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($cleanSpeciesName) {
            try {
                $response = Http::withHeaders([
                    'X-Authentication-Token' => $this->speciesPlusApiKey,
                ])->get('https://api.speciesplus.net/api/v1/taxon_concepts', [
                    'name' => $cleanSpeciesName,
                ]);

                if ($response->successful() && !empty($response->json()['taxon_concepts'][0]['cites_listings'])) {
                    $listings = $response->json()['taxon_concepts'][0]['cites_listings'];
                    $appendices = array_column($listings, 'appendix');
                    if (in_array('I', $appendices)) return 'I';
                    if (in_array('II', $appendices)) return 'II';
                    if (in_array('III', $appendices)) return 'III';
                }

                Log::warning('Species+ API request failed for species: ' . $cleanSpeciesName, ['response' => $response->body()]);
                return null;
            } catch (\Exception $e) {
                Log::error('Error fetching CITES appendix for ' . $cleanSpeciesName . ': ' . $e->getMessage());
                return null;
            }
        });
    }
}
