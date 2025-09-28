<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BadgeMemberService;
use App\Services\FobiApiService;
use App\Models\User;
use App\Models\BadgeMember;

class TestBadgeTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badge:test-tracking 
                            {--user-id= : Specific user ID to test}
                            {--simulate= : Simulate checklist count}
                            {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test badge tracking functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Badge Tracking Test');
        $this->info('=====================');
        $this->newLine();

        try {
            // Get user for testing
            $userId = $this->option('user-id');
            $user = $userId ? User::find($userId) : User::first();
            
            if (!$user) {
                $this->error('❌ No user found for testing');
                return 1;
            }

            $this->info("👤 Testing with User: {$user->name} (ID: {$user->id})");
            $this->newLine();

            // Test 1: FOBI API Connection
            $this->testFobiApi();

            // Test 2: Badge Member Service
            $this->testBadgeMemberService($user);

            // Test 3: Database Check
            $this->testDatabase($user);

            // Test 4: Simulation (if requested)
            if ($this->option('simulate')) {
                $this->testSimulation($user, (int) $this->option('simulate'));
            }

            $this->newLine();
            $this->info('✅ Badge tracking test completed successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            if ($this->option('detailed')) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }

        return 0;
    }

    private function testFobiApi()
    {
        $this->info('1️⃣ Testing FOBI API Connection...');
        
        try {
            $fobiApiService = app(FobiApiService::class);
            $badges = $fobiApiService->getBadges(['app' => 'akar']);
            
            if ($badges && isset($badges['data'])) {
                $this->info("   ✅ FOBI API working - Found " . count($badges['data']) . " badges");
                
                if ($this->option('detailed')) {
                    foreach ($badges['data'] as $badge) {
                        $this->line("   📋 {$badge['title']} (ID: {$badge['id']}, Type: {$badge['type']}, Target: {$badge['total']})");
                    }
                }
            } else {
                $this->warn('   ⚠️  FOBI API returned no badges or invalid response');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ FOBI API failed: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testBadgeMemberService(User $user)
    {
        $this->info('2️⃣ Testing Badge Member Service...');
        
        try {
            $badgeMemberService = app(BadgeMemberService::class);
            
            // Get current checklist count
            $currentCount = $user->checklists()
                ->where('status', 'published')
                ->where('is_completed', true)
                ->count();
            
            $this->info("   📊 Current checklist count: {$currentCount}");
            
            // Test badge tracking
            $previousCount = max(0, $currentCount - 1);
            $newBadges = $badgeMemberService->checkNewBadgesForMember($user->id, $currentCount, $previousCount);
            
            $this->info("   🎯 Badge tracking result: " . count($newBadges) . " new badges");
            
            if (!empty($newBadges)) {
                foreach ($newBadges as $badge) {
                    $this->info("   🏆 New Badge: {$badge['badge_data']['title']} (ID: {$badge['badge_id']})");
                }
            } else {
                $this->line("   📈 No new badges earned (progress tracking only)");
            }

            // Test badge stats
            $stats = $badgeMemberService->getMemberBadgeStats($user->id);
            $this->info("   📊 Badge Stats:");
            $this->line("      Total: {$stats['total_badges']}");
            $this->line("      Earned: {$stats['earned_badges']}");
            $this->line("      Progress: {$stats['in_progress_badges']}");
            $this->line("      Completion: {$stats['completion_percentage']}%");
            
        } catch (\Exception $e) {
            $this->error('   ❌ Badge Member Service failed: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testDatabase(User $user)
    {
        $this->info('3️⃣ Checking Database...');
        
        try {
            $badgeMembers = BadgeMember::where('member_id', $user->id)->get();
            $this->info("   📊 Badge progress records: " . $badgeMembers->count());
            
            if ($this->option('detailed')) {
                foreach ($badgeMembers as $badgeMember) {
                    $this->line("   📈 Badge ID {$badgeMember->badge_id}: {$badgeMember->total} progress (Updated: {$badgeMember->updated_at})");
                }
            }
            
            // Check checklists
            $checklists = $user->checklists()
                ->where('status', 'published')
                ->where('is_completed', true)
                ->get(['id', 'status', 'is_completed', 'created_at']);
            
            $this->info("   📋 Published & completed checklists: " . $checklists->count());
            
            if ($this->option('detailed') && $checklists->count() > 0) {
                foreach ($checklists as $checklist) {
                    $this->line("   ✅ Checklist ID {$checklist->id} - {$checklist->created_at}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error('   ❌ Database check failed: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testSimulation(User $user, int $simulateCount)
    {
        $this->info("4️⃣ Simulating {$simulateCount} Checklists...");
        
        try {
            $badgeMemberService = app(BadgeMemberService::class);
            $previousCount = max(0, $simulateCount - 1);
            
            $newBadges = $badgeMemberService->checkNewBadgesForMember($user->id, $simulateCount, $previousCount);
            
            $this->info("   🧪 Simulation result: " . count($newBadges) . " badges would be earned");
            
            if (!empty($newBadges)) {
                foreach ($newBadges as $badge) {
                    $this->info("   🏆 Would earn: {$badge['badge_data']['title']} (Target: {$badge['badge_data']['total']})");
                }
            } else {
                $this->line("   📈 No badges would be earned at {$simulateCount} checklists");
            }
            
            // Show progress for all badge types
            $this->line("   📊 Progress simulation:");
            
            // Checklist badges
            $checklistTargets = [1, 150, 300, 700, 1000];
            $this->line("   📋 Checklist Badges:");
            foreach ($checklistTargets as $i => $target) {
                $percentage = min(100, round(($simulateCount / $target) * 100, 1));
                $status = $simulateCount >= $target ? '🏆 EARNED' : '📈 IN PROGRESS';
                $badgeId = $i + 1;
                $this->line("      Badge {$badgeId} (Target {$target}): {$simulateCount}/{$target} ({$percentage}%) - {$status}");
            }
            
            // Species badges (simulate based on checklist count / 3)
            $speciesCount = max(1, intval($simulateCount / 3));
            $speciesTargets = [5, 20, 80, 150, 200];
            $this->line("   🐾 Species Badges (Simulated {$speciesCount} species):");
            foreach ($speciesTargets as $i => $target) {
                $percentage = min(100, round(($speciesCount / $target) * 100, 1));
                $status = $speciesCount >= $target ? '🏆 EARNED' : '📈 IN PROGRESS';
                $badgeId = $i + 6;
                $this->line("      Badge {$badgeId} (Target {$target}): {$speciesCount}/{$target} ({$percentage}%) - {$status}");
            }
            
            // Specialist badges
            $specialistTargets = [200, 200, 300, 2090, 7000];
            $this->line("   ⭐ Specialist Badges:");
            foreach ($specialistTargets as $i => $target) {
                $percentage = min(100, round(($simulateCount / $target) * 100, 1));
                $status = $simulateCount >= $target ? '🏆 EARNED' : '📈 IN PROGRESS';
                $badgeId = $i + 11;
                $this->line("      Badge {$badgeId} (Target {$target}): {$simulateCount}/{$target} ({$percentage}%) - {$status}");
            }
            
        } catch (\Exception $e) {
            $this->error('   ❌ Simulation failed: ' . $e->getMessage());
        }
        
        $this->newLine();
    }
}
