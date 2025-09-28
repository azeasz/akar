<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyEmail', 'forgotPassword', 'checkResetToken', 'resetPassword']]);
    }

    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'organisasi' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'social_media' => 'nullable|string|max:255',
            'domisili' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Generate verification token
        $verificationToken = Str::random(60);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'organisasi' => $request->organisasi,
            'phone_number' => $request->phone_number,
            'social_media' => $request->social_media,
            'domisili' => $request->domisili,
            'email_verification_token' => $verificationToken,
        ]);

        // Send verification email
        $this->sendVerificationEmail($user, $verificationToken);

        // Generate token but inform user to verify email
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Pendaftaran berhasil. Silakan verifikasi email Anda untuk melanjutkan.',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ]
        ], 201);
    }

    /**
     * Send verification email to user
     *
     * @param User $user
     * @param string $token
     * @return void
     */
    private function sendVerificationEmail(User $user, $token)
    {
        $verificationUrl = url('/api/verify-email/' . $token);
        
        Mail::send('emails.verify-email', ['user' => $user, 'verificationUrl' => $verificationUrl], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('Verifikasi Email Anda');
        });
    }

    /**
     * Verify user's email
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            // Tampilkan halaman error jika token tidak valid
            return view('auth.verification.error');
        }

        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        // Tampilkan halaman sukses
        return view('auth.verification.success');
    }

    /**
     * Resend verification email
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            // Cek apakah request dari API atau web
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email sudah diverifikasi sebelumnya.'], 400);
            }
            return redirect()->route('verification.notice')->with('error', 'Email sudah diverifikasi sebelumnya.');
        }

        // Generate new verification token
        $verificationToken = Str::random(60);
        $user->email_verification_token = $verificationToken;
        $user->save();

        // Resend verification email
        $this->sendVerificationEmail($user, $verificationToken);

        // Cek apakah request dari API atau web
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Link verifikasi baru telah dikirim ke email Anda.'
            ]);
        }
        
        return redirect()->route('verification.notice')->with('success', 'Link verifikasi baru telah dikirim ke email Anda.');
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password
        ];

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if email is verified
        $user = auth('api')->user();
        if (!$user->email_verified_at) {
            auth('api')->logout();
            return response()->json(['error' => 'Email belum diverifikasi. Silakan cek email Anda untuk tautan verifikasi.'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        // Refresh the user model from the database to ensure all accessors are applied.
        return response()->json(auth('api')->user()->fresh());
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password does not match'], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password successfully changed']);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        // Authorization: Ensure the authenticated user is updating their own profile.
        if (Auth::id() !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'organisasi' => 'nullable|string|max:255',
            'domisili' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('organisasi')) {
            $user->organisasi = $request->organisasi;
        }

        if ($request->has('domisili')) {
            $user->domisili = $request->domisili;
        }

        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if it exists
            if ($user->profile_picture) {
                // The path in the DB should be relative to the public disk's root
                Storage::disk('public')->delete($user->getAttributes()['profile_picture']);
            }
            // Store the new one and get the path
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }

        $user->save();

        // Reload the model one last time to be absolutely sure the accessor is applied before sending.
        $user->refresh();
        return response()->json($user);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth('api')->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout, please try again.'], 500);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            return $this->respondWithToken(auth('api')->refresh());
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token could not be refreshed'], 401);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }
    
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Hapus semua token reset password yang sudah ada untuk user ini
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Buat token baru
        $token = Str::random(60);
        
        // Simpan token baru
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // Kirim email dengan link reset password menggunakan named route
        $resetUrl = route('password.reset', ['token' => $token]);
        
        Mail::send('emails.reset-password', ['resetUrl' => $resetUrl], function($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password Anda');
        });

        return response()->json([
            'message' => 'Link reset password telah dikirim ke email Anda.'
        ]);
    }

    /**
     * Check if a reset token is valid.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkResetToken($token)
    {
        $resetRecord = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'message' => 'Token reset password tidak valid.',
                'valid' => false
            ], 400);
        }

        // Cek apakah token masih valid (belum kedaluwarsa, misalnya 24 jam)
        $tokenCreatedAt = Carbon::parse($resetRecord->created_at);
        $isTokenExpired = $tokenCreatedAt->diffInHours(Carbon::now()) > 24;

        if ($isTokenExpired) {
            return response()->json([
                'message' => 'Token reset password sudah kedaluwarsa.',
                'valid' => false
            ], 400);
        }

        return response()->json([
            'message' => 'Token reset password valid.',
            'valid' => true
        ]);
    }

    /**
     * Reset the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Cek apakah token valid
        $resetRecord = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'message' => 'Token reset password tidak valid.'
            ], 400);
        }

        // Cek apakah token masih valid (belum kedaluwarsa, misalnya 24 jam)
        $tokenCreatedAt = Carbon::parse($resetRecord->created_at);
        $isTokenExpired = $tokenCreatedAt->diffInHours(Carbon::now()) > 24;

        if ($isTokenExpired) {
            return response()->json([
                'message' => 'Token reset password sudah kedaluwarsa.'
            ], 400);
        }

        // Update password user
        $user = User::where('email', $resetRecord->email)->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus token yang sudah digunakan
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        return response()->json([
            'message' => 'Password berhasil direset.'
        ]);
    }
}
