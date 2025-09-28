<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SyncTaxaData;
use App\Console\Commands\SetupBadgeStorage;
use App\Console\Commands\TestFobiApi;
use App\Console\Commands\TestBadgeTracking;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SyncTaxaData::class,
        SetupBadgeStorage::class,
        TestFobiApi::class,
        TestBadgeTracking::class,
    ];
    
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Sinkronisasi taxa setiap hari pada jam 1 pagi
        $schedule->command('taxa:sync --limit=1000')->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
