<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChecklistImage extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Atribut yang dapat diisi
     */
    protected $fillable = [
        'checklist_id',
        'fauna_id',
        'image_path',
    ];
    
    /**
     * Relasi ke checklist
     */
    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    /**
     * Relasi ke taksa (fauna)
     */
    public function fauna()
    {
        return $this->belongsTo(Taxa::class, 'fauna_id');
    }
} 