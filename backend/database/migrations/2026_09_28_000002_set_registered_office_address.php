<?php

use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fill in the registered office address (from the trade licence) if staff have not entered one yet.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings') && trim((string) Setting::getVal('office_address', '')) === '') {
            Setting::setVal('office_address', SiteSettings::OFFICE_ADDRESS);
        }
    }

    public function down(): void
    {
        //
    }
};
