<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Every setting the admin panel can edit, with its default. Public settings are returned by GET /api/settings.
 */
class SiteSettings
{
    /** Registered office, as printed on the DSCC trade licence. */
    public const OFFICE_ADDRESS = 'স্বজন টাওয়ার-১, রুম ২০১ (২য় তলা), ৪ সেগুন বাগিচা, ঢাকা-১০০০';

    /**
     * @return array<string, array{default: mixed, rules: list<mixed>}>
     */
    public static function definitions(): array
    {
        return [
            'site_name' => ['default' => 'গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড', 'rules' => ['nullable', 'string', 'max:150']],
            'site_title' => ['default' => 'গ্রাম বাংলা রিয়েল এস্টেট | জমি, প্লট ও ফ্ল্যাট — GBREL', 'rules' => ['nullable', 'string', 'max:200']],
            'contact_phone' => ['default' => '', 'rules' => ['nullable', 'string', 'max:40']],
            'whatsapp_number' => ['default' => '', 'rules' => ['nullable', 'string', 'max:40']],
            'contact_email' => ['default' => '', 'rules' => ['nullable', 'email', 'max:150']],
            'office_address' => ['default' => self::OFFICE_ADDRESS, 'rules' => ['nullable', 'string', 'max:300']],
            'map_location' => ['default' => 'Swajan Tower, 4 Segun Bagicha, Dhaka 1000', 'rules' => ['nullable', 'string', 'max:200']],
            'company_registration_no' => ['default' => 'C-185751/2022', 'rules' => ['nullable', 'string', 'max:60']],
            'company_registration_date' => ['default' => '2022-12-11', 'rules' => ['nullable', 'date_format:Y-m-d']],
            'trade_license_no' => ['default' => 'TRAD/DSCC/026884/2022', 'rules' => ['nullable', 'string', 'max:60']],
            'trade_license_valid_until' => ['default' => '2027-06-30', 'rules' => ['nullable', 'date_format:Y-m-d']],
            'working_hours' => ['default' => '', 'rules' => ['nullable', 'string', 'max:150']],
            // Tracking: loaded only on public pages. Empty = off.
            'meta_pixel_id' => ['default' => '', 'rules' => ['nullable', 'string', 'regex:/^\d{10,20}\z/']],
            'gtm_container_id' => ['default' => '', 'rules' => ['nullable', 'string', 'regex:/^GTM-[A-Z0-9]{4,12}\z/']],
            'ga4_measurement_id' => ['default' => '', 'rules' => ['nullable', 'string', 'regex:/^G-[A-Z0-9]{4,15}\z/']],
            'home_headline' => ['default' => 'জমি দেখে, কাগজ বুঝে, তারপর কিনুন।', 'rules' => ['nullable', 'string', 'max:120']],
            'home_subtitle' => ['default' => 'প্লট, জমি শেয়ার আর ফ্ল্যাটের তালিকা — প্রতিটির দাম, আয়তন, লোকেশন ও কাগজপত্রের তথ্য এক জায়গায়। পছন্দ হলে আমাদের টিমের সঙ্গে সরাসরি কথা বলুন।', 'rules' => ['nullable', 'string', 'max:400']],
            'property_cta_label' => ['default' => 'ক্রয় তথ্য ও সাইট ভিজিট', 'rules' => ['nullable', 'string', 'min:2', 'max:50']],
            'property_cta_label_hidden_price' => ['default' => 'সর্বশেষ দাম ও সাইট ভিজিট', 'rules' => ['nullable', 'string', 'min:2', 'max:50']],
            'property_cta_note' => ['default' => 'কাগজপত্রের তথ্য, মোট খরচের হিসাব আর সাইট ভিজিটের সময়, সব জানাবে GBREL টিম। কোনো অগ্রিম ফি নেই।', 'rules' => ['nullable', 'string', 'max:250']],
            'owner_commission_percent' => ['default' => 2, 'rules' => ['nullable', 'numeric', 'min:0', 'max:20']],
            'owner_terms' => ['default' => self::defaultOwnerTerms(), 'rules' => ['nullable', 'string', 'max:6000']],
            'listing_document_types' => ['default' => self::defaultDocumentTypes(), 'rules' => ['nullable', 'array', 'min:1', 'max:40']],
            // Older keys kept so existing pages keep working.
            'commission_rate' => ['default' => '2.0%', 'rules' => ['nullable', 'string', 'max:20']],
            'dbh_rate' => ['default' => '', 'rules' => ['nullable', 'string', 'max:20']],
            'idlc_rate' => ['default' => '', 'rules' => ['nullable', 'string', 'max:20']],
            'brac_rate' => ['default' => '', 'rules' => ['nullable', 'string', 'max:20']],
        ];
    }

    /** Cache key for the stored settings rows; cleared whenever a Setting is saved or deleted. */
    public const CACHE_KEY = 'site_settings.rows';

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $definitions = self::definitions();
        // One query for every key, cached briefly: GET /api/settings is called on every public page load.
        $stored = Cache::remember(self::CACHE_KEY, 60, fn () => Setting::query()
            ->whereIn('key', array_keys($definitions))
            ->pluck('value', 'key')
            ->all());

