<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed default site settings and ensure company profile information is up to date.
     */
    public function run(): void
    {
        $companySettings = [
            'site_name' => 'গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড',
            'site_title' => 'Gram Bangla Real Estate Limited | গ্রাম বাংলা রিয়েল এস্টেট — GBREL',
            'contact_email' => 'grambanglarealestateltd@gmail.com',
            'contact_phone' => '+8809658510748',
            'whatsapp_number' => '+8809658510748',
            'office_address' => SiteSettings::OFFICE_ADDRESS,
            'corporate_office_address' => SiteSettings::CORPORATE_OFFICE_ADDRESS,
            'map_location' => 'House 461, Road 7, Avenue 6, Mirpur DOHS, Dhaka 1216',
            'working_hours' => 'শনি–বৃহস্পতি, সকাল ৯টা–সন্ধ্যা ৬টা',
            'company_registration_no' => 'C-185751/2022',
            'company_registration_date' => '2022-12-11',
            'trade_license_no' => 'TRAD/DSCC/026884/2022',
            'trade_license_valid_until' => '2027-06-30',
            'property_cta_label' => 'ক্রয় তথ্য ও সাইট ভিজিট',
            'property_cta_label_hidden_price' => 'সর্বশেষ দাম ও সাইট ভিজিট',
            'property_cta_note' => 'কাগজপত্রের তথ্য, মোট খরচের হিসাব আর সাইট ভিজিটের সময়, সব জানাবে GBREL টিম। কোনো অগ্রিম ফি নেই।',
        ];

        foreach ($companySettings as $key => $val) {
            Setting::setVal($key, $val);
        }

        foreach (SiteSettings::definitions() as $key => $def) {
            if (! Setting::where('key', $key)->exists() && $def['default'] !== '') {
                Setting::setVal($key, $def['default']);
            }
        }
    }
}
