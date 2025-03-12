<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseContentVideoController extends Controller
{
    public function stream(Request $request, $filename)
    {
        $disk = Storage::disk('public');
        $originalPath = "course/$filename";
        $quality = $request->query('quality', 'auto');
        $availableQualities = ['1080p', '720p', '480p'];
         // Predefined qualities for transcoding

            // Check if the requested quality is valid, if not fallback to 'auto'
        if (!in_array($quality, array_merge($availableQualities, ['auto']))) {
            return response()->json(['error' => 'Invalid quality parameter'], 400);
        }

        // Handle pre-encoded video versions (HLS playlists)
        if ($quality !== 'auto' && in_array($quality, $availableQualities)) {
            $encodedPath = "course/$quality/hls/$filename.m3u8";
            if ($disk->exists($encodedPath)) {
                return $this->streamHLS($encodedPath);
            }
        }

        // Fallback to original file if quality is auto or file is not pre-encoded
        if (!$disk->exists($originalPath)) {
            Log::error("Video not found: $filename");
            return response()->json(['error' => 'Video not found'], 404);
        }

        $filePath = $disk->path($originalPath);

        // If FFmpeg is unavailable, serve the original file
        if (!shell_exec("command -v ffmpeg")) {
            return $this->streamFile($request, $filePath);
        }

        // Otherwise, transcode live and generate HLS for adaptive bitrate streaming
        return $this->generateHLS($filePath, $filename, $quality);
    }

    protected function generateHLS($filePath, $filename, $quality)
    {
        // Set paths for the HLS files and playlist
        $hlsDirectory = storage_path('app/public/course/hls/' . pathinfo($filename, PATHINFO_FILENAME));
        if (!file_exists($hlsDirectory)) {
            mkdir($hlsDirectory, 0777, true); // Create directory if it doesn't exist
        }

        // Define available qualities
        $qualities = [
            '1080p' => '1920x1080',
            '720p' => '1280x720',
            '480p' => '854x480'
        ];

        // Check if the requested quality is available, default to auto
        $quality = $qualities[$quality] ?? 'auto';
        $cmd = "ffmpeg -i " . escapeshellarg($filePath) . " -preset fast -g 60 -sc_threshold 0 " .
               "-map 0 -map 0 -map 0 -f hls -hls_time 10 -hls_list_size 0 -hls_segment_filename " .
               escapeshellarg($hlsDirectory . "/%03d.ts") . " " . escapeshellarg($hlsDirectory . "/playlist.m3u8");

        shell_exec($cmd); // Execute the command

        // Return the generated HLS playlist file for the requested video
        $playlistPath = $hlsDirectory . '/playlist.m3u8';
        if (!file_exists($playlistPath)) {
            Log::error("HLS playlist generation failed for: $filename");
            return response()->json(['error' => 'HLS generation failed'], 500);
        }

        return $this->streamHLS($playlistPath);
    }

    protected function streamHLS($playlistPath)
    {
        $fileSize = filesize($playlistPath);
        $headers = [
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Accept-Ranges' => 'bytes',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'no-cache, must-revalidate',
        ];

        return response()->stream(function () use ($playlistPath) {
            readfile($playlistPath);
        }, 200, $headers);
    }

    protected function streamFile(Request $request, $filePath)
    {
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
            // Handle partial content request (seeking)
            if (!preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches)) {
                return response()->json(['error' => 'Invalid range request'], 416);
            }
            $start = intval($matches[1]);
            $end = isset($matches[2]) && $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;
            $end = min($end, $fileSize - 1);

            $headers['Content-Range'] = "bytes $start-$end/$fileSize";
            $headers['Content-Length'] = ($end - $start) + 1;

            if (ob_get_level()) ob_end_clean();
            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                Log::error("Failed to open file: $filePath");
                return response()->json(['error' => 'Failed to open file'], 500);
            }
            fseek($handle, $start);
            return response()->stream(function () use ($handle, $end) {
                while (!feof($handle) && ftell($handle) <= $end) {
                    echo fread($handle, 8192);
                    flush();
                }
                fclose($handle);
            }, 206, $headers);
        }

        if (ob_get_level()) ob_end_clean();
        return new StreamedResponse(function () use ($filePath) {
            readfile($filePath);
        }, 200, $headers);
    }
}
