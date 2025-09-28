<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriorityFaunaObservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'priority_fauna_id',
        'checklist_id',
        'user_id',
        'scientific_name',
        'common_name',
        'individual_count',
        'photos',
        'latitude',
        'longitude',
        'location_name',
        'status',
        'notes',
        'observed_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'photos' => 'array',
        'observed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the priority fauna that owns this observation.
     */
    public function priorityFauna(): BelongsTo
    {
        return $this->belongsTo(PriorityFauna::class);
    }

    /**
     * Get the checklist that owns this observation.
     */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    /**
     * Get the user who made this observation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who reviewed this observation.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope untuk observasi baru yang belum direview
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope untuk observasi hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope untuk observasi minggu ini
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Get formatted location string
     */
    public function getFormattedLocationAttribute(): string
    {
        if ($this->location_name) {
            return $this->location_name;
        }
        
        if ($this->latitude && $this->longitude) {
            return "Lat: {$this->latitude}, Lng: {$this->longitude}";
        }
        
        return 'Lokasi tidak tersedia';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'new' => 'warning',
            'reviewed' => 'info',
            'verified' => 'success',
            'flagged' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'new' => 'Baru',
            'reviewed' => 'Direview',
            'verified' => 'Terverifikasi',
            'flagged' => 'Ditandai',
            default => 'Unknown'
        };
    }
}
