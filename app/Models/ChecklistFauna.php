<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChecklistFauna extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Atribut yang dapat diisi
     */
    protected $fillable = [
        'checklist_id',
        'nama_spesies',
        'jumlah',
        'gender',
        'cincin',
        'tagging',
        'catatan',
        'status_buruan',
        'alat_buru',
        'fauna_id',
        'asal',
        'harga',
        'kondisi',
        'ijin',
        'total', // Untuk kompatibilitas dengan struktur lama
    ];
    
    /**
     * Atribut yang dikonversi ke tipe data lain
     */
    protected $casts = [
        'cincin' => 'boolean',
        'tagging' => 'boolean',
        'jumlah' => 'integer',
        'total' => 'integer', // Untuk kompatibilitas dengan struktur lama
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function($fauna) {
            \Log::warning('ChecklistFauna is being deleted', [
                'fauna_id' => $fauna->id,
                'checklist_id' => $fauna->checklist_id,
                'is_force_delete' => $fauna->isForceDeleting(),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
            ]);
        });
        
        // Sinkronkan antara jumlah dan total untuk kompatibilitas
        static::saving(function($fauna) {
            if (isset($fauna->attributes['jumlah']) && !isset($fauna->attributes['total'])) {
                $fauna->total = $fauna->jumlah;
            } elseif (isset($fauna->attributes['total']) && !isset($fauna->attributes['jumlah'])) {
                $fauna->jumlah = $fauna->total;
            }
            
            // Konversi gender dari kode ke string jika diperlukan
            if (isset($fauna->attributes['gender']) && is_numeric($fauna->attributes['gender'])) {
                $genderMap = [
                    0 => 'Tidak Diketahui',
                    1 => 'Jantan',
                    2 => 'Betina'
                ];
                
                if (array_key_exists($fauna->gender, $genderMap)) {
                    $fauna->gender_text = $genderMap[$fauna->gender];
                }
            }
            
            // Konversi kondisi ke status_buruan jika diperlukan
            if (isset($fauna->attributes['kondisi']) && !isset($fauna->attributes['status_buruan'])) {
                $kondisiMap = [
                    1 => 'hidup',
                    2 => 'mati',
                    3 => 'lainnya'
                ];
                
                if (array_key_exists($fauna->kondisi, $kondisiMap)) {
                    $fauna->status_buruan = $kondisiMap[$fauna->kondisi];
                }
            } elseif (isset($fauna->attributes['status_buruan']) && !isset($fauna->attributes['kondisi'])) {
                $statusMap = [
                    'hidup' => 1,
                    'mati' => 2,
                    'lainnya' => 3
                ];
                
                if (array_key_exists(strtolower($fauna->status_buruan), $statusMap)) {
                    $fauna->kondisi = $statusMap[strtolower($fauna->status_buruan)];
                }
            }
        });
    }
    
    /**
     * Relasi ke checklist
     */
    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }
    
    /**
     * Relasi ke taxa local
     */
    public function taxaLocal()
    {
        return $this->belongsTo(TaxaLocal::class, 'fauna_id', 'taxa_id');
    }
    
    /**
     * Relasi ke taxa di database kedua
     */
    public function taxa()
    {
        return $this->belongsTo(Taxa::class, 'fauna_id', 'id');
    }
    
    /**
     * Scope untuk mencari berdasarkan kata kunci
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            return $query->where(function ($q) use ($keyword) {
                $q->where('nama_spesies', 'like', "%{$keyword}%")
                  ->orWhere('gender', 'like', "%{$keyword}%")
                  ->orWhere('catatan', 'like', "%{$keyword}%")
                  ->orWhere('status_buruan', 'like', "%{$keyword}%")
                  ->orWhere('alat_buru', 'like', "%{$keyword}%");
            });
        }
        return $query;
    }
    
    /**
     * Accessor untuk mendapatkan teks gender
     */
    public function getGenderTextAttribute()
    {
        if (!isset($this->attributes['gender'])) {
            return 'Tidak Diketahui';
        }
        
        if (is_numeric($this->attributes['gender'])) {
            $genderMap = [
                0 => 'Tidak Diketahui',
                1 => 'Jantan',
                2 => 'Betina'
            ];
            
            return $genderMap[$this->attributes['gender']] ?? 'Tidak Diketahui';
        }
        
        return $this->attributes['gender'];
    }
    
    /**
     * Accessor untuk mendapatkan teks status buruan
     */
    public function getStatusTextAttribute()
    {
        if (isset($this->attributes['status_buruan'])) {
            return ucfirst($this->attributes['status_buruan']);
        }
        
        if (isset($this->attributes['kondisi'])) {
            $kondisiMap = [
                1 => 'Hidup',
                2 => 'Mati',
                3 => 'Lainnya'
            ];
            
            return $kondisiMap[$this->attributes['kondisi']] ?? 'Tidak Diketahui';
        }
        
        return 'Tidak Diketahui';
    }
} 