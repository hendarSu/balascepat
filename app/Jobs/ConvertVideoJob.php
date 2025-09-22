<?php

namespace App\Jobs;

use App\Events\VideoProgressUpdated;
use App\Events\VideoStatusChanged;
use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSExporter;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ConvertVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Allow long-running ffmpeg process.
     * Timeout is in seconds; set high to avoid worker killing job.
     */
    public int $timeout = 3600; // 60 minutes

    /**
     * Avoid re-trying long transcodes automatically.
     */
    public int $tries = 1;

    public function __construct(public int $videoId, public array $options = [])
    {
        $this->onQueue('videos');
    }

    public function handle(): void
    {
        $video = Video::find($this->videoId);
        if (!$video) return;

        $video->update(['status' => 'processing', 'progress' => 0]);
        event(new VideoStatusChanged($video));

        $disk = Storage::disk('minio');
        if (!$disk->exists($video->file_path)) {
            $video->update(['status' => 'failed']);
            event(new VideoStatusChanged($video));
            return;
        }

        $outputName = 'video_' . $video->id . '_' . time();

        // Probe basic media info
        try {
            $media = FFMpeg::fromDisk('minio')->open($video->file_path);
            $streams = $media->getFFMpegDriver()->getFFProbe()->streams($disk->path($video->file_path));
            $width = (int)($streams->videos()->first()->get('width') ?? 0);
            $height = (int)($streams->videos()->first()->get('height') ?? 0);
            $duration = (int)($streams->first()->get('duration') ?? 0);
            $video->update(['width' => $width, 'height' => $height, 'duration_seconds' => $duration]);
        } catch (\Throwable $e) {
            // ignore probe errors, continue
        }

        // Build per-title ladder
        $ladder = $this->buildLadder($video->width ?? 0, $video->height ?? 0);

        // Watermark options
        $watermark = $this->options['watermark'] ?? null; // ['text'=>..., 'logo_path'=>..., 'position'=>'top-right']

        // Encryption keys (rotating by default)
        $encryptionKeys = [];

        try {
            $exporter = FFMpeg::fromDisk('minio')
                ->open($video->file_path)
                ->exportForHLS()
                ->onProgress(function ($percentage) use ($video) {
                    $video->update(['progress' => (int)$percentage]);
                    event(new VideoProgressUpdated($video));
                })
                ->withRotatingEncryptionKey(function ($filename, $contents) use (&$encryptionKeys, $disk) {
                    $disk->put("keys/{$filename}", $contents);
                    $encryptionKeys[] = $filename;
                });

            foreach ($ladder as $fmt) {
                $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
                $format->setKiloBitrate($fmt['vb']);
                $format->setAudioKiloBitrate($fmt['ab']);
                // Optional tune for faststart
                $threads = (string) (env('FFMPEG_THREADS', '1'));
                $preset = (string) (env('FFMPEG_PRESET', 'veryfast'));
                $format->setAdditionalParameters(['-movflags', '+faststart', '-preset', $preset, '-threads', $threads]);

                if ($watermark) {
                    try {
                        if (!empty($watermark['logo_path'])) {
                            $pos = $this->mapPosition($watermark['position'] ?? 'top-right');
                            $exporter->addFilter("movie={$disk->path($watermark['logo_path'])} [wm]; [in][wm] overlay={$pos} [out]");
                        } elseif (!empty($watermark['text'])) {
                            // Simple text overlay (requires drawtext support)
                            $pos = $this->mapPosition($watermark['position'] ?? 'top-right');
                            [$x,$y] = explode(':', $pos);
                            $exporter->addFilter("drawtext=text='".addslashes($watermark['text'])."':fontcolor=white:fontsize=24:x={$x}:y={$y}");
                        }
                    } catch (\Throwable $e) {
                        // ignore watermark filter errors
                    }
                }

                $exporter->addFormat($format, function ($media) use ($fmt) {
                    $media->scale($fmt['w'], $fmt['h']);
                });
            }

            $exporter->toDisk('minio')->save("hls/{$outputName}.m3u8");

            // Poster thumbnail (1s)
            try {
                $posterPath = "posters/video_{$video->id}.jpg";
                FFMpeg::fromDisk('minio')->open($video->file_path)
                    ->getFrameFromSeconds(1)
                    ->export()->toDisk('minio')->save($posterPath);
                $video->poster_path = $posterPath;
            } catch (\Throwable $e) {}

            $video->status = 'completed';
            $video->hls_path = "hls/{$outputName}.m3u8";
            $video->encryption_type = 'rotating';
            $video->encryption_keys = $encryptionKeys;
            $video->progress = 100;
            $video->save();
            event(new VideoStatusChanged($video));
        } catch (\Throwable $e) {
            Log::error('ConvertVideoJob failed: '.$e->getMessage());
            $video->update(['status' => 'failed']);
            event(new VideoStatusChanged($video));
        }
    }

    private function buildLadder(int $w, int $h): array
    {
        // Basic per-title ladder mapping with caps by source
        $max = max($w, $h);
        $ladder = [];
        if ($max >= 1920) { // Full HD source
            $ladder[] = ['w'=>1920,'h'=>1080,'vb'=>4500,'ab'=>192];
            $ladder[] = ['w'=>1280,'h'=>720,'vb'=>3000,'ab'=>160];
            $ladder[] = ['w'=>854,'h'=>480,'vb'=>1500,'ab'=>128];
        } elseif ($max >= 1280) {
            $ladder[] = ['w'=>1280,'h'=>720,'vb'=>2500,'ab'=>160];
            $ladder[] = ['w'=>854,'h'=>480,'vb'=>1200,'ab'=>128];
            $ladder[] = ['w'=>640,'h'=>360,'vb'=>800,'ab'=>96];
        } else { // SD source
            $ladder[] = ['w'=>854,'h'=>480,'vb'=>1000,'ab'=>128];
            $ladder[] = ['w'=>640,'h'=>360,'vb'=>700,'ab'=>96];
        }
        return $ladder;
    }

    private function mapPosition(string $pos): string
    {
        return match($pos){
            'top-left' => '10:10',
            'top-right' => 'main_w-overlay_w-10:10',
            'bottom-left' => '10:main_h-overlay_h-10',
            default => 'main_w-overlay_w-10:main_h-overlay_h-10', // bottom-right
        };
    }
}
