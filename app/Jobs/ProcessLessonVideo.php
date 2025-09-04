<?php

namespace App\Jobs;

use App\Models\Course\CourseContent;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;

class ProcessLessonVideo implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $videoPath;
    protected $lessonId;
    public $tries = 1;
    public $timeout = 900;

    public function __construct($videoPath, $lessonId)
    {
        $this->videoPath = $videoPath;
        $this->lessonId = $lessonId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Processing started for lesson ID: {$this->lessonId}");
        Log::info("Original video path: {$this->videoPath}");

        $lesson = CourseContent::find($this->lessonId);

        if (!$lesson) {
            Log::error("Lesson not found for ID: {$this->lessonId}");
            return;
        }

        $filename = pathinfo($this->videoPath, PATHINFO_FILENAME);

        if (!$filename || !Storage::disk('private')->exists($this->videoPath)) {
            Log::error("Video file does not exist on disk: {$this->videoPath}");
            return;
        }

        $optimizedPath = "lesson/video/optimized/{$filename}_streamable.mp4";
        $thumbnailPath = "lesson/video/thumbnails/{$filename}.jpg";

        try {
            FFMpeg::fromDisk('private')
                ->open($this->videoPath)
                ->export()
                ->addFilter('-movflags', '+faststart')
                ->addFilter('-preset', 'veryfast')
                ->addFilter('-threads', '2')
                ->toDisk('private')
                ->inFormat(
                    (new X264('libmp3lame'))
                        ->setKiloBitrate(500)
                        ->setAudioKiloBitrate(128)
                )
                ->save($optimizedPath);

            Log::info("Optimized video saved: $optimizedPath");

            FFMpeg::fromDisk('private')
                ->open($this->videoPath)
                ->getFrameFromSeconds(2)
                ->export()
                ->toDisk('private')
                ->save($thumbnailPath);

            Log::info("Thumbnail created: $thumbnailPath");

            $lesson->update([
                'content_url' => $optimizedPath,
                'video_optimized' => true,
                'thumbnail_url' => $thumbnailPath,
            ]);

            Log::info("Lesson DB updated successfully: {$this->lessonId}");
        } catch (\Throwable $e) {
            Log::error("Video processing failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
