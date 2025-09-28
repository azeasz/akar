<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'badge_members';

    protected $fillable = [
        'member_id',
        'badge_id',
        'total',
    ];

    protected $casts = [
        'member_id' => 'integer',
        'badge_id' => 'integer',
        'total' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the member that owns the badge
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get badge information from main FOBI database
     * This will be fetched via API or direct database connection
     */
    public function getBadgeDataAttribute()
    {
        // This will be populated by the service layer
        return null;
    }

    /**
     * Check if badge is earned based on total
     */
    public function getIsEarnedAttribute(): bool
    {
        // Will be determined by comparing with badge requirements
        return $this->total > 0;
    }

    /**
     * Get earned badges for a specific member
     */
    public static function getEarnedBadgesForMember(int $memberId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('member_id', $memberId)
            ->where('total', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all badge progress for a specific member
     */
    public static function getBadgeProgressForMember(int $memberId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('member_id', $memberId)
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Update or create badge progress for member
     */
    public static function updateBadgeProgress(int $memberId, int $badgeId, int $total): self
    {
        try {
            \Log::info('BadgeMember: updateBadgeProgress called', [
                'member_id' => $memberId,
                'badge_id' => $badgeId,
                'total' => $total
            ]);

            // Validate input parameters
            if ($memberId <= 0) {
                throw new \InvalidArgumentException('Invalid member_id: ' . $memberId);
            }
            
            if ($badgeId <= 0) {
                throw new \InvalidArgumentException('Invalid badge_id: ' . $badgeId);
            }
            
            if ($total < 0) {
                throw new \InvalidArgumentException('Invalid total (cannot be negative): ' . $total);
            }

            $badgeMember = static::updateOrCreate(
                [
                    'member_id' => $memberId,
                    'badge_id' => $badgeId,
                ],
                [
                    'total' => $total,
                ]
            );

            // Verify the badge member was created/updated successfully
            if (!$badgeMember || !$badgeMember->id) {
                throw new \Exception('Failed to create or update badge member record');
            }

            \Log::info('BadgeMember: Badge progress saved successfully', [
                'id' => $badgeMember->id,
                'member_id' => $badgeMember->member_id,
                'badge_id' => $badgeMember->badge_id,
                'total' => $badgeMember->total,
                'created_at' => $badgeMember->created_at ? $badgeMember->created_at->toISOString() : null,
                'updated_at' => $badgeMember->updated_at ? $badgeMember->updated_at->toISOString() : null
            ]);

            return $badgeMember;
        } catch (\Exception $e) {
            \Log::error('BadgeMember: Error updating badge progress', [
                'member_id' => $memberId,
                'badge_id' => $badgeId,
                'total' => $total,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to let caller handle
        }
    }

    /**
     * Get badge statistics for member
     */
    public static function getBadgeStatsForMember(int $memberId): array
    {
        $totalBadges = static::where('member_id', $memberId)->count();
        $earnedBadges = static::where('member_id', $memberId)
            ->where('total', '>', 0)
            ->count();

        return [
            'total_badges' => $totalBadges,
            'earned_badges' => $earnedBadges,
            'completion_percentage' => $totalBadges > 0 ? round(($earnedBadges / $totalBadges) * 100, 2) : 0,
        ];
    }
}
