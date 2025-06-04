<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class BookVideoController extends Controller {
    public function stream(Request $request, $filename) {
        $disk = Storage::disk('public');
        $path = "books/videos/$filename";
        $filePath = $disk->path($path);

        if (!$disk->exists($path)) {
            Log::error("Video not found: $filename");
            return response()->json(['error' => 'Video not found'], 404);
        }

        $fileSize = filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;

        // Base headers including no-cache
        $headers = [
            'Content-Type' => 'video/mp4',
            'Accept-Ranges' => 'bytes',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'no-cache, must-revalidate',
        ];

        // Check for Range header
        if ($request->headers->has('Range')) {
            if (!preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches)) {
                return response()->json(['error' => 'Invalid range request'], 416);
            }

            $start = intval($matches[1]);
            $end = isset($matches[2]) && $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;
            if ($end >= $fileSize) {
                $end = $fileSize - 1;
            }

            $headers['Content-Range'] = "bytes $start-$end/$fileSize";
            $headers['Content-Length'] = ($end - $start) + 1;

            Log::debug("Range Request: start=$start, end=$end, fileSize=$fileSize");

            if (ob_get_level()) {
                ob_end_clean();
            }

            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                Log::error("Failed to open file: $filePath");
                return response()->json(['error' => 'Failed to open file'], 500);
            }
            fseek($handle, $start);

            return response()->stream(function () use ($handle, $end) {
                $bufferSize = 1024 * 8; // 8KB chunks
                while (!feof($handle) && ftell($handle) <= $end) {
                    $currentPos = ftell($handle);
                    $readSize = $bufferSize;
                    if ($currentPos + $readSize > $end) {
                        $readSize = $end - $currentPos + 1;
                    }
                    echo fread($handle, $readSize);
                    flush();
                }
                fclose($handle);
            }, 206, $headers);
        }

        if (ob_get_level()) {
            ob_end_clean();
        }
        return new StreamedResponse(function () use ($filePath) {
            readfile($filePath);
        }, 200, $headers);
    }

     public function contentPdfStream(Request $request, $filename) {
        $disk = Storage::disk('public');
        $path = "course/$filename";

        if (!$disk->exists($path)) {
            Log::error("PDF not found: $filename");
            return response()->json(['error' => 'PDF not found'], 404);
        }

        $filePath = $disk->path($path);
        $fileSize = filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;

        $headers = [
            'Content-Type' => 'application/pdf',
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Access-Control-Allow-Origin' => '*',
        ];

        if ($request->headers->has('Range')) {
            if (!preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches)) {
                return response()->json(['error' => 'Invalid range'], 416);
            }

            $start = intval($matches[1]);
            $end = isset($matches[2]) && $matches[2] !== '' ? intval($matches[2]) : $end;
            $end = min($end, $fileSize - 1);

            $headers['Content-Range'] = "bytes $start-$end/$fileSize";
            $headers['Content-Length'] = ($end - $start) + 1;

            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                Log::error("Failed to open PDF: $filename");
                return response()->json(['error' => 'Failed to open file'], 500);
            }

            fseek($handle, $start);

            return response()->stream(function () use ($handle, $end) {
                $bufferSize = 8192;
                while (!feof($handle) && ftell($handle) <= $end) {
                    $readSize = min($bufferSize, $end - ftell($handle) + 1);
                    echo fread($handle, $readSize);
                    flush();
                }
                fclose($handle);
            }, 206, $headers);
        }

        return new StreamedResponse(function () use ($filePath) {
            readfile($filePath);
        }, 200, $headers);
    }
}
