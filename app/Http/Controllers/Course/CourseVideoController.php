<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\CloudFrontService;

class CourseVideoController extends Controller
{
    public function stream(Request $request, $filename)
    {
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)->withHeaders([
                'Access-Control-Allow-Origin'  => $request->header('Origin') ?? '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Range, Content-Type, Origin, Accept',
                'Access-Control-Max-Age'       => '86400',
            ]);
        }

        $disk = Storage::disk('s3');
        $relativePath = "course/video/original/{$filename}";

        /*
        |------------------------------------------------------------------
        | Check if we can find a course with HLS ready for this video path
        | and redirect to the HLS master playlist if available.
        |------------------------------------------------------------------
        */
        $course = Course::where('intro_video', $relativePath)
            ->orWhere('intro_video', '/' . $relativePath)
            ->orWhere('intro_video', 'like', '%' . $filename)
            ->first();

        if ($course && $course->hls_status === 'ready' && $course->hls_path) {
            if ($disk->exists($course->hls_path)) {
                Log::info('Redirecting course video to HLS', [
                    'course_id' => $course->id,
                    'hls_path'  => $course->hls_path,
                ]);

                if (CloudFrontService::isConfigured()) {
                    $redirect = redirect()->away(CloudFrontService::hlsUrl($course->hls_path));
                    return CloudFrontService::attachCookies($redirect, CloudFrontService::hlsCookiePrefix($course->hls_path));
                }

                $signedUrl = CloudFrontService::signedUrl($course->hls_path, now()->addMinutes(60));
                return redirect()->away($signedUrl);
            }
        }

        /*
        |------------------------------------------------------------------
        | Fallback: redirect to a pre-signed URL for the original video.
        | S3/CloudFront natively supports byte-range requests (HTTP 206), so seeking
        | and adaptive playback work correctly without proxying the file.
        |------------------------------------------------------------------
        */
        if (!$disk->exists($relativePath)) {
            Log::error("Course video not found on S3: {$relativePath}");
            return response()->json(['error' => 'Video not found'], 404);
        }

        $signedUrl = CloudFrontService::signedUrl($relativePath, now()->addMinutes(60));

        Log::info("Redirecting course intro video to CDN signed URL", [
            'path' => $relativePath,
        ]);

        return redirect()->away($signedUrl);
    }
}