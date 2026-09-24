<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PropertyBuyerDetails
{
    public static function validate(mixed $details): array
    {
        $choices = [
            'landUse' => ['Residential', 'Commercial', 'Mixed'],
            'cornerPlot' => ['Yes', 'No'],
            'priceBasis' => ['Total', 'Per land unit', 'Per sqft', 'Per share'],
            'negotiable' => ['Yes', 'No'],
            'ownershipSource' => ['Purchase', 'Inheritance', 'Allotment', 'Other'],
            'possession' => ['Owner', 'Tenant', 'Vacant', 'Other'],
            'bankLoan' => ['None declared', 'Exists'],
            'existingAgreement' => ['None declared', 'Exists'],
            'mutationStatus' => ['Available', 'Pending', 'Unavailable'],
        ];
        $texts = ['buildingDescription', 'utilities', 'priceIncludes', 'agreementDuration', 'paymentSchedule', 'paymentMethod', 'registrationCost', 'buyerCosts', 'sellerCosts', 'transferTimeline', 'transferTrigger', 'saleAuthority', 'ownershipNotes', 'taxPaidThrough', 'serviceChargeStatus', 'approvalDetails', 'documentSummary'];
        $numbers = ['roadWidth' => 1000, 'depositPercent' => 100, 'buyerCommission' => 100, 'sellerCommission' => 100, 'ownerCount' => 10000, 'registrationValue' => 1000000000000];
        $dates = ['sourceDate', 'updatedOn'];
        $keys = array_merge(array_keys($choices), $texts, array_keys($numbers), $dates);
        $rules = ['buyer_details' => ['nullable', 'array:'.implode(',', $keys)]];
        foreach ($choices as $key => $options) {
            $rules['buyer_details.'.$key] = ['nullable', Rule::in($options)];
        }
        foreach ($texts as $key) {
            $rules['buyer_details.'.$key] = ['nullable', 'string', 'max:2000'];
        }
        foreach ($numbers as $key => $max) {
            $rules['buyer_details.'.$key] = ['nullable', $key === 'ownerCount' ? 'integer' : 'numeric', 'min:0', 'max:'.$max];
        }
        foreach ($dates as $key) {
            $rules['buyer_details.'.$key] = ['nullable', 'date_format:Y-m-d'];
        }
        if (is_array($details)) {
            $details = array_map(fn ($value) => is_string($value) ? (trim($value) === '' ? null : trim($value)) : $value, $details);
        }
        $validated = Validator::make(['buyer_details' => $details], $rules)->validate();

        return array_filter($validated['buyer_details'] ?? [], fn ($value) => $value !== null);
    }
}
