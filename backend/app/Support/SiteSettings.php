<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Every setting the admin panel can edit, with its default. Public settings are returned by GET /api/settings.
 */
class SiteSettings
{
    /**
     * @return array<string, array{default: mixed, rules: list<mixed>}>
     */
    public static function definitions(): array
    {
        return [
            'site_name' => ['default' => 'গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড', 'rules' => ['string', 'max:150']],
            'site_title' => ['default' => 'গ্রাম বাংলা রিয়েল এস্টেট | জমি, প্লট ও ফ্ল্যাট — GBREL', 'rules' => ['string', 'max:200']],
            'contact_phone' => ['default' => '', 'rules' => ['string', 'max:40']],
            'whatsapp_number' => ['default' => '', 'rules' => ['string', 'max:40']],
            'contact_email' => ['default' => '', 'rules' => ['nullable', 'email', 'max:150']],
            'office_address' => ['default' => '', 'rules' => ['string', 'max:300']],
            'working_hours' => ['default' => '', 'rules' => ['string', 'max:150']],
            'home_headline' => ['default' => 'জমি দেখে, কাগজ বুঝে, তারপর কিনুন।', 'rules' => ['string', 'max:120']],
            'home_subtitle' => ['default' => 'প্লট, জমি শেয়ার আর ফ্ল্যাটের তালিকা — প্রতিটির দাম, আয়তন, লোকেশন ও কাগজপত্রের তথ্য এক জায়গায়। পছন্দ হলে আমাদের টিমের সঙ্গে সরাসরি কথা বলুন।', 'rules' => ['string', 'max:400']],
            'owner_commission_percent' => ['default' => 2, 'rules' => ['numeric', 'min:0', 'max:20']],
            'owner_terms' => ['default' => self::defaultOwnerTerms(), 'rules' => ['string', 'max:6000']],
            'listing_document_types' => ['default' => self::defaultDocumentTypes(), 'rules' => ['array', 'min:1', 'max:40']],
            // Older keys kept so existing pages keep working.
            'commission_rate' => ['default' => '2.0%', 'rules' => ['string', 'max:20']],
            'dbh_rate' => ['default' => '', 'rules' => ['nullable', 'string', 'max:20']],
            'idlc_rate' => ['default' => '', 'rules' => ['nullable', 'string', 'max:20']],
            'brac_rate' => ['default' => '', 'rules' => ['nullable', 'string', 'max:20']],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $values = [];
        foreach (self::definitions() as $key => $definition) {
            $value = Setting::getVal($key, $definition['default']);
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
