<?php

namespace App\Http\Controllers\Book\Trait;

use App\Http\Resources\Book\MyBookResource;
use App\Models\Book\Book;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage;
use App\Services\CloudFrontService;

trait PdfReaderTrait {

    public function streamPdf($filename)
    {
        $disk = Storage::disk('s3');
        $path = "books/pdfFiles/{$filename}";

        if (!$disk->exists($path)) {
            $path = "books/images/{$filename}";
        }

        if (!$disk->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $signedUrl = CloudFrontService::signedUrl($path, now()->addMinutes(30));

        return redirect()->away($signedUrl);
    }

    public function myBooks() { 
        $user = Auth::user();

        $books = Book::query()
            ->whereHas('transactions', function($query) use ($user) {
                $query->where('status', TRANSACTION_SUCCESS)
                    ->where('customer_id', $user->id);
            })
            ->get();
        
        if(!$books) {
            return response()->json([
                'message' => $this->langService->getLang('book_not_found'),
            ], 404);
        }

        return response()->json([
            'data' => MyBookResource::collection($books)
        ]);
    }
    
}