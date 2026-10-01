<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\VisitorEvent;
use App\Models\VisitorSession;
use App\Support\VisitorJourney;
use Illuminate\Http\JsonResponse;

class LeadJourneyController extends Controller
{
    /** GET /api/leads/{id}/journey. The ProtectLeadData middleware already limits /api/leads/* to staff with lead access. */
    public function show(int $id): JsonResponse
    {
        $lead = Lead::withTrashed()->findOrFail($id);
        $sessions = $lead->visitor_id
            ? VisitorSession::where('visitor_id', $lead->visitor_id)->orderBy('started_at')->limit(50)->get()
            : collect();
        $events = $sessions->isEmpty()
            ? collect()
            : VisitorEvent::whereIn('session_id', $sessions->pluck('session_id'))
                ->orderBy('occurred_at')->limit(2000)
                ->get(['session_id', 'event', 'label', 'value', 'step', 'path', 'occurred_at'])
                ->groupBy('session_id');

        return response()->json([
            'success' => true,
            'data' => [
                'lead' => [
                    'id' => $lead->id, 'name' => $lead->name, 'phone' => $lead->phone,
                    'tier' => VisitorJourney::tier($lead), 'created_at' => $lead->created_at,
                ],
                'sessions' => $sessions->map(fn (VisitorSession $s) => [
                    'started_at' => $s->started_at,
                    'source' => $s->utm_source ?: ($s->fbclid ? 'facebook (no UTM)' : 'direct / other'),
                    'campaign' => $s->utm_campaign,
                    'adset' => $s->utm_term,
                    'ad' => $s->utm_content,
                    'device' => $s->device,
                    'browser' => $s->browser,
                    'in_facebook_app' => $s->in_facebook_app,
                    'city' => $s->city,
                    'seconds_active' => $s->seconds_active,
                    'max_scroll' => $s->max_scroll,
                    'furthest_step' => $s->furthest_step,
                    'events' => $events[$s->session_id] ?? [],
                ])->values(),
            ],
        ]);
    }
}
