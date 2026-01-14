<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CourseVideoController extends Controller
{
    public function stream(Request $request, $filename)
    {
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)->withHeaders([
                'Access-Control-Allow-Origin' => $request->header('Origin') ?? '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Range, Content-Type, Origin, Accept',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        $disk = Storage::disk('public');
        $relativePath = "course/video/optimized/$filename";

        if (!$disk->exists($relativePath)) {
            Log::error("Video not found: $filename");
            return response()->json(['error' => 'Video not found'], 404);
        }

        $filePath = $disk->path($relativePath);
        $fileSize = filesize($filePath);

        $start = 0;
        $defaultChunkSize = 2 * 1024 * 1024; // 2MB
        $end = $fileSize - 1;

        $headers = [
            'Content-Type' => 'video/mp4',
            'Accept-Ranges' => 'bytes',
            'Access-Control-Allow-Origin' => $request->header('Origin') ?? '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Range, Content-Type, Origin, Accept',
            'Cache-Control' => 'no-cache, must-revalidate',
        ];

        $status = 200;

        if ($request->headers->has('Range')) {
            if (preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches)) {
                $start = intval($matches[1]);
                $end = isset($matches[2]) && $matches[2] !== '' ? intval($matches[2]) : min($fileSize - 1, $start + $defaultChunkSize - 1);
                $status = 206;
            }
        }

        $end = min($fileSize - 1, $end);
        $length = ($end - $start) + 1;

        $headers['Content-Range'] = "bytes $start-$end/$fileSize";
        $headers['Content-Length'] = $length;

        Log::info("Streaming video range: $start-$end / $fileSize");

        if (ob_get_level()) {
            ob_end_clean();
        }

        $handle = fopen($filePath, 'rb');

        if (!$handle) {
            Log::error("Failed to open file: $filePath");
            return response()->json(['error' => 'Failed to open file'], 500);
        }

        fseek($handle, $start);

        return response()->stream(function () use ($handle, $start, $end) {
            $bufferSize = 1024 * 16; // 16KB
            $position = ftell($handle);

            while (!feof($handle) && $position <= $end) {
                $bytesToRead = min($bufferSize, $end - $position + 1);
                echo fread($handle, $bytesToRead);
                flush();
                $position = ftell($handle);
            }

            fclose($handle);
        }, $status, $headers);
    }
}
