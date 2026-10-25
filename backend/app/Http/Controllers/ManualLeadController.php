<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManualLeadRequest;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

/**
 * POST /api/leads/manual: the CRM's "Log Buyer Inquiry" form.
 *
 * Kept apart from the public website form (PropertyLeadController) so a lead the team types in is never linked to the
 * staff member's own browsing and never reported to Meta as an ad conversion.
 */
class ManualLeadController extends Controller
{
    /** Channels where the team has already spoken with the buyer, so the lead starts as Contacted. */
    private const LIVE_CHANNELS = ['Phone call', 'Messenger', 'WhatsApp', 'Walk-in'];

    public function store(StoreManualLeadRequest $request): JsonResponse
    {
        $data = $request->validated();

        $existing = Lead::withTrashed()->where('phone', $data['phone'])->latest('id')->first();
        if ($existing && empty($data['allow_duplicate'])) {
            return response()->json([
                'success' => false,
                'message' => 'A buyer with this number is already in the CRM.',
                'data' => ['duplicate' => [
                    'id' => $existing->id, 'name' => $existing->name, 'status' => $existing->status,
                    'property_title' => $existing->property_title, 'deleted' => $existing->trashed(), 'created_at' => $existing->created_at,
                ]],
            ], 409);
        }

        $user = $request->user();
        if ($user && ! $user->isLeadAdmin()) {
            $owner = $user;
        } else {
            $owner = ! empty($data['assigned_to']) ? User::find($data['assigned_to']) : null;
            if ($owner && ! $owner->isStaff()) {
                return response()->json(['success' => false, 'message' => 'Leads can only be assigned to team members.', 'errors' => ['assigned_to' => ['Leads can only be assigned to team members.']]], 422);
            }
        }

        $property = ! empty($data['property_id']) ? Property::find($data['property_id']) : null;
        $isLive = in_array($data['channel'], self::LIVE_CHANNELS, true);

        $lead = Lead::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'property_id' => $property?->id,
            'property_title' => $property?->title ?? ($data['property_title'] ?? null) ?: 'General Inquiry',
            'lead_type' => 'Manual entry',
            'buyer_category' => $data['buyer_category'] ?? null,
            'investment_readiness' => $data['investment_readiness'] ?? null,
            'budget_range' => $data['budget_range'] ?? null,
            'preferred_contact' => $data['preferred_contact'] ?? ($data['channel'] === 'WhatsApp' ? 'WhatsApp' : 'Phone Call'),
            'callback_time' => $data['callback_time'] ?? null,
            'message' => $this->surveyLines($data) ?: null,
            'status' => $isLive ? 'Contacted' : 'New',
            'utm_source' => 'crm_manual',
            'utm_medium' => $data['channel'],
            'form_version' => 'crm_manual',
            'assigned_to' => $owner?->id,
            'last_contacted_at' => $isLive ? now() : null,
            'next_follow_up_at' => ! empty($data['follow_up_at']) ? Carbon::parse($data['follow_up_at'])->utc() : null,
        ]);

        $this->log($request->user()?->id, $lead, 'manual', $data['notes'] ?? null, ['channel' => $data['channel']]);
        if ($owner) {
            $this->log($request->user()?->id, $lead, 'assign', "Assigned to {$owner->name}", ['user_id' => $owner->id]);
        }
        if ($lead->next_follow_up_at) {
            $this->log($request->user()?->id, $lead, 'follow_up', null, ['at' => $lead->next_follow_up_at->toIso8601String()]);
        }

        return response()->json(['success' => true, 'data' => $lead->fresh()], 201);
    }

    /**
     * Same "Key: value" lines the website survey writes, so the CRM reads the score and answers the same way.
     *
     * @param  array<string, mixed>  $data
     */
    private function surveyLines(array $data): string
    {
        return implode("\n", array_filter([
            ! empty($data['lead_score']) ? 'Lead score: '.$data['lead_score'] : null,
            isset($data['answered'], $data['total']) && $data['answered'] > 0
                ? 'Survey: '.($data['answered'] >= $data['total'] ? 'complete' : 'partial')." ({$data['answered']} of {$data['total']} questions)"
                : null,
            ! empty($data['payment']) ? 'Payment plan: '.$data['payment'] : null,
            ! empty($data['residence']) ? 'Lives: '.$data['residence'] : null,
        ]));
    }

    /** @param  array<string, mixed>  $meta */
    private function log(?int $userId, Lead $lead, string $type, ?string $body, array $meta = []): void
    {
        LeadActivity::create([
            'lead_id' => $lead->id, 'user_id' => $userId, 'type' => $type, 'body' => $body,
            'meta' => $meta ?: null, 'occurred_at' => now(),
        ]);
    }
}
