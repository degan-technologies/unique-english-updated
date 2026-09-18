<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use App\Models\Book\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\CloudFrontService;


class BookVideoController extends Controller
{
    /**
     * Stream (redirect via pre-signed URL) the book intro video.
     *
     * If HLS optimization is complete, redirect to the master.m3u8 playlist.
     * Otherwise, redirect to a pre-signed URL for the original video.
     * S3/CloudFront natively handles byte-range (HTTP 206) requests, so seeking works.
     */
    public function stream(Request $request, $filename)
    {
        $disk = Storage::disk('s3');
        $path = "books/video/original/{$filename}";

        /*
        |------------------------------------------------------------------
        | Check if this book has HLS ready and prefer it
        |------------------------------------------------------------------
        */
        $book = Book::where('intro_vedio', $path)
            ->orWhere('intro_vedio', '/' . $path)
            ->orWhere('intro_vedio', 'like', '%' . $filename)
            ->first();

        if ($book && $book->hls_status === 'ready' && $book->hls_path) {
            if ($disk->exists($book->hls_path)) {
                Log::info('Redirecting book video to HLS via CDN', [
                    'book_id'  => $book->id,
                    'hls_path' => $book->hls_path,
                ]);

                if (CloudFrontService::isConfigured()) {
                    $redirect = redirect()->away(CloudFrontService::hlsUrl($book->hls_path));
                    return CloudFrontService::attachCookies($redirect, CloudFrontService::hlsCookiePrefix($book->hls_path));
                }

                $signedUrl = CloudFrontService::signedUrl($book->hls_path, now()->addMinutes(60));
                return redirect()->away($signedUrl);
            }
        }

        /*
        |------------------------------------------------------------------
        | Fallback: pre-signed URL for the original video
        |------------------------------------------------------------------
        */
        if (!$disk->exists($path)) {
            Log::error("Book video not found on S3: {$filename}");
            return response()->json(['error' => 'Video not found'], 404);
        }

        $signedUrl = CloudFrontService::signedUrl($path, now()->addMinutes(60));

        Log::info("Redirecting book video to CDN signed URL", ['path' => $path]);

        return redirect()->away($signedUrl);
    }

    /**
     * Stream (redirect via pre-signed URL) a course lesson PDF.
     */
    public function contentPdfStream(Request $request, $filename)
    {
        $disk = Storage::disk('s3');
        $path = "course/pdf/{$filename}";

        // Also check old path for backwards compatibility
        if (!$disk->exists($path)) {
            $path = "course/{$filename}";
        }

        if (!$disk->exists($path)) {
            Log::error("PDF not found on S3: {$filename}");
            return response()->json(['error' => 'PDF not found'], 404);
        }

        $signedUrl = CloudFrontService::signedUrl($path, now()->addMinutes(30));

        Log::info("Redirecting course PDF to CDN signed URL", ['path' => $path]);

        return redirect()->away($signedUrl);
    }

    /**
     * Stream (redirect via pre-signed URL) a book PDF file.
     */
    public function bookPdfStream(Request $request, $filename)
    {
        $disk = Storage::disk('s3');
        $path = "books/pdfFiles/{$filename}";

        if (!$disk->exists($path)) {
            Log::error("Book PDF not found on S3: {$filename}");
            return response()->json(['error' => 'PDF not found'], 404);
        }

        $signedUrl = CloudFrontService::signedUrl($path, now()->addMinutes(30));

        Log::info("Redirecting book PDF to CDN signed URL", ['path' => $path]);

        return redirect()->away($signedUrl);
    }
}
