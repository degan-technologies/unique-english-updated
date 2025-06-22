<?php

namespace App\Helper;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class TokenGenerator {
    public static function generateSecureUrl(string $routeName, string $filename): string
    {
        /**
         * @var User $user
         */
        
        $user = Auth::user();
        $token = $user->token(); // Gets the current Passport token

        return URL::temporarySignedRoute(
            $routeName,
            now()->addHours(2), // Longer expiry for testing
            [
                'filename' => $filename,
                'token_id' => $token->id // Store ONLY the token IDv
            ]
        );
    }

    protected function generateUserToken(int $userId): string
    {
        // Generate a simple token based on user ID and current time
        return hash('sha256', $userId . now()->timestamp . config('app.key'));
    }

    public static function generateSecurePdfUrl(string $routeName, string $filename): string
    {
        /**
         * @var User $user
         */ 
        $user = Auth::user();
        $token = $user->token(); // Gets the current Passport token

        // Generate a full signed URL
        $fullSignedUrl = URL::temporarySignedRoute(
            $routeName,
            now()->addHours(2),
            [
                'filename' => $filename,
                'token_id' => $token->id,
            ]
        );

        // Convert to relative URL (remove domain)
        $parsed = parse_url($fullSignedUrl);
        $relativeUrl = $parsed['path'] . (isset($parsed['query']) ? '?' . $parsed['query'] : '');

        if (str_starts_with($relativeUrl, '/api')) {
            $relativeUrl = substr($relativeUrl, 4); // remove '/api'
        }

        return $relativeUrl;
    }
}
