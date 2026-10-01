<?php

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
