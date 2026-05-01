<?php

namespace App\Jobs;

use App\Models\Course\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    public $tries = 2;
    public $timeout = 1200;

    public function __construct($videoPath, $courseId)
    {
        $this->videoPath = $videoPath;
        $this->courseId = $courseId;
    }

    public function handle(): void
    {
        Log::info("Processing started for course ID: {$this->courseId}");
        Log::info("Original video path: {$this->videoPath}");

        if (empty($this->videoPath) || $this->videoPath === 'undefined') {
            Log::error("Invalid video path received", [
                'videoPath' => $this->videoPath,
                'courseId' => $this->courseId,
            ]);
            return;
        }

        $course = Course::find($this->courseId);
        if (!$course) {
            Log::error("Course not found: {$this->courseId}");
            return;
        }

        if (!Storage::disk('public')->exists($this->videoPath)) {
            Log::error("File not found: {$this->videoPath}");
            return;
        }

        // Prevent processing very small (broken) files
        if (Storage::disk('public')->size($this->videoPath) < 100000) {
            Log::error("File too small / corrupted: {$this->videoPath}");
            return;
        }

        $filename = pathinfo($this->videoPath, PATHINFO_FILENAME);
        $optimizedPath = "course/video/optimized/{$filename}_{$this->courseId}_streamable.mp4";

        try {

            // ✅ SAFE FORMAT (NO CRAZY FLAGS)
            $format = new X264('aac', 'libx264');
            $format->setKiloBitrate(null); // use CRF instead
            $format->setAudioKiloBitrate(128);

            FFMpeg::fromDisk('public')
                ->open($this->videoPath)
                ->export()

                // ✅ Handle broken frames safely
                ->addFilter('-fflags', '+genpts+discardcorrupt')
                ->addFilter('-err_detect', 'ignore_err')

                // ✅ Optimize streaming
                ->addFilter('-movflags', '+faststart')

                // ✅ Performance
                ->addFilter('-preset', 'veryfast')

                // ✅ Quality-based encoding (BETTER than bitrate)
                ->addFilter('-crf', '23')

                ->toDisk('public')
                ->inFormat($format)
                ->save($optimizedPath);

            Log::info("Video encoded successfully");
        } catch (\Throwable $e) {

            Log::warning("Main encoding failed, trying fallback (no audio)...");

            try {
                // 🔁 FALLBACK: remove audio if it's broken
                FFMpeg::fromDisk('public')
                    ->open($this->videoPath)
                    ->export()
                    ->addFilter('-an') // remove audio
                    ->addFilter('-movflags', '+faststart')
                    ->addFilter('-preset', 'veryfast')
                    ->addFilter('-crf', '23')
                    ->toDisk('public')
                    ->inFormat(new X264)
                    ->save($optimizedPath);

                Log::info("Fallback encoding (no audio) succeeded");
            } catch (\Throwable $e2) {
                Log::error("Fallback encoding failed", [
                    'error' => $e2->getMessage()
                ]);
                throw $e2;
            }
        }

        // ✅ Update DB only if file exists
        if (Storage::disk('public')->exists($optimizedPath)) {
            $course->update([
                'intro_video' => $optimizedPath,
                'video_optimized' => true,
            ]);

            Log::info("Course updated successfully: {$this->courseId}");
        } else {
            Log::error("Optimized file not found after processing");
            throw new \RuntimeException('Optimized file missing after processing');
        }
    }
}
