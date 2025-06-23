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
        Schema::table('taxa_locals', function (Blueprint $table) {
            // IDs and keys (using smaller varchar sizes)
            $table->string('burnes_fauna_id', 50)->nullable();
            $table->string('kupnes_fauna_id', 50)->nullable();
            $table->string('taxon_key', 50)->nullable();
            $table->string('accepted_taxon_key', 50)->nullable();
            $table->string('kingdom_key', 50)->nullable();
            $table->string('phylum_key', 50)->nullable();
            $table->string('class_key', 50)->nullable();
            $table->string('order_key', 50)->nullable();
            $table->string('family_key', 50)->nullable();
            $table->string('genus_key', 50)->nullable();
            $table->string('species_key', 50)->nullable();
            
            // Scientific names (using smaller varchar sizes)
            $table->string('accepted_scientific_name', 255)->nullable();
            $table->string('subspecies', 100)->nullable();
            $table->string('variety', 100)->nullable();
            $table->string('form', 100)->nullable();
            $table->string('subform', 100)->nullable();
            
            // Taxonomic ranks (using enum for fixed values)
            $table->enum('taxon_rank', ['domain', 'kingdom', 'phylum', 'class', 'order', 'family', 'genus', 'species', 'subspecies', 'variety', 'form', 'subform'])->nullable();
            $table->enum('taxonomic_status', ['accepted', 'synonym', 'doubtful', 'provisionally accepted'])->nullable();
            
            // Common names (using smaller varchar sizes)
            $table->string('cname_domain', 100)->nullable();
            $table->string('cname_superkingdom', 100)->nullable();
            $table->string('cname_kingdom', 100)->nullable();
            $table->string('cname_phylum', 100)->nullable();
            $table->string('cname_class', 100)->nullable();
            $table->string('cname_order', 100)->nullable();
            $table->string('cname_family', 100)->nullable();
            $table->string('cname_genus', 100)->nullable();
            $table->string('cname_species', 100)->nullable();
            $table->string('cname_subspecies', 100)->nullable();
            $table->string('cname_variety', 100)->nullable();
            
            // Taxonomic hierarchy (using smaller varchar sizes)
            $table->string('domain', 50)->nullable();
            $table->string('superkingdom', 50)->nullable();
            $table->string('subkingdom', 50)->nullable();
            $table->string('superphylum', 50)->nullable();
            $table->string('subphylum', 50)->nullable();
            $table->string('superclass', 50)->nullable();
            $table->string('subclass', 50)->nullable();
            $table->string('infraclass', 50)->nullable();
            $table->string('subterclass', 50)->nullable();
            $table->string('magnorder', 50)->nullable();
            $table->string('superorder', 50)->nullable();
            $table->string('suborder', 50)->nullable();
            $table->string('infraorder', 50)->nullable();
            $table->string('parvorder', 50)->nullable();
            $table->string('superfamily', 50)->nullable();
            $table->string('subfamily', 50)->nullable();
            $table->string('supertribe', 50)->nullable();
            $table->string('tribe', 50)->nullable();
            $table->string('subtribe', 50)->nullable();
            $table->string('subgenus', 50)->nullable();
            
            // Status fields (using enum for fixed values)
            $table->enum('status_dilindungi', ['tidak', 'ya', 'tidak diketahui'])->nullable();
            $table->enum('iucn_red_list_category', ['LC', 'NT', 'VU', 'EN', 'CR', 'EW', 'EX', 'DD', 'NE'])->nullable();
            $table->enum('cites_status', ['I', 'II', 'III', 'tidak terdaftar'])->nullable();
            $table->enum('status_kepunahan', ['tidak terancam', 'terancam', 'terancam kritis', 'terancam parah'])->nullable();
            
            // Metadata and user fields
            $table->text('metadata')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            
            // Boolean flags
            $table->boolean('Hybrid')->nullable();
            $table->boolean('Introduced')->nullable();
            $table->boolean('Eksotis')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxa_locals', function (Blueprint $table) {
            $table->dropColumn([
                'burnes_fauna_id', 'kupnes_fauna_id', 'taxon_key', 'accepted_taxon_key',
                'accepted_scientific_name', 'taxon_rank', 'taxonomic_status', 'domain',
                'cname_domain', 'superkingdom', 'cname_superkingdom', 'kingdom_key',
                'subkingdom', 'cname_subkingdom', 'superphylum', 'cname_superphylum',
                'phylum_key', 'subphylum', 'cname_subphylum', 'superclass',
                'cname_superclass', 'class_key', 'subclass', 'cname_subclass',
                'infraclass', 'cname_infraclass', 'subterclass', 'magnorder',
                'cname_magnorder', 'superorder', 'cname_superorder', 'order_key',
                'suborder', 'cname_suborder', 'infraorder', 'cname_infraorder',
                'parvorder', 'cname_parvorder', 'superfamily', 'cname_superfamily',
                'family_key', 'subfamily', 'cname_subfamily', 'supertribe',
                'cname_supertribe', 'tribe', 'cname_tribe', 'subtribe',
                'cname_subtribe', 'genus_key', 'subgenus', 'cname_subgenus',
                'species_key', 'subspecies', 'cname_subspecies', 'variety',
                'form', 'subform', 'cname_variety', 'status_dilindungi',
                'iucn_red_list_category', 'cites_status', 'status_kepunahan',
                'metadata', 'created_by', 'updated_by', 'Hybrid',
                'Introduced', 'Eksotis'
            ]);
        });
    }
};
