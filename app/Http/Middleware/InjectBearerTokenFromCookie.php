<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * InjectBearerTokenFromCookie
 *
 * Reads the Passport access token from the HttpOnly `authToken` cookie and
 * injects it as the Authorization Bearer header before Passport's auth guard
 * runs on API routes.
 *
 * The `authToken` cookie is excluded from EncryptCookies (see bootstrap/app.php)
 * so the raw token value arrives here directly — no decryption needed.
 *
 * The frontend never touches this token: it lives in an HttpOnly + Secure +
 * SameSite:Strict cookie that JavaScript cannot read.
 */
class InjectBearerTokenFromCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->bearerToken()) {
            // authToken is excluded from EncryptCookies, so this is the raw token.
            $token = $request->cookies->get('authToken');

            if ($token) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }

        return $next($request);
    }
}
