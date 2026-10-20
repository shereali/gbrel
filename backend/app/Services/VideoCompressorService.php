<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Property;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Throwable;

class VideoCompressorService
{
    /**
     * Check if FFmpeg is configured and callable on the host system.
     */
    public function isAvailable(): bool
    {
        if (! config('media.video.enabled', true)) {
            return false;
        }

        $binary = (string) config('media.ffmpeg_path', 'ffmpeg');

        try {
            $result = Process::timeout(5)->run([$binary, '-version']);

            return $result->successful();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Compress an uploaded video file to a stream-ready, lightweight MP4 (H.264 + AAC).
     *
     * @param  string  $path  Storage path relative to the 'public' disk (e.g. 'media/2026/10/video.mp4')
     * @param  int|null  $mediaId  Optional Media model ID to update metadata
     * @return array{success: bool, original_size?: int, compressed_size?: int, saved_bytes?: int, poster_url?: string|null, skipped?: bool, error?: string}
     */
    public function compress(string $path, ?int $mediaId = null): array
    {
        $cleanPath = $this->normalizePath($path);
        $disk = Storage::disk('public');

        if (! $disk->exists($cleanPath)) {
            Log::warning("VideoCompressorService: File not found on public disk: {$cleanPath}");

            return ['success' => false, 'error' => "File not found: {$cleanPath}"];
        }

        $fullInputPath = $disk->path($cleanPath);
        $originalSize = (int) @filesize($fullInputPath);
        $media = $mediaId ? Media::find($mediaId) : Media::where('path', $cleanPath)->orWhere('url', '/storage/'.$cleanPath)->first();
        if ($media && ! $media->original_size) {
            $media->original_size = $originalSize;
            $media->compression_status = 'processing';
            $media->save();
        }

        if (! $this->isAvailable()) {
            Log::info("VideoCompressorService: FFmpeg is not available on this host. Preserving original video: {$cleanPath}");
            if ($media) {
                $media->compression_status = 'skipped';
                $media->save();
            }

            return [
                'success' => false,
                'skipped' => true,
                'original_size' => $originalSize,
                'error' => 'FFmpeg binary not found or disabled',
                'status' => 'skipped',
            ];
        }

        $ffmpeg = (string) config('media.ffmpeg_path', 'ffmpeg');
        $crf = (int) config('media.video.crf', 24);
        $maxWidth = (int) config('media.video.max_width', 1920);
        $preset = (string) config('media.video.preset', 'fast');
        $audioBitrate = (string) config('media.video.audio_bitrate', '128k');
        $timeout = (int) config('media.video.timeout', 600);

        $tempOutputPath = $fullInputPath.'.compressed_'.uniqid().'.mp4';

        try {
            // Compress with H.264 + AAC, downscale to max width (preserving aspect ratio and even dimensions),
            // and enable +faststart so browsers can immediately stream without downloading the full 200MB.
            $cmd = [
                $ffmpeg,
                '-y',
                '-i', $fullInputPath,
                '-vf', "scale='min({$maxWidth},iw)':-2",
                '-c:v', 'libx264',
                '-crf', (string) $crf,
                '-preset', $preset,
                '-c:a', 'aac',
                '-b:a', $audioBitrate,
                '-movflags', '+faststart',
                $tempOutputPath,
            ];

            $result = Process::timeout($timeout)->run($cmd);

            if (! $result->successful() || ! file_exists($tempOutputPath) || filesize($tempOutputPath) === 0) {
                if (file_exists($tempOutputPath)) {
                    @unlink($tempOutputPath);
                }
                Log::warning("VideoCompressorService: FFmpeg compression failed for {$cleanPath}. Error: ".$result->errorOutput());
                if ($media) {
                    $media->compression_status = 'failed';
                    $media->save();
                }

                return [
                    'success' => false,
                    'original_size' => $originalSize,
                    'error' => 'Compression failed: '.$result->errorOutput(),
                    'status' => 'failed',
                ];
            }

            $compressedSize = (int) filesize($tempOutputPath);

            // If compressed version is smaller, atomically replace the original.
            // If the original was already smaller (e.g. tiny low-res video), keep original to prevent quality loss.
            if ($compressedSize < $originalSize) {
                // Windows-safe replace: unlink target first if needed or copy
                @copy($tempOutputPath, $fullInputPath);
                @unlink($tempOutputPath);
                $finalSize = (int) filesize($fullInputPath);
            } else {
                @unlink($tempOutputPath);
                $finalSize = $originalSize;
            }

            // Generate thumbnail poster if enabled
            $posterUrl = $this->generatePoster($fullInputPath, $cleanPath);

            // Update Media model if found
            if ($media) {
                $media->size = $finalSize;
                $media->original_size = $originalSize;
                $media->saved_bytes = max(0, $originalSize - $finalSize);
                $media->compression_status = 'completed';
                $media->mime_type = 'video/mp4';
                if (! $media->thumbnail_url && $posterUrl) {
                    $media->thumbnail_url = $posterUrl;
                }
                $media->save();
            }

            // If any Property references this video without a poster, auto-attach poster
            if ($posterUrl) {
                Property::where('video_url', '/storage/'.$cleanPath)
                    ->where(function ($q) {
                        $q->whereNull('video_poster')->orWhere('video_poster', '');
                    })
                    ->update(['video_poster' => $posterUrl]);
            }

            Log::info("VideoCompressorService: Successfully processed {$cleanPath}. Original: {$originalSize} bytes, Final: {$finalSize} bytes.");

            return [
                'success' => true,
                'original_size' => $originalSize,
                'compressed_size' => $finalSize,
                'saved_bytes' => max(0, $originalSize - $finalSize),
                'poster_url' => $posterUrl,
            ];
        } catch (Throwable $e) {
            if (file_exists($tempOutputPath)) {
                @unlink($tempOutputPath);
            }
            Log::error("VideoCompressorService: Exception during compression of {$cleanPath}: ".$e->getMessage());

            return [
                'success' => false,
                'original_size' => $originalSize,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a crisp video poster frame at 1s mark (or 0s fallback).
     */
    public function generatePoster(string $fullVideoPath, string $relativeVideoPath): ?string
    {
        if (! config('media.video.generate_poster', true) || ! $this->isAvailable()) {
            return null;
        }

        $dir = pathinfo($relativeVideoPath, PATHINFO_DIRNAME);
        $filename = pathinfo($relativeVideoPath, PATHINFO_FILENAME);
        $posterRelativePath = ($dir === '.' ? '' : $dir.'/').$filename.'_poster.jpg';
        $fullPosterPath = Storage::disk('public')->path($posterRelativePath);

        if (file_exists($fullPosterPath) && filesize($fullPosterPath) > 0) {
            return '/storage/'.$posterRelativePath;
        }

        $ffmpeg = (string) config('media.ffmpeg_path', 'ffmpeg');

        try {
            $cmd = [
                $ffmpeg,
                '-y',
                '-ss', '00:00:01',
                '-i', $fullVideoPath,
                '-vframes', '1',
                '-q:v', '2',
                $fullPosterPath,
            ];

            $result = Process::timeout(30)->run($cmd);

            if (! $result->successful() || ! file_exists($fullPosterPath) || filesize($fullPosterPath) === 0) {
                // Fallback to start of video (0s) if 1s seek was out of bounds
                $cmdFallback = [
                    $ffmpeg,
                    '-y',
                    '-ss', '00:00:00',
                    '-i', $fullVideoPath,
                    '-vframes', '1',
                    '-q:v', '2',
                    $fullPosterPath,
                ];
                $fallbackResult = Process::timeout(30)->run($cmdFallback);
                if (! $fallbackResult->successful() || ! file_exists($fullPosterPath) || filesize($fullPosterPath) === 0) {
                    if (file_exists($fullPosterPath)) {
                        @unlink($fullPosterPath);
                    }

                    return null;
                }
            }

            return '/storage/'.$posterRelativePath;
        } catch (Throwable $e) {
            Log::notice("VideoCompressorService: Poster generation skipped for {$relativeVideoPath}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Strip leading slashes, storage prefixes, or full URLs to get a clean disk path.
     */
    public function normalizePath(string $path): string
    {
        $clean = trim($path);
        if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://')) {
            $parsed = parse_url($clean, PHP_URL_PATH);
            $clean = $parsed ?: $clean;
        }
        $clean = ltrim($clean, '/\\');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        return ltrim($clean, '/\\');
    }
}
