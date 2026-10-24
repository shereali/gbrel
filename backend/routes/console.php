<?php

use App\Jobs\CompressVideoJob;
use App\Models\Media;
use App\Services\VideoCompressorService;
use App\Support\MetaLeadImporter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

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

// Backfill Meta Lead Ad leads (the webhook only sees new ones; Meta keeps form leads for 90 days):
// php artisan meta:import-leads 1618000993676451 --since=2026-10-01
Artisan::command('meta:import-leads {forms* : One or more Meta lead form IDs} {--since= : Only leads created after this date}', function () {
    if (! MetaLeadImporter::isConfigured()) {
        $this->error('META_PAGE_ACCESS_TOKEN is not set in .env.');

        return 1;
    }
    $since = $this->option('since') ? Carbon::parse($this->option('since'), 'Asia/Dhaka') : null;
    $failed = false;
    foreach ($this->argument('forms') as $formId) {
        try {
            $counts = MetaLeadImporter::importForm((string) $formId, $since);
            $this->info("Form {$formId}: {$counts['created']} new leads imported, {$counts['skipped']} already in the CRM.");
        } catch (Throwable $e) {
            $failed = true;
            $this->error("Form {$formId}: ".$e->getMessage());
        }
    }

    return $failed ? 1 : 0;
})->purpose('Import leads from Meta Lead Ad forms into the CRM (skips leads that are already there)');

// Polls the Page's lead forms for new Facebook / Instagram leads, so leads arrive even while the Meta App is unpublished
// (an unpublished app gets no live webhooks). Runs every 2 minutes through `schedule:work` (see docker/entrypoint.sh).
// The first run, or --full, reads the last 90 days: everything Meta still keeps.
Artisan::command('meta:sync-leads {--full : Re-read the last 90 days of every form, archived ones included}', function () {
    if (! MetaLeadImporter::isConfigured()) {
        $this->warn('META_PAGE_ACCESS_TOKEN is not set; skipping.');

        return 0;
    }
    $lastSync = Cache::get('meta_leads_synced_at');
    $startedAt = now();
    // Overlap the previous run by 10 minutes; leads already in the CRM are skipped, so nothing is lost or doubled.
    $since = $this->option('full') || ! $lastSync ? null : Carbon::parse($lastSync)->subMinutes(10);
    try {
        $totals = MetaLeadImporter::syncPageForms($since);
    } catch (Throwable $e) {
        $this->error('Meta lead sync failed: '.$e->getMessage());
        logger()->warning('Meta lead sync failed', ['error' => $e->getMessage()]);

        return 1;
    }
    Cache::forever('meta_leads_synced_at', $startedAt->toIso8601String());
    $this->info("Checked {$totals['forms']} forms: {$totals['created']} new leads, {$totals['skipped']} already in the CRM.");

    return 0;
})->purpose('Import new leads from all of the Page\'s Meta lead forms into the CRM');

// Shows what META_PAGE_ACCESS_TOKEN is (User or Page) and which lead forms it can read: php artisan meta:check
Artisan::command('meta:check', function () {
    if (! MetaLeadImporter::isConfigured()) {
        $this->error('META_PAGE_ACCESS_TOKEN is not set.');

        return 1;
    }
    foreach (MetaLeadImporter::diagnose() as $label => $value) {
        $this->line("{$label}: {$value}");
    }

    return 0;
})->purpose('Check the Meta token and list the lead forms it can read');

Schedule::command('meta:sync-leads')->everyTwoMinutes()->withoutOverlapping(10);
