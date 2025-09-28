<?php

namespace App\Services;

use App\Models\BadgeMember;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BadgeMemberService
{
    protected FobiApiService $fobiApiService;

    public function __construct(FobiApiService $fobiApiService)
    {
        $this->fobiApiService = $fobiApiService;
    }

    /**
     * Get member's badge progress with badge data from FOBI API
     */
    public function getMemberBadgeProgress(int $memberId, bool $simplified = false): array
    {
        try {
            \Log::info('BadgeMemberService: Getting progress for member ID: ' . $memberId . ', simplified: ' . ($simplified ? 'true' : 'false'));
            
            // Get local badge progress
            $localBadges = BadgeMember::getBadgeProgressForMember($memberId);
            \Log::info('BadgeMemberService: Local badges count: ' . $localBadges->count());
            
            // Try to get badge data from FOBI API
            try {
                $fobiBadges = $this->fobiApiService->getBadges(['app' => 'akar']);
                \Log::info('BadgeMemberService: FOBI API response received');
            } catch (\Exception $apiError) {
                \Log::warning('BadgeMemberService: FOBI API failed, using local data only', [
                    'error' => $apiError->getMessage()
                ]);
                return $this->formatLocalBadgeProgress($localBadges);
            }
            
            if (!$fobiBadges || !isset($fobiBadges['data'])) {
                \Log::warning('BadgeMemberService: Invalid FOBI API response, using local data');
                return $this->formatLocalBadgeProgress($localBadges);
            }

            \Log::info('BadgeMemberService: FOBI badges count: ' . count($fobiBadges['data']));

            // Merge local progress with FOBI badge data
            $badgeProgress = [];
            foreach ($fobiBadges['data'] as $fobiBadge) {
                $localBadge = $localBadges->firstWhere('badge_id', $fobiBadge['id']);
                
                // Create badge data based on simplified flag
                if ($simplified) {
                    // Ultra-simplified response for mobile clients
                    $simplifiedBadgeData = [
                        'id' => $fobiBadge['id'],
                        'title' => $fobiBadge['title'] ?? 'Badge #' . $fobiBadge['id'],
                        'type' => $fobiBadge['type'] ?? 1,
                        'total' => $fobiBadge['total'] ?? 0,
                    ];
                } else {
                    // Standard simplified response
                    $simplifiedBadgeData = [
                        'id' => $fobiBadge['id'],
                        'title' => $fobiBadge['title'] ?? 'Badge #' . $fobiBadge['id'],
                        'type' => $fobiBadge['type'] ?? 1,
                        'total' => $fobiBadge['total'] ?? 0,
                        'type_data' => $fobiBadge['type_data'] ?? null,
                        'applications' => $fobiBadge['applications'] ?? null,
                        'icons' => $fobiBadge['icons'] ?? null,
                        'congratulations' => $fobiBadge['congratulations'] ?? null,
                        'timestamps' => $fobiBadge['timestamps'] ?? null,
                    ];
                }
                
                $badgeProgress[] = [
                    'badge_id' => $fobiBadge['id'],
                    'badge_data' => $simplifiedBadgeData,
                    'current_total' => $localBadge ? $localBadge->total : 0,
                    'target_total' => $fobiBadge['total'] ?? 0,
                    'is_earned' => $localBadge ? ($localBadge->total > 0 && $localBadge->total >= ($fobiBadge['total'] ?? 1)) : false,
                    'progress_percentage' => $this->calculateProgressPercentage(
                        $localBadge ? $localBadge->total : 0,
                        $fobiBadge['total'] ?? 0
                    ),
                    'earned_at' => $localBadge && $localBadge->total > 0 ? $localBadge->updated_at->toISOString() : null,
                ];
            }
            
            // Clean output buffer to prevent malformed JSON
            if (ob_get_level()) {
                ob_clean();
            }
            
            // Validate JSON before returning
            $jsonTest = json_encode($badgeProgress);
            if ($jsonTest === false) {
                \Log::error('BadgeMemberService: JSON encoding failed', [
                    'member_id' => $memberId,
                    'json_error' => json_last_error_msg(),
                    'progress_count' => count($badgeProgress)
                ]);
                
                // Return simplified version if JSON encoding fails
                return array_map(function($badge) {
                    return [
                        'badge_id' => $badge['badge_id'],
                        'badge_data' => [
                            'id' => $badge['badge_data']['id'],
                            'title' => $badge['badge_data']['title'],
                            'type' => $badge['badge_data']['type'],
                            'total' => $badge['badge_data']['total'],
                        ],
                        'current_total' => $badge['current_total'],
                        'target_total' => $badge['target_total'],
                        'is_earned' => $badge['is_earned'],
                        'progress_percentage' => $badge['progress_percentage'],
                        'earned_at' => $badge['earned_at'],
                    ];
                }, $badgeProgress);
            }
            
            // Log final badge progress for debugging
            \Log::info('BadgeMemberService: Final badge progress', [
                'member_id' => $memberId,
                'progress_count' => count($badgeProgress),
                'json_valid' => true,
                'response_size' => strlen($jsonTest)
            ]);
            
            return $badgeProgress;
        } catch (\Exception $e) {
            \Log::error('BadgeMemberService: Error getting badge progress', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty array as fallback
            return [];
        }
    }

    /**
     * Get only earned badges for member
     */
    public function getMemberEarnedBadges(int $memberId): array
    {
        $allProgress = $this->getMemberBadgeProgress($memberId);
        return array_filter($allProgress, fn($badge) => $badge['is_earned']);
    }

    /**
     * Get badge statistics for member
     */
    public function getMemberBadgeStats(int $memberId): array
    {
        $progress = $this->getMemberBadgeProgress($memberId);
        $earnedCount = count(array_filter($progress, fn($badge) => $badge['is_earned']));
        $totalCount = count($progress);

        return [
            'total_badges' => $totalCount,
            'earned_badges' => $earnedCount,
            'in_progress_badges' => $totalCount - $earnedCount,
            'completion_percentage' => $totalCount > 0 ? round(($earnedCount / $totalCount) * 100, 2) : 0,
            'latest_earned' => $this->getLatestEarnedBadge($memberId),
            'next_badge' => $this->getNextBadgeToEarn($progress),
        ];
    }

    /**
     * Update member's badge progress
     */
    public function updateMemberBadgeProgress(int $memberId, int $badgeId, int $total): BadgeMember
    {
        return BadgeMember::updateBadgeProgress($memberId, $badgeId, $total);
    }

    /**
     * Check for new badges earned based on checklist count
     */
    public function checkNewBadgesForMember(int $memberId, int $currentChecklistCount, int $previousCount = 0): array
    {
        try {
            \Log::info('BadgeMemberService: checkNewBadgesForMember called', [
                'member_id' => $memberId,
                'current_count' => $currentChecklistCount,
                'previous_count' => $previousCount
            ]);

            // Get badges from FOBI API
            try {
                $fobiBadges = $this->fobiApiService->getBadges(['app' => 'akar']);
                \Log::info('BadgeMemberService: FOBI API response received', [
                    'has_data' => isset($fobiBadges['data']),
                    'badge_count' => isset($fobiBadges['data']) ? count($fobiBadges['data']) : 0
                ]);
            } catch (\Exception $apiError) {
                \Log::warning('BadgeMemberService: FOBI API failed, using fallback badges', [
                    'error' => $apiError->getMessage()
                ]);
                
                // Use fallback badges when FOBI API is not available
                $fobiBadges = [
                    'data' => [
                        // Checklist/Laporan badges (Type 1)
                        ['id' => 1, 'type' => 1, 'total' => 1, 'title' => 'Laporan Level 1'],
                        ['id' => 2, 'type' => 1, 'total' => 150, 'title' => 'Laporan Level 2'],
                        ['id' => 3, 'type' => 1, 'total' => 300, 'title' => 'Laporan Level 3'],
                        ['id' => 4, 'type' => 1, 'total' => 700, 'title' => 'Laporan Level 4'],
                        ['id' => 5, 'type' => 1, 'total' => 1000, 'title' => 'Laporan Level 5'],
                        
                        // Species badges (Type 2) - Fixed target values
                        ['id' => 6, 'type' => 2, 'total' => 5, 'title' => 'Spesies Level 1'],
                        ['id' => 7, 'type' => 2, 'total' => 20, 'title' => 'Spesies Level 2'],
                        ['id' => 8, 'type' => 2, 'total' => 80, 'title' => 'Spesies Level 3'],
                        ['id' => 9, 'type' => 2, 'total' => 150, 'title' => 'Spesies Level 4'],
                        ['id' => 10, 'type' => 2, 'total' => 200, 'title' => 'Spesies Level 5'],
                        
                        // Specialist badges (Type 3)
                        ['id' => 11, 'type' => 3, 'total' => 200, 'title' => 'Spesialis burung ga bisa terbang'],
                        ['id' => 12, 'type' => 3, 'total' => 200, 'title' => 'Spesialis 2'],
                        ['id' => 13, 'type' => 3, 'total' => 300, 'title' => 'Spesialis 3'],
                        ['id' => 14, 'type' => 3, 'total' => 2090, 'title' => 'Spesialis 4'],
                        ['id' => 15, 'type' => 3, 'total' => 7000, 'title' => 'Spesialis 5'],
                    ]
                ];
            }
            
            if (!$fobiBadges || !isset($fobiBadges['data'])) {
                \Log::warning('BadgeMemberService: No badge data available');
                return [];
            }

            $newBadges = [];
            foreach ($fobiBadges['data'] as $fobiBadge) {
                \Log::info('BadgeMemberService: Processing badge', [
                    'badge_id' => $fobiBadge['id'],
                    'badge_type' => $fobiBadge['type'],
                    'badge_total' => $fobiBadge['total'],
                    'badge_title' => $fobiBadge['title'] ?? 'Unknown'
                ]);

                // Process different badge types
                if ($fobiBadge['total'] > 0) {
                    $targetTotal = $fobiBadge['total'];
                    $currentTotal = 0;
                    $previousTotal = 0;
                    
                    // Determine current and previous totals based on badge type
                    switch ($fobiBadge['type']) {
                        case 1: // Checklist/Laporan badges
                            $currentTotal = $currentChecklistCount;
                            $previousTotal = $previousCount;
                            break;
                            
                        case 2: // Species badges
                            $currentTotal = $this->getUserSpeciesCount($memberId);
                            $previousTotal = max(0, $currentTotal - 1); // Assume previous was 1 less
                            break;
                            
                        case 3: // Spesialis badges (custom achievements)
                            $currentTotal = $this->getUserSpecialistProgress($memberId, $fobiBadge['id']);
                            $previousTotal = max(0, $currentTotal - 1);
                            break;
                            
                        default:
                            \Log::warning('BadgeMemberService: Unknown badge type', [
                                'badge_id' => $fobiBadge['id'],
                                'badge_type' => $fobiBadge['type']
                            ]);
                            continue 2; // Skip this badge
                    }
                    
                    \Log::info('BadgeMemberService: Checking badge eligibility', [
                        'badge_id' => $fobiBadge['id'],
                        'badge_type' => $fobiBadge['type'],
                        'target_total' => $targetTotal,
                        'current_total' => $currentTotal,
                        'previous_total' => $previousTotal,
                        'is_eligible' => ($currentTotal >= $targetTotal && $previousTotal < $targetTotal)
                    ]);
                    
                    // Always update progress for all badge types
                    $this->updateMemberBadgeProgress($memberId, $fobiBadge['id'], $currentTotal);
                    
                    // Check if badge was just earned (reached target for first time)
                    if ($currentTotal >= $targetTotal && $previousTotal < $targetTotal) {
                        \Log::info('BadgeMemberService: Badge earned! Target reached', [
                            'member_id' => $memberId,
                            'badge_id' => $fobiBadge['id'],
                            'badge_type' => $fobiBadge['type'],
                            'current_total' => $currentTotal,
                            'target_total' => $targetTotal
                        ]);
                        
                        $newBadges[] = [
                            'badge_id' => $fobiBadge['id'],
                            'badge_data' => [
                                'id' => $fobiBadge['id'],
                                'title' => $fobiBadge['title'] ?? 'Badge #' . $fobiBadge['id'],
                                'type' => $fobiBadge['type'] ?? 1,
                                'total' => $fobiBadge['total'] ?? 0,
                                'description' => $fobiBadge['description'] ?? null,
                            ],
                            'earned_at' => now()->toISOString(),
                            'current_total' => $currentTotal,
                        ];
                    } else {
                        \Log::info('BadgeMemberService: Progress updated but badge not earned yet', [
                            'badge_id' => $fobiBadge['id'],
                            'badge_type' => $fobiBadge['type'],
                            'current_total' => $currentTotal,
                            'target_total' => $targetTotal,
                            'progress_percentage' => round(($currentTotal / $targetTotal) * 100, 2)
                        ]);
                    }
                }
            }

            \Log::info('BadgeMemberService: checkNewBadgesForMember completed', [
                'new_badges_count' => count($newBadges)
            ]);

            return $newBadges;
        } catch (\Exception $e) {
            \Log::error('BadgeMemberService: Error checking new badges', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Sync all badges for member from FOBI API
     */
    public function syncMemberBadges(int $memberId, int $currentChecklistCount = 0): bool
    {
        try {
            // Get badges from FOBI API
            $fobiBadges = $this->fobiApiService->getBadges(['app' => 'akar']);
            
            if (!$fobiBadges || !isset($fobiBadges['data'])) {
                return false;
            }

            foreach ($fobiBadges['data'] as $fobiBadge) {
                $total = 0;
                
                // Calculate progress based on badge type
                if ($fobiBadge['type'] == 1) { // Checklist badges
                    $total = min($currentChecklistCount, $fobiBadge['total'] ?? 0);
                }
                // Add other badge types as needed

                // Update or create badge progress
                $this->updateMemberBadgeProgress($memberId, $fobiBadge['id'], $total);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error syncing member badges: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate progress percentage
     */
    private function calculateProgressPercentage(int $current, int $target): float
    {
        if ($target <= 0) {
            return 0;
        }
        
        return min(100, round(($current / $target) * 100, 2));
    }

    /**
     * Format local badge progress when API is unavailable
     */
    private function formatLocalBadgeProgress(Collection $localBadges): array
    {
        return $localBadges->map(function ($badge) {
            return [
                'badge_id' => $badge->badge_id,
                'badge_data' => [
                    'id' => $badge->badge_id,
                    'title' => 'Badge #' . $badge->badge_id,
                    'type' => 1,
                    'total' => 0,
                ],
                'current_total' => $badge->total,
                'target_total' => 0,
                'is_earned' => $badge->is_earned,
                'progress_percentage' => 0,
                'earned_at' => $badge->created_at,
            ];
        })->toArray();
    }

    /**
     * Get latest earned badge
     */
    private function getLatestEarnedBadge(int $memberId): ?array
    {
        $latestBadge = BadgeMember::where('member_id', $memberId)
            ->where('total', '>', 0)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$latestBadge) {
            return null;
        }

        // Get badge data from API
        try {
            $badgeData = $this->fobiApiService->getBadge($latestBadge->badge_id);
            return [
                'badge_id' => $latestBadge->badge_id,
                'badge_data' => $badgeData['data'] ?? null,
                'earned_at' => $latestBadge->updated_at,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get next badge to earn
     */
    private function getNextBadgeToEarn(array $progress): ?array
    {
        $unearned = array_filter($progress, fn($badge) => !$badge['is_earned'] && $badge['target_total'] > 0);
        
        if (empty($unearned)) {
            return null;
        }

        // Sort by target total ascending to get the closest badge
        usort($unearned, fn($a, $b) => $a['target_total'] <=> $b['target_total']);
        
        return $unearned[0] ?? null;
    }

    /**
     * Get user's unique species count from ChecklistFauna
     */
    private function getUserSpeciesCount(int $memberId): int
    {
        try {
            // Get unique fauna_id from ChecklistFauna through published checklists
            $user = \App\Models\User::find($memberId);
            if (!$user) {
                return 0;
            }

            // Count unique fauna_id from ChecklistFauna via published checklists
            $uniqueSpeciesCount = \App\Models\ChecklistFauna::whereHas('checklist', function($query) use ($memberId) {
                    $query->where('user_id', $memberId)
                          ->where('status', 'published'); // Remove is_completed requirement
                })
                ->whereNotNull('fauna_id')
                ->where('fauna_id', '>', 0)
                ->distinct('fauna_id')
                ->count('fauna_id');
            
            \Log::info('BadgeMemberService: getUserSpeciesCount', [
                'member_id' => $memberId,
                'unique_species_count' => $uniqueSpeciesCount,
                'method' => 'ChecklistFauna_fauna_id'
            ]);

            return $uniqueSpeciesCount;
        } catch (\Exception $e) {
            \Log::error('BadgeMemberService: Error getting species count', [
                'member_id' => $memberId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get user's specialist progress for specific badge
     */
    private function getUserSpecialistProgress(int $memberId, int $badgeId): int
    {
        try {
            // Specialist badges are custom achievements
            // For now, we'll implement basic logic based on badge ID
            
            switch ($badgeId) {
                case 11: // Spesialis burung ga bisa terbang
                    return $this->getFlightlessBirdProgress($memberId);
                    
                case 12: // Spesialis 2
                case 13: // Spesialis 3  
                case 14: // Spesialis 4
                case 15: // Spesialis 5
                    return $this->getGeneralSpecialistProgress($memberId, $badgeId);
                    
                default:
                    \Log::warning('BadgeMemberService: Unknown specialist badge', [
                        'badge_id' => $badgeId
                    ]);
                    return 0;
            }
        } catch (\Exception $e) {
            \Log::error('BadgeMemberService: Error getting specialist progress', [
                'member_id' => $memberId,
                'badge_id' => $badgeId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get flightless bird specialist progress
     */
    private function getFlightlessBirdProgress(int $memberId): int
    {
        try {
            // Count flightless birds from ChecklistFauna
            $flightlessBirdCount = \App\Models\ChecklistFauna::whereHas('checklist', function($query) use ($memberId) {
                    $query->where('user_id', $memberId)
                          ->where('status', 'published'); // Remove is_completed requirement
                })
                ->whereHas('taxa', function($query) {
                    // Check if taxa has flightless bird characteristics
                    $query->where(function($q) {
                        $q->where('scientific_name', 'like', '%Struthio%') // Ostrich
                          ->orWhere('scientific_name', 'like', '%Dromaius%') // Emu
                          ->orWhere('scientific_name', 'like', '%Casuarius%') // Cassowary
                          ->orWhere('scientific_name', 'like', '%Rhea%') // Rhea
                          ->orWhere('scientific_name', 'like', '%Apteryx%') // Kiwi
                          ->orWhere('scientific_name', 'like', '%Spheniscus%') // Penguins
                          ->orWhere('scientific_name', 'like', '%Pygoscelis%') // Penguins
                          ->orWhere('scientific_name', 'like', '%Aptenodytes%'); // Penguins
                    });
                })
                ->count();

            \Log::info('BadgeMemberService: getFlightlessBirdProgress', [
                'member_id' => $memberId,
                'flightless_bird_count' => $flightlessBirdCount,
                'method' => 'ChecklistFauna_taxa_relation'
            ]);

            return $flightlessBirdCount;
        } catch (\Exception $e) {
            \Log::error('BadgeMemberService: Error getting flightless bird progress', [
                'member_id' => $memberId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Check if species is a flightless bird
     */
    private function isFlightlessBird(string $scientificName): bool
    {
        // List of common flightless birds
        $flightlessBirds = [
            'Struthio camelus', // Ostrich
            'Dromaius novaehollandiae', // Emu
            'Casuarius casuarius', // Cassowary
            'Rhea americana', // Greater Rhea
            'Apteryx australis', // Kiwi
            'Spheniscus', // Penguins (genus)
            'Pygoscelis', // Penguins (genus)
            'Aptenodytes', // Penguins (genus)
            'Gallus gallus', // Domestic chicken (flightless varieties)
        ];

        foreach ($flightlessBirds as $flightlessBird) {
            if (stripos($scientificName, $flightlessBird) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get general specialist progress
     */
    private function getGeneralSpecialistProgress(int $memberId, int $badgeId): int
    {
        try {
            // For general specialist badges, use checklist count
            $user = \App\Models\User::find($memberId);
            if (!$user) {
                return 0;
            }

            // Use checklist count as base progress for specialist badges (remove is_completed requirement)
            $checklistCount = $user->checklists()
                ->where('status', 'published')
                ->count();

            \Log::info('BadgeMemberService: getGeneralSpecialistProgress', [
                'member_id' => $memberId,
                'badge_id' => $badgeId,
                'checklist_count' => $checklistCount,
                'method' => 'published_checklists_only'
            ]);
            
            return $checklistCount;
        } catch (\Exception $e) {
            \Log::error('BadgeMemberService: Error getting general specialist progress', [
                'member_id' => $memberId,
                'badge_id' => $badgeId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
