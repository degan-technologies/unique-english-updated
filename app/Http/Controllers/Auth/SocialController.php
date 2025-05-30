<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    /**
     * Redirect the user to the social provider.
     *
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        $allowedProviders = ['google', 'facebook', 'twitter', 'linkedin'];

        if (!in_array($provider, $allowedProviders)) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Handle the callback from the social provider.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // First try to find by provider ID
            $user = User::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            // If not found, try by email (but ensure provider matches)
            if (!$user && $socialUser->getEmail()) {
                $user = User::where('email', $socialUser->getEmail())
                    ->where('provider', $provider)
                    ->first();
            }

            // If still not found, create new user
            if (!$user) {
                $user = User::create([
                    'slug' => Str::uuid(),
                    'first_name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'phone' => '+251000000000', // Consider making nullable
                    'role' => 3,
                    'provider_id' => $socialUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                    'profile' => $socialUser->getAvatar(),
                ]);
            }

           $token = $user->createToken('AuthToken')->accessToken;

            // Main auth token cookie (secure, encrypted)
            $authCookie = Cookie::make(
                'authToken',
                $token,
                60 * 24 * 7, // 7 days
                '/',
                null,
                config('app.env') === 'production', // Secure in production
                true,  // HttpOnly
                false,
                'Lax'
            ); 
        return redirect(config('app.frontend_url') . '/')
            ->withCookie($authCookie);
        } catch (\Exception $e) {
            \Log::error("Social Login Error: " . $e->getMessage());
            return redirect(config('app.frontend_url') . '/login')
                ->with('error', 'Social login failed. Please try again.');
        }
    }
}
