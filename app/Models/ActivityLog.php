<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'activity_logs';
    
    /**
     * Atribut yang dapat diisi
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
    ];
    
    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 