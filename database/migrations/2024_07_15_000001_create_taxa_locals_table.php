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
        Schema::create('taxa_locals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('taxa_id')->unique()->comment('ID dari tabel taxas di database amaturalist');
            $table->string('scientific_name');
            $table->string('common_name')->nullable();
            $table->string('rank')->nullable();
            $table->string('kingdom')->nullable();
            $table->string('phylum')->nullable();
            $table->string('class')->nullable();
            $table->string('order')->nullable();
            $table->string('family')->nullable();
            $table->string('genus')->nullable();
            $table->string('species')->nullable();
            $table->string('iucn_status')->nullable();
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('taxa_id');
            $table->index('scientific_name');
            $table->index('common_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxa_locals');
    }
}; 