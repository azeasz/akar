<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checklist extends Model
{
    use HasFactory, SoftDeletes;


    
    /**
     * Atribut yang dapat diisi
     */
    protected $fillable = [
        'user_id',
        'type',
        'is_completed',
        'status',
        'tanggal',
        'nama_lokasi',
        'latitude',
        'longitude',
        'pemilik',
        'catatan',
        'app_id',
        'category_id',
        'name',
        'nama_event',
        'nama_arena',
        'total_hunter',
        'teknik_berburu',
        'status_tangkapan',
        'category_tempat_id',
        'nama_toko',
        'nama_penjual',
        'domisili_penjual',
        'profesi_penjual_id',
        'nama_pemilik',
        'domisili_pemilik',
        'profesi_pemilik_id',
        'confirmed',
        'published_at',
    ];
    
    /**
     * Atribut yang dikonversi ke tipe data lain
     */
    protected $casts = [
        'is_completed' => 'boolean',
        'confirmed' => 'boolean',
        'tanggal' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
        'published_at' => 'datetime',
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function($checklist) {
            \Log::warning('Checklist is being deleted', [
                'checklist_id' => $checklist->id,
                'is_force_delete' => $checklist->isForceDeleting(),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
            ]);
        });
        
        // Sinkronkan status kelengkapan antara is_completed dan confirmed
        static::saving(function($checklist) {
            if (isset($checklist->attributes['is_completed']) && !isset($checklist->attributes['confirmed'])) {
                $checklist->confirmed = $checklist->is_completed;
            } elseif (isset($checklist->attributes['confirmed']) && !isset($checklist->attributes['is_completed'])) {
                $checklist->is_completed = $checklist->confirmed;
            }
        });
    }
    
    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relasi ke fauna
     */
    public function faunas()
    {
        return $this->hasMany(ChecklistFauna::class);
    }
    
    /**
     * Relasi ke gambar
     */
    public function images()
    {
        return $this->hasMany(ChecklistImage::class);
    }
    
    /**
     * Scope untuk filter data berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Scope untuk filter data berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope untuk filter data berdasarkan tipe
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Scope untuk filter data berdasarkan kategori
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
    
    /**
     * Scope untuk filter data berdasarkan kelengkapan data
     */
    public function scopeByCompletionStatus($query, $isCompleted)
    {
        return $query->where(function($q) use ($isCompleted) {
            $q->where('is_completed', $isCompleted)
              ->orWhere('confirmed', $isCompleted);
        });
    }
    
    /**
     * Scope untuk mencari berdasarkan kata kunci
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            return $query->where(function ($q) use ($keyword) {
                $q->where('nama_lokasi', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%")
                  ->orWhere('pemilik', 'like', "%{$keyword}%")
                  ->orWhere('nama_pemilik', 'like', "%{$keyword}%")
                  ->orWhere('catatan', 'like', "%{$keyword}%");
            });
        }
        return $query;
    }
} 