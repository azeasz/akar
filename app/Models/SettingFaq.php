<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingFaq extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'settings_faqs';

    protected $fillable = [
        'title',
        'description',
        'type'
    ];

    /**
     * Get type name based on type value
     *
     * @return string
     */
    public function getTypeNameAttribute()
    {
        $types = [
            1 => 'Description',
            2 => 'Privacy Policy',
            3 => 'Terms and Conditions',
            4 => 'About',
            5 => 'FAQ'
        ];

        return $types[$this->type] ?? 'Unknown';
    }
} 