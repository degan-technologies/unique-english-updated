<?php

namespace App\Jobs;

use App\Models\Course\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

class ProcessCourseVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 14400;
    public $tries = 3;

    public function __construct(
        protected string $videoPath,
        protected int $courseId
    ) {}

    public function handle(): void
    {
        Log::info('ProcessCourseVideo started', [
            'courseId' => $this->courseId,
            'videoPath' => $this->videoPath
        ]);
        $course = Course::find($this->courseId);

        if (!$course) {
            Log::error("Course not found", ['id' => $this->courseId]);
            return;
        }

        $course->update(['hls_status' => 'processing']);

        $jobId = uniqid('hls_', true);
        $baseDir = storage_path("app/temp/{$jobId}");

        $inputFile = "{$baseDir}/input.mp4";
        $outputDir = "{$baseDir}/output";

        $this->makeDir($baseDir);
        $this->makeDir($outputDir);

        try {

            /*
            |------------------------------------------
            | 1. STREAM SAFE S3 DOWNLOAD
            |------------------------------------------
            */
            Log::info("Downloading video", ['course_id' => $course->id]);

            $stream = Storage::disk('s3')->readStream($this->videoPath);
            Log::info('After readStream');

            if (!$stream) {
                throw new \Exception("Cannot read S3 file: {$this->videoPath}");
            }

            $out = fopen($inputFile, 'w');

            while (!feof($stream)) {
                fwrite($out, fread($stream, 1024 * 1024));
            }

            fclose($stream);
            fclose($out);

            /*
            |------------------------------------------
            | 2. NETFLIX ABR LADDER
            |------------------------------------------
            */
            $renditions = [
                ['name' => '360p',  'scale' => '640:360',   'bitrate' => '800k'],
                ['name' => '480p',  'scale' => '854:480',   'bitrate' => '1400k'],
                ['name' => '720p',  'scale' => '1280:720',  'bitrate' => '2800k'],
                ['name' => '1080p', 'scale' => '1920:1080', 'bitrate' => '5000k'],
            ];

            $variants = [];

            /*
            |------------------------------------------
            | 3. ENCODE EACH RENDITION (SAFE FFmpeg)
            |------------------------------------------
            */
            foreach ($renditions as $r) {

                $variantDir = "{$outputDir}/{$r['name']}";
                $this->makeDir($variantDir);

                $segmentPattern = str_replace(
                    '\\',
                    '/',
                    "{$variantDir}/segment_%03d.ts"
                );

                $playlistFile = str_replace(
                    '\\',
                    '/',
                    "{$variantDir}/index.m3u8"
                );

                Log::info("Encoding {$r['name']}");

                $process = new Process([
                    'ffmpeg',
                    '-y',
                    '-i',
                    $inputFile,
                    '-vf',
                    "scale={$r['scale']}",
                    '-c:v',
                    'libx264',
                    '-b:v',
                    $r['bitrate'],
                    '-preset',
                    'veryfast',
                    '-c:a',
                    'aac',
                    '-ac',
                    '2',
                    '-ar',
                    '44100',
                    '-hls_time',
                    '10',
                    '-hls_playlist_type',
                    'vod',
                    '-hls_segment_filename',
                    $segmentPattern,
                    $playlistFile
                ]);

                $process->setTimeout(14400);

                try {
                    $process->mustRun();
                } catch (\Throwable $e) {
                    Log::error("FFmpeg failed", [
                        'quality' => $r['name'],
                        'error' => $process->getErrorOutput()
                    ]);

                    throw new \Exception(
                        "FFmpeg failed ({$r['name']}): " . $process->getErrorOutput()
                    );
                }

                $variants[] = [
                    'name' => $r['name'],
                    'bandwidth' => $this->getBandwidth($r['bitrate']),
                    'resolution' => $r['scale'],
                ];
            }

            /*
            |------------------------------------------
            | 4. MASTER PLAYLIST (NETFLIX STYLE)
            |------------------------------------------
            */
            $master = "#EXTM3U\n#EXT-X-VERSION:3\n";

            foreach ($variants as $v) {
                $master .= "#EXT-X-STREAM-INF:"
                    . "BANDWIDTH={$v['bandwidth']},"
                    . "RESOLUTION={$v['resolution']}\n";

                $master .= $v['name'] . "/index.m3u8\n";
            }

            file_put_contents("{$outputDir}/master.m3u8", $master);

            /*
            |------------------------------------------
            | 5. UPLOAD TO S3 (CDN READY)
            |------------------------------------------
            */
            $this->uploadFolder(
                $outputDir,
                "videos/courses/{$course->id}/hls"
            );

            /*
            |------------------------------------------
            | 6. SAVE FINAL OUTPUT
            |------------------------------------------
            */
            $course->update([
                'hls_path' => "videos/courses/{$course->id}/hls/master.m3u8",
                'hls_status' => 'ready',
            ]);

            Log::info("HLS completed successfully", [
                'course_id' => $course->id
            ]);
        } catch (\Throwable $e) {

            Log::error("HLS pipeline failed", [
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);

            $course->update(['hls_status' => 'failed']);

            throw $e;
        } finally {
            $this->deleteDir($baseDir);
        }
    }

    private function makeDir($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function uploadFolder($dir, $s3Path)
    {
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') continue;

            $full = $dir . '/' . $file;

            if (is_dir($full)) {
                $this->uploadFolder($full, $s3Path . '/' . $file);
            } else {
                Storage::disk('s3')->put(
                    $s3Path . '/' . $file,
                    fopen($full, 'r')
                );
            }
        }
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $file) {

            if ($file === '.' || $file === '..') {
                continue;
            }

            $full = $dir . DIRECTORY_SEPARATOR . $file;

            Log::info('Deleting item', [
                'path' => $full,
                'is_dir' => is_dir($full)
            ]);

            if (is_dir($full)) {

                $this->deleteDir($full);
            } else {

                if (file_exists($full)) {

                    try {

                        @chmod($full, 0777);

                        if (!@unlink($full)) {
                            Log::warning('Failed to delete file', [
                                'file' => $full
                            ]);
                        } else {
                            Log::info('File deleted', [
                                'file' => $full
                            ]);
                        }
                    } catch (\Throwable $e) {

                        Log::warning('Exception deleting file', [
                            'file' => $full,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        Log::info('Directory contents before removal', [
            'dir' => $dir,
            'contents' => scandir($dir)
        ]);

        /*
    |--------------------------------------------------
    | Windows sometimes keeps files locked briefly.
    | Retry a few times before giving up.
    |--------------------------------------------------
    */
        for ($i = 1; $i <= 5; $i++) {

            if (@rmdir($dir)) {

                Log::info('Directory removed', [
                    'dir' => $dir
                ]);

                return;
            }

            Log::warning('Directory removal attempt failed', [
                'dir' => $dir,
                'attempt' => $i
            ]);

            usleep(500000); // 0.5 second
        }

        Log::warning('Could not remove directory after retries', [
            'dir' => $dir,
            'remaining_contents' => scandir($dir)
        ]);
    }

    private function getBandwidth($bitrate)
    {
        return (int) str_replace('k', '', $bitrate) * 1000;
    }
}
