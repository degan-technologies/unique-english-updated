<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeaders
 *
 * Adds a comprehensive set of HTTP security headers to every response.
 * These complement the spatie/laravel-csp Content-Security-Policy header.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent browsers from MIME-sniffing a response away from the declared content-type
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent the page from being embedded in a frame (clickjacking protection)
        // YouTube iframes are handled by CSP frame-ancestors, not this header
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Enable the browser's built-in XSS filter (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control information sent in the Referer header
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict access to browser features / APIs
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()'
        );

        // Enforce HTTPS for 1 year (only in production)
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Remove the X-Powered-By header (information disclosure)
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
