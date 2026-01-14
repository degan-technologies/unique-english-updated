<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Course\CourseContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;

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

        $path = "lesson/video/optimized/$filename";

        $courseContent = CourseContent::query()
            ->where('content_url', $path)
            ->first();

        if (!$courseContent) {
            return response()->json([
                'error' => 'Video not found'
            ], 404);
        }

        // $checkEligibility = Course::checkEligibility($courseContent->course_id);


        // if (!($checkEligibility || $courseContent->user_id === Auth::id())) {
        //     return response()->json(['error' => 'You are not eligible to view this video'], 403);
        // }

        $disk = Storage::disk('private');

        if (!$disk->exists($path)) {
            Log::error("Video not found: $filename");
            return response()->json(['error' => 'Video not found'], 404);
        }

        $filePath = $disk->path($path);
        $fileSize = filesize($filePath);

        $start = 0;
        $end = $fileSize - 1;

        $headers = [
            'Content-Type' => 'video/mp4',
            'Accept-Ranges' => 'bytes',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'no-cache, must-revalidate',
        ];

        if ($request->headers->has('Range')) {
            // Validate Range header syntax
            if (!preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches)) {
                return response()->json(['error' => 'Invalid range request'], 416);
            }

            $start = (int) $matches[1];
            $end = ($matches[2] !== '') ? (int) $matches[2] : $fileSize - 1;

            // Sanitize end position to not exceed file size
            if ($end >= $fileSize) {
                $end = $fileSize - 1;
            }

            $contentLength = ($end - $start) + 1;

            $headers['Content-Range'] = "bytes $start-$end/$fileSize";
            $headers['Content-Length'] = $contentLength;

            Log::debug("Range Request: start=$start, end=$end, fileSize=$fileSize");

            if (ob_get_level()) {
                ob_end_clean(); // Clear output buffering for cleaner streaming
            }

            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                Log::error("Failed to open file: $filePath");
                return response()->json(['error' => 'Failed to open file'], 500);
            }
            fseek($handle, $start);

            return response()->stream(function () use ($handle, $end) {
                $bufferSize = 1024 * 16; // 16KB buffer for better throughput with manageable memory
                while (!feof($handle) && ftell($handle) <= $end) {
                    $currentPos = ftell($handle);
                    $readSize = min($bufferSize, $end - $currentPos + 1);
                    echo fread($handle, $readSize);
                    flush();
                }
                fclose($handle);
            }, 206, $headers);
        }

        // No Range header: serve full file
        $headers['Content-Length'] = $fileSize;

        if (ob_get_level()) {
            ob_end_clean();
        }

        return new StreamedResponse(function () use ($filePath) {
            readfile($filePath);
        }, 200, $headers);
    }
}
