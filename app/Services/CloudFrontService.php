<?php

namespace App\Services;

use Aws\CloudFront\CloudFrontClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * CloudFrontService
 *
 * Handles all CloudFront signed URL and signed cookie generation.
 *
 * Signed URLs  → single private file (thumbnail, PDF, profile image, MP4)
 * Signed Cookies → HLS video paths (covers master.m3u8 + all variant
 *                  playlists + all .ts segments with a single cookie set)
 *
 * Configuration (via .env):
 *   CLOUDFRONT_URL              = https://d3frugy4uvyrp6.cloudfront.net
 *   CLOUDFRONT_KEY_PAIR_ID      = YOUR_KEY_PAIR_ID
 *   CLOUDFRONT_PRIVATE_KEY_PATH = /absolute/path/to/cloudfront-private-key.pem
 */
class CloudFrontService
{
    private static ?CloudFrontClient $client = null;

    /**
     * Build (and cache) the CloudFront SDK client.
     */
    private static function client(): CloudFrontClient
    {
        if (self::$client === null) {
            self::$client = new CloudFrontClient([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region', 'eu-north-1'),
            ]);
        }

        return self::$client;
    }

    /**
     * Return the CloudFront base URL (no trailing slash).
     */
    public static function baseUrl(): string
    {
        return rtrim(config('services.cloudfront.url') ?: (env('CLOUDFRONT_URL') ?: ''), '/');
    }

    /**
     * Return the CloudFront Key Pair ID.
     */
    public static function keyPairId(): string
    {
        return config('services.cloudfront.key_pair_id') ?: (env('CLOUDFRONT_KEY_PAIR_ID') ?: '');
    }

    /**
     * Return the CloudFront private key path.
     */
    public static function privateKeyPath(): string
    {
        return config('services.cloudfront.private_key_path') ?: (env('CLOUDFRONT_PRIVATE_KEY_PATH') ?: '');
    }

    /**
     * Is CloudFront properly configured?
     * Falls back gracefully to S3 presigned URLs when CloudFront env vars are missing.
     */
    public static function isConfigured(): bool
    {
        $keyPath = self::privateKeyPath();

        return !empty(self::baseUrl())
            && !empty(self::keyPairId())
            && !empty($keyPath)
            && file_exists($keyPath);
    }

    /**
     * Generate a CloudFront Signed URL for a single private object.
     *
     * Use this for: thumbnails, cover images, profile photos, PDF books,
     *               single MP4 files (fallback before HLS is ready).
     *
     * @param  string         $s3Key    The S3 object key, e.g. "course/images/foo.jpg"
     * @param  \DateTimeInterface|null $expiry  Defaults to +30 minutes
     * @return string
     */
    public static function signedUrl(string $s3Key, ?\DateTimeInterface $expiry = null): string
    {
        if (!self::isConfigured()) {
            if (!empty(self::baseUrl())) {
                return self::baseUrl() . '/' . ltrim($s3Key, '/');
            }
            try {
                return Storage::disk('s3')->temporaryUrl($s3Key, $expiry ?? now()->addMinutes(30));
            } catch (\Throwable $e) {
                return Storage::disk('s3')->url($s3Key);
            }
        }

        $expiry = $expiry ?? now()->addMinutes(30);

        try {
            $url = self::client()->getSignedUrl([
                'url'         => self::baseUrl() . '/' . ltrim($s3Key, '/'),
                'expires'     => $expiry->getTimestamp(),
                'key_pair_id' => self::keyPairId(),
                'private_key' => self::privateKeyPath(),
            ]);

            return $url;
        } catch (\Throwable $e) {
            Log::error('CloudFront signedUrl failed', [
                'key'   => $s3Key,
                'error' => $e->getMessage(),
            ]);

            // Graceful fallback
            if (!empty(self::baseUrl())) {
                return self::baseUrl() . '/' . ltrim($s3Key, '/');
            }
            try {
                return Storage::disk('s3')->temporaryUrl($s3Key, $expiry);
            } catch (\Throwable $ex) {
                return Storage::disk('s3')->url($s3Key);
            }
        }
    }

    /**
     * Generate a plain (unsigned) CloudFront URL.
     *
     * Use this for: public images (hero banner, logo, background) that do
     * not require authorization — they should be cached at the edge freely.
     *
     * @param  string $s3Key
     * @return string
     */
    public static function publicUrl(string $s3Key): string
    {
        if (!empty(self::baseUrl())) {
            return self::baseUrl() . '/' . ltrim($s3Key, '/');
        }
        return Storage::disk('s3')->url($s3Key);
    }

