<?php

use App\Jobs\CompressVideoJob;
use App\Models\Media;
use App\Services\VideoCompressorService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Privacy housekeeping for visitor tracking. Run monthly: php artisan tracking:prune
Artisan::command('tracking:prune', function () {
    $old = now()->subDays(180);
    $events = DB::table('visitor_events')->whereIn('session_id', fn ($q) => $q->select('session_id')->from('visitor_sessions')->whereNull('lead_id')->where('started_at', '<', $old))->delete();
    $sessions = DB::table('visitor_sessions')->whereNull('lead_id')->where('started_at', '<', $old)->delete();
    $drafts = DB::table('lead_drafts')->whereNull('converted_lead_id')->where('updated_at', '<', now()->subDays(90))->delete();
    $this->info("Deleted {$events} events, {$sessions} anonymous visits and {$drafts} unconverted drafts.");
})->purpose('Delete anonymous visitor journeys older than 180 days and unconverted drafts older than 90 days');

// FFmpeg Video Background Compression & Inspection: php artisan media:compress-videos
Artisan::command('media:compress-videos {--check : Check if FFmpeg is installed and available} {--id= : Compress a specific media item ID} {--queue : Dispatch to background queue worker}', function (VideoCompressorService $compressor) {
    if ($this->option('check')) {
        $available = $compressor->isAvailable();
        if ($available) {
            $this->info('✓ FFmpeg is installed and available on this host: '.config('media.ffmpeg_path', 'ffmpeg'));
        } else {
            $this->warn('! FFmpeg is NOT found in PATH on this host (it is installed inside the Docker container for production).');
        }

        return $available ? 0 : 1;
    }

    $id = $this->option('id');
    if ($id) {
        $media = Media::find($id);
        if (! $media) {
            $this->error("Media ID {$id} not found.");

            return 1;
        }
        if ($media->type !== 'video' || ! $media->path) {
            $this->error("Media ID {$id} is not an uploaded video.");

            return 1;
        }
        if ($this->option('queue')) {
            CompressVideoJob::dispatch($media->path, $media->id);
            $this->info("Dispatched compression job for Media #{$media->id} to queue.");

            return 0;
        }
        $this->info("Compressing video #{$media->id} ({$media->path})...");
        $res = $compressor->compress($media->path, $media->id);
        $this->info(json_encode($res, JSON_PRETTY_PRINT));

        return ! empty($res['success']) ? 0 : 1;
    }

    $videos = Media::where('type', 'video')->where('source', 'upload')->whereNotNull('path')->get();
    $this->info("Found {$videos->count()} uploaded video(s) in media library.");
    foreach ($videos as $media) {
        if ($this->option('queue')) {
            CompressVideoJob::dispatch($media->path, $media->id);
            $this->line("Queued Media #{$media->id} ({$media->path})");
        } else {
            $this->line("Processing Media #{$media->id} ({$media->path})...");
            $res = $compressor->compress($media->path, $media->id);
            if (! empty($res['success'])) {
                $savedMb = round(($res['saved_bytes'] ?? 0) / (1024 * 1024), 2);
                $this->info("✓ Media #{$media->id} compressed. Saved {$savedMb} MB.");
            } else {
                $this->warn("! Media #{$media->id}: ".($res['error'] ?? 'skipped'));
            }
        }
    }

    return 0;
})->purpose('Check FFmpeg status or compress uploaded property videos to stream-ready MP4s');
