<?php

namespace App\Jobs;

use App\Models\Course\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264; 

class ProcessCourseVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $videoPath;
    protected $courseId;
    public $tries = 1;
    public $timeout = 900;


    public function __construct($videoPath, $courseId)
    {
        $this->videoPath = $videoPath;
        $this->courseId = $courseId;
    }

    public function handle(): void
    {
        Log::info("Processing started for course ID: {$this->courseId}");
        Log::info("Original video path: {$this->videoPath}");

        $course = Course::find($this->courseId);
        if (!$course) {
            Log::error(" Course not found for ID: {$this->courseId}");
            return;
        }

        $filename = pathinfo($this->videoPath, PATHINFO_FILENAME);
        $fullPath = storage_path('app/public/' . $this->videoPath);

        if (!$filename || !file_exists($fullPath)) {
            log::error("Video file does not exist: {$fullPath}");
            return;
        }

        $optimizedPath = "course/video/optimized/{$filename}_streamable.mp4";
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


            $course->update([
                'intro_video' => $optimizedPath,
                'video_optimized' => true,

                // 'thumbnail_url' => $thumbnailPath,
            ]);

            Log::info("Course DB updated successfully: {$this->courseId}");
        } catch (\Throwable $e) {
            Log::error("Video processing failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
