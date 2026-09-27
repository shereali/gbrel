<?php

use App\Models\Property;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Remove buyerCommission and sellerCommission from all existing property buyer_details.
     */
    public function up(): void
    {
        Property::all()->each(function (Property $property) {
            $details = $property->buyer_details;
            if (is_array($details) && (isset($details['buyerCommission']) || isset($details['sellerCommission']))) {
                unset($details['buyerCommission'], $details['sellerCommission']);
                $property->buyer_details = $details;
                $property->save();
            }
        });
    }

    public function down(): void
    {
    }
};
