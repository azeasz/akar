<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaxaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\SettingFaqController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\BadgeMemberController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail'])->name('verification.resend');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/reset-password/{token}/check', [AuthController::class, 'checkResetToken']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Settings and FAQ routes
Route::get('/settings/about', [SettingFaqController::class, 'about']);
Route::get('/settings/privacy-policy', [SettingFaqController::class, 'privacyPolicy']);
Route::get('/settings/terms-conditions', [SettingFaqController::class, 'termsConditions']);
Route::get('/settings/faq', [SettingFaqController::class, 'faq']);

// Global statistics route
Route::get('/statistics/global', [StatisticsController::class, 'global'])->middleware('auth:api');

// Protected routes
Route::group(['middleware' => 'auth:api'], function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/users/{user}', [AuthController::class, 'update']);
    Route::get('/checklists/export', [ChecklistController::class, 'export']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::get('/checklists', [ChecklistController::class, 'index']);
    Route::post('/checklists', [ChecklistController::class, 'store']);
    
    // Bulk operations must come BEFORE parameterized routes
    Route::post('/checklists/bulk-delete', [ChecklistController::class, 'bulkDelete']);
    Route::post('/checklists/bulk-publish', [ChecklistController::class, 'bulkPublish']);
    
    // Individual checklist routes
    Route::get('/checklists/{checklist}', [ChecklistController::class, 'show']);
    Route::post('/checklists/{checklist}', [ChecklistController::class, 'update']);
    Route::patch('/checklists/{checklist}/status', [ChecklistController::class, 'updateStatus']);
    Route::delete('/checklists/{checklist}', [ChecklistController::class, 'destroy']);
    // Badge Member API routes
    Route::prefix('badges')->group(function () {
        Route::get('progress', [BadgeMemberController::class, 'getProgress']);
        Route::get('earned', [BadgeMemberController::class, 'getEarnedBadges']);
        Route::get('stats', [BadgeMemberController::class, 'getStats']);
        Route::post('progress', [BadgeMemberController::class, 'updateProgress']);
        Route::post('check-new', [BadgeMemberController::class, 'checkNewBadges']);
        Route::post('sync', [BadgeMemberController::class, 'syncBadges']);
        
        // Test route untuk debug badge tracking
        Route::get('test-tracking', function () {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }
            
            // Get current checklist count
            $currentCount = $user->checklists()
                ->where('status', 'published')
                ->where('is_completed', true)
                ->count();
            
            $previousCount = max(0, $currentCount - 1);
            
            $badgeService = app(\App\Services\BadgeMemberService::class);
            $newBadges = $badgeService->checkNewBadgesForMember($user->id, $currentCount, $previousCount);
            
            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'current_checklist_count' => $currentCount,
                'previous_count' => $previousCount,
                'new_badges' => $newBadges,
                'message' => 'Badge tracking test completed'
            ]);
        });

        // Test route untuk simulasi badge earning
        Route::get('test-simulate/{count}', function ($count) {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }
            
            $currentCount = (int) $count;
            $previousCount = max(0, $currentCount - 1);
            
            $badgeService = app(\App\Services\BadgeMemberService::class);
            $newBadges = $badgeService->checkNewBadgesForMember($user->id, $currentCount, $previousCount);
            
            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'simulated_checklist_count' => $currentCount,
                'previous_count' => $previousCount,
                'new_badges' => $newBadges,
                'message' => "Simulated badge tracking for {$currentCount} checklists"
            ]);
        });

        // Debug route untuk melihat data badge_members
        Route::get('debug-data', function () {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }
            
            $badgeMembers = \App\Models\BadgeMember::where('member_id', $user->id)->get();
            $checklists = $user->checklists()
                ->where('status', 'published')
                ->where('is_completed', true)
                ->get(['id', 'status', 'is_completed', 'created_at']);
            
            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'badge_members' => $badgeMembers,
                'checklists' => $checklists,
                'checklist_count' => $checklists->count(),
                'message' => 'Debug data retrieved'
            ]);
        });
        
        // Debug JSON response
        Route::get('debug-json', function() {
            return response()->json([
                'success' => true,
                'message' => 'JSON debug test',
                'data' => [
                    ['id' => 1, 'title' => 'Test Badge 1'],
                    ['id' => 2, 'title' => 'Test Badge 2']
                ]
            ]);
        });
    });

    // Taxa API routes
    Route::prefix('taksa')->group(function () {
        Route::get('search', [TaxaController::class, 'search']);
        Route::get('search/animalia', [TaxaController::class, 'searchAnimalia']);
        Route::get('search/plantae', [TaxaController::class, 'searchPlantae']);
        Route::get('search/fungi', [TaxaController::class, 'searchFungi']);
        Route::get('iucn-status', [TaxaController::class, 'getIUCNStatus']);
        Route::get('{id}', [TaxaController::class, 'detail']);
        Route::post('sync', [TaxaController::class, 'syncTaxa'])->name('api.taxas.sync');
    });
});
