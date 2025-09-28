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
        Schema::create('priority_fauna_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'CR', 'Dilindungi', 'EN', 'VU', etc.
            $table->string('type'); // 'iucn' atau 'protection_status' atau 'custom'
            $table->string('description')->nullable();
            $table->string('color_code', 7)->default('#dc3545'); // Hex color untuk UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priority_fauna_categories');
    }
};
