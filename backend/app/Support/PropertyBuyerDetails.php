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
        $numbers = ['roadWidth' => 1000, 'shareLandSize' => 100000, 'depositPercent' => 100, 'ownerCount' => 10000, 'registrationValue' => 1000000000000];
        $dates = ['sourceDate', 'updatedOn'];
        $arrays = ['flatVariants'];
        $keys = array_merge(array_keys($choices), $texts, array_keys($numbers), $dates, $arrays);
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
        $rules['buyer_details.flatVariants'] = ['nullable', 'array', 'max:20'];
        $rules['buyer_details.flatVariants.*.name'] = ['nullable', 'string', 'max:100'];
        $rules['buyer_details.flatVariants.*.sqft'] = ['nullable', 'numeric', 'min:0'];
        $rules['buyer_details.flatVariants.*.bedrooms'] = ['nullable', 'integer', 'min:0'];
        $rules['buyer_details.flatVariants.*.bathrooms'] = ['nullable', 'integer', 'min:0'];
        $rules['buyer_details.flatVariants.*.balconies'] = ['nullable', 'integer', 'min:0'];
        $rules['buyer_details.flatVariants.*.price'] = ['nullable', 'numeric', 'min:0'];
        $rules['buyer_details.flatVariants.*.price_per_sqft'] = ['nullable', 'numeric', 'min:0'];
        $rules['buyer_details.flatVariants.*.floor_available'] = ['nullable', 'string', 'max:150'];
        $rules['buyer_details.flatVariants.*.facing'] = ['nullable', 'string', 'max:50'];
        $rules['buyer_details.flatVariants.*.floor_plan_image'] = ['nullable', 'string', 'max:500'];
        $rules['buyer_details.flatVariants.*.is_available'] = ['nullable', 'boolean'];

        if (is_array($details)) {
            $details = array_map(fn ($value) => is_string($value) ? (trim($value) === '' ? null : trim($value)) : $value, $details);
        }
        $validated = Validator::make(['buyer_details' => $details], $rules)->validate();

        return array_filter($validated['buyer_details'] ?? [], fn ($value) => $value !== null);
    }
}
