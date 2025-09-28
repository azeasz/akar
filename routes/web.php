<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\WebViewController;
use App\Http\Controllers\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Tambahkan route untuk redirect ke admin login
Route::get('/admin', function () {
    return redirect()->route('admin.login');
});

// Rute untuk verifikasi email
Route::get('/email/verify', function () {
    return view('auth.verification.resend');
})->name('verification.notice');

// Rute untuk halaman web
Route::get('/terms-conditions', [WebViewController::class, 'termsConditions'])->name('terms');
Route::get('/privacy-policy', [WebViewController::class, 'privacyPolicy'])->name('privacy');
Route::get('/about', [WebViewController::class, 'about'])->name('about');

// Rute untuk reset password
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
