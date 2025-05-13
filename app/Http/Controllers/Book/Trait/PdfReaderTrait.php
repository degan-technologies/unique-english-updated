<?php

namespace App\Http\Controllers\Book\Trait;

use App\Http\Resources\Book\MyBookResource;
use App\Models\Book\Book;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage;

trait PdfReaderTrait {

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