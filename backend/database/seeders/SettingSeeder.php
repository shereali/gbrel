<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed default site settings.
     * Only inserts missing settings; leaves existing configuration untouched.
     */
    public function run(): void
    {
        foreach (SiteSettings::definitions() as $key => $def) {
            if (! Setting::where('key', $key)->exists() && $def['default'] !== '') {
                Setting::setVal($key, $def['default']);
            }
        }
    }
}
