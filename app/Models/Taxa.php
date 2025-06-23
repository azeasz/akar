<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxa extends Model
{
    use HasFactory;
    
    /**
     * Koneksi database yang digunakan
     */
    protected $connection = 'amaturalist';
    
    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'taxas';
    
    /**
     * Atribut yang dapat diisi
     */
    protected $fillable = [
        'id',
        'kingdom',
        'phylum',
        'class',
        'order',
        'family',
        'genus',
        'species',
        'subspecies',
        'common_name',
        'local_name',
        'scientific_name',
        'author',
        'rank',
        'taxonomic_status',
        'iucn_status',
        'iucn_criteria',
        'cites_status',
        'cites_source',
        'cites_listing_date',
        'image_url',
        'description',
    ];
    
    /**
     * Atribut yang dikonversi ke tipe data lain
     */
    protected $casts = [
        'cites_listing_date' => 'date',
    ];
    
    /**
     * Relasi ke checklist fauna
     */
    public function checklistFaunas()
    {
        return $this->hasMany(ChecklistFauna::class, 'fauna_id', 'id');
    }
} 