<?php

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;

return [

    /*
     * Presets determine which CSP headers are set.
     * Each class must implement Spatie\Csp\Preset.
     *
     * AppCspPolicy is the app-specific preset covering Vue 3, Tailwind,
     * YouTube embeds, and self-hosted API calls.
     */
    'presets' => [
        \App\Policies\AppCspPolicy::class,
    ],

    /*
     * Additional global CSP directives applied on top of all presets.
     * Use these for quick per-environment tweaks without touching the preset.
     */
    'directives' => [
        // Example: allow unsafe-eval only in development
        // [Directive::SCRIPT, Keyword::UNSAFE_EVAL],
    ],

    /*
     * Presets placed here run in report-only mode.
     * Good for testing a new policy before enforcing it.
     */
    'report_only_presets' => [],

    'report_only_directives' => [],

    /*
     * All violations against the policy will be reported to this URL.
     * Leave empty to disable the report-uri directive.
     */
    'report_uri' => env('CSP_REPORT_URI', ''),

    /*
     * Headers are only added when this is true.
     * Set CSP_ENABLED=false in .env during debug/scaffolding, then re-enable.
     */
    'enabled' => env('CSP_ENABLED', true),

    /*
     * When Vite hot-reloads in development, the CSP would block the HMR socket.
     * Setting this to true keeps CSP active even during hot reloading.
     * Recommended to leave false so development is not blocked.
     */
    'enabled_while_hot_reloading' => env('CSP_ENABLED_WHILE_HOT_RELOADING', false),

    /*
     * Nonce generator class.
     */
    'nonce_generator' => Spatie\Csp\Nonce\RandomString::class,

    /*
     * Set to false if you rely on unsafe-inline instead of nonces.
     * Since Vue/Tailwind require unsafe-inline for styles (already allowed in
     * AppCspPolicy), nonces for styles are less critical but harmless.
     * Script nonces provide significant XSS protection — keep enabled.
     */
    'nonce_enabled' => env('CSP_NONCE_ENABLED', false),

];
