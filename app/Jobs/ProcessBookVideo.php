<?php

namespace App\Jobs;

use App\Models\Book\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;

class ProcessBookVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $videoPath;
    protected $bookId;
    public $tries = 1;
    public $timeout = 900;


    public function __construct($videoPath, $bookId)
    {
        $this->videoPath = $videoPath;
        $this->bookId = $bookId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Processing started for Book ID: {$this->bookId}");
        Log::info("Original video path: {$this->videoPath}");

        if (empty($this->videoPath) || $this->videoPath === 'undefined') {
            Log::error("Invalid video path received", [
                'videoPath' => $this->videoPath,
                'bookId' => $this->bookId,
            ]);
            return;
        }

        $book = Book::find($this->bookId);
        if (!$book) {
            Log::error(" Book not found for ID: {$this->bookId}");
            return;
        }

        $filename = pathinfo($this->videoPath, PATHINFO_FILENAME);

        if (!$filename || !Storage::disk('public')->exists($this->videoPath)) {
            Log::error("Video file does not exist on disk: {$this->videoPath}");
            return;
        }

        // Prevent processing very small (broken) files
        if (Storage::disk('public')->size($this->videoPath) < 100000) {
            Log::error("File too small / corrupted: {$this->videoPath}");
            return;
        }

        $optimizedPath = "books/video/optimized/{$filename}_{$this->bookId}_streamable.mp4";
        // $thumbnailPath = "course/video/thumbnails/{$filename}.jpg";

        try {
            FFMpeg::fromDisk('public')
                ->open($this->videoPath)
                ->export()
                ->addFilter('-movflags', '+faststart')
                ->addFilter('-preset', 'veryfast')
                ->addFilter('-threads', '2')
                ->toDisk('public')
                ->inFormat(
                    (new X264('libmp3lame'))
                        ->setKiloBitrate(500)
                        ->setAudioKiloBitrate(128)
                )
                ->save($optimizedPath);

            Log::info("Optimized video saved: $optimizedPath");

            // FFMpeg::fromDisk('public')
            //     ->open($this->videoPath)
            //     ->getFrameFromSeconds(2)
            //     ->export()
            //     ->toDisk('public')
            //     ->save($thumbnailPath);


            if (!Storage::disk('public')->exists($optimizedPath)) {
                Log::error("Optimized file not found after processing");
                throw new \RuntimeException('Optimized file missing after processing');
            }

            $book->update([
                'intro_vedio' => $optimizedPath,
                'video_optimized' => true,

                // 'thumbnail_url' => $thumbnailPath,
            ]);

            Log::info("Course DB updated successfully: {$this->bookId}");
        } catch (\Throwable $e) {
            Log::error("Video processing failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