    /**
     * Generate a set of CloudFront Signed Cookies for an HLS video path prefix.
     *
     * Use this for HLS streaming. One cookie set authorizes the browser to
     * fetch:  master.m3u8, 360p/index.m3u8, 360p/segment_000.ts, etc.
     * The browser sends the cookies automatically with every sub-request.
     *
     * @param  string              $hlsPathPrefix   e.g. "videos/courses/42/hls/*"
     * @param  \DateTimeInterface|null $expiry
     * @return array{
     *   'CloudFront-Policy': string,
     *   'CloudFront-Signature': string,
     *   'CloudFront-Key-Pair-Id': string,
     *   'domain': string,
     *   'expires': int
     * }
     */
    public static function signedCookies(string $hlsPathPrefix, ?\DateTimeInterface $expiry = null): array
    {
        if (!self::isConfigured()) {
            return [];
        }

        $expiry = $expiry ?? now()->addMinutes(120);

        // The wildcard resource URL that the cookie will authorize
        $resourceUrl = self::baseUrl() . '/' . ltrim($hlsPathPrefix, '/');

        try {
            $cookies = self::client()->getSignedCookie([
                'url'         => $resourceUrl,
                'expires'     => $expiry->getTimestamp(),
                'key_pair_id' => self::keyPairId(),
                'private_key' => self::privateKeyPath(),
            ]);

            // Extract the CloudFront domain for the Set-Cookie Domain attribute
            $parsed = parse_url(self::baseUrl());
            $domain = $parsed['host'] ?? '';

            return array_merge($cookies, [
                'domain'  => $domain,
                'expires' => $expiry->getTimestamp(),
            ]);
        } catch (\Throwable $e) {
            Log::error('CloudFront signedCookies failed', [
                'prefix' => $hlsPathPrefix,
                'error'  => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Apply CloudFront signed cookies to a Laravel HTTP response.
     *
     * Call this in a controller/middleware before returning the API response
     * when delivering HLS content to an authorized student.
     *
     * @param  \Illuminate\Http\Response|\Illuminate\Http\JsonResponse $response
     * @param  string $hlsPathPrefix  e.g. "videos/courses/42/hls/*"
     * @param  \DateTimeInterface|null $expiry
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public static function attachCookies($response, string $hlsPathPrefix, ?\DateTimeInterface $expiry = null)
    {
        $cookies = self::signedCookies($hlsPathPrefix, $expiry);

        if (empty($cookies)) {
            return $response;
        }

        $expiry    = $expiry ?? now()->addMinutes(120);
        $domain    = $cookies['domain'] ?? '';
        $expiresAt = $cookies['expires'] ?? $expiry->getTimestamp();

        foreach (['CloudFront-Policy', 'CloudFront-Signature', 'CloudFront-Key-Pair-Id'] as $name) {
            if (!isset($cookies[$name])) {
                continue;
            }

            $response->headers->setCookie(
                cookie(
                    name:     $name,
                    value:    $cookies[$name],
                    minutes:  (int) ceil(($expiresAt - time()) / 60),
                    path:     '/',
                    domain:   $domain,
                    secure:   true,
                    httpOnly: false,  // Must be false — HLS.js needs to send them
                    sameSite: 'None', // Cross-origin CDN requests require SameSite=None
                )
            );
        }

        return $response;
    }

    /**
     * Build the plain (unsigned) CloudFront URL for an HLS master playlist.
     * Pair this with signedCookies() — the URL itself is not signed but the
     * browser cookie authorizes access.
     *
     * @param  string $hlsMasterPath  e.g. "videos/courses/42/hls/master.m3u8"
     * @return string
     */
    public static function hlsUrl(string $hlsMasterPath): string
    {
        return self::baseUrl() . '/' . ltrim($hlsMasterPath, '/');
    }

    /**
     * Derive the wildcard cookie path prefix from an HLS master playlist path.
     *
     * Input:  "videos/courses/42/hls/master.m3u8"
     * Output: "videos/courses/42/hls/*"
     *
     * @param  string $hlsMasterPath
     * @return string
     */
    public static function hlsCookiePrefix(string $hlsMasterPath): string
    {
        $dir = dirname($hlsMasterPath);
        return $dir . '/*';
    }
}
