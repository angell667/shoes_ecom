<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $settings = Setting::getOAuthSettings();

        if ($settings['google_enabled'] !== '1' || empty($settings['google_client_id'])) {
            return response()->json(['error' => 'Google login is disabled or not configured'], 400);
        }

        config([
            'services.google.client_id' => $settings['google_client_id'],
            'services.google.client_secret' => $settings['google_client_secret'],
            'services.google.redirect' => $settings['google_redirect_uri'],
        ]);

        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(Request $request)
    {
        $settings = Setting::getOAuthSettings();

        if ($settings['google_enabled'] !== '1') {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect("{$frontendUrl}/login?error=" . urlencode('Google login is disabled'));
        }

        config([
            'services.google.client_id' => $settings['google_client_id'],
            'services.google.client_secret' => $settings['google_client_secret'],
            'services.google.redirect' => $settings['google_redirect_uri'],
        ]);

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (InvalidStateException $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect("{$frontendUrl}/login?error=" . urlencode('Invalid authentication state. Please try again.'));
        } catch (\Exception $e) {
            Log::error('Google OAuth error: ' . $e->getMessage());
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect("{$frontendUrl}/login?error=" . urlencode('Google authentication failed: ' . $e->getMessage()));
        }

        try {
            // Find user by Google ID or email
            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($user) {
                // Update existing user with Google ID if not set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                    ]);
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                ]);
            }

            // Generate token
            $token = $user->createToken('google-auth-token')->plainTextToken;

            // Redirect to frontend with token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $userData = json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'is_admin' => $user->is_admin,
            ]);

            return redirect("{$frontendUrl}/auth/google/callback?token={$token}&user=" . urlencode($userData));

        } catch (\Exception $e) {
            Log::error('Google OAuth user creation error: ' . $e->getMessage());
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect("{$frontendUrl}/login?error=" . urlencode('Failed to create user account'));
        }
    }

    public function getUser(Request $request)
    {
        return response()->json($request->user());
    }
}
