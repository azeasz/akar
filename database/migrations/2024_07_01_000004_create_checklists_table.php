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
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->boolean('is_completed')->default(false);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->date('tanggal');
            $table->string('nama_lokasi');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('pemilik')->nullable();
            $table->text('catatan')->nullable();
            
            // Kolom-kolom dari struktur lama untuk kompatibilitas 
            $table->integer('app_id')->default(0);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name')->nullable(); // untuk menyimpan name dari struktur lama
            $table->string('nama_event')->nullable();
            $table->string('nama_arena')->nullable();
            $table->integer('total_hunter')->default(0);
            $table->string('teknik_berburu', 100)->nullable();
            $table->integer('status_tangkapan')->nullable()->default(0);
            $table->unsignedBigInteger('category_tempat_id')->nullable()->default(0);
            $table->string('nama_toko')->nullable();
            $table->string('nama_penjual')->nullable();
            $table->string('domisili_penjual')->nullable();
            $table->unsignedBigInteger('profesi_penjual_id')->nullable()->default(0);
            $table->string('nama_pemilik')->nullable();
            $table->string('domisili_pemilik')->nullable();
            $table->unsignedBigInteger('profesi_pemilik_id')->nullable()->default(0);
            $table->boolean('confirmed')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('tanggal');
            $table->index('app_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
}; 