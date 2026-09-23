<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyLeadRequest;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Http\JsonResponse;

class PropertyLeadController extends Controller
{
    public function store(StorePropertyLeadRequest $request): JsonResponse
    {
        $input = $request->validated();
        $property = ! empty($input['property_id']) ? Property::findOrFail($input['property_id']) : null;
        $fields = [
            'property_id' => $property?->id,
            'property_title' => $property?->title ?? $input['property_title'] ?? $input['property'] ?? 'General Inquiry',
            'name' => $input['name'], 'phone' => $input['phone'], 'email' => $input['email'] ?? null,
            'lead_type' => $input['buyer_category'] ?? $input['lead_type'] ?? $input['buyer_type'] ?? $input['type'] ?? 'Direct Inquiry',
            'buyer_category' => $input['buyer_category'] ?? null,
            'investment_readiness' => $input['investment_readiness'] ?? null,
            'budget_range' => $input['budget_range'] ?? null,
            'preferred_contact' => $input['preferred_contact'] ?? 'Phone Call',
            'message' => $input['message'] ?? null,
            'status' => 'New',
            'utm_source' => $input['utm_source'] ?? null,
            'utm_medium' => $input['utm_medium'] ?? null,
            'utm_campaign' => $input['utm_campaign'] ?? null,
            'utm_content' => $input['utm_content'] ?? null,
            'utm_term' => $input['utm_term'] ?? null,
            'callback_time' => $input['callback_time'] ?? null,
            'next_step' => $input['next_step'] ?? null,
            'form_version' => $input['form_version'] ?? null,
            'contact_consented_at' => ! empty($input['contact_consent']) ? now() : null,
        ];
        $lead = ! empty($input['request_id'])
            ? Lead::firstOrCreate(['request_id' => $input['request_id']], $fields)
            : Lead::create($fields);

        return response()->json(['success' => true, 'data' => ['id' => $lead->id]], $lead->wasRecentlyCreated ? 201 : 200);
    }
}
