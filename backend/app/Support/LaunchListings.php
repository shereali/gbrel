<?php

namespace App\Support;

use App\Models\Property;
use Illuminate\Support\Facades\Schema;

/**
 * Corrections for the four launch listings seeded from the owners' PDFs: price quoted the way buyers compare
 * (per katha for land), Bangla facts taken from the papers only, illustrations instead of stock photos (until real photos are uploaded), no owner names in public text.
 */
class LaunchListings
{
    public static function correct(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }

        // Keyed by the slug the seeder used, so a listing staff already replaced is never touched.
        $listings = [
            'gulshan-1-135-6-15-katha-building' => [
                'image' => '/img/properties/gulshan-1-15-katha-site.jpg',
                'property_type' => 'Land',
                'price' => 60000000,
                'tagline' => '১৫ কাঠা জমি ও পুরাতন ২ তলা দালান | প্রতি কাঠা ৳ ৬ কোটি (আলোচনা সাপেক্ষ)',
                'amenities' => ['১৫ কাঠা জমি', 'পুরাতন ২ তলা দালান', 'জমি মালিকের দখলে', 'নামজারি ও খাজনা পরিশোধিত (বিক্রেতার তথ্য)', 'লেনদেন ব্যাংকের মাধ্যমে'],
                'parking' => 0,
                'details' => ['priceBasis' => 'Per land unit', 'saleAuthority' => 'অপ্রত্যাহারযোগ্য পাওয়ার অব অ্যাটর্নিপ্রাপ্ত প্রতিনিধির মাধ্যমে'],
                'replace' => [' গনিউর রহমান গং বর্তমানে অপ্রত্যাহার যোগ্য পাওয়ার বলে জমির মালিক।' => ' অপ্রত্যাহারযোগ্য পাওয়ার অব অ্যাটর্নিপ্রাপ্ত প্রতিনিধির মাধ্যমে বিক্রি হবে।'],
            ],
            'gulshan-2-road-92-plot-06-17-katha-building' => [
                'image' => '/img/properties/gulshan-2-road-92-17-katha-site.jpg',
                'property_type' => 'Land',
                'price' => 70000000,
                'amenities' => ['১৭.১৮ কাঠা আবাসিক জমি', '২ তলা ভবন', 'জমি ও দখল মালিকের অধীনে', 'বিক্রয় অনুমতি ও সার্ভিস চার্জ বিক্রেতা বহন করবেন', 'লেনদেন ব্যাংকের মাধ্যমে'],
                'parking' => 0,
                'details' => ['priceBasis' => 'Per land unit'],
            ],
            'gulshan-2-road-48-4b-31-katha-commercial-corner-plot' => [
                'image' => '/img/properties/gulshan-2-31-katha-corner-site.jpg',
                'property_type' => 'Plot',
                'price' => 140000000,
                'amenities' => ['৩১ কাঠা বাণিজ্যিক কর্নার প্লট', '২ তলা দালান', 'নামজারি সম্পন্ন (বিক্রেতার তথ্য)', 'বিক্রয় অনুমতি ও সার্ভিস চার্জ বিক্রেতা বহন করবেন', 'লেনদেন ব্যাংকের মাধ্যমে'],
                'parking' => 0,
                'details' => ['priceBasis' => 'Per land unit'],
            ],
            'lake-view-gulshan-1-road-8-23-katha-building' => [
                'image' => '/img/properties/lake-view-gulshan-1-23-katha-site.jpg',
                'title' => 'লেক ভিউ — গুলশান-১, রোড ৮-এ ২৩ কাঠা জমিসহ ৬ তলা ভবন',
                'property_type' => 'Land',
                'price' => 1350000000,
                'amenities' => ['২৩ কাঠা জমি', '৬ তলা ভবন', '২৮টি কার পার্কিং', 'ইউটিলিটি সংযোগ হালনাগাদ', 'কোনো ব্যাংক ঋণ নেই (বিক্রেতার তথ্য)'],
                'details' => ['priceBasis' => 'Total', 'saleAuthority' => 'মালিকের নিয়োগকৃত প্রতিনিধির মাধ্যমে', 'landUse' => null, 'roadWidth' => null],
                'replace' => [' (মূল মালিক মোঃ তওফিকুল ইসলাম)' => ''],
            ],
        ];

        foreach ($listings as $slug => $fix) {
            $property = Property::where('slug', $slug)->first();
            if (! $property) {
                continue;
            }

            $details = is_array($property->buyer_details) ? $property->buyer_details : [];
            foreach ($fix['details'] as $key => $value) {
                if ($value === null) {
                    unset($details[$key]);
                } else {
                    $details[$key] = $value;
                }
            }

            $description = (string) $property->description;
            foreach ($fix['replace'] ?? [] as $from => $to) {
                $description = str_replace($from, $to, $description);
            }

            $images = is_array($property->images) ? $property->images : [];
            $onlyStockPhotos = $images !== [] && collect($images)->every(fn ($url) => str_contains((string) $url, 'images.unsplash.com'));

            $property->forceFill(array_filter([
                'title' => $fix['title'] ?? null,
                'tagline' => $fix['tagline'] ?? null,
            ]) + [
                'property_type' => $fix['property_type'],
                'price' => $fix['price'],
                'price_unit' => null,
                'description' => $description,
                'amenities' => $fix['amenities'],
                'buyer_details' => $details,
                // These were never in the owner's papers.
                'bedrooms' => 0,
                'bathrooms' => 0,
                'balconies' => 0,
                'square_footage' => null,
                'facing' => null,
                'year_built' => null,
                'floor_number' => null,
            ] + (array_key_exists('parking', $fix) ? ['parking' => $fix['parking']] : []) + ($images === [] || $onlyStockPhotos ? ['images' => [$fix['image']]] : []))->save();
        }
    }
}
