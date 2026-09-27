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
}
