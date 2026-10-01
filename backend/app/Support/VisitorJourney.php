<?php

namespace App\Support;

use App\Jobs\SendMetaEvent;
use App\Models\Lead;
use App\Models\LeadDraft;
use App\Models\VisitorEvent;
use App\Models\VisitorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Connects a saved lead to the visitor journey recorded by gb-track.js, and sends the lead to Meta
 * from the server. Visitor ids exist only for people who accepted cookies, so without them nothing here runs.
 */
class VisitorJourney
{
    /** Lead score the survey stores on the lead: "Lead score: HOT (8/9)". */
    public static function tier(Lead $lead): ?string
    {
        return preg_match('/^Lead score: (HOT|WARM|COLD)\b/m', (string) $lead->message, $m) ? $m[1] : null;
    }

    public static function record(Request $request, Lead $lead, array $input = []): void
    {
        try {
            $vid = self::cleanId($input['vid'] ?? $request->cookie('gb_vid'));
            $sid = self::cleanId($input['sid'] ?? $request->cookie('gb_sid'));
            if (! $vid) {
                return;
            }

            $lead->forceFill(['visitor_id' => $vid, 'session_id' => $sid])->save();
            $session = $sid ? VisitorSession::where('session_id', $sid)->where('visitor_id', $vid)->first() : null;
            $session?->forceFill(['lead_id' => $lead->id, 'furthest_step' => 'lead'])->save();

            // The lead is saved, so the sales team must not also call the unfinished draft.
            LeadDraft::where('visitor_id', $vid)->whereNull('converted_lead_id')->update(['converted_lead_id' => $lead->id]);

            // Logged here as well, because the browser copy can be blocked.
            if ($sid) {
                VisitorEvent::create([
                    'session_id' => $sid, 'visitor_id' => $vid, 'event' => 'lead_saved',
                    'property_id' => $lead->property_id, 'label' => self::tier($lead), 'occurred_at' => now(),
                ]);
            }

            self::sendToMeta($request, $lead, $vid, $session);
        } catch (\Throwable $e) {
            Log::warning('Visitor journey could not be linked to lead '.$lead->id, ['error' => $e->getMessage()]);
        }
    }

    /** Same event ids as the browser pixel (PropertyInquiry.vue) so Meta counts each event once. */
    private static function sendToMeta(Request $request, Lead $lead, string $vid, ?VisitorSession $session): void
    {
        if (! $lead->request_id) {
            return;
        }

        $userData = [
            'phone' => $lead->phone,
            'first_name' => explode(' ', trim((string) $lead->name))[0] ?: null,
            'visitor_id' => $vid,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'fbc' => $request->cookie('_fbc') ?: $session?->fbc,
            'fbp' => $request->cookie('_fbp') ?: $session?->fbp,
        ];
        $tier = self::tier($lead);
        $custom = array_filter([
            'content_ids' => $lead->property_id ? [(string) $lead->property_id] : null,
            'content_type' => 'product',
            'lead_tier' => $tier,
        ]);
        $url = $request->headers->get('referer');

        SendMetaEvent::dispatchAfterResponse('Lead', (string) $lead->request_id, $userData, $custom, $url);
        if ($tier === 'HOT') {
            SendMetaEvent::dispatchAfterResponse('QualifiedLead', $lead->request_id.'-q', $userData, $custom, $url);
        }
    }

    private static function cleanId(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9_-]{8,40}$/', $value) ? $value : null;
    }
}
