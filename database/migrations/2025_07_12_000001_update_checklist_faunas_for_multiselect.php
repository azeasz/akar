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
        Schema::table('checklist_faunas', function (Blueprint $table) {
            // Change gender to support multi-select (e.g., 'Jantan, Remaja')
            $table->string('gender')->nullable()->change();

            // Change status_buruan from enum to string to support multi-select
            $table->string('status_buruan')->nullable()->change();

            // Add a new unified column for tagging status
            $table->string('tagging_status')->nullable()->after('gender');

            // Drop the old boolean columns
            $table->dropColumn('cincin');
            $table->dropColumn('tagging');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_faunas', function (Blueprint $table) {
            // Revert gender
            $table->string('gender')->nullable()->change(); // Remains string, but conceptually reverted

            // Revert status_buruan back to enum. NOTE: Data loss may occur if new values were stored.
            $table->enum('status_buruan', ['hidup', 'mati'])->nullable()->change();

            // Drop the new tagging_status column
            $table->dropColumn('tagging_status');

            // Re-add the old boolean columns
            $table->boolean('cincin')->default(false)->after('gender');
            $table->boolean('tagging')->default(false)->after('cincin');
        });
    }
};
