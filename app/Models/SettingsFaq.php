<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingsFaq extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'settings_faqs';
    
    /**
     * Atribut yang dapat diisi
     */
    protected $fillable = [
        'title',
        'description',
        'type',
    ];
    
    /**
     * Atribut yang dikonversi ke tipe data lain
     */
    protected $casts = [
        'type' => 'integer',
    ];
    
    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Dapatkan tipe dalam bentuk teks
     */
    public function getTypeNameAttribute()
    {
        $types = [
            1 => 'Deskripsi',
            2 => 'Privacy Policy',
            3 => 'Terms & Conditions',
            4 => 'About',
            5 => 'FAQ',
        ];
        
        return $types[$this->type] ?? 'Unknown';
    }
} 