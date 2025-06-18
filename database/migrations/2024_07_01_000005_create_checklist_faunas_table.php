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
        Schema::create('checklist_faunas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained()->onDelete('cascade');
            $table->string('nama_spesies');
            $table->integer('jumlah')->default(1);
            $table->string('gender')->nullable();
            $table->boolean('cincin')->default(false);
            $table->boolean('tagging')->default(false);
            $table->text('catatan')->nullable();
            $table->enum('status_buruan', ['hidup', 'mati'])->nullable();
            $table->string('alat_buru')->nullable();
            
            // Kolom-kolom dari struktur lama untuk kompatibilitas
            $table->unsignedBigInteger('fauna_id')->nullable(); // Untuk menyimpan fauna_id dari struktur lama
            $table->string('asal')->nullable();
            $table->string('harga')->nullable();
            $table->integer('kondisi')->nullable()->default(0);
            $table->integer('ijin')->nullable()->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('checklist_id');
            $table->index('nama_spesies');
            $table->index('fauna_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_faunas');
    }
}; 