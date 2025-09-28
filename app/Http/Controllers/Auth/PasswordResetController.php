<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        $resetRecord = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

        // Check if the token is invalid or expired
        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->diffInHours(Carbon::now()) > 24) {
            // If the token is invalid, show an error view
            return view('auth.passwords.invalid-token');
        }

        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $resetRecord->email]
        );
    }
}
