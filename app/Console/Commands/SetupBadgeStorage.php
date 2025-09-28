<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupBadgeStorage extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'badge:setup-storage';

    /**
     * The console command description.
     */
    protected $description = 'Setup storage link and directories for badge management';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up badge storage...');

        // Create storage directories if they don't exist
        $storagePath = storage_path('app/public/badges');
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
            $this->info("Created directory: {$storagePath}");
        } else {
            $this->info("Directory already exists: {$storagePath}");
        }

        // Create symbolic link
        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (File::exists($linkPath)) {
            if (is_link($linkPath)) {
                $this->info('Storage link already exists.');
            } else {
                $this->error('A directory named "storage" already exists in public folder.');
                $this->info('Please remove or rename it first, then run this command again.');
                return 1;
            }
        } else {
            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    // Windows symbolic link
                    $command = 'mklink /D "' . $linkPath . '" "' . $targetPath . '"';
                    exec($command, $output, $returnCode);
                    
                    if ($returnCode === 0) {
                        $this->info('Storage link created successfully (Windows).');
                    } else {
                        $this->error('Failed to create symbolic link. Try running as administrator.');
                        $this->info('Manual command: ' . $command);
                        return 1;
                    }
                } else {
                    // Unix/Linux symbolic link
                    symlink($targetPath, $linkPath);
                    $this->info('Storage link created successfully (Unix/Linux).');
                }
            } catch (\Exception $e) {
                $this->error('Failed to create symbolic link: ' . $e->getMessage());
                return 1;
            }
        }

        // Set permissions (Unix/Linux only)
        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($storagePath, 0755);
            $this->info('Set permissions for badge storage directory.');
        }

        // Test write access
        $testFile = $storagePath . '/test_write.txt';
        try {
            File::put($testFile, 'test');
            File::delete($testFile);
            $this->info('Write access test: PASSED');
        } catch (\Exception $e) {
            $this->error('Write access test: FAILED - ' . $e->getMessage());
            return 1;
        }

        $this->info('');
        $this->info('✅ Badge storage setup completed successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Access badge management in admin panel');
        $this->info('2. Try uploading a badge image');
        $this->info('3. Verify the image displays correctly');
        $this->info('');
        $this->info('Storage path: ' . $storagePath);
        $this->info('Public link: ' . $linkPath);

        return 0;
    }
}
