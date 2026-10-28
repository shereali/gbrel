<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Models\Viewing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Lead CRM: pipeline stage, owner, follow-up reminder, activity timeline and site visits.
 * Every route lives under /api/leads/*, which the ProtectLeadData middleware limits to staff
 * (leads.view to read, leads.manage to change).
 */
class LeadCrmController extends Controller
{
    public const STAGES = ['New', 'Contacted', 'Qualified', 'Site Visit', 'Visited', 'Negotiation', 'Converted', 'Lost'];

    public const CALL_OUTCOMES = ['Answered', 'No answer', 'Busy', 'Wrong number'];

    public const VISIT_STATUSES = ['Confirmed', 'Completed', 'No-show', 'Cancelled'];

    public const VISIT_TYPES = ['Site visit', 'Video call'];

    /** Bangladesh has no daylight saving; all dates and times staff type are Dhaka time. */
    private const TZ = 'Asia/Dhaka';

    /** GET /api/leads/overview: upcoming visits and the people a lead can be assigned to. */
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user && $user->isLeadAdmin();

        $today = now(self::TZ)->toDateString();
        $visitsQuery = Viewing::whereNotNull('lead_id')->where('status', 'Confirmed')->where('scheduled_date', '>=', $today);

        if (! $isAdmin && $user) {
            $visitsQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhereHas('lead', fn ($lq) => $lq->where('assigned_to', $user->id));
            });
        }

        $visits = $visitsQuery->orderBy('scheduled_date')->orderBy('scheduled_at')->limit(300)->get();

        return response()->json(['success' => true, 'data' => [
            'stages' => self::STAGES,
            'visits' => $visits->map(fn (Viewing $v) => $this->visit($v))->values(),
            'staff' => $this->staff(),
        ]]);
    }

    /** GET /api/leads/{id}/crm */
    public function show(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        if (! $this->canAccessLead($request, $lead)) {
            return response()->json(['success' => false, 'message' => 'Access denied. This lead is not transferred to you.'], 403);
        }

        return response()->json(['success' => true, 'data' => [
            'activities' => $lead->activities()->with('user:id,name,role')->orderByDesc('occurred_at')->orderByDesc('id')->limit(300)->get()
                ->map(fn (LeadActivity $a) => $this->activity($a))->values(),
            'visits' => Viewing::where('lead_id', $lead->id)->orderByDesc('scheduled_date')->orderByDesc('scheduled_at')->get()
                ->map(fn (Viewing $v) => $this->visit($v))->values(),
        ]]);
    }

    /** POST /api/leads/{id}/activities: a note, or a call / WhatsApp / email the team had with the buyer. */
    public function addActivity(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        if (! $this->canAccessLead($request, $lead)) {
            return response()->json(['success' => false, 'message' => 'Access denied. This lead is not transferred to you.'], 403);
        }

        $type = (string) $request->input('type');
        $data = $request->validate([
            'type' => ['required', Rule::in(['note', 'call', 'whatsapp', 'email'])],
            'body' => [$type === 'note' ? 'required' : 'nullable', 'string', 'max:2000'],
            'outcome' => [$type === 'call' ? 'required' : 'nullable', Rule::in(self::CALL_OUTCOMES)],
            'follow_up_at' => ['nullable', 'date'],
        ]);

        $this->log($request, $lead, $data['type'], $data['body'] ?? null, array_filter(['outcome' => $data['outcome'] ?? null]));

        if ($data['type'] !== 'note') {
            $lead->last_contacted_at = now();
            // A follow-up that was due is considered done once the buyer has been contacted.
            $lead->next_follow_up_at = null;
            if ($lead->status === 'New') {
                $this->moveStage($request, $lead, 'Contacted');
            }
        }
        if (! empty($data['follow_up_at'])) {
            $lead->next_follow_up_at = Carbon::parse($data['follow_up_at'])->utc();
            $this->log($request, $lead, 'follow_up', null, ['at' => $lead->next_follow_up_at->toIso8601String()]);
        }
        $lead->save();

        $fresh = $lead->fresh();
        self::attachActivitySummary($fresh);

        return response()->json(['success' => true, 'data' => $fresh]);
    }

    /** PATCH /api/leads/{id}/stage */
    public function setStage(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        if (! $this->canAccessLead($request, $lead)) {
            return response()->json(['success' => false, 'message' => 'Access denied. This lead is not transferred to you.'], 403);
        }

        $data = $request->validate([
            'stage' => ['required_without:status', Rule::in(self::STAGES)],
            'status' => ['nullable', Rule::in(self::STAGES)],
            'lost_reason' => [$request->input('stage', $request->input('status')) === 'Lost' ? 'required' : 'nullable', 'string', 'max:200'],
        ]);
        $stage = $data['stage'] ?? $data['status'];

        $this->moveStage($request, $lead, $stage, $data['lost_reason'] ?? null);
        $lead->save();

        $fresh = $lead->fresh();
        self::attachActivitySummary($fresh);

        return response()->json(['success' => true, 'message' => 'Lead stage updated', 'data' => $fresh]);
    }

    /** PATCH /api/leads/{id}/assign */
    public function assign(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isLeadAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only administrators can transfer leads.'], 403);
        }

        $lead = Lead::findOrFail($id);
        $data = $request->validate(['user_id' => ['nullable', 'integer', 'exists:users,id']]);
        $owner = $data['user_id'] ? User::find($data['user_id']) : null;
        if ($owner && ! $owner->isStaff()) {
            return response()->json(['success' => false, 'message' => 'Leads can only be assigned to team members.'], 422);
        }

        $lead->assigned_to = $owner?->id;
        $lead->save();
        $this->log($request, $lead, 'assign', $owner ? "Assigned to {$owner->name}" : 'Unassigned', ['user_id' => $owner?->id]);

        $fresh = $lead->fresh();
        self::attachActivitySummary($fresh);

        return response()->json(['success' => true, 'data' => $fresh]);
    }

    /** POST /api/leads/bulk-assign: assign multiple leads to a team member in one batch */
    public function bulkAssign(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isLeadAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only administrators can transfer leads.'], 403);
        }

        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['integer', 'exists:leads,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $owner = $data['user_id'] ? User::find($data['user_id']) : null;
        if ($owner && ! $owner->isStaff()) {
            return response()->json(['success' => false, 'message' => 'Leads can only be assigned to team members.'], 422);
        }

        $leads = Lead::whereIn('id', $data['lead_ids'])->get();
        foreach ($leads as $lead) {
            $lead->assigned_to = $owner?->id;
            $lead->save();
            $this->log($request, $lead, 'assign', $owner ? "Assigned to {$owner->name}" : 'Unassigned', ['user_id' => $owner?->id]);
        }

        return response()->json([
            'success' => true,
            'message' => count($leads).' lead(s) transferred to '.($owner ? $owner->name : 'Unassigned'),
            'count' => count($leads),
            'owner' => $owner ? ['id' => $owner->id, 'name' => $owner->name] : null,
        ]);
    }

    /** PATCH /api/leads/{id}/follow-up: the next time someone should contact this buyer. Null clears it. */
    public function followUp(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        if (! $this->canAccessLead($request, $lead)) {
            return response()->json(['success' => false, 'message' => 'Access denied. This lead is not transferred to you.'], 403);
        }

        $data = $request->validate(['at' => ['nullable', 'date'], 'note' => ['nullable', 'string', 'max:300']]);

        $lead->next_follow_up_at = ! empty($data['at']) ? Carbon::parse($data['at'])->utc() : null;
        $lead->save();
        $this->log($request, $lead, 'follow_up', $data['note'] ?? null, ['at' => $lead->next_follow_up_at?->toIso8601String()]);

        $fresh = $lead->fresh();
        self::attachActivitySummary($fresh);

        return response()->json(['success' => true, 'data' => $fresh]);
    }

    /** POST /api/leads/{id}/visits: schedule a site visit (or video call for buyers abroad) for this lead. */
    public function scheduleVisit(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        if (! $this->canAccessLead($request, $lead)) {
            return response()->json(['success' => false, 'message' => 'Access denied. This lead is not transferred to you.'], 403);
        }

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now(self::TZ)->toDateString()],
            'time' => ['required', 'date_format:H:i'],
            'visit_type' => ['required', Rule::in(self::VISIT_TYPES)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'vip_pickup' => ['nullable', 'boolean'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $guide = ! empty($data['assigned_to']) ? User::find($data['assigned_to']) : null;
        $startsAt = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'], self::TZ);

        $visit = Viewing::create([
            'lead_id' => $lead->id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'contact_method' => $lead->preferred_contact ?: 'Phone Call',
            'property_id' => $lead->property_id,
            'property_title' => $lead->property_title ?: 'General inquiry',
            'scheduled_date' => $data['date'],
            'scheduled_time' => $startsAt->format('h:i A'),
            'scheduled_at' => $startsAt->clone()->utc(),
            'visit_type' => $data['visit_type'],
            'vip_pickup' => (bool) ($data['vip_pickup'] ?? false),
            'pickup_location' => $data['pickup_location'] ?? null,
            'assigned_to' => $guide?->id,
            'assigned_agent' => $guide?->name,
            'status' => 'Confirmed',
            'notes' => $data['notes'] ?? null,
        ]);

        $this->log($request, $lead, 'visit', $data['visit_type'].' booked for '.$startsAt->format('j M, g:i A').($guide ? " with {$guide->name}" : ''), ['visit_id' => $visit->id, 'event' => 'scheduled']);
        if (in_array($lead->status, ['New', 'Contacted', 'Qualified'], true)) {
            $this->moveStage($request, $lead, 'Site Visit');
        }
        $lead->save();

        return response()->json(['success' => true, 'data' => $this->visit($visit)], 201);
    }

    /** PATCH /api/leads/{id}/visits/{visitId}: reschedule, change the guide, or record what happened. */
    public function updateVisit(Request $request, int $id, int $visitId): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        if (! $this->canAccessLead($request, $lead)) {
            return response()->json(['success' => false, 'message' => 'Access denied. This lead is not transferred to you.'], 403);
        }

        $visit = Viewing::where('lead_id', $lead->id)->findOrFail($visitId);
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(self::VISIT_STATUSES)],
            'date' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:'.now(self::TZ)->toDateString()],
            'time' => ['sometimes', 'date_format:H:i'],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'outcome_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $changes = [];
        if (isset($data['date']) || isset($data['time'])) {
            $date = $data['date'] ?? $visit->scheduled_date->format('Y-m-d');
            $time = $data['time'] ?? ($visit->scheduled_at ? $visit->scheduled_at->clone()->setTimezone(self::TZ)->format('H:i') : '10:00');
            $startsAt = Carbon::createFromFormat('Y-m-d H:i', "$date $time", self::TZ);
            $visit->fill(['scheduled_date' => $date, 'scheduled_time' => $startsAt->format('h:i A'), 'scheduled_at' => $startsAt->clone()->utc()]);
            $changes[] = 'moved to '.$startsAt->format('j M, g:i A');
        }
        if (array_key_exists('assigned_to', $data)) {
            $guide = $data['assigned_to'] ? User::find($data['assigned_to']) : null;
            $visit->fill(['assigned_to' => $guide?->id, 'assigned_agent' => $guide?->name]);
            $changes[] = $guide ? "guide is now {$guide->name}" : 'guide removed';
        }
        if (isset($data['status']) && $data['status'] !== $visit->status) {
            $visit->status = $data['status'];
            $changes[] = strtolower($data['status']);
        }
        if (array_key_exists('outcome_notes', $data)) {
            $visit->outcome_notes = $data['outcome_notes'];
        }
        $visit->save();

        if ($changes) {
            $this->log($request, $lead, 'visit', $visit->visit_type.' '.implode(', ', $changes), ['visit_id' => $visit->id, 'event' => $visit->status === 'Confirmed' ? 'updated' : strtolower($visit->status)]);
        }
        if (($data['status'] ?? null) === 'Completed') {
            if ($visit->outcome_notes) {
                $this->log($request, $lead, 'note', 'Visit outcome: '.$visit->outcome_notes);
            }
            if ($lead->status === 'Site Visit') {
                $this->moveStage($request, $lead, 'Visited');
                $lead->save();
            }
        }

        return response()->json(['success' => true, 'data' => $this->visit($visit->fresh())]);
    }

    private function moveStage(Request $request, Lead $lead, string $stage, ?string $lostReason = null): void
    {
        $from = $lead->status ?: 'New';
        if (strcasecmp($from, $stage) === 0 && $stage !== 'Lost') {
            return;
        }
        $lead->status = $stage;
        $lead->lost_reason = $stage === 'Lost' ? $lostReason : null;
        $this->log($request, $lead, 'stage', $stage === 'Lost' && $lostReason ? "Reason: {$lostReason}" : null, ['from' => $from, 'to' => $stage]);
    }

    /** @param  array<string, mixed>  $meta */
    private function log(Request $request, Lead $lead, string $type, ?string $body, array $meta = []): void
    {
        $user = $request->user();
        if ($user) {
            $meta['by_user_id'] = $user->id;
            $meta['by_user_name'] = $user->name;
            $meta['by_user_role'] = $user->role;
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user?->id,
            'type' => $type,
            'body' => $body,
            'meta' => $meta ?: null,
            'occurred_at' => now(),
        ]);
    }

    /** @return array<string, mixed> */
    private function activity(LeadActivity $a): array
    {
        $meta = $a->meta ?? [];
        $user = $a->user
            ? ['id' => $a->user->id, 'name' => $a->user->name, 'role' => $a->user->role]
            : (! empty($meta['by_user_name'])
                ? ['id' => $a->user_id ?? ($meta['by_user_id'] ?? null), 'name' => $meta['by_user_name'], 'role' => $meta['by_user_role'] ?? null]
                : null);

        return [
            'id' => $a->id, 'type' => $a->type, 'body' => $a->body, 'meta' => $meta,
            'user' => $user,
            'occurred_at' => $a->occurred_at,
        ];
    }

    public static function attachActivitySummary($leads): void
    {
        $collection = $leads instanceof Lead ? collect([$leads]) : collect($leads);
        if ($collection->isEmpty()) {
            return;
        }

        $leadIds = $collection->pluck('id');
        $allActivities = LeadActivity::with('user:id,name,role')
            ->whereIn('lead_id', $leadIds)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get(['id', 'lead_id', 'user_id', 'type', 'body', 'meta', 'occurred_at'])
            ->groupBy('lead_id');

        $formatUser = function (LeadActivity $a): ?array {
            $meta = $a->meta ?? [];
            if ($a->user) {
                return ['id' => $a->user->id, 'name' => $a->user->name, 'role' => $a->user->role];
            }
            if (! empty($meta['by_user_name'])) {
                return [
                    'id' => $a->user_id ?? ($meta['by_user_id'] ?? null),
                    'name' => $meta['by_user_name'],
                    'role' => $meta['by_user_role'] ?? null,
                ];
            }

            return null;
        };

        $latestActivities = $allActivities->map(function ($items) use ($formatUser) {
            $a = $items->firstWhere(fn ($act) => in_array($act->type, ['note', 'call', 'whatsapp', 'email', 'manual'], true));
            if (! $a) {
                return null;
            }
            $u = $formatUser($a);

            return [
                'id' => $a->id,
                'type' => $a->type,
                'outcome' => $a->meta['outcome'] ?? null,
                'body' => $a->body ? \Illuminate\Support\Str::limit($a->body, 140) : null,
                'at' => $a->occurred_at?->toISOString() ?? (string) $a->occurred_at,
                'user' => $u,
                'user_name' => $u['name'] ?? null,
            ];
        });

        $latestAnyActivity = $allActivities->map(function ($items) use ($formatUser) {
            $a = $items->first();
            if (! $a) {
                return null;
            }
            $u = $formatUser($a);

            return [
                'id' => $a->id,
                'type' => $a->type,
                'outcome' => $a->meta['outcome'] ?? null,
                'body' => $a->body ? \Illuminate\Support\Str::limit($a->body, 140) : null,
                'meta' => $a->meta ?? [],
                'at' => $a->occurred_at?->toISOString() ?? (string) $a->occurred_at,
                'user' => $u,
                'user_name' => $u['name'] ?? null,
            ];
        });

        $latestStageActivity = $allActivities->map(function ($items) use ($formatUser) {
            $a = $items->firstWhere('type', 'stage');
            if (! $a) {
                return null;
            }
            $u = $formatUser($a);

            return [
                'id' => $a->id,
                'from' => $a->meta['from'] ?? null,
                'to' => $a->meta['to'] ?? null,
                'body' => $a->body,
                'at' => $a->occurred_at?->toISOString() ?? (string) $a->occurred_at,
                'user' => $u,
                'user_name' => $u['name'] ?? null,
            ];
        });

        $collection->each(function (Lead $lead) use ($latestActivities, $latestAnyActivity, $latestStageActivity) {
            $lead->setAttribute('last_activity', $latestActivities->get($lead->id));
            $lead->setAttribute('recent_activity', $latestAnyActivity->get($lead->id));
            $lead->setAttribute('last_stage_activity', $latestStageActivity->get($lead->id));
        });
    }

    /** @return array<string, mixed> */
    private function visit(Viewing $v): array
    {
        return [
            'id' => $v->id, 'lead_id' => $v->lead_id,
            'date' => $v->scheduled_date?->format('Y-m-d'),
            'time' => $v->scheduled_at ? $v->scheduled_at->clone()->setTimezone(self::TZ)->format('H:i') : null,
            'time_label' => $v->scheduled_time,
            'visit_type' => $v->visit_type, 'status' => $v->status,
            'property_title' => $v->property_title,
            'assigned_to' => $v->assigned_to, 'assigned_agent' => $v->assigned_agent,
            'vip_pickup' => (bool) $v->vip_pickup, 'pickup_location' => $v->pickup_location,
            'notes' => $v->notes, 'outcome_notes' => $v->outcome_notes,
        ];
    }

    /** @return list<array{id: int, name: string, role: string|null}> */
    private function staff(): array
    {
        return User::orderBy('name')->get()
            ->filter(fn (User $u) => $u->isStaff() && strtolower((string) $u->status) !== 'suspended')
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role])
            ->values()->all();
    }

    private function canAccessLead(Request $request, Lead $lead): bool
    {
        $user = $request->user();
        if (! $user) {
            return false;
        }
        if ($user->isLeadAdmin()) {
            return true;
        }

        return (int) $lead->assigned_to === (int) $user->id;
    }
}
