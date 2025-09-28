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
        Schema::create('priority_faunas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checklist_id')->nullable();
            $table->unsignedBigInteger('fauna_id')->nullable(); // dari checklist_faunas
            $table->unsignedBigInteger('taxa_id')->nullable(); // ID taksa dari API amaturalist
            $table->string('taxa_name'); // Nama taksa
            $table->string('scientific_name')->nullable();
            $table->string('common_name')->nullable();
            $table->json('taxa_data')->nullable(); // Data lengkap dari API
            $table->string('iucn_status')->nullable(); // CR, EN, VU, etc.
            $table->string('protection_status')->nullable(); // Dilindungi, Tidak Dilindungi
            $table->foreignId('category_id')->constrained('priority_fauna_categories')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->boolean('is_monitored')->default(true);
            $table->timestamp('last_api_sync')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['checklist_id', 'fauna_id']);
            $table->index('taxa_id');
            $table->index('category_id');
            $table->index('is_monitored');
            
            // Foreign keys
            $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priority_faunas');
    }
};
