<?php

namespace App\Http\Controllers;

use App\Models\LeadDraft;
use App\Models\VisitorEvent;
use App\Models\VisitorSession;
use App\Support\PhoneNumber;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Receives journey events from public/gb-track.js. The script only runs for visitors who accepted cookies.
 *   POST /api/t        batched events (sent with sendBeacon as text/plain, so no CORS preflight and no token)
 *   POST /api/t/draft  name and phone the visitor confirmed before finishing the survey
 */
class TrackController extends Controller
{
    /** Only these event names are stored; anything else is dropped. */
    public const EVENTS = [
        'page_view', 'view_content', 'scroll', 'engaged_time', 'cta_click', 'click',
        'video_play', 'call_click', 'whatsapp_click',
        'survey_open', 'survey_start', 'survey_step', 'survey_back', 'survey_close',
        'draft_saved', 'lead_submit', 'lead', 'qualified_lead', 'contact', 'page_exit',
    ];

    private const MAX_BODY_BYTES = 65536;

    private const MAX_EVENTS_PER_BATCH = 50;

    public function collect(Request $request): Response
    {
        $raw = $request->getContent();
        $data = strlen($raw) <= self::MAX_BODY_BYTES ? json_decode($raw, true) : null;
        $vid = is_array($data) ? $this->cleanId($data['vid'] ?? null) : null;
        $sid = is_array($data) ? $this->cleanId($data['sid'] ?? null) : null;
        if (! $vid || ! $sid) {
            return response()->noContent();
        }

        $session = $this->session($request, $data, $vid, $sid);
        if (! $session || $session->visitor_id !== $vid) {
            return response()->noContent();
        }

        $rows = [];
        $seconds = 0;
        $maxScroll = (int) $session->max_scroll;
        $furthest = $session->furthest_step;
        $events = array_slice(is_array($data['events'] ?? null) ? $data['events'] : [], 0, self::MAX_EVENTS_PER_BATCH);
        foreach ($events as $event) {
            $name = is_array($event) ? ($event['e'] ?? null) : null;
            if (! in_array($name, self::EVENTS, true)) {
                continue;
            }
            $at = isset($event['t']) && is_numeric($event['t']) ? Carbon::createFromTimestampMs((int) $event['t']) : now();
            if ($at->gt(now()->addMinutes(5)) || $at->lt(now()->subDay())) {
                $at = now(); // wrong phone clock
            }

            $rows[] = [
                'session_id' => $sid,
                'visitor_id' => $vid,
                'event' => $name,
                'property_id' => $this->digits($event['pid'] ?? null),
                'label' => $this->text($event['l'] ?? null),
                'value' => $this->text($event['v'] ?? null),
                'step' => isset($event['st']) && is_numeric($event['st']) ? max(0, min((int) $event['st'], 999)) : null,
                'meta' => $this->meta($event['m'] ?? null),
                'path' => $this->text($event['p'] ?? null, 255),
                'occurred_at' => $at,
                'created_at' => now(),
            ];

            if ($name === 'scroll') {
                $maxScroll = max($maxScroll, min((int) ($event['v'] ?? 0), 100));
            } elseif ($name === 'engaged_time') {
                $seconds += max(0, min((int) ($event['v'] ?? 0), 600));
            } elseif (in_array($name, ['survey_open', 'survey_start', 'survey_step', 'draft_saved', 'lead_submit', 'lead'], true)) {
                $furthest = $this->furthest($furthest, $name, $event['st'] ?? null);
            }
        }

        DB::transaction(function () use ($session, $rows, $seconds, $maxScroll, $furthest, $data) {
            $session->increment('events_count', count($rows), [
                'seconds_active' => DB::raw('seconds_active + '.$seconds),
                'max_scroll' => $maxScroll,
                'furthest_step' => $furthest,
                'last_seen_at' => now(),
                // The pixel sets these cookies after the first hit, so refresh them every time.
                'fbc' => $this->text($data['fbc'] ?? null) ?: $session->fbc,
                'fbp' => $this->text($data['fbp'] ?? null) ?: $session->fbp,
            ]);
            if ($rows) {
                VisitorEvent::insert($rows);
            }
        });

        return response()->noContent();
    }

