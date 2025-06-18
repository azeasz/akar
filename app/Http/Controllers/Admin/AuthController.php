<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login admin
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Proses login admin
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // bisa email atau username
            'password' => 'required|string',
        ]);

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        
        // Coba login sebagai admin
        $adminCredentials = [
            $loginType => $request->login,
            'password' => $request->password,
        ];
        
        // Jika login sebagai admin berhasil
        if (Auth::guard('admin')->attempt($adminCredentials)) {
            $request->session()->regenerate();
            
            // Log aktivitas login admin
            activity_log('login', 'Admin berhasil login', Auth::guard('admin')->user()->id);
            
            return redirect()->intended(route('admin.dashboard'));
        }
        
        // Jika login dengan admin gagal, coba cek di user dengan level 2
        $userCredentials = [
            $loginType => $request->login,
            'password' => $request->password,
            'level' => 2
        ];
        
        if (Auth::attempt($userCredentials)) {
            $request->session()->regenerate();
            
            // Log aktivitas login user level admin
            activity_log('login', 'User admin berhasil login', Auth::user()->id);
            
            return redirect()->intended(route('admin.dashboard'));
        }
        
        throw ValidationException::withMessages([
            'login' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
        ]);
    }

    /**
     * Logout admin
     */
    public function logout(Request $request)
    {
        $userId = null;
        
        if (Auth::guard('admin')->check()) {
            $userId = Auth::guard('admin')->user()->id;
            Auth::guard('admin')->logout();
        } else {
            $userId = Auth::user()->id;
            Auth::logout();
        }
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Log aktivitas logout
        activity_log('logout', 'Admin berhasil logout', $userId);
        
        return redirect()->route('admin.login');
    }
} 