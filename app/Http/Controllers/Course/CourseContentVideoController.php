<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course\CourseContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;
use App\Services\CloudFrontService;

class CourseContentVideoController extends Controller
{
    protected $user;

    public function __construct(Request $request)
    {
        // Authenticate user using Passport token_id
        if ($request->has('token_id')) {
            $this->authenticateUser($request->token_id);

            // Optional: Verify token expiration from request
            if ($request->has('expires') && time() > $request->expires) {
                abort(401, 'Token has expired');
            }
        }
    }

    protected function authenticateUser($tokenId)
    {
        // Find the Passport token
        $token = Token::find($tokenId);

        if (!$token) {
            abort(401, 'Unauthorized - Invalid token');
        }

        // Check if token is revoked
        if ($token->revoked) {
            abort(401, 'Unauthorized - Token revoked');
        }

        // Get the user associated with the token
        $this->user = $token->user;

        if (!$this->user) {
            abort(401, 'Unauthorized - User not found');
        }

        // Set the authenticated user globally
        auth()->setUser($this->user);
    }

    public function stream(Request $request, $filename)
    {
        // Verify authentication
        if (!$this->user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $path = "lesson/video/original/{$filename}";

        $courseContent = CourseContent::query()
            ->where('content_url', $path)
            ->orWhere('content_url', '/' . $path)
            ->orWhere('content_url', 'like', '%' . $filename)
            ->first();

        if (!$courseContent) {
            return response()->json([
                'error' => 'Video not found'
            ], 404);
        }

        $disk = Storage::disk('s3');

        /*
        |------------------------------------------------------------------
        | If HLS optimization is ready, redirect to the master playlist URL.
        | The client (HLS.js / Video.js) will handle adaptive bitrate
        | streaming directly from CloudFront CDN.
        |------------------------------------------------------------------
        */
        if ($courseContent->hls_status === 'ready' && $courseContent->hls_path) {
            if ($disk->exists($courseContent->hls_path)) {
                Log::info('Redirecting to HLS via CDN', [
                    'coursecontent_id' => $courseContent->id,
                    'hls_path' => $courseContent->hls_path,
                ]);

                if (CloudFrontService::isConfigured()) {
                    $redirect = redirect()->away(CloudFrontService::hlsUrl($courseContent->hls_path));
                    return CloudFrontService::attachCookies($redirect, CloudFrontService::hlsCookiePrefix($courseContent->hls_path));
                }

                $signedUrl = CloudFrontService::signedUrl($courseContent->hls_path, now()->addMinutes(60));
                return redirect()->away($signedUrl);
            }
        }

        /*
        |------------------------------------------------------------------
        | Fallback: redirect to a pre-signed URL for the original video.
        | S3/CloudFront handles byte-range requests natively, so video seeking works.
        |------------------------------------------------------------------
        */
        if (!$disk->exists($path)) {
            Log::error("Video not found on S3: {$path}");
            return response()->json(['error' => 'Video not found'], 404);
        }

        $signedUrl = CloudFrontService::signedUrl($path, now()->addMinutes(60));

        Log::info('Redirecting to original video via CDN', [
            'coursecontent_id' => $courseContent->id,
            'path' => $path,
        ]);

        return redirect()->away($signedUrl);
    }
}
