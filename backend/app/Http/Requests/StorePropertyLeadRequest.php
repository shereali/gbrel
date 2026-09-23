<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = strtr((string) $this->input('phone', ''), array_combine(
            preg_split('//u', '০১২৩৪৫৬৭৮৯', -1, PREG_SPLIT_NO_EMPTY), range(0, 9)
        ));
        $phone = preg_replace('/[\s().-]/u', '', $phone);
        if (preg_match('/^01[3-9]\d{8}$/', $phone)) {
            $phone = '+88'.$phone;
        } elseif (preg_match('/^8801[3-9]\d{8}$/', $phone)) {
            $phone = '+'.$phone;
        } elseif (str_starts_with($phone, '00')) {
            $phone = '+'.substr($phone, 2);
        }
        $this->merge(['phone' => $phone, 'name' => trim((string) $this->input('name', ''))]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $qualified = $this->input('form_version') === 'property_inquiry_v1';

        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(?:\+8801[3-9]\d{8}|\+(?!880)[1-9]\d{7,14})$/'],
            'email' => ['nullable', 'email', 'max:254'],
            'property_id' => [Rule::requiredIf($qualified), 'nullable', 'integer', 'exists:properties,id'],
            'property_title' => ['nullable', 'string', 'max:255'],
            'property' => ['nullable', 'string', 'max:255'],
            'lead_type' => ['nullable', 'string', 'max:255'],
            'buyer_type' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'buyer_category' => [Rule::requiredIf($qualified), 'nullable', 'string', 'max:255'],
            'investment_readiness' => [Rule::requiredIf($qualified), 'nullable', 'string', 'max:255'],
            'budget_range' => [Rule::requiredIf($qualified), 'nullable', 'string', 'max:255'],
            'preferred_contact' => [Rule::requiredIf($qualified), 'nullable', Rule::in(['Phone Call', 'WhatsApp', 'Email'])],
            'message' => ['nullable', 'string', 'max:5000'],
            'utm_source' => ['nullable', 'string', 'max:200'],
            'utm_medium' => ['nullable', 'string', 'max:200'],
            'utm_campaign' => ['nullable', 'string', 'max:200'],
            'utm_content' => ['nullable', 'string', 'max:200'],
            'utm_term' => ['nullable', 'string', 'max:200'],
            'callback_time' => ['nullable', 'string', 'max:255'],
            'next_step' => ['nullable', 'string', 'max:255'],
            'form_version' => ['nullable', Rule::in(['property_inquiry_v1'])],
            'contact_consent' => $qualified ? ['required', 'accepted'] : ['nullable', 'boolean'],
            'request_id' => [Rule::requiredIf($qualified), 'nullable', 'uuid'],
        ];
    }
}
