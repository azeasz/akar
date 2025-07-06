<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaxaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistController;

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

// Protected routes
Route::group(['middleware' => 'auth:api'], function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/checklists', [ChecklistController::class, 'index']);
    Route::post('/checklists', [ChecklistController::class, 'store']);
    Route::get('/checklists/{checklist}', [ChecklistController::class, 'show']);

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
