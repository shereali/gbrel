<?php

namespace App\Http\Requests;

use App\Http\Controllers\BookDownloadController;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * The /book order form: the same name, phone and ad fields as a property lead, plus the optional
 * "why do you want this book?" answer, which tells the sales team what to call the buyer about.
 */
class StoreBookLeadRequest extends StorePropertyLeadRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'reason' => ['nullable', 'string', Rule::in(array_keys(BookDownloadController::REASONS))],
        ];
    }
}
