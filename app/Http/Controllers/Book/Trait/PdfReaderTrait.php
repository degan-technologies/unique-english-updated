<?php

namespace App\Http\Controllers\Book\Trait;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait PdfReaderTrait
{

    public function streamPdf($filename) { 
        $url = "https://unique.et/storage/books/images/{$filename}";

        try {
            $response = Http::withoutVerifying()->get($url);

            if ($response->failed()) {
                return response()->json(['error' => 'File not found or error retrieving file'], 404);
            }

            return response($response->body(), 200, [
                'Content-Type' => 'application/pdf',
                'Access-Control-Allow-Origin' => 'http://127.0.0.1:8000',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error retrieving PDF'], 500);
        }
    }
}