    /** Stores a name and phone the visitor confirmed. The survey must show its consent line first. */
    public function draft(Request $request): JsonResponse
    {
        $input = $request->validate([
            'vid' => ['required', 'string', 'max:40'],
            'sid' => ['required', 'string', 'max:40'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'name' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'answers' => ['nullable', 'array', 'max:20'],
            'answers.*' => ['nullable', 'string', 'max:191'],
            'step' => ['nullable', 'integer', 'min:0', 'max:999'],
            'consent' => ['accepted'],
        ]);

        $vid = $this->cleanId($input['vid']);
        $sid = $this->cleanId($input['sid']);
        $phone = PhoneNumber::normalize($input['phone']);
        if (! $vid || ! $sid || ! PhoneNumber::isValid($phone)) {
            return response()->json(['success' => false, 'message' => 'A valid phone number is required.'], 422);
        }

        $draft = LeadDraft::updateOrCreate(
            ['visitor_id' => $vid, 'property_id' => $input['property_id'] ?? null],
            array_filter([
                'session_id' => $sid,
                'name' => isset($input['name']) ? trim($input['name']) : null,
                'phone' => $phone,
                'answers' => $input['answers'] ?? null,
                'last_step' => $input['step'] ?? null,
                'consent_at' => now(),
            ], fn ($value) => $value !== null)
        );

        return response()->json(['success' => true, 'data' => ['id' => $draft->id]]);
    }

    /** Finds the visit, or creates it from the ad source the tracker sends with its first batch. */
    private function session(Request $request, array $data, string $vid, string $sid): ?VisitorSession
    {
        $session = VisitorSession::where('session_id', $sid)->first();
        if ($session) {
            return $session;
        }

        $src = is_array($data['src'] ?? null) ? $data['src'] : [];
        $agent = (string) $request->userAgent();
        try {
            return VisitorSession::create([
                'session_id' => $sid,
                'visitor_id' => $vid,
                'started_at' => now(),
                'utm_source' => $this->text($src['utm_source'] ?? null, 100),
                'utm_medium' => $this->text($src['utm_medium'] ?? null, 100),
                'utm_campaign' => $this->text($src['utm_campaign'] ?? null),
                'utm_content' => $this->text($src['utm_content'] ?? null),
                'utm_term' => $this->text($src['utm_term'] ?? null),
                'fb_campaign_id' => $this->digits($src['fb_campaign_id'] ?? null),
                'fb_adset_id' => $this->digits($src['fb_adset_id'] ?? null),
                'fb_ad_id' => $this->digits($src['fb_ad_id'] ?? null),
                'fbclid' => $this->text($src['fbclid'] ?? null),
                'fbc' => $this->text($data['fbc'] ?? null),
                'fbp' => $this->text($data['fbp'] ?? null),
                'landing_url' => $this->text($src['landing_url'] ?? null, 2000),
                'referrer' => $this->text($src['referrer'] ?? null, 2000),
                'device' => $this->device($agent),
                'browser' => $this->browser($agent),
                'in_facebook_app' => (bool) preg_match('/FBAN|FBAV|FB_IAB/i', $agent),
                'country' => $this->text($request->header('CF-IPCountry'), 2),
                'city' => $this->text($request->header('CF-IPCity'), 100),
                'ip_hash' => hash('sha256', $request->ip().config('app.key')),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Two batches of the same new visit arrived together.
            return VisitorSession::where('session_id', $sid)->first();
        }
    }

    private function furthest(?string $current, string $event, mixed $step): string
    {
        $rank = fn (?string $value) => match (true) {
            $value === null => 0,
            $value === 'survey_open' => 1,
            $value === 'survey_start' => 2,
            str_starts_with($value, 'survey_step_') => 10 + (int) substr($value, 12),
            $value === 'draft_saved' => 500,
            $value === 'lead_submit' => 800,
            $value === 'lead' => 900,
            default => 0,
        };
        $new = $event === 'survey_step' ? 'survey_step_'.(int) $step : $event;

        return $rank($new) > $rank($current) ? $new : (string) $current;
    }

    private function cleanId(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9_-]{8,40}$/', $value) ? $value : null;
    }

    private function text(mixed $value, int $max = 191): ?string
    {
        if (! is_scalar($value) || $value === '') {
            return null;
        }

        return mb_substr(strip_tags((string) $value), 0, $max);
    }

    private function digits(mixed $value): ?string
    {
        $digits = preg_replace('/\D/', '', is_scalar($value) ? (string) $value : '');

        return $digits !== '' ? substr($digits, 0, 30) : null;
    }

    /** Small flat key/value data only (answers, scores); anything larger or nested is dropped. */
    private function meta(mixed $value): ?string
    {
        if (! is_array($value)) {
            return null;
        }
        $flat = [];
        foreach (array_slice($value, 0, 20, true) as $key => $item) {
            if (is_scalar($item) && is_string($key)) {
                $flat[mb_substr($key, 0, 40)] = is_string($item) ? mb_substr($item, 0, 191) : $item;
            }
        }
        $json = json_encode($flat);

        return $flat && strlen($json) <= 2000 ? $json : null;
    }

    private function device(string $agent): string
    {
        if (preg_match('/iPad|Tablet/i', $agent)) {
            return 'tablet';
        }

        return preg_match('/Mobi|Android|iPhone/i', $agent) ? 'mobile' : 'desktop';
    }

    private function browser(string $agent): string
    {
        foreach ([
            'FBAN|FBAV|FB_IAB' => 'Facebook app', 'Edg' => 'Edge', 'OPR|Opera' => 'Opera',
            'SamsungBrowser' => 'Samsung', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari',
        ] as $pattern => $name) {
            if (preg_match("/($pattern)/", $agent)) {
                return $name;
            }
        }

        return 'Other';
    }
}
