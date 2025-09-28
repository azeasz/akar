<?php

namespace App\Console\Commands;

use App\Services\FobiApiService;
use Illuminate\Console\Command;

class TestFobiApi extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fobi:test-api {--detailed : Show detailed response}';

    /**
     * The console command description.
     */
    protected $description = 'Test connectivity to FOBI API and show status';

    /**
     * Execute the console command.
     */
    public function handle(FobiApiService $fobiApi)
    {
        $this->info('🔍 Testing FOBI API Connectivity...');
        $this->newLine();

        // Test configuration
        $this->info('📋 Configuration:');
        $baseUrl = config('services.fobi_api.base_url');
        $hasToken = !empty(config('services.fobi_api.token'));
        $timeout = config('services.fobi_api.timeout');

        $this->line("   Base URL: " . ($baseUrl ?: '<not set>'));
        $this->line("   API Token: " . ($hasToken ? '✅ Configured' : '⚠️  Not set (Public API)'));
        $this->line("   Timeout: {$timeout}s");
        $this->newLine();

        if (!$baseUrl) {
            $this->error('❌ API configuration incomplete!');
            $this->info('Please set FOBI_API_BASE_URL in your .env file');
            $this->info('Example: FOBI_API_BASE_URL=https://amaturalist.com');
            return 1;
        }

        if (!$hasToken) {
            $this->warn('⚠️  No API token configured - assuming public API access');
        }

        // Test health check
        $this->info('🏥 Testing Health Check...');
        $healthCheck = $fobiApi->healthCheck();
        
        if ($healthCheck) {
            $this->info('   ✅ API is healthy and reachable');
        } else {
            $this->error('   ❌ API health check failed');
            $this->warn('   This could mean:');
            $this->warn('   - API server is down');
            if ($hasToken) {
                $this->warn('   - Invalid API token');
            }
            $this->warn('   - Network connectivity issues');
            $this->warn('   - API endpoint not implemented');
            $this->warn('   - CORS issues (if calling from browser)');
            return 1;
        }

        // Test badge types
        $this->info('🏷️  Testing Badge Types API...');
        $badgeTypes = $fobiApi->getBadgeTypes();
        
        if ($badgeTypes) {
            $count = count($badgeTypes['data'] ?? []);
            $this->info("   ✅ Retrieved {$count} badge types");
            
            if ($this->option('detailed') && !empty($badgeTypes['data'])) {
                $this->info('   Badge Types:');
                foreach (array_slice($badgeTypes['data'], 0, 5) as $type) {
                    $this->line("   - {$type['name']} (ID: {$type['id']})");
                }
                if (count($badgeTypes['data']) > 5) {
                    $this->line('   ... and ' . (count($badgeTypes['data']) - 5) . ' more');
                }
            }
        } else {
            $this->error('   ❌ Failed to retrieve badge types');
        }

        // Test badges
        $this->info('🏆 Testing Badges API...');
        $badges = $fobiApi->getBadges(['per_page' => 5]);
        
        if ($badges) {
            $total = $badges['pagination']['total'] ?? 0;
            $count = count($badges['data'] ?? []);
            $this->info("   ✅ Retrieved {$count} badges (Total: {$total})");
            
            if ($this->option('detailed') && !empty($badges['data'])) {
                $this->info('   Sample Badges:');
                foreach ($badges['data'] as $badge) {
                    $this->line("   - {$badge['title']} (ID: {$badge['id']})");
                }
            }
        } else {
            $this->error('   ❌ Failed to retrieve badges');
        }

        // Performance test
        $this->info('⚡ Testing Performance...');
        $start = microtime(true);
        $testBadges = $fobiApi->getBadges(['per_page' => 1]);
        $duration = round((microtime(true) - $start) * 1000, 2);
        
        if ($testBadges) {
            $this->info("   ✅ Response time: {$duration}ms");
            
            if ($duration > 2000) {
                $this->warn('   ⚠️  Response time is slow (>2s). Consider:');
                $this->warn('   - Increasing cache TTL');
                $this->warn('   - Optimizing API queries');
                $this->warn('   - Checking network latency');
            }
        }

        $this->newLine();
        $this->info('🎉 API Test Completed!');
        
        if ($healthCheck && $badgeTypes && $badges) {
            $this->info('✅ All tests passed - API is ready to use');
            $this->info('💡 You can now use API mode in Badge Management');
            return 0;
        } else {
            $this->error('❌ Some tests failed - check configuration and API server');
            $this->info('🔄 Fallback to database connection will be used');
            return 1;
        }
    }
}
