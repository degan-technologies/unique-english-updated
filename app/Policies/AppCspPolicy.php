<?php

namespace App\Policies;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

/**
 * AppCspPreset
 *
 * Production-ready Content Security Policy preset for a Vue 3 + Laravel app
 * that embeds YouTube iframes and calls self-hosted API endpoints.
 *
 * Implements Spatie\Csp\Preset (spatie/laravel-csp v3).
 *
 * Sources allowed:
 *  - 'self'   : all first-party assets
 *  - YouTube  : iframe embeds + thumbnail images
 *  - Google Fonts / CDN fonts
 *
 * unsafe-inline on style-src is required because:
 *  1. Vue 3's <style> blocks inject inline <style> tags
 *  2. Tailwind JIT emits inline utility classes
 * Mitigation: scripts remain free of unsafe-inline and unsafe-eval.
 */
class AppCspPolicy implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            // -------------------------------------------------------
            // Default: only first-party resources
            // -------------------------------------------------------
            ->add(Directive::DEFAULT, Keyword::SELF)

            // -------------------------------------------------------
            // Scripts: self + SHA-256 hash for the loading-screen inline
            // script in welcome.blade.php.
            // The hash was extracted from the browser CSP violation report.
            // Do NOT add unsafe-inline — this hash is narrowly scoped.
            // -------------------------------------------------------
            ->add(Directive::SCRIPT, [
                Keyword::SELF,
                "'sha256-MIkyB+mDKmLbxcbydHeB/deelDhO9+z583PGeV0WKnE='",
            ])

            // -------------------------------------------------------
            // Styles: self + unsafe-inline required by Vue/Tailwind
            // -------------------------------------------------------
            ->add(Directive::STYLE, [
                Keyword::SELF,
                Keyword::UNSAFE_INLINE,           // Required for Vue scoped styles & Tailwind JIT
                'https://fonts.googleapis.com',   // Google Fonts CSS
                'https://cdnjs.cloudflare.com',   // Font Awesome (if loaded from CDN)
            ])

            // -------------------------------------------------------
            // Fonts
            // -------------------------------------------------------
            ->add(Directive::FONT, [
                Keyword::SELF,
                'data:',                          // Base64 embedded fonts (some icon sets)
                'https://fonts.gstatic.com',
                'https://cdnjs.cloudflare.com',
            ])

            // -------------------------------------------------------
            // Images: self + data-URIs + YouTube thumbnails
            // -------------------------------------------------------
            ->add(Directive::IMG, [
                Keyword::SELF,
                'data:',                          // Base64 preview images / avatars
                'blob:',                          // File picker object URLs
                'https://i.ytimg.com',            // YouTube video thumbnails
                'https://img.youtube.com',        // YouTube high-res thumbnails
                'https://yt3.ggpht.com',          // YouTube channel avatars
                'https://lh3.googleusercontent.com', // Google social avatar
            ])

            // -------------------------------------------------------
            // Media (audio / video): self + blob for HTML5 video
            // -------------------------------------------------------
            ->add(Directive::MEDIA, [
                Keyword::SELF,
                'blob:',
            ])

            // -------------------------------------------------------
            // Iframes: YouTube embeds only
            // -------------------------------------------------------
            ->add(Directive::FRAME, [
                'https://www.youtube.com',
                'https://www.youtube-nocookie.com', // Privacy-enhanced YouTube
            ])

            // -------------------------------------------------------
            // Connections (fetch / XHR / WebSocket)
            // -------------------------------------------------------
            ->add(Directive::CONNECT, [
                Keyword::SELF,
                'wss:',                               // WebSockets (Laravel Echo)
                'https://www.googleapis.com',         // YouTube Data API v3
                'https://api.rss2json.com',           // RSS → JSON proxy for YouTube feed
            ])

            // -------------------------------------------------------
            // frame-ancestors: prevent clickjacking via iframing
            // -------------------------------------------------------
            ->add(Directive::FRAME_ANCESTORS, Keyword::SELF)

            // -------------------------------------------------------
            // object / base-uri: locked down completely
            // -------------------------------------------------------
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::BASE, Keyword::SELF);
    }
}
