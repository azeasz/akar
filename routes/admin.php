<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ChecklistController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
|
*/

// Auth routes
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });
    
    // Protected routes
    Route::middleware(['auth:admin,web', 'admin'])->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Users
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('users', UserController::class);
        
        // Admins
        Route::get('admins/export', [AdminController::class, 'export'])->name('admins.export');
        Route::resource('admins', AdminController::class);
        
        // Checklists
        Route::get('checklists/export', [ChecklistController::class, 'export'])->name('checklists.export');
        Route::put('checklists/{checklist}/complete', [ChecklistController::class, 'complete'])->name('checklists.complete');
        Route::put('checklists/{checklist}/publish', [ChecklistController::class, 'publish'])->name('checklists.publish');
        Route::resource('checklists', ChecklistController::class);
        
        // Activity Logs
        Route::get('logs/export', [ActivityLogController::class, 'export'])->name('logs.export');
        Route::resource('logs', ActivityLogController::class)->only(['index', 'show', 'destroy']);
        
        // Settings
        Route::resource('settings', SettingController::class);
        
        // Reports
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::put('reports/{report}/resolve', [ReportController::class, 'markResolved'])->name('reports.resolve');
        Route::put('reports/{report}/unresolve', [ReportController::class, 'markUnresolved'])->name('reports.unresolve');
        Route::resource('reports', ReportController::class)->only(['index', 'show', 'destroy']);
        
        // New Registrations
        Route::put('registrations/{registration}/approve', [RegistrationController::class, 'approve'])->name('registrations.approve');
        Route::put('registrations/{registration}/reject', [RegistrationController::class, 'reject'])->name('registrations.reject');
        Route::resource('registrations', RegistrationController::class)->only(['index', 'show']);
        
        // Logout
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
}); 