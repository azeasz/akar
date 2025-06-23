<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class TaxaLocal extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'taxa_locals';
    
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
        'updated_at',
    ];
    
    /**
     * Atribut yang dikonversi ke tipe data lain
     */
    protected $casts = [
        'cites_listing_date' => 'date',
    ];
    
    /**
     * Mendapatkan warna badge untuk status IUCN
     */
    public function getIucnBadgeColorAttribute()
    {
        if (!$this->iucn_status) {
            return 'secondary';
        }
        
        return match ($this->iucn_status) {
            'LC' => 'success',
            'NT' => 'info',
            'VU' => 'warning',
            'EN', 'CR', 'EW', 'EX' => 'danger',
            default => 'secondary',
        };
    }
    
    /**
     * Mendapatkan deskripsi status IUCN
     */
    public function getIucnStatusTextAttribute()
    {
        if (!$this->iucn_status) {
            return 'Tidak diketahui';
        }
        
        return match ($this->iucn_status) {
            'LC' => 'Least Concern (Risiko Rendah)',
            'NT' => 'Near Threatened (Hampir Terancam)',
            'VU' => 'Vulnerable (Rentan)',
            'EN' => 'Endangered (Terancam)',
            'CR' => 'Critically Endangered (Kritis)',
            'EW' => 'Extinct in the Wild (Punah di Alam Liar)',
            'EX' => 'Extinct (Punah)',
            default => $this->iucn_status,
        };
    }
    
    /**
     * Mendapatkan warna badge untuk status CITES
     */
    public function getCitesBadgeColorAttribute()
    {
        if (!$this->cites_status) {
            return 'secondary';
        }
        
        return match ($this->cites_status) {
            'I' => 'danger',
            'II' => 'warning',
            'III' => 'info',
            default => 'secondary',
        };
    }
    
    /**
     * Mendapatkan deskripsi status CITES
     */
    public function getCitesStatusTextAttribute()
    {
        if (!$this->cites_status) {
            return 'Tidak terdaftar';
        }
        
        return match ($this->cites_status) {
            'I' => 'Appendix I (Terancam Punah)',
            'II' => 'Appendix II (Tidak Terancam Punah, Tapi Dapat Terancam)',
            'III' => 'Appendix III (Dilindungi di Negara Tertentu)',
            default => $this->cites_status,
        };
    }
    
    /**
     * Relasi ke checklist fauna
     */
    public function checklistFaunas()
    {
        return $this->hasMany(ChecklistFauna::class, 'fauna_id', 'id');
    }
} 