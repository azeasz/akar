<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaxaController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
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
