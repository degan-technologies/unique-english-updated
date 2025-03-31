<?php

namespace App\Http\Controllers\Book\Trait;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait PdfReaderTrait
{

    public function streamPdf($filename)
    { 
        $disk = Storage::disk('public');
        $path = "books/images/{$filename}";
         
        if (!$disk->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
         
        $stream = $disk->readStream($path);
        if (!$stream) {
            return response()->json(['error' => 'Error opening file stream'], 500);
        }
         
        return response()->stream(function () use ($stream) { 
            
            fpassthru($stream);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Access-Control-Allow-Origin' => 'http://127.0.0.1:8000',
        ]);
    }
    
}