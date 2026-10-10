<?php

namespace App\Http\Requests;

use App\Http\Controllers\BookDownloadController;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * The /book download form: the same name, phone and ad fields as a property lead, plus the one question
 * the visitor answers before downloading ("why do you want this book?").
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
            'reason' => ['required', 'string', Rule::in(array_keys(BookDownloadController::REASONS))],
        ];
    }
}
