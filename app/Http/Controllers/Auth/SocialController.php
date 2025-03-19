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
        // Define allowed providers
        $allowedProviders = ['google', 'facebook', 'twitter', 'linkedin'];

        // Validate provider
        if (!in_array($provider, $allowedProviders)) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        // Redirect to provider login page
        return Socialite::driver($provider)->redirect();
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
            // Use stateless() for APIs
        $socialUser = Socialite::driver($provider)->stateless()->user();

        // Find or create the user
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->orWhere('email', $socialUser->getEmail())
            ->first();

            if (!$user) {
                // If user is not registered, create a new account
                $user = User::create([
                    'slug'         => Str::uuid(),
                    'first_name'   => $socialUser->getName(),
                    'email'        => $socialUser->getEmail(),
                    'provider'     => $provider,
                    'phone'        => '+251000000000',
                    'role'         => 3,
                    'provider_id'  => $socialUser->getId(),
                    'password'     => bcrypt(Str::random(16)),
                    'profile'      => $socialUser->getAvatar(),
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now(),
                ]);

                // Log user in
            }

            // Create a Passport token for the user
            $token = $user->createToken('AuthToken')->accessToken;

            // Log token for debugging
            \Log::info("Social Login Token: " . $token);

            // Set the token as a secure, HTTP-only cookie (aligned with AuthController)
            $cookie = Cookie::make(
                'authToken',
                $token,
                60 * 24 * 7, // 7 days
                '/',         // Path (root)
                null,        // Domain (null for localhost)
                false,       // Secure: false for local development (true for production with HTTPS)
                true,        // HttpOnly: inaccessible to JavaScript
                false,       // Raw
                'Lax'        // SameSite policy
            );

            // Set logged-in status via a separate cookie (for JS usage in Vue)
            $loginStatusCookie = Cookie::make('loggedin', 'true', 60 * 24 * 7, '/', null, false, false);

            // Redirect to frontend with cookies
            return redirect('http://127.0.0.1:8000/#/instructor')
                ->withCookie($cookie)
                ->withCookie($loginStatusCookie);
            } catch (\Exception $e) {
                \Log::error("Social Login Error: " . $e->getMessage());
                return redirect('http://127.0.0.1:8000/#/login')
                    ->with('error', 'Social login failed. Please try again.');
            }

    }
}
