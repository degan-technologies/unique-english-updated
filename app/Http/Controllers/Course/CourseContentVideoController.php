<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CourseContentVideoController extends Controller
{
    public function stream(Request $request, $filename)
    {
        $disk = Storage::disk('public');
        $path = "course/$filename";

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

