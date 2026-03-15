<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ----------------------------------------------------------------
        // 0. Exclude authToken from Laravel's EncryptCookies middleware.
        //    The Passport token is an opaque reference — it doesn't need
        //    cookie-level encryption on top of HttpOnly + Secure + SameSite.
        //    Keeping it raw lets InjectBearerTokenFromCookie read it directly
        //    on API routes where EncryptCookies never runs anyway.
        // ----------------------------------------------------------------
        $middleware->encryptCookies(except: ['authToken']);

        // ----------------------------------------------------------------
        // 1. Security headers on ALL responses (web + API)
        //    Sets X-Content-Type-Options, X-Frame-Options, Referrer-Policy,
        //    Permissions-Policy, HSTS (production only).
        // ----------------------------------------------------------------
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // ----------------------------------------------------------------
        // 2. Content-Security-Policy via spatie/laravel-csp
        //    Uses App\Policies\AppCspPolicy (see config/csp.php).
        //    Must run after SecurityHeaders so both headers are present.
        // ----------------------------------------------------------------
        $middleware->append(\Spatie\Csp\AddCspHeaders::class);

        // ----------------------------------------------------------------
        // 3. Inject the Passport Bearer token from the HttpOnly cookie
        //    BEFORE Passport's token guard runs on every API route.
        //    This way the JS frontend never touches the token — the cookie
        //    is sent automatically by the browser (withCredentials: true),
        //    and this middleware promotes it to an Authorization header.
        // ----------------------------------------------------------------
        $middleware->prependToGroup('api', \App\Http\Middleware\InjectBearerTokenFromCookie::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
