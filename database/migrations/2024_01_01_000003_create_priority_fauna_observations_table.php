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
        Schema::create('priority_fauna_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('priority_fauna_id')->constrained('priority_faunas')->onDelete('cascade');
            $table->foreignId('checklist_id')->constrained('checklists')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Data observasi
            $table->string('scientific_name');
            $table->string('common_name')->nullable();
            $table->integer('individual_count')->default(1);
            $table->json('photos')->nullable(); // Array foto yang diupload
            
            // Data lokasi
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            
            // Status monitoring
            $table->enum('status', ['new', 'reviewed', 'verified', 'flagged'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamp('observed_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['priority_fauna_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priority_fauna_observations');
    }
};
