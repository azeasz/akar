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
        Schema::create('fauna_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('mammalia, burung, reptil, primata, lain-lain');
            $table->string('color')->comment('mammalia:blue, burung:pink, reptil:green, primata:orange, lain-lain:gray');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fauna_types');
    }
}; 