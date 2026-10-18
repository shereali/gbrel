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
            unset($details['buyerCommission'], $details['sellerCommission']);
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

    /**
     * Rewrites the Lake View listing's seller text in GBREL's voice: plain Bangla, the seller's claims marked as the
     * seller's, and the RAJUK timeline stated as an estimate. A field is only replaced while it still holds the
     * original text, so anything staff have since edited in the admin panel is left alone.
     */
    public static function rewriteCopy(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }

        $property = Property::where('slug', 'lake-view-gulshan-1-road-8-23-katha-building')->first();
        if (! $property) {
            return;
        }

        $columns = [
            'title' => [
                'লেক ভিউ — গুলশান-১, রোড ৮-এ ২৩ কাঠা জমিসহ ৬ তলা ভবন',
                'গুলশান-১, রোড ৮-এ ২৩ কাঠা জমিসহ ৬ তলা ভবন "লেক ভিউ"',
            ],
            'tagline' => [
                'লেক ভিউ | ২৩ কাঠা জমিতে ৬ তলা ভবন ও ২৮টি কার পার্কিং | মূল্য ১৩৫ কোটি (আলোচনা সাপেক্ষ)',
                '২৩ কাঠা জমি | ৬ তলা ভবন | ২৮টি গাড়ির পার্কিং | মোট ৳ ১৩৫ কোটি, আলোচনা সাপেক্ষ',
            ],
            'description' => [
                'লেক ভিউ — বাড়ি #১০, রোড #৮, গুলশান-১, ঢাকা এ অবস্থিত ২৩ কাঠা জমির উপর ৬ তলা বিশিষ্ট সুদৃশ্য ভবন। ইউটিলিটি সরবরাহ সম্পূর্ণ আপডেটেড এবং ২৮টি কার পার্কিং সুবিধা রয়েছে। বর্তমান মালিক ২ জন। জমি মালিকের সরাসরি দখলে এবং নামজারি ও খাজনা পরিশোধিত। সম্পূর্ণ নিষ্কণ্টক ও টোটাল কাগজ-পাতি আপডেট কমপ্লিট। কোনো ব্যাংক লোন নেই। বিক্রিত মূল্যের ৩০% বায়না। রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ কার্যদিবস। ৩০০ টাকার নন-জুডিশিয়াল স্ট্যাম্পে হস্তান্তর চুক্তি সম্পন্ন হবে।',
                implode("\n\n", [
                    'গুলশান-১-এর রোড ৮-এ (বাড়ি ১০) ২৩ কাঠা জমির ওপর ৬ তলা ভবন "লেক ভিউ"। ভবনে ২৮টি গাড়ি রাখার জায়গা, নিজস্ব বিদ্যুৎ সাবস্টেশন, গ্যাস সংযোগ ও গভীর নলকূপ আছে।',
                    'মালিক ২ জন, জমি তাঁদের দখলে। বিক্রেতার তথ্য অনুযায়ী নামজারি ও খাজনা হালনাগাদ এবং কোনো ব্যাংক ঋণ নেই। সব কাগজ সাইট ভিজিটে মূল কপির সঙ্গে মিলিয়ে দেখাব।',
                    'বিক্রয়মূল্যের ৩০% বায়না, লেনদেন ব্যাংক ড্রাফট বা পে-অর্ডারে। বায়নার পর রাজউকের বিক্রয় অনুমতির আবেদন হবে। বিক্রেতার হিসাবে এতে সাধারণত ১৫ কার্যদিবসের মতো লাগে, তবে সময়টি রাজউকের ওপর নির্ভর করে।',
                ]),
            ],
        ];

        $details = [
            'buildingDescription' => [
                'লেক ভিউ: ২৩ কাঠা জমিতে ৬ তলা বিশিষ্ট আধুনিক ভবন। কার পার্কিং সংখ্যা ২৮টি।',
                '২৩ কাঠা জমিতে ৬ তলা ভবন "লেক ভিউ", ২৮টি গাড়ির পার্কিং।',
            ],
            'utilities' => [
                'ইউটিলিটি সরবরাহ সম্পূর্ণ আপডেট—বিদ্যুৎ সাবস্টেশন, গ্যাস সংযোগ ও গভীর নলকূপ ওয়াসা।',
                'নিজস্ব বিদ্যুৎ সাবস্টেশন, গ্যাস সংযোগ, গভীর নলকূপ ও ওয়াসার পানি (বিক্রেতার তথ্য)।',
            ],
            'transferTimeline' => [
                'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ (পনের) কার্যদিবস।',
                'বায়নার পর রাজউকের বিক্রয় অনুমতির আবেদন হবে। বিক্রেতার হিসাবে অনুমতি পেতে সাধারণত ১৫ কার্যদিবসের মতো লাগে; সময়টি রাজউকের ওপর নির্ভর করে।',
            ],
            'transferTrigger' => [
                'রাজউক অনুমোদন ও যৌথ চুক্তি সম্পাদনের পর।',
                'বায়না চুক্তি সম্পাদন ও রাজউকের বিক্রয় অনুমতি পাওয়ার পর।',
            ],
            'ownershipNotes' => [
                'মালিক ২ জন, কোনো ব্যাংক লোন বা আইনি ঝামেলা নেই, সম্পূর্ণ নিষ্কণ্টক।',
                'বিক্রেতার তথ্য অনুযায়ী মালিক ২ জন, কোনো ব্যাংক ঋণ বা মামলা নেই। কাগজ দেখে নিশ্চিত হয়ে নিন।',
            ],
            'approvalDetails' => [
                'রাজউক অনুমোদিত ভবন, বিক্রয়ের অনুমতি প্রাপ্তি ১৫ কার্যদিবসের মধ্যে।',
                'বিক্রেতার তথ্য অনুযায়ী ভবনের নকশা রাজউক অনুমোদিত। বিক্রয়ের জন্য রাজউকের অনুমতি বায়নার পর নেওয়া হবে।',
            ],
            'documentSummary' => [
                'নিষ্কণ্টক / টোটাল কাগজ-পাতি আপডেট কমপ্লিট।',
                'বিক্রেতা জানিয়েছেন দলিল, নামজারি ও খাজনার কাগজ হালনাগাদ। কাগজের তালিকা নিচে দেওয়া আছে; সাইট ভিজিটে মূল কপি দেখানো হবে।',
            ],
        ];

        foreach ($columns as $column => [$original, $rewrite]) {
            if (self::sameText($property->{$column}, $original)) {
                $property->{$column} = $rewrite;
            }
        }

        $buyerDetails = is_array($property->buyer_details) ? $property->buyer_details : [];
        foreach ($details as $key => [$original, $rewrite]) {
            if (self::sameText($buyerDetails[$key] ?? null, $original)) {
                $buyerDetails[$key] = $rewrite;
            }
        }
        $property->buyer_details = $buyerDetails;

        if ($property->isDirty()) {
            $property->save();
        }
    }

    /**
     * Compares Bangla text regardless of how nukta letters (য়, ড়, ঢ়) were encoded and of spacing.
     */
    private static function sameText(mixed $current, string $expected): bool
    {
        if (! is_string($current)) {
            return false;
        }

        $normalize = fn (string $text): string => preg_replace('/\s+/u', ' ', trim(strtr($text, [
            "\u{09AF}\u{09BC}" => "\u{09DF}",
            "\u{09A1}\u{09BC}" => "\u{09DC}",
            "\u{09A2}\u{09BC}" => "\u{09DD}",
        ])));

        return $normalize($current) === $normalize($expected);
    }

    /**
     * Fallback listings to serve with HTTP 200 during database maintenance/reconnection.
     */
    public static function getFallbackPayload(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'গুলশান ১, ১৩৫/৬ — ১৫ কাঠা জমি ও ২ তলা পুরাতন দালান',
                'slug' => 'gulshan-1-135-6-15-katha-building',
                'tagline' => '১৫ কাঠা জমি ও ২ তলা পুরাতন দালান | মোট মূল্য ৳ ৯০ কোটি (আলোচনা সাপেক্ষ)',
                'description' => 'গুলশান ১, ১৩৫/৬ নম্বরে ১৫ কাঠা জমিতে ২ তলা পুরাতন দালান বিক্রয়ের প্রস্তাব। ৫ জন ওয়ারিশ সূত্রে মালিক, গনিউর রহমান গং বর্তমানে অপ্রত্যাহার যোগ্য পাওয়ার বলে জমির মালিক। বাংলা ১৪৩২ সনের অনলাইন খাজনা ও ২০২৩ সালের নামজারি পরিশোধ করা আছে। সার্ভিস চার্জ ও ডকুমেন্টেশন ফি জমা দেওয়া আছে। সম্পূর্ণ নিষ্কণ্টক এবং টোটাল কাগজ-পাতি আপডেট। জমি মালিকের দখলে আছে। আর্থিক লেনদেন ব্যাংকের মাধ্যমে। রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ (পনের) কার্যদিবস।',
                'address' => 'প্লট ১৩৫/৬, গুলশান-১, ঢাকা-১২১২',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Gulshan-1',
                'price' => 900000000,
                'price_unit' => 'মোট মূল্য ৳ ৯০ কোটি (প্রতি কাঠা ৳ ৬.০০ কোটি আলোচনা সাপেক্ষ)',
                'listing_type' => 'Sale',
                'property_type' => 'Land',
                'status' => 'Active',
                'bedrooms' => 6,
                'bathrooms' => 6,
                'balconies' => 4,
                'square_footage' => 5500,
                'land_size' => 15.0,
                'land_unit' => 'Katha',
                'parking' => 6,
                'floor_number' => 1,
                'total_floors' => 2,
                'facing' => 'South',
                'completion_status' => 'Ready',
                'year_built' => 1998,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => false,
                'latitude' => 23.7808,
                'longitude' => 90.4192,
                'images' => ['/img/properties/gulshan-1-15-katha-site.jpg'],
                'feature_image' => '/img/properties/gulshan-1-15-katha-site.jpg',
                'gallery' => ['/img/properties/gulshan-1-15-katha-site.jpg'],
                'amenities' => ['১৫ কাঠা জমি', 'পুরাতন ২ তলা দালান', 'জমি মালিকের দখলে', 'নামজারি ও খাজনা পরিশোধিত', 'লেনদেন ব্যাংকের মাধ্যমে'],
                'documents_verified' => ['মূল মালিকানা দলিল ও বায়া দলিল', 'নামজারি খতিয়ান (২০২৩)', 'অনলাইন ভূমি উন্নয়ন কর ও খাজনা (১৪৩২)', 'সার্ভিস চার্জ ও ডকুমেন্টেশন ফি রশিদ'],
            ],
            [
                'id' => 2,
                'title' => 'গুলশান ২, রোড ৯২, প্লট ০৬ — ১৭.১৮ কাঠা জমি ও ২ তলা পুরাতন বাড়ি',
                'slug' => 'gulshan-2-road-92-plot-06-17-katha-building',
                'tagline' => '১৭.১৮ কাঠা আবাসিক জমি | মূল্য আলোচনা সাপেক্ষ (ব্যাংক লেনদেন)',
                'description' => 'গুলশান ২, রোড ৯২-এর প্লট ০৬ নম্বরে ১৭.১৮ কাঠা জমিতে ২ তলা পুরাতন বাড়িসহ বিক্রয় প্রস্তাব। ২ জন ওয়ারিশ সূত্রে মালিক। জমি ও দখল সম্পূর্ণ মালিকের নিয়ন্ত্রণে। আর্থিক লেনদেন ব্যাংকের মাধ্যমে হবে। রাজউকের বিক্রয় অনুমতি ও সার্ভিস চার্জ বিক্রেতা নিজ খরচে বহন করবেন। সমস্ত দলিলপত্র হালনাগাদ ও প্রস্তুত।',
                'address' => 'প্লট ০৬, রোড ৯২, গুলশান-২, ঢাকা-১২১২',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Gulshan-2',
                'price' => 70000000,
                'price_unit' => 'প্রতি কাঠা ৳ ৭.০০ কোটি (আলোচনা সাপেক্ষ)',
                'listing_type' => 'Sale',
                'property_type' => 'Land',
                'status' => 'Active',
                'bedrooms' => 5,
                'bathrooms' => 5,
                'balconies' => 3,
                'square_footage' => 4800,
                'land_size' => 17.18,
                'land_unit' => 'Katha',
                'parking' => 4,
                'floor_number' => 1,
                'total_floors' => 2,
                'facing' => 'East',
                'completion_status' => 'Ready',
                'year_built' => 1995,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => false,
                'latitude' => 23.7925,
                'longitude' => 90.4167,
                'images' => ['/img/properties/gulshan-2-road-92-17-katha-site.jpg'],
                'feature_image' => '/img/properties/gulshan-2-road-92-17-katha-site.jpg',
                'gallery' => ['/img/properties/gulshan-2-road-92-17-katha-site.jpg'],
                'amenities' => ['১৭.১৮ কাঠা আবাসিক জমি', '২ তলা ভবন', 'জমি ও দখল মালিকের অধীনে', 'বিক্রয় অনুমতি ও সার্ভিস চার্জ বিক্রেতা বহন করবেন', 'লেনদেন ব্যাংকের মাধ্যমে'],
                'documents_verified' => ['মূল মালিকানা দলিল ও বায়া দলিল', 'নামজারি ও ডি সি আর হালনাগাদ', 'অনলাইন ভূমি উন্নয়ন কর রশিদ', 'রাজউক লে-আউট ও অনুমোদন পত্র'],
            ],
            [
                'id' => 3,
                'title' => 'গুলশান ২, রোড ৪৮, প্লট ৪/বি — ৩১ কাঠা বাণিজ্যিক কর্নার প্লট',
                'slug' => 'gulshan-2-road-48-4b-31-katha-commercial-corner-plot',
                'tagline' => '৩১ কাঠা বাণিজ্যিক কর্নার প্লট ও ২ তলা দালান | প্রতি কাঠা ৳ ১৪ কোটি (আলোচনা সাপেক্ষ)',
                'description' => 'গুলশান ২, রোড ৪৮-এর ৪/বি নম্বরে ৩১ কাঠা জমিতে ২ তলা দালানসহ বাণিজ্যিক কর্নার প্লট বিক্রয়ের সুবর্ণ সুযোগ। ৩ জন ওয়ারিশ সূত্রে মালিক, মোসাম্মৎ নাদিরা বেগম গং। নামজারি সম্পন্ন ও খাজনা হালনাগাদ। রাজউকের বিক্রয় অনুমতি ও সার্ভিস চার্জ বিক্রেতা বহন করবেন। আর্থিক লেনদেন ব্যাংকের মাধ্যমে। বাণিজ্যিক ভবন, করপোরেট হেডকোয়ার্টার বা প্রিমিয়াম ডেভেলপার প্রকল্পের জন্য আদর্শ অবস্থান।',
                'address' => 'প্লট ৪/বি, রোড ৪৮, গুলশান-২, ঢাকা-১২১২',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Gulshan-2',
                'price' => 140000000,
                'price_unit' => 'প্রতি কাঠা ৳ ১৪.০০ কোটি (আলোচনা সাপেক্ষ)',
                'listing_type' => 'Sale',
                'property_type' => 'Plot',
                'status' => 'Active',
                'bedrooms' => 0,
                'bathrooms' => 0,
                'balconies' => 0,
                'square_footage' => 6000,
                'land_size' => 31.0,
                'land_unit' => 'Katha',
                'parking' => 8,
                'floor_number' => 1,
                'total_floors' => 2,
                'facing' => 'South-East',
                'completion_status' => 'Ready',
                'year_built' => 1992,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => false,
                'latitude' => 23.7981,
                'longitude' => 90.4132,
                'images' => ['/img/properties/gulshan-2-31-katha-corner-site.jpg'],
                'feature_image' => '/img/properties/gulshan-2-31-katha-corner-site.jpg',
                'gallery' => ['/img/properties/gulshan-2-31-katha-corner-site.jpg'],
                'amenities' => ['৩১ কাঠা বাণিজ্যিক কর্নার প্লট', '২ তলা দালান', 'নামজারি সম্পন্ন', 'বিক্রয় অনুমতি ও সার্ভিস চার্জ বিক্রেতা বহন করবেন', 'লেনদেন ব্যাংকের মাধ্যমে'],
                'documents_verified' => ['মূল মালিকানা দলিল ও বায়া দলিল', 'নামজারি খতিয়ান ও ডি সি আর', 'অনলাইন ভূমি উন্নয়ন কর রশিদ', 'বাণিজ্যিক রূপান্তর অনাপত্তিপত্র'],
            ],
            [
                'id' => 4,
                'title' => 'লেক ভিউ — গুলশান-১, রোড ৮-এ ২৩ কাঠা জমিসহ ৬ তলা ভবন',
                'slug' => 'lake-view-gulshan-1-road-8-23-katha-building',
                'tagline' => '২৩ কাঠা জমি ও ৬ তলা আধুনিক ভবন | মোট মূল্য ৳ ১৩৫ কোটি (আলোচনা সাপেক্ষ)',
                'description' => 'গুলশান ১, রোড ৮-এর ২৩ কাঠা জমিতে ৬ তলা সম্পূর্ণ আধুনিক ভবন বিক্রয় প্রস্তাব। নিষ্কণ্টক একক মালিকানা, কোনো ব্যাংক ঋণ নেই। প্রতিটি ফ্লোরে সুপরিসর স্পেস, মোট ২৮টি কার পার্কিং, আধুনিক লিফট, নিজস্ব সাব-স্টেশন ও জেনারেটর ব্যাকআপ। সম্পূর্ণ কাগজপত্র হালনাগাদ ও প্রস্তুত। করপোরেট অফিস, দূতাবাস, গেস্ট হাউস বা বিলাসবহুল অ্যাপার্টমেন্ট হিসেবে ব্যবহারের উপযোগী।',
                'address' => 'রোড ৮, গুলশান-১, ঢাকা-১২১২',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Gulshan-1',
                'price' => 1350000000,
                'price_unit' => 'মোট মূল্য ৳ ১৩৫ কোটি (আলোচনা সাপেক্ষ)',
                'listing_type' => 'Sale',
                'property_type' => 'Land',
                'status' => 'Active',
                'bedrooms' => 16,
                'bathrooms' => 18,
                'balconies' => 12,
                'square_footage' => 28000,
                'land_size' => 23.0,
                'land_unit' => 'Katha',
                'parking' => 28,
                'floor_number' => 1,
                'total_floors' => 6,
                'facing' => 'East',
                'completion_status' => 'Ready',
                'year_built' => 2018,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => false,
                'latitude' => 23.7782,
                'longitude' => 90.4173,
                'images' => ['/img/properties/lake-view-gulshan-1-23-katha-site.jpg'],
                'feature_image' => '/img/properties/lake-view-gulshan-1-23-katha-site.jpg',
                'gallery' => ['/img/properties/lake-view-gulshan-1-23-katha-site.jpg'],
                'amenities' => ['২৩ কাঠা জমি', '৬ তলা ভবন', '২৮টি কার পার্কিং', 'ইউটিলিটি সংযোগ হালনাগাদ', 'কোনো ব্যাংক ঋণ নেই'],
                'documents_verified' => ['মূল মালিকানা দলিল ও বায়া দলিল', 'রাজউক অনুমোদিত ৬ তলা ভবনের প্ল্যান', 'নামজারি ও ডি সি আর হালনাগাদ', 'অনলাইন ভূমি উন্নয়ন কর রশিদ'],
            ],
            [
                'id' => 5,
                'title' => 'ধউর, কামারপাড়া — ৮.৫ কাঠা জমিতে রাজউক প্রস্তাবিত B+G+9 ভবনের জমির শেয়ার',
                'slug' => 'dhaur-kamarpara-8-5-katha-b-g-9-land-share',
                'tagline' => '৮.৫ কাঠা জমি | রাজউক প্রস্তাবিত B+G+9 প্ল্যান পাস | ১২৫০ বর্গফুট ফ্ল্যাট | প্রতি শেয়ার ৳ ২২ লাখ',
                'description' => "সীমিত সংখ্যক জমির শেয়ার বিক্রয় হবে।\nজমির পরিমাণ : ৮.৫ কাঠা।\nরাজউক প্রস্তাবিত প্ল্যান পাস B+G+9 (বেজমেন্ট + গ্রাউন্ড + ৯ তলা)।\nমোট ফ্ল্যাটের সংখ্যা : ৩৬ টি।\nপ্রতি ফ্লোরে ফ্ল্যাটের সংখ্যা : ৪ টি।\nফ্ল্যাটের আয়তন : ১২৫০ বর্গফুট।\nপ্রতি ইউনিটে : ৩ টি বেড রুম, ৩ টি বাথরুম, ৩ টি বারান্দা, ড্রয়িং, ডাইনিং ও রান্নাঘর।\nলিফট : ১ টি।\nসিঁড়ি : ১টি।\nসাব - স্টেশন : ১ টি।\nবেজমেন্ট + গ্রাউন্ড ফ্লোর পার্কিং।\nপ্রতি ইউনিটে থাকবে ইন্টারনেট, ডিশ ও ইন্টারকম সহ সকল আধুনিক সুযোগ সুবিধা।\nছাদের উপর কমিউনিটি রুম বা অফিস ও বাচ্চাদের খেলাধুলার ওপেন স্পেস।\nনির্মাণ শেষে সিসি টিভি ক্যামেরা ও নিরাপত্তা কর্মী দ্বারা সার্বক্ষণিক নিরাপত্তার ব্যবস্থা রাখা হবে।\n\nজমির লোকেশন :\nধউর, গোলগোলার মোড়, ওয়ালটন প্লাজার রোডে। কামারপাড়া - দিয়াবাড়ী মেইন রোডের ১০০ ফুট ভিতরে ২১ ফুট প্রশস্ত পাকা রাস্তার পাশে জমির অবস্থান।",
                'address' => 'ধউর, গোলগোলার মোড়, ওয়ালটন প্লাজার রোড, কামারপাড়া, উত্তরা, ঢাকা',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Uttara / Kamarpara',
                'price' => 2200000,
                'price_unit' => 'প্রতি শেয়ার ৳ ২২ লাখ',
                'hide_price' => false,
                'listing_type' => 'Sale',
                'property_type' => 'Land Share',
                'status' => 'Active',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'balconies' => 3,
                'square_footage' => 1250,
                'land_size' => 8.5,
                'land_unit' => 'Katha',
                'parking' => 36,
                'floor_number' => 1,
                'total_floors' => 10,
                'facing' => 'East',
                'completion_status' => 'Upcoming',
                'year_built' => 2026,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 23.8967,
                'longitude' => 90.3752,
                'images' => [
                    '/img/properties/dhaur-kamarpara-8-5-katha-building.jpg',
                    '/img/properties/dhaur-kamarpara-8-5-katha-map.jpg',
                    '/img/properties/dhaur-kamarpara-1250-sqft-floorplan.jpg',
                ],
                'feature_image' => '/img/properties/dhaur-kamarpara-8-5-katha-building.jpg',
                'gallery' => [
                    '/img/properties/dhaur-kamarpara-8-5-katha-building.jpg',
                    '/img/properties/dhaur-kamarpara-8-5-katha-map.jpg',
                    '/img/properties/dhaur-kamarpara-1250-sqft-floorplan.jpg',
                ],
                'amenities' => [
                    '৮.৫ কাঠা জমির শেয়ার (মোট ৩৬টি শেয়ার)',
                    'রাজউক প্রস্তাবিত প্ল্যান পাস B+G+9 (বেজমেন্ট + গ্রাউন্ড + ৯ তলা)',
                    '১২৫০ বর্গফুটের আধুনিক ফ্ল্যাট (৩ বেড, ৩ বাথ, ৩ বারান্দা)',
                    'ড্রয়িং, ডাইনিং ও সুপরিসর রান্নাঘর',
                    'বেজমেন্ট ও গ্রাউন্ড ফ্লোর কার পার্কিং',
                    'আধুনিক হাই-স্পিড প্যাসেঞ্জার লিফট (১টি)',
                    'প্রশস্ত জরুরি অগ্নিনির্বাপক সিঁড়ি (১টি)',
                    'নিজস্ব বৈদ্যুতিক সাব-স্টেশন (১টি)',
                    'ইন্টারনেট, ডিশ ও ইন্টারকম সংযোগ সুবিধা',
                    'ছাদের উপর কমিউনিটি রুম / অফিস স্পেস',
                    'বাচ্চাদের খেলাধুলার জন্য রুফটপ ওপেন স্পেস',
                    'সিসিটিভি ক্যামেরা ও সার্বক্ষণিক নিরাপত্তা কর্মী',
                    '২১ ফুট প্রশস্ত পাকা রাস্তার সাথে সংযোগ',
                ],
                'documents_verified' => [
                    'জমির মূল মালিকানা দলিল ও বায়া দলিল (CS, SA, RS, BS)',
                    'হালনাগাদ নামজারি ও জমাভাগ খতিয়ান (Updated Mutation)',
                    'অনলাইন ভূমি উন্নয়ন কর ও খাজনা হালনাগাদ রশিদ',
                    'রাজউক প্রস্তাবিত প্ল্যান পাস অনুমোদন নথি (B+G+9 Proposed Plan)',
                    'জমির সীমানা চিহ্নিতকরণ ও লোকেশন লেআউট ম্যাপ',
                    'নিষ্কণ্টক মালিকানা প্রত্যয়ন (Clear Title Certificate)',
                ],
                'buyer_details' => [
                    'landUse' => 'Residential',
                    'roadWidth' => 21,
                    'priceBasis' => 'Per share',
                    'flatVariants' => [
                        [
                            'name' => 'Type A (১,২৫০ বর্গফুট)',
                            'sqft' => 1250,
                            'bedrooms' => 3,
                            'bathrooms' => 3,
                            'balconies' => 3,
                            'price' => 2200000,
                            'price_per_sqft' => 1760,
                            'floor_available' => '৩য়, ৫ম ও ৭ম তলা',
                            'facing' => 'East',
                            'floor_plan_image' => '/img/properties/dhaur-kamarpara-1250-sqft-floorplan.jpg',
                            'is_available' => true,
                        ],
                        [
                            'name' => 'Type B (১,৪৫০ বর্গফুট)',
                            'sqft' => 1450,
                            'bedrooms' => 3,
                            'bathrooms' => 3,
                            'balconies' => 3,
                            'price' => 2550000,
                            'price_per_sqft' => 1758,
                            'floor_available' => '৪র্থ ও ৬ষ্ঠ তলা',
                            'facing' => 'South-East',
                            'floor_plan_image' => '/img/properties/dhaur-kamarpara-1250-sqft-floorplan.jpg',
                            'is_available' => true,
                        ],
                        [
                            'name' => 'Type C (১,৬৫০ বর্গফুট)',
                            'sqft' => 1650,
                            'bedrooms' => 4,
                            'bathrooms' => 4,
                            'balconies' => 3,
                            'price' => 2900000,
                            'price_per_sqft' => 1757,
                            'floor_available' => '৮ম তলা',
                            'facing' => 'South',
                            'floor_plan_image' => '/img/properties/dhaur-kamarpara-1250-sqft-floorplan.jpg',
                            'is_available' => true,
                        ],
                        [
                            'name' => 'Type D (১,৮৫০ বর্গফুট)',
                            'sqft' => 1850,
                            'bedrooms' => 4,
                            'bathrooms' => 4,
                            'balconies' => 4,
                            'price' => 3250000,
                            'price_per_sqft' => 1756,
                            'floor_available' => '৯ম তলা (টপ ফ্লোর)',
                            'facing' => 'North-East',
                            'floor_plan_image' => '/img/properties/dhaur-kamarpara-1250-sqft-floorplan.jpg',
                            'is_available' => true,
                        ],
                    ],
                ],
            ],
            [
                'id' => 6,
                'title' => 'উত্তরা সেক্টর ১২, রোড ১৮ — ৩ কাঠা জমিতে ১৮২০ বর্গফুটের এক্সক্লুসিভ রেডি ফ্ল্যাট',
                'slug' => 'uttara-sector-12-road-18-1820-sqft-flat',
                'tagline' => '৩ কাঠা জমিতে নির্মিত সুদৃশ্য ভবনে ১৮২০ বর্গফুটের রেডি ফ্ল্যাট | ৪র্থ তলা | উত্তরা সেক্টর ১২',
                'description' => "উত্তরা সেক্টর ১২, রোড ১৮, বাড়ি ০৩-এ ৩ কাঠা জমিতে নির্মিত সুদৃশ্য আবাসিক ভবনের ৪র্থ তলায় ১৮২০ বর্গফুটের একটি এক্সক্লুসিভ রেডি ফ্ল্যাট বিক্রয়ের প্রস্তাব। মোট ইউনিট ৬টি, যার মধ্যে একটি মাত্র প্রিমিয়াম ইউনিট বিক্রয়ের জন্য উপলব্ধ।\n\nফ্ল্যাটের বিবরণ:\n* আয়তন: ১৮২০ বর্গফুট\n* অবস্থান: ৪র্থ তলা (উত্তরমুখী / North Facing)\n* বেডরুম ও বাথরুম: ২ টি প্রশস্ত বেডরুম ও ৩ টি আধুনিক বাথরুম\n* বারান্দা: ১ টি খোলামেলা বারান্দা\n* ড্রয়িং ও ডাইনিং: আলাদা সুপরিসর ড্রয়িং ও ডাইনিং স্পেস\n* রান্নাঘর: ১ টি আধুনিক কিচেন\n* পার্কিং: ১টি ডেডিকেটেড সুরক্ষিত কার পার্কিং\n* ভবন সুবিধা: আধুনিক প্যাসেঞ্জার লিফট, ফুল জেনারেটর ব্যাকআপ, ইন্টারকম ও সার্বক্ষণিক সিসিটিভি সিকিউরিটি ব্যবস্থা।\n* বর্তমান অবস্থা: সম্পূর্ণ রেডি ফ্ল্যাট, অবিলম্বে ব্যবহার ও বসবাস উপযোগী। ব্যাংক লোন সুবিধা উপলব্ধ।\n\nআশপাশের সুযোগ-সুবিধা ও পরিবেশ:\n* পার্ক ও প্রকৃতি: ১২ নম্বর সেক্টর পার্ক (৩-৫ মিনিট হাঁটা পথ), লেকসাইড ওয়াকওয়ে।\n* যোগাযোগ: উত্তরা নর্থ মেট্রো রেল স্টেশন (৫-৭ মিনিট)।\n* শিক্ষা প্রতিষ্ঠান: রাজউক উত্তরা মডেল কলেজ, মাইলস্টোন কলেজ, স্কলাস্টিকা, মাস্টারমাইন্ড ও IUBAT বিশ্ববিদ্যালয়।\n* হাসপাতাল: আহছানিয়া মিশন ক্যান্সার হাসপাতাল, লুবানা জেনারেল হাসপাতাল, পপুলার ডায়াগনস্টিক সেন্টার ও কুয়েত-বাংলাদেশ মৈত্রী হাসপাতাল।\n* বাজার ও সুপারশপ: সেক্টর ১২ সোসাইটি বাজার, স্বপ্ন, মীনা বাজার ও ডেলি শপিং।",
                'address' => 'বাড়ি ০৩, রোড ১৮, সেক্টর ১২, উত্তরা, ঢাকা-১২৩০',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Uttara Sector 12',
                'price' => 0,
                'price_unit' => 'মূল্য আলোচনা সাপেক্ষ',
                'hide_price' => true,
                'price_display_text' => 'মূল্য আলোচনা সাপেক্ষ',
                'listing_type' => 'Sale',
                'property_type' => 'Flat',
                'status' => 'Active',
                'bedrooms' => 2,
                'bathrooms' => 3,
                'balconies' => 1,
                'square_footage' => 1820,
                'land_size' => 3.0,
                'land_unit' => 'Katha',
                'parking' => 1,
                'floor_number' => 4,
                'total_floors' => 7,
                'facing' => 'North',
                'completion_status' => 'Ready',
                'year_built' => 2024,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 23.8805,
                'longitude' => 90.3860,
                'images' => [
                    '/img/properties/uttara-sector-12-living-hall.jpg',
                    '/img/properties/uttara-sector-12-bedroom-1.jpg',
                    '/img/properties/uttara-sector-12-kitchen.jpg',
                    '/img/properties/uttara-sector-12-bedroom-2.jpg',
                    '/img/properties/uttara-sector-12-corridor.jpg',
                ],
                'feature_image' => '/img/properties/uttara-sector-12-living-hall.jpg',
                'gallery' => [
                    '/img/properties/uttara-sector-12-bedroom-1.jpg',
                    '/img/properties/uttara-sector-12-kitchen.jpg',
                    '/img/properties/uttara-sector-12-bedroom-2.jpg',
                    '/img/properties/uttara-sector-12-corridor.jpg',
                ],
                'amenities' => [
                    '১৮২০ বর্গফুটের খোলামেলা রেডি ফ্ল্যাট (৪র্থ তলা)',
                    '৩ কাঠা জমির ওপর নির্মিত একক ইউনিট বিশিষ্ট ভবন',
                    '২টি প্রশস্ত বেডরুম ও ৩টি আধুনিক বাথরুম',
                    'আলাদা ড্রয়িং ও ডাইনিং স্পেস এবং ১টি খোলামেলা বারান্দা',
                    'আধুনিক প্যাসেঞ্জার লিফট সুবিধা',
                    'সার্বক্ষণিক স্ট্যান্ডবাই জেনারেটর ব্যাকআপ',
                    '১টি ডেডিকেটেড সুরক্ষিত কার পার্কিং',
                    'সিসিটিভি ক্যামেরা ও সার্বক্ষণিক নিরাপত্তা প্রহরী',
                    'উত্তরা নর্থ মেট্রোরেল স্টেশন (৫-৭ মিনিট দূরত্ব)',
                    '১২ নম্বর সেক্টর পার্ক ও লেকসাইড ওয়াকওয়ে (৩-৫ মিনিট)',
                    'ব্যাংক লোন সুবিধা উপলব্ধ',
                    'রাজউক উত্তরা মডেল কলেজ, মাইলস্টোন ও স্কলাস্টিকা সন্নিকটে',
                    'আহছানিয়া মিশন, লুবানা ও কুয়েত-মৈত্রী হাসপাতাল সন্নিকটে',
                ],
                'documents_verified' => [
                    'জমির মূল মালিকানা দলিল ও বায়া দলিল (Title Deed)',
                    'রাজউক অনুমোদিত ভবনের স্থাপত্য নকশা ও প্ল্যান',
                    'হালনাগাদ নামজারি ও জমাভাগ খতিয়ান (Updated Mutation)',
                    'অনলাইন ভূমি উন্নয়ন কর ও খাজনা হালনাগাদ রশিদ',
                    'ভবনের অকুপেন্সি ও ইউটিলিটি অনুমোদন পত্র',
                    'নিষ্কণ্টক মালিকানা সনদ (Clear Title Verification)',
                ],
                'buyer_details' => [
                    'landUse' => 'Residential',
                    'cornerPlot' => 'No',
                    'roadWidth' => 25,
                    'buildingDescription' => '৩ কাঠা জমির উপর নির্মিত জি+৬ ভবনের ৪র্থ তলায় ১৮২০ বর্গফুটের একক ইউনিট রেডি ফ্ল্যাট। ভবনে মোট ৬টি ইউনিট।',
                    'utilities' => 'বিদ্যুৎ সংযোগ, সার্বক্ষণিক পানি সরবরাহ ও স্ট্যান্ডবাই জেনারেটর ব্যাকআপ রয়েছে।',
                    'priceBasis' => 'Total',
                    'negotiable' => 'Yes',
                    'priceIncludes' => '১৮২০ বর্গফুটের ফ্ল্যাট, ১টি কার পার্কিং এবং লিফট ও জেনারেটরের আনুপাতিক শেয়ারসহ মোট মূল্য।',
                    'depositPercent' => 20,
                    'agreementDuration' => 'পারস্পরিক চুক্তি সাপেক্ষে',
                    'paymentSchedule' => 'আলোচনা সাপেক্ষে বায়না ও হস্তান্তরকালে পরিশোধযোগ্য।',
                    'paymentMethod' => 'ব্যাংক পে-অর্ডার / একাউন্ট ট্রান্সফার',
                    'registrationCost' => 'সরকারি নিয়ম অনুযায়ী প্রযোজ্য রেজিস্ট্রেশন ফি ক্রেতা বহন করবেন।',
                    'transferTimeline' => 'রেডি ফ্ল্যাট, চুক্তি ও পেমেন্ট সম্পন্ন সাপেক্ষে অবিলম্বে হস্তান্তরযোগ্য।',
                    'ownershipSource' => 'Purchase',
                    'possession' => 'Owner',
                    'bankLoan' => 'None declared',
                    'existingAgreement' => 'None declared',
                    'saleAuthority' => 'মালিক / অনুমোদিত প্রতিনিধির মাধ্যমে',
                    'ownershipNotes' => 'সম্পূর্ণ নিষ্কণ্টক, একক মালিকানাধীন ও বৈধ কাগজপত্র সম্পন্ন।',
                    'mutationStatus' => 'Available',
                    'taxPaidThrough' => 'হালনাগাদ পরিশোধিত',
                    'approvalDetails' => 'রাজউক অনুমোদিত আবাসিক ভবন ও প্ল্যান পাস।',
                    'documentSummary' => 'নিষ্কণ্টক ও প্রস্তুত কাগজপত্র। ব্যাংক ঋণ নেওয়ার উপযোগী।',
                    'sourceDate' => '2026-10-18',
                    'updatedOn' => '2026-10-18',
                ],
            ],
        ];
    }

    public static function findFallback(string|int $idOrSlug): ?array
    {
        $all = self::getFallbackPayload();
        foreach ($all as $item) {
            if ((string) $item['id'] === (string) $idOrSlug || $item['slug'] === (string) $idOrSlug) {
                return $item;
            }
        }

        return null;
    }
}
