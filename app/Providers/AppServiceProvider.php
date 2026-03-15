<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Configure Laravel Passport token lifetimes.
     * Access tokens expire after 60 minutes.
     * Refresh tokens expire after 7 days (used for "remember me").
     * Personal access tokens follow the same rules.
     */
    public function boot(): void
    {
        // DateInterval format: PT5M = 5 minutes, P7D = 7 days
        // Must be a DateInterval instance — not a Carbon datetime.
        Passport::tokensExpireIn(new \DateInterval('PT5M'));
        Passport::refreshTokensExpireIn(new \DateInterval('P7D'));
        Passport::personalAccessTokensExpireIn(new \DateInterval('PT5M'));
    }
}
