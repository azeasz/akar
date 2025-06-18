<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\MigrateOldDataSeeder;

class MigrateOldData extends Command
{
    protected $signature = 'akar:migrate-old-data {--force : Paksa migrasi tanpa konfirmasi}';
    protected $description = 'Migrasi data dari struktur lama ke struktur baru';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Peringatan: Proses ini akan memigrasikan data dari struktur lama. Pastikan Anda sudah melakukan backup. Lanjutkan?')) {
            $this->info('Migrasi dibatalkan.');
            return;
        }

        $this->info('Mulai migrasi data lama...');
        
        $seeder = new MigrateOldDataSeeder();
        $seeder->setContainer($this->getLaravel());
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('Migrasi data selesai!');
    }
} 