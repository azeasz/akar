<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BadgeMemberService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BadgeMemberController extends Controller
{
    protected BadgeMemberService $badgeMemberService;

    public function __construct(BadgeMemberService $badgeMemberService)
    {
        $this->badgeMemberService = $badgeMemberService;
        
        // Add middleware for authentication
        $this->middleware('auth:api');
    }

    /**
     * Get member's badge progress
     */
    public function getProgress(Request $request): JsonResponse
    {
        try {
            $memberId = Auth::id();
            
            if (!$memberId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Log for debugging
            \Log::info('BadgeMemberController: Getting progress for member ID: ' . $memberId);

            // Check if simplified response is requested
            $simplified = $request->query('simplified', false);
            \Log::info('BadgeMemberController: Simplified response requested: ' . ($simplified ? 'true' : 'false'));

            $progress = $this->badgeMemberService->getMemberBadgeProgress($memberId, $simplified);

            \Log::info('BadgeMemberController: Progress retrieved', ['count' => count($progress)]);

            return response()->json([
                'success' => true,
                'message' => 'Badge progress retrieved successfully',
                'data' => $progress
            ]);
        } catch (\Exception $e) {
            \Log::error('BadgeMemberController: Error getting progress', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get badge progress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get member's earned badges only
     */
    public function getEarnedBadges(Request $request): JsonResponse
    {
        try {
            $memberId = Auth::id();
            
            if (!$memberId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $earnedBadges = $this->badgeMemberService->getMemberEarnedBadges($memberId);

            return response()->json([
                'success' => true,
                'message' => 'Earned badges retrieved successfully',
                'data' => $earnedBadges
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get earned badges: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get member's badge statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $memberId = Auth::id();
            
            if (!$memberId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $stats = $this->badgeMemberService->getMemberBadgeStats($memberId);

            return response()->json([
                'success' => true,
                'message' => 'Badge statistics retrieved successfully',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get badge statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update member's badge progress
     */
    public function updateProgress(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'badge_id' => 'required|integer',
                'total' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $memberId = Auth::id();
            
            if (!$memberId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $badgeMember = $this->badgeMemberService->updateMemberBadgeProgress(
                $memberId,
                $request->badge_id,
                $request->total
            );

            return response()->json([
                'success' => true,
                'message' => 'Badge progress updated successfully',
                'data' => $badgeMember
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update badge progress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for new badges earned
     */
    public function checkNewBadges(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_checklist_count' => 'required|integer|min:0',
                'previous_checklist_count' => 'integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $memberId = Auth::id();
            
            if (!$memberId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $newBadges = $this->badgeMemberService->checkNewBadgesForMember(
                $memberId,
                $request->current_checklist_count,
                $request->previous_checklist_count ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => 'New badges check completed',
                'data' => $newBadges,
                'meta' => [
                    'has_new_badges' => count($newBadges) > 0,
                    'new_badges_count' => count($newBadges),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check new badges: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync member badges with FOBI API
     */
    public function syncBadges(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_checklist_count' => 'integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $memberId = Auth::id();
            
            if (!$memberId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $success = $this->badgeMemberService->syncMemberBadges(
                $memberId,
                $request->current_checklist_count ?? 0
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Badges synced successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to sync badges'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync badges: ' . $e->getMessage()
            ], 500);
        }
    }
}