        $values = [];
        foreach ($definitions as $key => $definition) {
            $raw = $stored[$key] ?? null;
            // Text settings are read as stored, so numeric-looking IDs (Meta Pixel) stay exact strings.
            if (is_string($definition['default'])) {
                $value = $raw ?? $definition['default'];
            } elseif ($raw === null) {
                $value = $definition['default'];
            } else {
                $decoded = json_decode($raw, true);
                $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
            }
            $values[$key] = ($value === null || $value === '') && $definition['default'] !== '' ? $definition['default'] : $value;
        }
        $values['owner_terms_version'] = substr(sha1($values['owner_terms'].'|'.$values['owner_commission_percent']), 0, 12);

        return $values;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function validationRules(): array
    {
        $rules = [];
        foreach (self::definitions() as $key => $definition) {
            $rules[$key] = array_merge(['sometimes'], $definition['rules']);
        }
        $rules['listing_document_types.*.key'] = ['required', 'string', 'max:40', 'regex:/^[a-z0-9_]+$/', 'distinct'];
        $rules['listing_document_types.*.label'] = ['required', 'string', 'max:120'];
        $rules['listing_document_types.*.hint'] = ['nullable', 'string', 'max:300'];
        $rules['listing_document_types.*.required'] = ['boolean'];

        return $rules;
    }

    /**
     * @return list<array{key: string, label: string, hint: string, required: bool}>
     */
    public static function documentTypes(): array
    {
        $types = self::all()['listing_document_types'];

        return is_array($types) && $types !== [] ? array_values($types) : self::defaultDocumentTypes();
    }

    public static function defaultOwnerTerms(): string
    {
        return implode("\n", [
            'প্রপার্টিটি বিক্রির জন্য GBREL-কে একমাত্র বিপণন ও বিক্রয় প্রতিনিধি হিসেবে নিয়োগ দিচ্ছি।',
            'ক্রেতা খোঁজা, যোগাযোগ, দরদাম ও সাইট ভিজিট GBREL করবে। আমি নিজে ক্রেতার সঙ্গে যোগাযোগ বা লেনদেন করব না।',
            'বিক্রি সম্পন্ন হলে বিক্রয়মূল্যের {commission}% GBREL-এর সার্ভিস চার্জ হিসেবে রেখে বাকি টাকা আমাকে পরিশোধ করা হবে।',
            'আমার দেওয়া সব তথ্য ও কাগজপত্র সত্য। ভুল বা গোপন তথ্যের দায় আমার।',
            'কাগজপত্র যাচাই ও লিখিত চুক্তি সম্পন্ন হওয়ার আগে প্রপার্টি ওয়েবসাইটে প্রকাশ করা হবে না।',
        ]);
    }

    /**
     * @return list<array{key: string, label: string, hint: string, required: bool}>
     */
    public static function defaultDocumentTypes(): array
    {
        return [
            ['key' => 'deed', 'label' => 'মূল দলিল', 'hint' => 'সাফ কবলা, হেবা বা বণ্টননামা — যে দলিলে বর্তমান মালিক হয়েছেন', 'required' => true],
            ['key' => 'bia_deed', 'label' => 'বায়া দলিল', 'hint' => 'আগের মালিকদের দলিল', 'required' => false],
            ['key' => 'khatian', 'label' => 'খতিয়ান (সিএস, এসএ, আরএস, বিএস)', 'hint' => 'যতগুলো আছে', 'required' => true],
            ['key' => 'namjari', 'label' => 'নামজারি খতিয়ান ও ডিসিআর', 'hint' => '', 'required' => true],
            ['key' => 'khajna', 'label' => 'হালনাগাদ খাজনার দাখিলা', 'hint' => '', 'required' => true],
            ['key' => 'owner_nid', 'label' => 'সব মালিকের জাতীয় পরিচয়পত্র', 'hint' => '', 'required' => true],
            ['key' => 'warish', 'label' => 'ওয়ারিশ সনদ', 'hint' => 'উত্তরাধিকার সূত্রে মালিক হলে', 'required' => false],
            ['key' => 'poa', 'label' => 'আমমোক্তারনামা (পাওয়ার অব অ্যাটর্নি)', 'hint' => 'মালিকের প্রতিনিধি হিসেবে জমা দিলে', 'required' => false],
            ['key' => 'mouza_map', 'label' => 'মৌজা ম্যাপ বা প্লটের নকশা', 'hint' => '', 'required' => false],
            ['key' => 'plan_approval', 'label' => 'রাজউক/সিডিএ অনুমোদিত নকশা বা বরাদ্দপত্র', 'hint' => 'ভবন বা বরাদ্দকৃত প্লট হলে', 'required' => false],
            ['key' => 'utility_bill', 'label' => 'ইউটিলিটি বিল', 'hint' => 'বিদ্যুৎ, গ্যাস বা পানির সাম্প্রতিক বিল', 'required' => false],
            ['key' => 'other', 'label' => 'অন্যান্য কাগজ', 'hint' => '', 'required' => false],
        ];
    }
}
