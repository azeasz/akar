<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriorityFauna extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'fauna_id',
        'taxa_id',
        'taxa_name',
        'scientific_name',
        'common_name',
        'taxa_data',
        'iucn_status',
        'protection_status',
        'category_id',
        'notes',
        'is_monitored',
        'last_api_sync',
    ];

    protected $casts = [
        'taxa_data' => 'array',
        'is_monitored' => 'boolean',
        'last_api_sync' => 'datetime',
    ];

    /**
     * Get the category for this priority fauna
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PriorityFaunaCategory::class, 'category_id');
    }

    /**
     * Get the checklist for this priority fauna
     */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }

    /**
     * Get all observations for this priority fauna
     */
    public function observations(): HasMany
    {
        return $this->hasMany(PriorityFaunaObservation::class);
    }

    /**
     * Get recent observations (last 30 days)
     */
    public function recentObservations(): HasMany
    {
        return $this->hasMany(PriorityFaunaObservation::class)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Scope for monitored faunas
     */
    public function scopeMonitored($query)
    {
        return $query->where('is_monitored', true);
    }

    /**
     * Scope for specific IUCN status
     */
    public function scopeIucnStatus($query, $status)
    {
        return $query->where('iucn_status', $status);
    }

    /**
     * Scope for specific protection status
     */
    public function scopeProtectionStatus($query, $status)
    {
        return $query->where('protection_status', $status);
    }

    /**
     * Scope for recently synced (within last 24 hours)
     */
    public function scopeRecentlySync($query)
    {
        return $query->where('last_api_sync', '>=', now()->subDay());
    }

    /**
     * Check if fauna data needs API sync (older than 7 days)
     */
    public function needsApiSync(): bool
    {
        return !$this->last_api_sync || $this->last_api_sync->lt(now()->subWeek());
    }

    /**
     * Get display name (common name or scientific name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->common_name ?: $this->scientific_name ?: $this->taxa_name;
    }

    /**
     * Get status badge color based on category
     */
    public function getStatusColorAttribute(): string
    {
        return $this->category->color_code ?? '#6c757d';
    }
}
