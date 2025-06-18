<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'reports';
    
    /**
     * Atribut yang dapat diisi
     */
    protected $fillable = [
        'user_id',
        'masalah',
        'is_resolved',
    ];
    
    /**
     * Atribut yang dikonversi ke tipe data lain
     */
    protected $casts = [
        'is_resolved' => 'boolean',
    ];
    
    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope untuk filter laporan yang sudah selesai
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }
    
    /**
     * Scope untuk filter laporan yang belum selesai
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }
} 