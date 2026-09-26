<?php

namespace App\Support;

use App\Models\Property;
use Illuminate\Validation\Rule;

/**
 * What a property owner may send when listing a property, and how it maps onto the property record.
 * Private facts (exact address, papers, expected price) go to owner_details, which only staff and the owner see.
 */
class OwnerListing
{
    public const PROPERTY_TYPES = ['Plot', 'Land', 'Land Share', 'Flat', 'Duplex', 'Penthouse', 'Commercial', 'Hotel'];

    public const LAND_UNITS = ['Katha', 'Bigha', 'Shotok', 'Decimal', 'Sqft'];

    public const EDITABLE_STATES = ['draft', 'submitted', 'changes_requested'];

    /**
     * Columns of the property record an owner fills in.
     *
     * @var list<string>
     */
    public const PROPERTY_FIELDS = [
        'title', 'property_type', 'state', 'city', 'area_name', 'address', 'land_size', 'land_unit', 'square_footage',
        'total_floors', 'bedrooms', 'bathrooms', 'parking', 'facing', 'completion_status', 'description', 'images',
    ];

    /**
     * @return array<string, list<mixed>>
     */
    public static function rules(bool $isDraft): array
    {
        $required = $isDraft ? 'nullable' : 'required';

        return [
            'title' => [$required, 'string', 'max:160'],
            'property_type' => [$required, Rule::in(self::PROPERTY_TYPES)],
            'state' => ['nullable', 'string', 'max:80'],
            'city' => [$required, 'string', 'max:80'],
            'area_name' => [$required, 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:200'],
            'land_size' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'land_unit' => ['nullable', Rule::in(self::LAND_UNITS)],
            'square_footage' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'total_floors' => ['nullable', 'integer', 'min:0', 'max:200'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:100'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:100'],
            'parking' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'facing' => ['nullable', Rule::in(['North', 'South', 'East', 'West', 'North-East', 'North-West', 'South-East', 'South-West'])],
            'completion_status' => ['nullable', Rule::in(['Ready', 'Under Construction', 'Upcoming Project'])],
            'description' => ['nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['string', 'max:300', 'regex:#^/storage/properties/[A-Za-z0-9_.\-]+$#'],

            'owner_details' => ['nullable', 'array'],
            'owner_details.fullAddress' => [$required, 'string', 'max:300'],
            'owner_details.mouza' => ['nullable', 'string', 'max:120'],
            'owner_details.dagNumbers' => ['nullable', 'string', 'max:200'],
            'owner_details.khatianNumbers' => ['nullable', 'string', 'max:200'],
            'owner_details.landUse' => ['nullable', Rule::in(['Residential', 'Commercial', 'Mixed', 'Agricultural'])],
            'owner_details.cornerPlot' => ['nullable', Rule::in(['Yes', 'No'])],
            'owner_details.roadWidth' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'owner_details.buildingDescription' => ['nullable', 'string', 'max:500'],

            'owner_details.submitterRole' => [$required, Rule::in(['Owner', 'Co-owner', 'Authorized representative'])],
            'owner_details.ownerCount' => [$required, 'integer', 'min:1', 'max:1000'],
            'owner_details.allOwnersAgree' => [$required, Rule::in(['Yes', 'No', 'Not sure'])],
            'owner_details.ownershipSource' => [$required, Rule::in(['Purchase', 'Inheritance', 'Allotment', 'Gift', 'Other'])],
            'owner_details.possession' => [$required, Rule::in(['Owner', 'Tenant', 'Vacant', 'Other'])],
            'owner_details.bankLoan' => [$required, Rule::in(['None', 'Exists'])],
            'owner_details.existingAgreement' => [$required, Rule::in(['None', 'Exists'])],
            'owner_details.mutationStatus' => [$required, Rule::in(['Done', 'Pending', 'Not done'])],
            'owner_details.taxPaidThrough' => ['nullable', 'string', 'max:40'],
            'owner_details.disputeOrCase' => [$required, Rule::in(['No', 'Yes'])],
            'owner_details.disputeNote' => ['nullable', 'string', 'max:1000'],

            'owner_details.expectedPrice' => [$required, 'numeric', 'min:0', 'max:1000000000000'],
            'owner_details.priceBasis' => [$required, Rule::in(['Total', 'Per land unit', 'Per sqft'])],
            'owner_details.negotiable' => ['nullable', Rule::in(['Yes', 'No'])],
            'owner_details.sellTimeline' => ['nullable', Rule::in(['Within 1 month', '1–3 months', '3–6 months', 'No rush'])],
            'owner_details.bestTimeToCall' => ['nullable', 'string', 'max:80'],
            'owner_details.notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function toAttributes(array $validated, ?Property $existing = null): array
    {
        $attributes = [];
        foreach (self::PROPERTY_FIELDS as $field) {
            if (array_key_exists($field, $validated)) {
                $attributes[$field] = $validated[$field];
            }
        }

        if (array_key_exists('owner_details', $validated)) {
            $details = array_merge($existing?->owner_details ?? [], array_filter(
                $validated['owner_details'] ?? [],
                fn ($value) => $value !== null && $value !== ''
            ));
            $attributes['owner_details'] = $details;
            if (isset($details['expectedPrice'])) {
                $attributes['price'] = (float) $details['expectedPrice'];
            }
        }

        foreach (['bedrooms', 'bathrooms', 'parking'] as $integerField) {
            if (array_key_exists($integerField, $attributes) && $attributes[$integerField] === null) {
                $attributes[$integerField] = 0;
            }
        }

        return $attributes;
    }

    /**
     * Required document types the owner has not uploaded yet (rejected uploads do not count).
     *
     * @return list<array{key: string, label: string}>
     */
    public static function missingRequiredDocuments(Property $property): array
    {
        $uploaded = $property->documents()->where('status', '!=', 'rejected')->pluck('document_type')->all();

        return collect(SiteSettings::documentTypes())
            ->filter(fn (array $type) => ! empty($type['required']) && ! in_array($type['key'], $uploaded, true))
            ->map(fn (array $type) => ['key' => $type['key'], 'label' => $type['label']])
            ->values()
            ->all();
    }
}
