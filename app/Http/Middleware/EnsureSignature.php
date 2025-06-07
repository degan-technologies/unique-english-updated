<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Laravel\Passport\TokenRepository;
use Illuminate\Support\Facades\Auth;

class EnsureSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function handle(Request $request, Closure $next)
    {
        // 1. Verify URL signature first
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired URL.');
        }

        // 2. Check if the Passport token exists and matches
        if ($request->has('access_token_id')) {
            $token = app(TokenRepository::class)->find($request->access_token_id);

            if (!$token || $token->revoked) {
                abort(403, 'Invalid access token.');
            }

            // Optional: Verify the token belongs to the requesting user
            if ($token->user_id != Auth::id()) {
                abort(403, 'Token does not match the authenticated user.');
            }
        }

        return $next($request);
    }
}
