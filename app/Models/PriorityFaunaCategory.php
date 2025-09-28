<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriorityFaunaCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'color_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all priority faunas for this category
     */
    public function priorityFaunas(): HasMany
    {
        return $this->hasMany(PriorityFauna::class, 'category_id');
    }

    /**
     * Get active priority faunas for this category
     */
    public function activePriorityFaunas(): HasMany
    {
        return $this->hasMany(PriorityFauna::class, 'category_id')
                    ->where('is_monitored', true);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for IUCN categories
     */
    public function scopeIucn($query)
    {
        return $query->where('type', 'iucn');
    }

    /**
     * Scope for protection status categories
     */
    public function scopeProtectionStatus($query)
    {
        return $query->where('type', 'protection_status');
    }

    /**
     * Get count of monitored faunas in this category
     */
    public function getMonitoredCountAttribute()
    {
        return $this->activePriorityFaunas()->count();
    }
}
