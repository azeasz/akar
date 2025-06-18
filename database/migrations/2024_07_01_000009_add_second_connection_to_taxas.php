<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ini hanya migrasi pendukung untuk menunjukkan bahwa tabel taxas terhubung ke database kedua
        // dalam implementasi sebenarnya, hubungan ini akan dikonfigurasi pada model
        // Tidak ada perubahan struktur database yang dilakukan di sini
        
        // Catatan: Tabel taxas sebenarnya ada di database kedua (amaturalist/fobi)
        // Database kedua diatur dalam konfigurasi 'second' di config/database.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada yang perlu dibalik karena tidak ada perubahan struktur yang dilakukan
    }
}; 