<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A buyer the team logs by hand in the CRM (a phone call, a Messenger chat, someone at the office).
 * The route sits under /api/leads/*, so ProtectLeadData already requires leads.manage.
 */
class StoreManualLeadRequest extends FormRequest
{
    /** Where the buyer reached the team. */
    public const CHANNELS = ['Phone call', 'Messenger', 'WhatsApp', 'Walk-in', 'Referral', 'Facebook comment', 'Other'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize((string) $this->input('phone', '')),
            'name' => trim((string) $this->input('name', '')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::PATTERN],
            'email' => ['nullable', 'email', 'max:254'],
            'channel' => ['required', Rule::in(self::CHANNELS)],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'property_title' => ['nullable', 'string', 'max:255'],
            'buyer_category' => ['nullable', 'string', 'max:255'],
            'investment_readiness' => ['nullable', 'string', 'max:255'],
            'budget_range' => ['nullable', 'string', 'max:255'],
            'payment' => ['nullable', 'string', 'max:255'],
            'residence' => ['nullable', 'string', 'max:255'],
            'callback_time' => ['nullable', 'string', 'max:100'],
            'preferred_contact' => ['nullable', Rule::in(['Phone Call', 'WhatsApp', 'Email'])],
            'lead_score' => ['nullable', 'regex:/^(HOT|WARM|COLD) \(\d{1,2}\/\d{1,2}\)$/'],
            'answered' => ['nullable', 'integer', 'min:0', 'max:20'],
            'total' => ['nullable', 'integer', 'min:1', 'max:20'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'follow_up_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'allow_duplicate' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid mobile number, e.g. 01712345678, or a foreign number with its country code (+…).',
            'name.required' => 'Enter the buyer\'s name.',
            'channel.required' => 'Choose how the buyer reached us.',
        ];
    }
}
